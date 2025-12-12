<?php

/**
 * Thrive Plugin Updater class
 * Handles automatic updates from GitHub repository
 */
class Thrive_Updater
{
    private $github_username;
    private $github_repo;
    private $github_api_url = 'https://api.github.com/repos/';
    private $current_version;
    private $plugin_basename;
    private $plugin_slug;
    private $main_file;
    private static $github_token_singleton = null;

    public function __construct()
    {
        $options = get_option('thrive_login_settings', []);
        $this->github_username = $options['github_username'] ?? 'glossyit';
        $this->github_repo = $options['github_repo'] ?? 'LoginWithThrives_plugin';
        $this->current_version = defined('THRIVE_LOGIN_VERSION') ? THRIVE_LOGIN_VERSION : '1.0.0';

        // Define plugin basename (path relative to plugins dir)
        $this->plugin_basename = plugin_basename(THRIVE_LOGIN_PLUGIN_DIR . 'thrive-login.php');

        // Plugin slug is the folder name of plugin (e.g. thrive-login-plugin)
        $this->plugin_slug = dirname($this->plugin_basename);

        // Main plugin file name inside plugin folder (e.g. thrive-login.php)
        $this->main_file = basename(THRIVE_LOGIN_PLUGIN_DIR . 'thrive-login.php');

        $this->init();
    }

    public function init()
    {
//        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);

        if (!wp_next_scheduled('thrive_check_for_updates')) {
            wp_schedule_event(time(), 'daily', 'thrive_check_for_updates');
        }

        add_action('thrive_check_for_updates', [$this, 'trigger_update_check']);
        add_action('admin_init', [$this, 'add_updater_settings']);
    }

    public function add_updater_settings()
    {
        add_settings_field(
            'github_username',
            __('GitHub Username', 'thrive-login'),
            [$this, 'github_username_callback'],
            'thrive-login',
            'thrive_login_settings_section'
        );

        add_settings_field(
            'github_repo',
            __('GitHub Repository', 'thrive-login'),
            [$this, 'github_repo_callback'],
            'thrive-login',
            'thrive_login_settings_section'
        );

        add_settings_field(
            'github_token',
            __('GitHub Token', 'thrive-login'),
            array($this, 'github_token_callback'),
            'thrive-login',
            'thrive_login_settings_section'
        );

        add_settings_field(
            'auto_update',
            __('Enable Auto Updates', 'thrive-login'),
            [$this, 'auto_update_callback'],
            'thrive-login',
            'thrive_login_settings_section'
        );

    }

    public function github_username_callback()
    {
        $options = get_option('thrive_login_settings');
        $val = esc_attr($options['github_username'] ?? '');
        echo "<input type='text' name='thrive_login_settings[github_username]' value='{$val}' class='regular-text'>";
        echo "<p class='description'>" . __('GitHub username that owns the plugin repo.', 'thrive-login') . "</p>";
    }

    public function github_repo_callback()
    {
        $options = get_option('thrive_login_settings');
        $val = esc_attr($options['github_repo'] ?? '');
        echo "<input type='text' name='thrive_login_settings[github_repo]' value='{$val}' class='regular-text'>";
        echo "<p class='description'>" . __('Repository name of the plugin.', 'thrive-login') . "</p>";
    }

    public function github_token_callback()
    {
        $options = get_option('thrive_login_settings');
        $github_token = isset($options['github_token']) ? $options['github_token'] : '';

        echo '<input type="password" id="github_token" name="thrive_login_settings[github_token]" value="' . esc_attr($github_token) . '" class="regular-text">';
        echo '<p class="description">' . __('Enter a GitHub token with repo access to increase API rate limits and access private repositories', 'thrive-login') . '</p>';
    }

    public function auto_update_callback()
    {
        $options = get_option('thrive_login_settings');
        $checked = checked(1, $options['auto_update'] ?? 0, false);
        echo "<input type='checkbox' name='thrive_login_settings[auto_update]' value='1' {$checked}>";
        echo "<label>" . __('Automatically update when new release available.', 'thrive-login') . "</label>";
    }

