<?php
/**
 * Handles blocked and required plugin/theme policies for Thrive.
 *
 * This is NOT a standalone plugin file. Do not add a plugin header here.
 */

defined('ABSPATH') || exit;

class THRIVE_SECURITY_PLUGIN_THEMES_MANAGER
{
    /**
     * Cached config to avoid multiple API calls
     * @var array|null
     */
    private static $cached_config = null;

    /**
     * Get the main plugin file for a given slug
     *
     * @param string $slug Plugin slug
     * @return string|null Main plugin file path or null if not found
     */
    private static function get_plugin_main_file($slug)
    {
        $all_plugins = get_plugins('/' . $slug);
        if (!empty($all_plugins)) {
            return $slug . '/' . array_keys($all_plugins)[0];
        }
        return null;
    }

    public static function init()
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Exit early if blocking is disabled
        }

        // Get config once and cache it for this request
        self::$cached_config = THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        if (is_null(self::$cached_config) || empty(self::$cached_config)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Thrive: Remote config is null or empty.');
            // Don't return early - allow the plugin to initialize with empty config
            self::$cached_config = [
                'blocked_plugins' => [],
                'blocked_themes' => [],
                'required_plugins' => [],
                'recommended_plugins' => [],
                'restricted_pages' => []
            ];
        }

        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        // Add early installation prevention hooks
        add_filter('upgrader_pre_install', function ($response, $package) {
            return self::prevent_installation($response, $package, self::$cached_config);
        }, 10, 2);

        add_filter('upgrader_pre_download', function ($response, $package) {
            return self::prevent_download($response, $package, self::$cached_config);
        }, 10, 2);

        // Hook core restrictions
        add_action('activated_plugin', function ($plugin) {
            self::handle_plugin_activation($plugin, self::$cached_config['blocked_plugins']);
        }, 10, 1);

        add_action('switch_theme', function ($new_name, $new_theme) {
            $blocked_themes = self::$cached_config['blocked_themes'] ?? [];
            self::handle_theme_activation($new_theme, $blocked_themes);
        }, 10, 2);

        // Prevent default activation messages
        add_action('admin_init', function () {
            // Prevent default activation messages
            remove_action('admin_notices', 'update_nag', 3);
        });



        // Add AJAX handler for bulk plugin actions
        add_action('wp_ajax_thrive_bulk_plugin_action', function () {
            if (!current_user_can('install_plugins')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            if (!check_ajax_referer('thrive_plugin_action', 'nonce', false)) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            // Include required WordPress files
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';

            $plugins = $_POST['plugins'] ?? [];
            $bulk_action = $_POST['bulk_action'] ?? 'install';
            $notifications = [];

            if (!empty($plugins)) {
                foreach ($plugins as $plugin) {
                    $slug = $plugin['slug'];
                    $name = $plugin['name'] ?? $slug;
                    $plugin_type = $plugin['type'] ?? 'required';

                    switch ($bulk_action) {
                        case 'install':
                            $notifications = array_merge($notifications, self::bulk_install_plugin($slug, $name, $plugin));
                            break;
                        case 'activate':
                            $notifications = array_merge($notifications, self::bulk_activate_plugin($slug, $name, $plugin));
                            break;
                        case 'force_delete':
                            $notifications = array_merge($notifications, self::bulk_delete_plugin($slug, $name, $plugin));
                            break;
                        default:
                            $notifications[] = [
                                'text' => "Unknown action: {$bulk_action}",
                                'color' => '#F44336'
                            ];
                    }
                }
            }

            wp_send_json_success([
                'message' => 'Bulk action completed',
                'notifications' => $notifications
            ]);
        });

        // Add AJAX handlers for force actions
        add_action('wp_ajax_thrive_force_plugin_action', function () {
            if (!current_user_can('delete_plugins')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            if (!check_ajax_referer('thrive_plugin_action', 'nonce', false)) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            $slug = sanitize_text_field($_POST['slug'] ?? '');
            $action_type = sanitize_text_field($_POST['action_type'] ?? '');

            if (empty($slug) || empty($action_type)) {
                wp_send_json_error(['message' => 'Missing required parameters']);
                return;
            }

            $result = self::ajax_plugin_action($slug, $action_type);
            wp_send_json_success($result);
        });

        add_action('wp_ajax_thrive_force_theme_action', function () {
            if (!current_user_can('switch_themes')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            if (!check_ajax_referer('thrive_theme_action', 'nonce', false)) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            $slug = sanitize_text_field($_POST['slug'] ?? '');
            $action = sanitize_text_field($_POST['action'] ?? '');

            if (empty($slug) || empty($action)) {
                wp_send_json_error(['message' => 'Missing required parameters']);
                return;
            }

            $result = self::ajax_theme_action($slug, $action);
            wp_send_json_success($result);
        });

        // Add filters for plugin/theme management
        add_filter('plugin_action_links', function ($actions, $plugin_file) {
            return self::filter_plugin_action_links($actions, $plugin_file, self::$cached_config['blocked_plugins']);
        }, 10, 2);

        add_filter('site_transient_update_plugins', function ($value) {
            return self::filter_plugin_updates($value, self::$cached_config['blocked_plugins']);
        });

        add_filter('site_transient_update_themes', function ($value) {
            return self::filter_theme_updates($value, self::$cached_config['blocked_themes']);
        });

        add_filter('upgrader_package_options', function ($options) {
            return self::filter_installation($options, self::$cached_config);
        });

        add_filter('themes_api_result', function ($response, $action, $args) {
            return self::filter_theme_search($response, $action, $args, self::$cached_config['blocked_themes']);
        }, 10, 3);

        // Prevent theme installation
        add_action('admin_init', function () {
            self::prevent_theme_installation(self::$cached_config['blocked_themes']);
        });

        // Filter admin themes list
        add_filter('wp_prepare_themes_for_js', function ($themes) {
            return self::filter_admin_themes_list($themes, self::$cached_config['blocked_themes']);
        });



        // Add background management cron job
        add_action('thrive_background_plugin_management', function () {
            self::background_plugin_management();
        });



        // Hook to automatically reactivate required plugins if deactivated
        add_action('deactivated_plugin', function ($plugin) {
            if (!get_option('thrive_blocking_enabled', false)) {
                return;
            }

            $remote_config = self::$cached_config ?: THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
            if (empty($remote_config)) {
                return;
            }

            $required_plugins = $remote_config['required_plugins'] ?? [];
            $plugin_slug = dirname($plugin);

            // Check if the deactivated plugin is a required plugin
            foreach ($required_plugins as $required_plugin) {
                if ($required_plugin['slug'] === $plugin_slug) {
                    // Reactivate the required plugin
                    $activate_result = activate_plugin($plugin);
                    if (!is_wp_error($activate_result)) {
                        error_log("Thrive: Auto-reactivated required plugin: {$plugin_slug}");
                        THRIVE_SECURITY_HELPER::display_notice(
                            sprintf(__('Required plugin "%s" was automatically reactivated.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($plugin_slug)),
                            'warning'
                        );
                    } else {
                        error_log("Thrive: Failed to auto-reactivate required plugin: {$plugin_slug}");
                    }
                    break;
                }
            }
        });

        // Add AJAX handler for manual background run
        add_action('wp_ajax_thrive_manual_background_run', function () {
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            if (!check_ajax_referer('thrive_plugin_action', 'nonce', false)) {
                wp_send_json_error(['message' => 'Security check failed']);
                return;
            }

            // Run background management
            self::background_plugin_management();

            // Get updated results
            $results = self::get_background_management_results();

            wp_send_json_success([
                'message' => 'Background process completed successfully',
                'results' => $results
            ]);
        });



        // Schedule background management on plugin activation
        add_action('thrive_security_activated', function () {
            self::schedule_background_management();
            
            // Run background management immediately on activation
            self::background_plugin_management();
        });

        // Unschedule background management on plugin deactivation
        add_action('thrive_security_deactivated', function () {
            self::unschedule_background_management();
        });

        // Add admin menu
        add_action('admin_menu', function () {
            add_submenu_page(
                'thrive-security',
                __('Plugin & Theme Manager', THRIVE_SECURITY_TEXT_DOMAIN),
                __('Plugin & Theme Manager', THRIVE_SECURITY_TEXT_DOMAIN),
                'manage_options',
                'thrive-plugin-theme-manager',
                [__CLASS__, 'render']
            );
        });

        // Add admin notice for cron status
        add_action('admin_notices', function () {
            if (isset($_GET['page']) && $_GET['page'] === 'thrive-plugin-theme-manager') {
                $cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
                $next_run = wp_next_scheduled('thrive_background_plugin_management');
                $is_scheduled = $next_run !== false;
                
                if ($cron_disabled) {
                    echo '<div class="notice notice-warning is-dismissible">';
                    echo '<p><strong>Thrive Security:</strong> WordPress cron is disabled. Background plugin management may not work automatically. ';
                    echo 'Consider enabling WP-Cron or setting up a real cron job.</p>';
                    echo '</div>';
                } elseif (!$is_scheduled) {
                    echo '<div class="notice notice-error is-dismissible">';
                    echo '<p><strong>Thrive Security:</strong> Background plugin management is not scheduled. ';
                    echo 'Please deactivate and reactivate the plugin to fix this issue.</p>';
                    echo '</div>';
                }
            }
        });
    }



    public static function enqueue_assets()
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Don't enqueue assets if blocking is disabled
        }

        // Only load assets on plugin/theme management pages
        $current_page = $_GET['page'] ?? '';
        if (!in_array($current_page, ['thrive-plugins', 'thrive-log', 'thrive-settings'])) {
            return;
        }

        wp_enqueue_script('jquery');
        wp_enqueue_script('toastify', 'https://cdn.jsdelivr.net/npm/toastify-js', [], null, true);
        wp_enqueue_style('toastify', 'https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css');

        // Add ajaxurl to the page
        wp_add_inline_script('jquery', 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";');

        wp_enqueue_script(
            'thrive-dev-handler',
            plugins_url('assets/js/thrive-script.js', dirname(__FILE__)),
            ['jquery'],
            THRIVE_SECURITY_VERSION,
            true
        );

        wp_localize_script('thrive-dev-handler', 'ThrivePluginAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('thrive_plugin_action')
        ]);

        wp_enqueue_style(
            'thrive-dev-style',
            plugins_url('assets/css/thrive-style.css', dirname(__FILE__)),
            [],
            THRIVE_SECURITY_VERSION
        );
    }

    public static function handle_plugin_activation($plugin, $blocked_plugins)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Exit early if blocking is disabled
        }

        $slug = dirname($plugin);

        // Check if plugin is in blocked list
        foreach ($blocked_plugins as $blocked_plugin) {
            if (is_array($blocked_plugin) && isset($blocked_plugin['slug']) && $blocked_plugin['slug'] === $slug) {
                deactivate_plugins($plugin);

                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('plugin-activation-blocked', $slug);
                }
                THRIVE_SECURITY_HELPER::display_notice(sprintf(__('Plugin "%s" is blocked and was deactivated.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)), 'error');
                break;
            } elseif (is_string($blocked_plugin) && $blocked_plugin === $slug) {
                deactivate_plugins($plugin);

                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('plugin-activation-blocked', $slug);
                }
                THRIVE_SECURITY_HELPER::display_notice(sprintf(__('Plugin "%s" is blocked and was deactivated.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)), 'error');
                break;
            }
        }
    }

    public static function handle_theme_activation($new_theme, $blocked_themes)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Exit early if blocking is disabled
        }

        $slug = $new_theme->get_stylesheet();
        $is_blocked = false;

        // Check if theme is in blocked list
        foreach ($blocked_themes as $blocked_theme) {
            if (is_array($blocked_theme) && isset($blocked_theme['slug']) && $blocked_theme['slug'] === $slug) {
                $is_blocked = true;
                break;
            } elseif (is_string($blocked_theme) && $blocked_theme === $slug) {
                $is_blocked = true;
                break;
            }
        }

        if ($is_blocked) {
            // Try fallback themes in order
            $fallbacks = ['dt-the7', 'hello-biz', 'hello-elementor', 'twentytwentyfive', 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo', 'twentytwentyone', 'twentytwenty'];
            $found = false;

            $all_themes = wp_get_themes();

            foreach ($fallbacks as $parent_slug) {
                // 1. Find installed child theme of this parent
                $child_theme_slug = null;
                foreach ($all_themes as $theme_slug => $theme_obj) {
                    if ($theme_obj->get('Template') === $parent_slug && $theme_obj->exists()) {
                        $child_theme_slug = $theme_slug;
                        break;
                    }
                }
                // 2. If child theme found, activate it
                if ($child_theme_slug) {
                    switch_theme($child_theme_slug);
                    $found = true;
                    THRIVE_SECURITY_HELPER::maybe_debug_log('Switched from blocked theme to child theme: ' . $slug . ' -> ' . $child_theme_slug);
                    if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                        THRIVE_SECURITY_LOG_MANAGER::log('theme-switched-child', $slug . '->' . $child_theme_slug);
                    }
                    break;
                }
                // 3. If parent exists, activate it
                $parent_theme = wp_get_theme($parent_slug);
                if ($parent_theme->exists()) {
                    switch_theme($parent_slug);
                    $found = true;
                    THRIVE_SECURITY_HELPER::maybe_debug_log('Switched from blocked theme to fallback: ' . $slug . ' -> ' . $parent_slug);
                    if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                        THRIVE_SECURITY_LOG_MANAGER::log('theme-switched', $slug . '->' . $parent_slug);
                    }
                    break;
                }
                // 4. If neither is installed, log a message
                THRIVE_SECURITY_HELPER::maybe_debug_log('Neither fallback parent nor child theme found for: ' . $parent_slug);
            }

            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('theme-activation-blocked', $slug);
            }

            THRIVE_SECURITY_HELPER::display_notice(
                sprintf(__('Theme "%s" is blocked and was deactivated.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)),
                'error'
            );
        }
    }



    public static function handle_required_plugin($plugin)
    {
        // Initialize filesystem if not already done
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Include all required WordPress files
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $slug = $plugin['slug'];
        $name = $plugin['name'] ?? $slug;

        // Find the actual main plugin file instead of assuming slug/slug.php
        $main_file = self::get_plugin_main_file($slug);

        if (!self::is_installed($slug)) {
            // Perform installation
            $api = plugins_api('plugin_information', ['slug' => $slug]);
            if (!is_wp_error($api)) {
                $upgrader = new Plugin_Upgrader(new Thrive_Silent_Skin());
                $result = $upgrader->install($api->download_link);
                if (is_wp_error($result)) {
                    error_log('Thrive: Failed to install plugin ' . $slug . ': ' . $result->get_error_message());
                    return;
                }
            } else {
                error_log('Thrive: Failed to get plugin information for ' . $slug . ': ' . $api->get_error_message());
                return;
            }
        }

        if ($plugin['required'] && $main_file && !is_plugin_active($main_file)) {
            activate_plugin($main_file, '', false, true);
        }
    }

    public static function handle_blocked_plugin($plugin)
    {
        $slug = is_array($plugin) ? $plugin['slug'] : $plugin;

        // Find the actual main plugin file instead of assuming slug/slug.php
        $main_file = self::get_plugin_main_file($slug);

        // Deactivate if active
        if ($main_file && is_plugin_active($main_file)) {
            deactivate_plugins($main_file);
            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('plugin-deactivated', $slug);
            }
        }

        // Delete if force_delete is true using improved method
        if (is_array($plugin) && isset($plugin['force_delete']) && $plugin['force_delete'] === true) {
            self::force_delete_plugin($slug, $plugin);
        }
    }

    public static function handle_blocked_theme($theme)
    {
        $slug = is_array($theme) ? $theme['slug'] : $theme;

        // Switch theme if it's active
        $current_theme = wp_get_theme();
        if ($current_theme->get_stylesheet() === $slug) {
            self::switch_to_fallback_theme($slug);
        }

        // Delete if force_delete is true using improved method
        if (is_array($theme) && isset($theme['force_delete']) && $theme['force_delete'] === true) {
            self::force_delete_theme($slug, $theme);
        }
    }

    public static function filter_plugin_action_links($actions, $plugin_file, $blocked_plugins)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $actions; // Return actions unchanged if blocking is disabled
        }

        $slug = dirname($plugin_file);
        if (in_array($slug, $blocked_plugins, true)) unset($actions['activate']);
        return $actions;
    }

    public static function filter_plugin_updates($value, $blocked_plugins)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $value; // Return value unchanged if blocking is disabled
        }

        if (!isset($value->response)) return $value;
        foreach ($value->response as $plugin_path => $data) {
            $slug = dirname($plugin_path);
            if (in_array($slug, $blocked_plugins, true)) {
                unset($value->response[$plugin_path]);

                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('plugin-update-blocked', $slug);
                }
            }
        }
        return $value;
    }

    public static function filter_theme_updates($value, $blocked_themes)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $value; // Return value unchanged if blocking is disabled
        }

        if (!isset($value->response)) return $value;

        foreach ($blocked_themes as $theme) {
            $slug = $theme['slug'];
            if (isset($value->response[$slug])) {
                unset($value->response[$slug]);

                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-update-blocked', $slug);
                }
            }
        }
        return $value;
    }

    public static function filter_installation($options, $remote_config)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $options; // Return options unchanged if blocking is disabled
        }

        $destination = $options['destination'] ?? '';

        // Check for blocked plugins
        foreach ($remote_config['blocked_plugins'] as $plugin) {
            $slug = is_array($plugin) ? $plugin['slug'] : $plugin;
            if (stripos($destination, '/plugins/' . $slug) !== false) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('plugin-install-blocked', $slug);
                }

                // If force_delete is true, delete the plugin directory if it exists
                if (is_array($plugin) && isset($plugin['force_delete']) && $plugin['force_delete'] === true) {
                    self::force_delete_plugin($slug, $plugin);
                }

                THRIVE_SECURITY_HELPER::display_notice(sprintf(__('Installation blocked: plugin "%s"', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)), 'error');
                return new WP_Error('blocked_plugin', sprintf(__('Plugin "%s" is blocked from installation.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)));
            }
        }

        // Check for blocked themes
        foreach ($remote_config['blocked_themes'] as $theme) {
            $slug = is_array($theme) ? $theme['slug'] : $theme;
            if (stripos($destination, '/themes/' . $slug) !== false) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-install-blocked', $slug);
                }

                // If force_delete is true, delete the theme directory if it exists
                if (is_array($theme) && isset($theme['force_delete']) && $theme['force_delete'] === true) {
                    self::force_delete_theme($slug, $theme);
                }

                THRIVE_SECURITY_HELPER::display_notice(sprintf(__('Installation blocked: theme "%s"', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)), 'error');
                return new WP_Error('blocked_theme', sprintf(__('Theme "%s" is blocked from installation.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)));
            }
        }

        return $options;
    }

    public static function is_installed($slug)
    {
        $all = get_plugins();
        foreach ($all as $plugin_file => $data) {
            if (strpos($plugin_file, $slug . '/') === 0) return true;
        }
        return false;
    }

    public static function ajax_plugin_action($slug, $action_type)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return ['message' => 'Blocking module is disabled.'];
        }

        check_ajax_referer('thrive_plugin_action', 'nonce');

        // Find the actual main plugin file instead of assuming slug/slug.php
        $main_file = self::get_plugin_main_file($slug);

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        if ($action_type === 'install') {
            if (!$main_file || !file_exists(WP_PLUGIN_DIR . '/' . $main_file)) {
                $api = plugins_api('plugin_information', ['slug' => $slug]);
                if (!is_wp_error($api)) {
                    $upgrader = new Plugin_Upgrader(new Thrive_Silent_Skin());
                    $result = $upgrader->install($api->download_link);
                    if (is_wp_error($result)) {
                        return ['message' => 'Install failed: ' . $result->get_error_message()];
                    }
                } else {
                    return ['message' => 'Failed to get plugin information: ' . $api->get_error_message()];
                }
            }
        } elseif ($action_type === 'activate') {
            if ($main_file && file_exists(WP_PLUGIN_DIR . '/' . $main_file) && !is_plugin_active($main_file)) {
                $result = activate_plugin($main_file);
                if (is_wp_error($result)) {
                    return ['message' => 'Activation failed: ' . $result->get_error_message()];
                }
            } else {
                return ['message' => 'Plugin not found or already active.'];
            }
        } elseif ($action_type === 'delete') {
            // Deactivate if active
            if ($main_file && is_plugin_active($main_file)) {
                deactivate_plugins($main_file);
            }
            // Delete plugin directory
            $plugin_dir = WP_PLUGIN_DIR . '/' . $slug;
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }
            $deleted = false;
            if ($wp_filesystem && $wp_filesystem->delete($plugin_dir, true)) {
                $deleted = true;
            } else {
                // Fallback: try PHP delete
                $deleted = self::recursive_delete_directory($plugin_dir);
            }
            if ($deleted) {
                return ['message' => 'Plugin deleted successfully.'];
            } else {
                return ['message' => 'Failed to delete plugin directory. Please check file permissions.'];
            }
        }

        return ['message' => ucfirst($action_type) . ' complete.'];
    }

    public static function ajax_theme_action($slug, $action_type)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return ['message' => 'Blocking module is disabled.'];
        }

        check_ajax_referer('thrive_theme_action', 'nonce');

        if ($action_type === 'deactivate' || $action_type === 'delete') {
            $current_theme = wp_get_theme();
            if ($current_theme->get_stylesheet() === $slug) {
                switch_theme('twentytwentyfour');
            }

            if ($action_type === 'delete') {
                $theme_dir = get_theme_root() . '/' . $slug;
                if (is_dir($theme_dir)) {
                    global $wp_filesystem;
                    if (empty($wp_filesystem)) {
                        require_once ABSPATH . '/wp-admin/includes/file.php';
                        WP_Filesystem();
                    }
                    $wp_filesystem->delete($theme_dir, true);
                }
            }

            return ['message' => ucfirst($action_type) . ' theme complete.'];
        }

        return ['message' => 'Invalid request.'];
    }

    public static function render()
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            // Redirect to dashboard if someone tries to access the page directly
            echo '<div class="wrap" id="thrive-dependencies">';
            echo '<h1>Thrive Dependencies</h1>';
            echo '<p>This page is not available because the <b>Thrive blocking module</b> is <span style="color:red">disabled</span>.</p>';
            echo '<p>Please enable the blocking module in Thrive settings to access this page.</p>';
            echo '</div>';
            return;
        }

        if (!current_user_can('manage_options')) return;

        $remote_config = self::$cached_config ?: THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        $required_plugins = $remote_config['required_plugins'] ?? [];
        $blocked_plugins = $remote_config['blocked_plugins'] ?? [];

        // Get recommended plugins (plugins that are not required but suggested)
        $recommended_plugins = $remote_config['recommended_plugins'] ?? [];

        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'required';

        echo '<div class="wrap" id="thrive-dependencies">';
        echo '<h1>Thrive Plugin Manager</h1>';

        // Background process status widget
        self::render_background_status_widget();

        // Tab navigation
        echo '<nav class="nav-tab-wrapper">';
        echo '<a href="?page=thrive-plugins&tab=required" class="nav-tab' . ($current_tab === 'required' ? ' nav-tab-active' : '') . '">Required Plugins</a>';
        echo '<a href="?page=thrive-plugins&tab=recommended" class="nav-tab' . ($current_tab === 'recommended' ? ' nav-tab-active' : '') . '">Recommended Plugins</a>';
        echo '<a href="?page=thrive-plugins&tab=blocked" class="nav-tab' . ($current_tab === 'blocked' ? ' nav-tab-active' : '') . '">Blocked Plugins</a>';
        echo '</nav>';

        // Tab content
        switch ($current_tab) {
            case 'required':
                self::render_required_plugins_tab($required_plugins);
                break;
            case 'recommended':
                self::render_recommended_plugins_tab($recommended_plugins);
                break;
            case 'blocked':
                self::render_blocked_plugins_tab($blocked_plugins);
                break;
            default:
                self::render_required_plugins_tab($required_plugins);
                break;
        }

        echo '</div>';
    }

    /**
     * Render the Required Plugins tab
     */
    private static function render_required_plugins_tab($required_plugins)
    {
        $needs_install = false;
        $needs_activate = false;

        foreach ($required_plugins as $plugin) {
            $slug = $plugin['slug'];
            $plugin_file = self::get_plugin_main_file($slug);
            $is_installed = $plugin_file && file_exists(WP_PLUGIN_DIR . '/' . $plugin_file);
            $is_active = $plugin_file && in_array($plugin_file, get_option('active_plugins', []), true);

            if (!$is_installed) {
                $needs_install = true;
            } elseif (!$is_active) {
                $needs_activate = true;
            }
        }

        echo '<div class="thrive-tab-content">';
        echo '<h2>Required Plugins</h2>';
        echo '<p>These plugins are required for your site to function properly and <strong>must be installed and active</strong>.</p>';

        if ($needs_install || $needs_activate) {
            echo '<div id="thrive-bulk-actions" style="margin-bottom: 10px;">';
            if ($needs_install) {
                echo '<button id="thrive-bulk-install" class="button button-secondary">Install All Required</button> ';
            }
            if ($needs_activate) {
                echo '<button id="thrive-bulk-activate" class="button button-secondary">Activate All Required</button>';
            }
            echo '</div>';
        }

        self::render_plugins_table($required_plugins, 'required');
        echo '</div>';
    }

    /**
     * Render the Recommended Plugins tab
     */
    private static function render_recommended_plugins_tab($recommended_plugins)
    {
        $needs_install = false;
        $needs_activate = false;

        foreach ($recommended_plugins as $plugin) {
            $slug = $plugin['slug'];
            $plugin_file = self::get_plugin_main_file($slug);
            $is_installed = $plugin_file && file_exists(WP_PLUGIN_DIR . '/' . $plugin_file);
            $is_active = $plugin_file && in_array($plugin_file, get_option('active_plugins', []), true);

            if (!$is_installed) {
                $needs_install = true;
            } elseif (!$is_active) {
                $needs_activate = true;
            }
        }

        echo '<div class="thrive-tab-content">';
        echo '<h2>Recommended Plugins</h2>';
        echo '<p>These plugins are recommended for enhanced functionality and security.</p>';

        if (!empty($recommended_plugins) && ($needs_install || $needs_activate)) {
            echo '<div id="thrive-bulk-actions" style="margin-bottom: 10px;">';
            if ($needs_install) {
                echo '<button id="thrive-bulk-install-recommended" class="button button-secondary">Install All Recommended</button> ';
            }
            if ($needs_activate) {
                echo '<button id="thrive-bulk-activate-recommended" class="button button-secondary">Activate All Recommended</button>';
            }
            echo '</div>';
        }

        self::render_plugins_table($recommended_plugins, 'recommended');
        echo '</div>';
    }

    /**
     * Render the Blocked Plugins tab
     */
    private static function render_blocked_plugins_tab($blocked_plugins)
    {
        echo '<div class="thrive-tab-content">';
        echo '<h2>Blocked Plugins</h2>';
        echo '<p>These plugins are blocked from installation and activation for security reasons.</p>';

        if (!empty($blocked_plugins)) {
            echo '<div id="thrive-bulk-actions" style="margin-bottom: 10px;">';
            echo '<button id="thrive-bulk-delete-blocked" class="button button-danger">Delete All Blocked</button>';
            echo '</div>';
        }

        self::render_plugins_table($blocked_plugins, 'blocked');
        echo '</div>';
    }

    /**
     * Render plugins table
     */
    private static function render_plugins_table($plugins, $type)
    {
        if (empty($plugins)) {
            echo '<div class="notice notice-info"><p>No ' . esc_html($type) . ' plugins found.</p></div>';
            return;
        }

        echo '<div id="thrive-dependency-table">';
        echo '<table class="widefat fixed striped">';
        echo '<thead><tr><th><input type="checkbox" id="thrive-select-all-' . esc_attr($type) . '" /></th><th>Name</th><th>Slug</th><th>Type</th><th>Status</th><th>Action</th></tr></thead><tbody>';

        foreach ($plugins as $plugin) {
            $slug = sanitize_text_field($plugin['slug']);
            $name = isset($plugin['name']) ? esc_html($plugin['name']) : esc_html($slug);
            $plugin_data = htmlspecialchars(json_encode([
                'slug' => $slug,
                'name' => $name,
                'required' => !empty($plugin['required']),
                'type' => $type
            ]), ENT_QUOTES, 'UTF-8');

            $plugin_file = self::get_plugin_main_file($slug);
            $is_installed = $plugin_file && file_exists(WP_PLUGIN_DIR . '/' . $plugin_file);
            $active_plugins = get_option('active_plugins', []);
            $is_active = $plugin_file && in_array($plugin_file, $active_plugins, true);

            $status = '';
            if ($is_active && $is_installed) {
                $status = '&#9989; Active';
            } elseif ($is_installed) {
                $status = '&#9888;&#65039; Installed';
            } else {
                $status = '&#10060; Not Installed';
            }

            // Action buttons based on type
            $action = self::get_action_buttons($plugin, $type, $is_installed, $is_active, $slug, $name);

            echo "<tr class='thrive-plugin-row thrive-{$type}-row' data-slug='" . esc_attr($slug) . "' data-type='plugin' data-installed='" . ($is_installed ? '1' : '0') . "' data-active='" . ($is_active ? '1' : '0') . "'>";
            echo '<td>';
            if ($type === 'blocked' || ($type === 'required' && !$is_active) || ($type === 'recommended' && !$is_active)) {
                echo '<input type="checkbox" class="thrive-plugin-checkbox" data-plugin=\'' . $plugin_data . '\' />';
            }
            echo '</td>';
            $type_display = $type === 'required' ? 'Plugin <span style="color: #EF4444; font-weight: 600;">(REQUIRED)</span>' : 'Plugin';
            echo "<td>{$name}</td><td>" . esc_html($slug) . "</td><td>{$type_display}</td><td>{$status}</td><td>{$action}</td></tr>";
        }

        echo '</tbody></table></div>';
    }

    /**
     * Get action buttons based on plugin type and status
     */
    private static function get_action_buttons($plugin, $type, $is_installed, $is_active, $slug, $name)
    {
        $action = '';

        switch ($type) {
            case 'required':
                if (!$is_installed) {
                    $action = '<button class="button button-primary thrive-install-plugin" data-plugin=\'' . htmlspecialchars(json_encode([
                            'slug' => $slug,
                            'name' => $name,
                            'required' => !empty($plugin['required']),
                            'type' => $type
                        ]), ENT_QUOTES, 'UTF-8') . '\' aria-label="Install plugin ' . esc_attr($name) . '">Install</button>';
                } elseif (!$is_active) {
                    $action = '<button class="button thrive-activate-plugin" data-slug="' . esc_attr($slug) . '" aria-label="Activate plugin ' . esc_attr($name) . '">Activate</button>';
                } else {
                    $action = '<span class="thrive-required-status">Required Plugin</span>';
                }
                break;

            case 'recommended':
                if (!$is_installed) {
                    $action = '<button class="button button-primary thrive-install-plugin" data-plugin=\'' . htmlspecialchars(json_encode([
                            'slug' => $slug,
                            'name' => $name,
                            'required' => !empty($plugin['required']),
                            'type' => $type
                        ]), ENT_QUOTES, 'UTF-8') . '\' aria-label="Install plugin ' . esc_attr($name) . '">Install</button>';
                } elseif (!$is_active) {
                    $action = '<button class="button thrive-activate-plugin" data-slug="' . esc_attr($slug) . '" aria-label="Activate plugin ' . esc_attr($name) . '">Activate</button>';
                    $action .= ' <button class="button button-danger thrive-uninstall-plugin" data-slug="' . esc_attr($slug) . '" aria-label="Uninstall plugin ' . esc_attr($name) . '">Uninstall</button>';
                } else {
                    $action = '<button class="button button-danger thrive-uninstall-plugin" data-slug="' . esc_attr($slug) . '" aria-label="Uninstall plugin ' . esc_attr($name) . '">Uninstall</button>';
                }
                break;

            case 'blocked':
                if ($is_installed) {
                    $action = '<button class="button button-danger thrive-force-delete-plugin" data-slug="' . esc_attr($slug) . '" aria-label="Force delete plugin ' . esc_attr($name) . '">Force Delete</button>';
                } else {
                    $action = '<span class="thrive-blocked-status">Blocked</span>';
                }
                break;
        }

        return $action;
    }

    /**
     * Bulk install plugin
     */
    private static function bulk_install_plugin($slug, $name, $plugin)
    {
        $notifications = [];
        $main_file = self::get_plugin_main_file($slug);
        $required = $plugin['required'] ?? false;

        if (!self::is_installed($slug)) {
            $notifications[] = [
                'text' => "Installing {$name}...",
                'color' => '#2196F3'
            ];

            try {
                $upgrader = new Plugin_Upgrader(new Thrive_Silent_Skin());
                $result = $upgrader->install("https://downloads.wordpress.org/plugin/{$slug}.latest-stable.zip");

                if (is_wp_error($result)) {
                    $notifications[] = [
                        'text' => "Failed to install {$name}: " . $result->get_error_message(),
                        'color' => '#F44336'
                    ];
                } else {
                    $notifications[] = [
                        'text' => "Successfully installed {$name}",
                        'color' => '#4CAF50'
                    ];

                    // ALWAYS activate required plugins after installation
                    if ($main_file) {
                        $activate_result = activate_plugin($main_file);
                        if (is_wp_error($activate_result)) {
                            $notifications[] = [
                                'text' => "Failed to activate {$name}: " . $activate_result->get_error_message(),
                                'color' => '#F44336'
                            ];
                        } else {
                            $notifications[] = [
                                'text' => "Successfully activated {$name}",
                                'color' => '#4CAF50'
                            ];
                        }
                    }
                }
            } catch (Exception $e) {
                $notifications[] = [
                    'text' => "Error installing {$name}: " . $e->getMessage(),
                    'color' => '#F44336'
                ];
            }
        } else {
            $notifications[] = [
                'text' => "{$name} is already installed",
                'color' => '#FF9800'
            ];
        }

        return $notifications;
    }

    /**
     * Bulk activate plugin
     */
    private static function bulk_activate_plugin($slug, $name, $plugin)
    {
        $notifications = [];
        $main_file = self::get_plugin_main_file($slug);

        if ($main_file && !is_plugin_active($main_file)) {
            $notifications[] = [
                'text' => "Activating {$name}...",
                'color' => '#2196F3'
            ];

            $activate_result = activate_plugin($main_file);
            if (is_wp_error($activate_result)) {
                $notifications[] = [
                    'text' => "Failed to activate {$name}: " . $activate_result->get_error_message(),
                    'color' => '#F44336'
                ];
            } else {
                $notifications[] = [
                    'text' => "Successfully activated {$name}",
                    'color' => '#4CAF50'
                ];
            }
        } else {
            $notifications[] = [
                'text' => "{$name} is already active or not installed",
                'color' => '#FF9800'
            ];
        }

        return $notifications;
    }

    /**
     * Bulk delete plugin
     */
    private static function bulk_delete_plugin($slug, $name, $plugin)
    {
        $notifications = [];
        $main_file = self::get_plugin_main_file($slug);

        if ($main_file && is_plugin_active($main_file)) {
            deactivate_plugins($main_file);
            $notifications[] = [
                'text' => "Deactivated {$name}",
                'color' => '#FF9800'
            ];
        }

        if (self::is_installed($slug)) {
            $notifications[] = [
                'text' => "Deleting {$name}...",
                'color' => '#2196F3'
            ];

            $deleted = self::force_delete_plugin($slug, $plugin);
            if ($deleted) {
                $notifications[] = [
                    'text' => "Successfully deleted {$name}",
                    'color' => '#4CAF50'
                ];
            } else {
                $notifications[] = [
                    'text' => "Failed to delete {$name}",
                    'color' => '#F44336'
                ];
            }
        } else {
            $notifications[] = [
                'text' => "{$name} is not installed",
                'color' => '#FF9800'
            ];
        }

        return $notifications;
    }

    /**
     * Background process for automatic plugin management
     * This method is called by cron job
     */
    public static function background_plugin_management()
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return;
        }
        THRIVE_SECURITY_CONFIG_MANAGER::refresh();

        $remote_config = self::$cached_config ?: THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        if (empty($remote_config)) {
            error_log('Thrive: No config available for background plugin management');
            return;
        }

        $results = [
            'required_installed' => 0,
            'required_activated' => 0,
            'blocked_deleted' => 0,
            'errors' => []
        ];

        // Process required plugins
        $required_plugins = $remote_config['required_plugins'] ?? [];
        foreach ($required_plugins as $plugin) {
            $slug = $plugin['slug'];
            $name = $plugin['name'] ?? $slug;
            $main_file = self::get_plugin_main_file($slug);
            $is_installed = $main_file && file_exists(WP_PLUGIN_DIR . '/' . $main_file);
            $is_active = $main_file && is_plugin_active($main_file);

            // Install if not installed
            if (!$is_installed) {
                try {
                    $upgrader = new Plugin_Upgrader(new Thrive_Silent_Skin());
                    $result = $upgrader->install("https://downloads.wordpress.org/plugin/{$slug}.latest-stable.zip");

                    if (!is_wp_error($result)) {
                        $results['required_installed']++;
                        error_log("Thrive: Background installed required plugin: {$name}");

                        // ALWAYS activate required plugins after installation
                        if ($main_file) {
                            $activate_result = activate_plugin($main_file);
                            if (!is_wp_error($activate_result)) {
                                $results['required_activated']++;
                                error_log("Thrive: Background activated required plugin: {$name}");
                            } else {
                                $results['errors'][] = "Failed to activate {$name}: " . $activate_result->get_error_message();
                            }
                        }
                    } else {
                        $results['errors'][] = "Failed to install {$name}: " . $result->get_error_message();
                    }
                } catch (Exception $e) {
                    $results['errors'][] = "Error installing {$name}: " . $e->getMessage();
                }
            } elseif (!$is_active) {
                // ALWAYS activate required plugins if they are installed but not active
                $activate_result = activate_plugin($main_file);
                if (!is_wp_error($activate_result)) {
                    $results['required_activated']++;
                } else {
                    $results['errors'][] = "Failed to activate {$name}: " . $activate_result->get_error_message();
                }
            } else {
                // Double-check: Ensure required plugins are still active (in case they were deactivated)
                if ($main_file && !is_plugin_active($main_file)) {
                    $activate_result = activate_plugin($main_file);
                    if (!is_wp_error($activate_result)) {
                        $results['required_activated']++;
                    } else {
                        $results['errors'][] = "Failed to re-activate {$name}: " . $activate_result->get_error_message();
                    }
                }
            }
        }

        // Process blocked plugins
        $blocked_plugins = $remote_config['blocked_plugins'] ?? [];
        foreach ($blocked_plugins as $plugin) {
            $slug = is_array($plugin) ? $plugin['slug'] : $plugin;
            $name = is_array($plugin) ? ($plugin['name'] ?? $slug) : $slug;
            $main_file = self::get_plugin_main_file($slug);
            $is_installed = $main_file && file_exists(WP_PLUGIN_DIR . '/' . $main_file);

            if ($is_installed) {
                // Deactivate first if active
                if (is_plugin_active($main_file)) {
                    deactivate_plugins($main_file);
                }

                // Delete the plugin
                $deleted = self::force_delete_plugin($slug, $plugin);
                if ($deleted) {
                    $results['blocked_deleted']++;
                } else {
                    $results['errors'][] = "Failed to delete blocked plugin: {$name}";
                }
            }
        }

        // Store results for monitoring
        update_option('thrive_background_management_results', [
            'last_run' => time(),
            'results' => $results
        ]);

    }

    /**
     * Get background management results
     */
    public static function get_background_management_results()
    {
        $results = get_option('thrive_background_management_results', []);
        if (empty($results)) {
            return [
                'last_run' => 0,
                'last_run_human' => 'Never',
                'results' => [
                    'required_installed' => 0,
                    'required_activated' => 0,
                    'blocked_deleted' => 0,
                    'errors' => []
                ]
            ];
        }

        $results['last_run_human'] = $results['last_run'] ? date('Y-m-d H:i:s', $results['last_run']) : 'Never';
        return $results;
    }

    /**
     * Schedule background management cron job
     */
    public static function schedule_background_management()
    {
        // Clear any existing schedule first
        wp_clear_scheduled_hook('thrive_background_plugin_management');
        
        // Schedule the new event
        wp_schedule_event(time(), 'thrice_daily', 'thrive_background_plugin_management');

    }

    /**
     * Unschedule background management cron job
     */
    public static function unschedule_background_management()
    {
        wp_clear_scheduled_hook('thrive_background_plugin_management');
    }

    /**
     * Render background process status widget
     */
    private static function render_background_status_widget()
    {
        $results = self::get_background_management_results();
        $next_run = wp_next_scheduled('thrive_background_plugin_management');
        $is_scheduled = $next_run !== false;
        
        // Check if cron is working properly
        $cron_disabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;

        echo '<div class="thrive-background-status-widget">';
        echo '<div class="thrive-status-card">';
        
        // Show warning if cron is disabled
        if ($cron_disabled) {
            echo '<div style="background: #FEF3C7; border: 1px solid #F59E0B; border-radius: 4px; padding: 10px; margin-bottom: 15px;">';
            echo '<strong>⚠️ Warning:</strong> WordPress cron is disabled. Background processes may not run automatically. ';
            echo 'Consider using a real cron job or enabling WP-Cron for automatic background management.';
            echo '</div>';
        }
        
        echo '<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">';
        echo '<h3>🔄 Background Plugin Management</h3>';
        echo '<button id="thrive-manual-background-run" class="button button-secondary">Run Now</button>';
        echo '</div>';

        if ($is_scheduled) {
            echo '<div class="thrive-status-item">';
            echo '<span class="thrive-status-label">Status:</span>';
            echo '<span class="thrive-status-value thrive-status-active">Active</span>';
            echo '</div>';

            echo '<div class="thrive-status-item">';
            echo '<span class="thrive-status-label">Next Run:</span>';
            echo '<span class="thrive-status-value">' . date('Y-m-d H:i:s', $next_run) . '</span>';
            echo '</div>';
        } else {
            echo '<div class="thrive-status-item">';
            echo '<span class="thrive-status-label">Status:</span>';
            echo '<span class="thrive-status-value thrive-status-inactive">Inactive</span>';
            echo '</div>';
        }

        echo '<div class="thrive-status-item">';
        echo '<span class="thrive-status-label">Last Run:</span>';
        echo '<span class="thrive-status-value">' . $results['last_run_human'] . '</span>';
        echo '</div>';

        if ($results['last_run'] > 0) {
            echo '<div class="thrive-status-stats">';
            echo '<div class="thrive-stat-item">';
            echo '<span class="thrive-stat-number">' . $results['results']['required_installed'] . '</span>';
            echo '<span class="thrive-stat-label">Required Installed</span>';
            echo '</div>';

            echo '<div class="thrive-stat-item">';
            echo '<span class="thrive-stat-number">' . $results['results']['required_activated'] . '</span>';
            echo '<span class="thrive-stat-label">Required Activated</span>';
            echo '<small style="color: #EF4444; font-weight: 600;">(MUST BE ACTIVE)</small>';
            echo '</div>';

            echo '<div class="thrive-stat-item">';
            echo '<span class="thrive-stat-number">' . $results['results']['blocked_deleted'] . '</span>';
            echo '<span class="thrive-stat-label">Blocked Deleted</span>';
            echo '</div>';
            echo '</div>';

            if (!empty($results['results']['errors'])) {
                echo '<div class="thrive-status-errors">';
                echo '<h4>⚠️ Errors in Last Run:</h4>';
                echo '<ul>';
                foreach (array_slice($results['results']['errors'], 0, 3) as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                if (count($results['results']['errors']) > 3) {
                    echo '<li>... and ' . (count($results['results']['errors']) - 3) . ' more errors</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        }

        echo '</div>';
        echo '</div>';
    }

    public static function prevent_installation($response, $package, $remote_config, $upgrader = null)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $response; // Return response unchanged if blocking is disabled
        }

        if (is_wp_error($response)) {
            return $response;
        }

        // Get the destination path
        $destination = '';
        if ($upgrader && isset($upgrader->result['destination'])) {
            $destination = $upgrader->result['destination'];
        } elseif ($upgrader && isset($upgrader->skin->result['destination'])) {
            $destination = $upgrader->skin->result['destination'];
        } elseif (is_array($package) && isset($package['destination'])) {
            $destination = $package['destination'];
        }

        if (empty($destination)) {
            return $response;
        }

        // Check for blocked plugins
        foreach ($remote_config['blocked_plugins'] as $plugin) {
            $slug = is_array($plugin) ? $plugin['slug'] : $plugin;
            if (stripos($destination, '/plugins/' . $slug) !== false) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('plugin-install-prevented', $slug);
                }
                return new WP_Error('blocked_plugin', sprintf(__('Plugin "%s" is blocked from installation.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)));
            }
        }

        // Check for blocked themes
        foreach ($remote_config['blocked_themes'] as $theme) {
            $slug = is_array($theme) ? $theme['slug'] : $theme;
            if (stripos($destination, '/themes/' . $slug) !== false) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-install-prevented', $slug);
                }
                return new WP_Error('blocked_theme', sprintf(__('Theme "%s" is blocked from installation.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)));
            }
        }

        return $response;
    }

    public static function prevent_download($response, $package, $remote_config, $upgrader = null)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $response; // Return response unchanged if blocking is disabled
        }

        if (is_wp_error($response)) {
            return $response;
        }

        // Extract slug from package URL
        $slug = '';
        if (is_string($package) && preg_match('/\/downloads\.wordpress\.org\/(?:plugin|theme)\/([^\/]+)\.zip/', $package, $matches)) {
            $slug = $matches[1];
        } elseif (is_array($package) && isset($package['slug'])) {
            $slug = $package['slug'];
        }

        if (empty($slug)) {
            return $response;
        }

        // Check for blocked plugins
        foreach ($remote_config['blocked_plugins'] as $plugin) {
            $blocked_slug = is_array($plugin) ? $plugin['slug'] : $plugin;
            if ($slug === $blocked_slug) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('plugin-download-prevented', $slug);
                }
                return new WP_Error('blocked_plugin', sprintf(__('Plugin "%s" is blocked from installation.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)));
            }
        }

        // Check for blocked themes
        foreach ($remote_config['blocked_themes'] as $theme) {
            $blocked_slug = is_array($theme) ? $theme['slug'] : $theme;
            if ($slug === $blocked_slug) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-download-prevented', $slug);
                }
                return new WP_Error('blocked_theme', sprintf(__('Theme "%s" is blocked from installation.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug)));
            }
        }

        return $response;
    }

    public static function filter_theme_search($response, $action, $args, $blocked_themes)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $response;
        }

        if (is_wp_error($response)) {
            return $response;
        }

        // Normalize blocked themes to flat list of slugs
        $blocked_slugs = array_map(function ($bt) {
            if (is_array($bt) && isset($bt['slug'])) {
                return $bt['slug'];
            }
            if (is_object($bt) && isset($bt->slug)) {
                return $bt->slug;
            }
            return $bt; // string slug fallback
        }, $blocked_themes);

        // Block single theme info request
        if ($action === 'theme_information' && isset($args->slug)) {
            $slug = $args->slug;

            if (in_array($slug, $blocked_slugs, true)) {
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-search-blocked', $slug);
                }
                return new WP_Error(
                    'blocked_theme',
                    sprintf(__('Theme "%s" is blocked from search.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($slug))
                );
            }
        }

        // Block themes from general search results
        if ($action === 'query_themes' && isset($response->themes) && is_array($response->themes)) {
            foreach ($response->themes as $key => $theme) {
                // Only support object-based themes (standard WP response)
                if (isset($theme->slug) && in_array($theme->slug, $blocked_slugs, true)) {
                    unset($response->themes[$key]);
                    if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                        THRIVE_SECURITY_LOG_MANAGER::log('theme-search-result-blocked', $theme->slug);
                    }
                }
            }

            // Re-index the themes array after removal
            $response->themes = array_values($response->themes);
        }

        return $response;
    }


    public static function prevent_theme_installation($blocked_themes)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Exit early if blocking is disabled
        }

        // Check for theme installation attempts
        if (isset($_POST['theme']) && !empty($_POST['theme'])) {
            $theme_slug = sanitize_text_field($_POST['theme']);
            foreach ($blocked_themes as $blocked_theme) {
                if (is_array($blocked_theme) && isset($blocked_theme['slug'])) {
                    $blocked_slug = $blocked_theme['slug'];
                } elseif (is_object($blocked_theme) && isset($blocked_theme->slug)) {
                    $blocked_slug = $blocked_theme->slug;
                } else {
                    $blocked_slug = $blocked_theme;
                }
                if ($theme_slug === $blocked_slug) {
                    if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                        THRIVE_SECURITY_LOG_MANAGER::log('theme-installation-attempt-blocked', $theme_slug);
                    }
                    THRIVE_SECURITY_HELPER::display_notice(
                        sprintf(__('Theme "%s" is blocked and cannot be installed.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($theme_slug)),
                        'error',
                        admin_url('themes.php')
                    );
                    return;
                }
            }
        }

        // Check for theme activation attempts
        if (isset($_GET['action']) && $_GET['action'] === 'activate' && isset($_GET['stylesheet'])) {
            $theme_slug = sanitize_text_field($_GET['stylesheet']);
            foreach ($blocked_themes as $blocked_theme) {
                if (is_array($blocked_theme) && isset($blocked_theme['slug'])) {
                    $blocked_slug = $blocked_theme['slug'];
                } elseif (is_object($blocked_theme) && isset($blocked_theme->slug)) {
                    $blocked_slug = $blocked_theme->slug;
                } else {
                    $blocked_slug = $blocked_theme;
                }
                if ($theme_slug === $blocked_slug) {
                    if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                        THRIVE_SECURITY_LOG_MANAGER::log('theme-activation-attempt-blocked', $theme_slug);
                    }
                    THRIVE_SECURITY_HELPER::display_notice(
                        sprintf(__('Theme "%s" is blocked and cannot be activated.', THRIVE_SECURITY_TEXT_DOMAIN), esc_html($theme_slug)),
                        'error',
                        admin_url('themes.php')
                    );
                    return;
                }
            }
        }
    }

    // Filter blocked themes from admin themes list
    public static function filter_admin_themes_list($themes, $blocked_themes)
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return $themes; // Return themes unchanged if blocking is disabled
        }

        foreach ($themes as $key => $theme) {
            if (isset($theme['id'])) {
                foreach ($blocked_themes as $blocked_theme) {
                    $blocked_slug = is_array($blocked_theme) ? $blocked_theme['slug'] : $blocked_theme;
                    if ($theme['id'] === $blocked_slug) {
                        unset($themes[$key]);
                        if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                            THRIVE_SECURITY_LOG_MANAGER::log('theme-admin-list-blocked', $theme['id']);
                        }
                        break;
                    }
                }
            }
        }
        return $themes;
    }

    /**
     * Force delete a plugin with improved error handling and validation
     */
    public static function force_delete_plugin($slug, $blocked_plugin)
    {
        // Validate input
        if (empty($slug) || !is_string($slug)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Invalid plugin slug for deletion: ' . var_export($slug, true));
            return false;
        }

        $plugin_dir = WP_PLUGIN_DIR . '/' . $slug;

        // Check if directory exists
        if (!is_dir($plugin_dir)) {
            return false;
        }

        // Check if it's a valid plugin directory
        $plugin_files = array();
        $all_plugins = get_plugins('/' . $slug);
        $plugin_files = array_keys($all_plugins);

        if (empty($plugin_files)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('No valid plugin files found in directory: ' . $plugin_dir);
            return false;
        }

        // Initialize filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Ensure plugin is deactivated before deletion
        $main_file = self::get_plugin_main_file($slug);
        if ($main_file && is_plugin_active($main_file)) {
            deactivate_plugins($main_file);
        }

        // Attempt deletion with multiple fallback methods
        $deleted = false;

        // Method 1: WordPress filesystem
        if ($wp_filesystem && $wp_filesystem->delete($plugin_dir, true)) {
            $deleted = true;
        }

        // Method 2: Custom delete_dir method
        if (!$deleted && class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
            THRIVE_SECURITY_LOG_MANAGER::delete_dir($plugin_dir);
            if (!is_dir($plugin_dir)) {
                $deleted = true;
            }
        }

        // Method 3: PHP unlink/rmdir as last resort
        if (!$deleted) {
            $deleted = self::recursive_delete_directory($plugin_dir);
            if ($deleted) {
            }
        }

        // Log the result
        if ($deleted) {
            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('auto-deleted-plugin', $slug);
            }
            return true;
        } else {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Failed to delete plugin: ' . $slug);
            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('plugin-deletion-failed', $slug);
            }
            return false;
        }
    }

    /**
     * Force delete a theme with improved error handling and validation
     */
    public static function force_delete_theme($slug, $blocked_theme)
    {
        // Validate input
        if (empty($slug) || !is_string($slug)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Invalid theme slug for deletion: ' . var_export($slug, true));
            return false;
        }

        $theme_dir = get_theme_root() . '/' . $slug;

        // Check if directory exists
        if (!is_dir($theme_dir)) {
            return false;
        }

        // Check if it's a valid theme directory
        $style_file = $theme_dir . '/style.css';
        if (!file_exists($style_file)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Theme style.css not found: ' . $style_file);
            return false;
        }

        // Initialize filesystem
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Ensure theme is not active before deletion
        $current_theme = wp_get_theme();
        if ($current_theme->get_stylesheet() === $slug) {
            self::switch_to_fallback_theme($slug);
        }

        // Attempt deletion with multiple fallback methods
        $deleted = false;

        // Method 1: WordPress filesystem
        if ($wp_filesystem && $wp_filesystem->delete($theme_dir, true)) {
            $deleted = true;
        }

        // Method 2: Custom delete_dir method
        if (!$deleted && class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
            THRIVE_SECURITY_LOG_MANAGER::delete_dir($theme_dir);
            if (!is_dir($theme_dir)) {
                $deleted = true;
            }
        }

        // Method 3: PHP unlink/rmdir as last resort
        if (!$deleted) {
            $deleted = self::recursive_delete_directory($theme_dir);
            if ($deleted) {
            }
        }

        // Log the result
        if ($deleted) {
            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('auto-deleted-theme', $slug);
            }
            return true;
        } else {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Failed to delete theme: ' . $slug);
            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('theme-deletion-failed', $slug);
            }
            return false;
        }
    }

    /**
     * Switch to a fallback theme when a blocked theme is active
     */
    public static function switch_to_fallback_theme($blocked_theme_slug)
    {
        $fallbacks = ['dt-the7', 'hello-biz', 'hello-elementor', 'twentytwentyfive', 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo', 'twentytwentyone', 'twentytwenty'];
        $switched = false;

        $all_themes = wp_get_themes();

        foreach ($fallbacks as $parent_slug) {
            // 1. Find installed child theme of this parent
            $child_theme_slug = null;
            foreach ($all_themes as $theme_slug => $theme_obj) {
                if ($theme_obj->get('Template') === $parent_slug && $theme_obj->exists()) {
                    $child_theme_slug = $theme_slug;
                    break;
                }
            }
            // 2. If child theme found, activate it
            if ($child_theme_slug) {
                switch_theme($child_theme_slug);
                $switched = true;
                THRIVE_SECURITY_HELPER::maybe_debug_log('Switched from blocked theme to child theme: ' . $blocked_theme_slug . ' -> ' . $child_theme_slug);
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-switched-child', $blocked_theme_slug . '->' . $child_theme_slug);
                }
                break;
            }
            // 3. If parent exists, activate it
            $parent_theme = wp_get_theme($parent_slug);
            if ($parent_theme->exists()) {
                switch_theme($parent_slug);
                $switched = true;
                THRIVE_SECURITY_HELPER::maybe_debug_log('Switched from blocked theme to fallback: ' . $blocked_theme_slug . ' -> ' . $parent_slug);
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('theme-switched', $blocked_theme_slug . '->' . $parent_slug);
                }
                break;
            }
            // 4. If neither is installed, log a message
            THRIVE_SECURITY_HELPER::maybe_debug_log('Neither fallback parent nor child theme found for: ' . $parent_slug);
        }

        if (!$switched) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('No fallback theme found for: ' . $blocked_theme_slug);
            if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                THRIVE_SECURITY_LOG_MANAGER::log('theme-switch-failed', $blocked_theme_slug);
            }
        }

        return $switched;
    }

    /**
     * Recursive directory deletion as fallback method
     */
    public static function recursive_delete_directory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                self::recursive_delete_directory($path);
            } else {
                @unlink($path);
            }
        }

        return @rmdir($dir);
    }

    // Validate and process API configuration
    public static function validate_api_configuration()
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Exit early if blocking is disabled
        }

        $remote_config = THRIVE_SECURITY_CONFIG_MANAGER::get_config();
        if (empty($remote_config)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('No remote config available for validation');
            return;
        }

        // Validate blocked plugins configuration
        if (isset($remote_config['blocked_plugins']) && is_array($remote_config['blocked_plugins'])) {
            foreach ($remote_config['blocked_plugins'] as $index => $plugin) {
                if (!is_array($plugin)) {
                    // Convert string to array format for consistency
                    $remote_config['blocked_plugins'][$index] = [
                        'slug' => $plugin,
                        'name' => $plugin,
                        'force_delete' => false
                    ];
                } elseif (!isset($plugin['slug'])) {
                    // Remove invalid entries
                    unset($remote_config['blocked_plugins'][$index]);
                    THRIVE_SECURITY_HELPER::maybe_debug_log('Removed invalid blocked plugin entry: ' . var_export($plugin, true));
                } else {
                    // Ensure force_delete is boolean
                    if (isset($plugin['force_delete'])) {
                        $remote_config['blocked_plugins'][$index]['force_delete'] = (bool)$plugin['force_delete'];
                    } else {
                        $remote_config['blocked_plugins'][$index]['force_delete'] = false;
                    }
                }
            }
        }

        // Validate blocked themes configuration
        if (isset($remote_config['blocked_themes']) && is_array($remote_config['blocked_themes'])) {
            foreach ($remote_config['blocked_themes'] as $index => $theme) {
                if (!is_array($theme)) {
                    // Convert string to array format for consistency
                    $remote_config['blocked_themes'][$index] = [
                        'slug' => $theme,
                        'name' => $theme,
                        'force_delete' => false
                    ];
                } elseif (!isset($theme['slug'])) {
                    // Remove invalid entries
                    unset($remote_config['blocked_themes'][$index]);
                    THRIVE_SECURITY_HELPER::maybe_debug_log('Removed invalid blocked theme entry: ' . var_export($theme, true));
                } else {
                    // Ensure force_delete is boolean
                    if (isset($theme['force_delete'])) {
                        $remote_config['blocked_themes'][$index]['force_delete'] = (bool)$theme['force_delete'];
                    } else {
                        $remote_config['blocked_themes'][$index]['force_delete'] = false;
                    }
                }
            }
        }

        // Update the cached config with validated data
        set_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY, $remote_config, apply_filters('thrive_admin_config_cache_time', 3600));
    }
}

if (!class_exists('Automatic_Upgrader_Skin')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

class Thrive_Silent_Skin extends Automatic_Upgrader_Skin
{
    public function feedback($string, ...$args)
    {
        // Silenced for AJAX
    }
}