    public function trigger_update_check()
    {
        $options = get_option('thrive_login_settings');
        if (!empty($options['auto_update'])) {
            delete_site_transient('update_plugins');
            $latest = $this->get_latest_release();

            if ($latest && version_compare($this->current_version, $latest['version'], '<')) {
                $this->perform_auto_update($latest['download_url']);
            }
        }
    }

    public function check_for_update($transient)
    {
        if (empty($transient->checked)) {
            return $transient;
        }

        $latest = $this->get_latest_release();

        if ($latest && version_compare($this->current_version, $latest['version'], '<')) {
            $plugin = new stdClass();
            $plugin->slug = $this->plugin_slug;
            $plugin->new_version = $latest['version'];
            $plugin->url = $latest['url'];
            $plugin->package = $latest['download_url'];
            $transient->response[$this->plugin_basename] = $plugin;
        }

        return $transient;
    }

    public function plugin_info($result, $action, $args)
    {
        if ($action !== 'plugin_information' || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $latest = $this->get_latest_release();
        if (!$latest) return $result;

        $info = new stdClass();
        $info->name = 'Login with Thrive';
        $info->slug = $this->plugin_slug;
        $info->version = $latest['version'];
        $info->author = 'Thrive';
        $info->author_profile = 'https://thrive.com';
        $info->requires = '5.0';
        $info->tested = '6.5';
        $info->last_updated = $latest['published_at'];
        $info->sections = [
            'description' => 'Login to WordPress using Thrive credentials.',
            'changelog' => $latest['changelog'],
        ];
        $info->download_link = $latest['download_url'];

        return $info;
    }

    private function get_latest_release()
    {
        try {
            $url = "{$this->github_api_url}{$this->github_username}/{$this->github_repo}/releases/latest";

            $response = wp_remote_get($url, [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                    'Authorization' => 'token ' . $this->get_github_token_from_api(),
                ]
            ]);

            if (is_wp_error($response)) {
                throw new Exception('[GitHub API Error] Connection failed: ' . $response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                throw new Exception("[GitHub API Error] Unexpected response code: {$response_code}");
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('[GitHub API Error] JSON decoding failed: ' . json_last_error_msg());
            }

            if (empty($data['tag_name']) || empty($data['zipball_url'])) {
                throw new Exception('[GitHub API Error] Missing required release fields: tag_name or zipball_url.');
            }

            return [
                'version' => ltrim($data['tag_name'], 'v'),
                'url' => $data['html_url'] ?? '',
                'download_url' => $data['zipball_url'],
                'published_at' => !empty($data['published_at']) ? date('Y-m-d', strtotime($data['published_at'])) : '',
                'changelog' => $data['body'] ?? '',
            ];

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }


    private function perform_auto_update($download_url)
    {
        try {
            include_once ABSPATH . 'wp-admin/includes/file.php';
            include_once ABSPATH . 'wp-admin/includes/misc.php';
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            include_once ABSPATH . 'wp-admin/includes/plugin.php';

            // Initialize WP Filesystem
            if (!WP_Filesystem()) {
                throw new Exception('Could not initialize WP_Filesystem.');
            }

            global $wp_filesystem;

            $plugin_dir = WP_PLUGIN_DIR . '/' . $this->plugin_slug;

            // Download ZIP
            $tmp_file = $this->thrive_download_zip($download_url);
            if (!$tmp_file || !file_exists($tmp_file)) {
                throw new Exception('Download failed or ZIP file not found: ' . print_r($tmp_file, true));
            }

            error_log('Thrive auto-update downloaded successfully: ' . $tmp_file);

            // Unzip to plugin directory
            $unzip_result = unzip_file($tmp_file, $plugin_dir);
            @unlink($tmp_file); // Clean up ZIP regardless of success

            if (is_wp_error($unzip_result)) {
                throw new Exception('Unzipping failed: ' . $unzip_result->get_error_message());
            }

            // Find extracted directory
            $extracted_dirs = glob($plugin_dir . '/glossyit-LoginWithThrives_plugin-*', GLOB_ONLYDIR);
            if (empty($extracted_dirs)) {
                throw new Exception('No extracted subdirectory found matching "glossyit-LoginWithThrives_plugin-*"');
            }

            $target_path = $extracted_dirs[0];
            $files = $wp_filesystem->dirlist($target_path);

            if (!$files || !is_array($files)) {
                throw new Exception('Failed to list files in extracted directory.');
            }

            // Move files
            foreach ($files as $file => $details) {
                $from = trailingslashit($target_path) . $file;
                $to = trailingslashit($plugin_dir) . $file;

                if (!$wp_filesystem->move($from, $to, true)) {
                    throw new Exception("Failed to move file from $from to $to");
                }
            }

            // Clean up extracted subdirectory
            $wp_filesystem->rmdir($target_path, true);

            // Reactivate plugin
            activate_plugin($this->plugin_slug . '/' . $this->main_file);

            error_log('Thrive auto-update completed and plugin reactivated.');

        } catch (Exception $e) {
            error_log('[Thrive Auto-Update Error] ' . $e->getMessage());
        }
    }


    private function thrive_download_zip($zip_url)
    {
        try {
            // Validate URL
            if (empty($zip_url) || !filter_var($zip_url, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid ZIP URL provided.');
            }

            $plugin_dir = WP_PLUGIN_DIR;
            $file_name = 'Thrive-login-plugin.zip';
            $tmp_file = trailingslashit($plugin_dir) . $file_name;

            $response = wp_remote_get($zip_url, [
                'headers' => [
                    'Accept' => 'application/vnd.github.v3+json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                    'Authorization' => 'token ' . $this->get_github_token_from_api(),
                ]
            ]);

            if (is_wp_error($response)) {
                throw new Exception('Download failed: ' . $response->get_error_message());
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                throw new Exception("Unexpected response code: {$response_code}");
            }

            $body = wp_remote_retrieve_body($response);

            if (!$body) {
                throw new Exception('Empty body received from download.');
            }

            if (file_put_contents($tmp_file, $body) === false) {
                throw new Exception("Failed to save ZIP file to: {$tmp_file}");
            }

            return $tmp_file; // Absolute path to saved zip

        } catch (Exception $e) {
            error_log('[Thrive Download Error] ' . $e->getMessage());
            return false;
        }
    }

    private function get_github_token_from_api()
    {
        // If already fetched in this request, return it
        if (self::$github_token_singleton !== null) {
            return self::$github_token_singleton;
        }

        try {
            $api_url = 'https://accounts.thrivewebdesigns.com/api/github/token';

            $response = wp_remote_get($api_url, [
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                    'User-Agent' => 'WordPress/' . get_bloginfo('version'),
                ]
            ]);

            if (is_wp_error($response)) {
                error_log('[GitHub Token API Error] Connection failed: ' . $response->get_error_message());
                self::$github_token_singleton = '';
                return '';
            }

            $response_code = wp_remote_retrieve_response_code($response);
            if ($response_code !== 200) {
                error_log("[GitHub Token API Error] Unexpected response code: {$response_code}");
                self::$github_token_singleton = '';
                return '';
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log('[GitHub Token API Error] JSON decoding failed: ' . json_last_error_msg());
                self::$github_token_singleton = '';
                return '';
            }

            if (empty($data['token'])) {
                error_log('[GitHub Token API Error] No token found in API response');
                self::$github_token_singleton = '';
                return '';
            }

            self::$github_token_singleton = $data['token'];
            return $data['token'];

        } catch (Exception $e) {
            error_log('[GitHub Token API Error] ' . $e->getMessage());
            self::$github_token_singleton = '';
            return '';
        }
    }


    /**
     * Clean up on plugin deactivation
     */
    public static function deactivate()
    {
        // Clear scheduled cron job
        wp_clear_scheduled_hook('thrive_check_for_updates');
    }
}

