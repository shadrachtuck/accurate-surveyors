<?php
defined('ABSPATH') || exit;

/**
 * Class THRIVE_SECURITY_HELPER
 *
 * Provides shared UI utility methods such as admin notices and redirects.
 */
class THRIVE_SECURITY_HELPER {
    /**
     * Get the visitor's IP address.
     *
     * @return string IP address
     */
    public static function get_ip(): string {
        // Prioritize known headers
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        }
        if (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            return trim($_SERVER['HTTP_X_REAL_IP']);
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return trim($_SERVER['HTTP_CLIENT_IP']);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($forwarded[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Check if the IP is in the blacklist.
     *
     * @param string $ip
     * @param array $blacklist List of IPs or CIDR ranges
     * @return bool
     */
    public static function is_blacklisted($ip, array $blacklist) {
        foreach ($blacklist as $blocked) {
            $blocked = trim($blocked);
            if (strpos($blocked, '/') !== false) {
                if (self::ip_in_cidr($ip, $blocked)) return true;
            } elseif ($ip === $blocked) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the IP is in the blacklisted.
     *
     * @return bool
     */
    public static function is_blocked_admin() {
        // Skip for AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }

        $remote_config = THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        $user_ip = self::get_ip();
        $logged_user = wp_get_current_user();
        $is_admin = in_array('administrator', (array)$logged_user->roles, true);
        $is_blacklisted = self::is_blacklisted($user_ip, $remote_config['blacklist_ips'] ?? []);
        return $is_admin && $is_blacklisted;
    }

    /**
     * Check if IP is inside a CIDR block.
     *
     * @param string $ip
     * @param string $cidr
     * @return bool
     */
    public static function ip_in_cidr($ip, $cidr) {
        [$subnet, $mask] = explode('/', $cidr);
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === (ip2long($subnet) & ~((1 << (32 - $mask)) - 1));
    }

    /**
     * Get the current admin page slug.
     *
     * @return string
     */
    public static function get_current_admin_page(): string {
        $script = basename($_SERVER['PHP_SELF'] ?? '');
        
        // Handle pages accessed via query parameters
        if ($script === 'admin.php' && !empty($_GET['page'])) {
            return sanitize_text_field($_GET['page']);
        }

        // Fallback to script name
        return $script;
    }

    /**
     * Display or redirect with a notice.
     *
     * @param string $msg       The notice message.
     * @param string $type      Notice type: success, error, warning, info.
     * @param string $redirect  Target URL (defaults to admin dashboard).
     * @param bool   $same_page Whether to display on the same page.
     */
    public static function display_notice(string $msg, string $type = 'error', string $redirect = '', bool $same_page = false): void {
        // Sanitize inputs
        $msg = esc_html($msg);
        $type = in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'warning';
        $redirect = $redirect ? esc_url_raw($redirect) : admin_url();

        // Store notice in transient
        set_transient('thrive_notice', [
            'msg'  => $msg,
            'type' => $type,
        ], 60);

        if ($same_page) {
            // Display immediately
            $wp_class = 'notice-' . $type;
            echo '<div class="notice ' . esc_attr($wp_class) . ' is-dismissible"><p><strong>' . $msg . '</strong></p></div>';
            return;
        }

        // Handle redirect
        //if (!headers_sent()) {
        // Perform redirect using wp_redirect for better compatibility
        wp_redirect(add_query_arg('thrive_notice', '1', $redirect));
        exit;
        // } else {
        //     // Fallback to JavaScript redirect if headers already sent
        //     error_log('Thrive: Headers already sent, using JavaScript redirect to ' . $redirect);
        //     $redirect = add_query_arg('thrive_notice', '1', $redirect);
        //     echo '<script>window.location.href="' . esc_url($redirect) . '";</script>';
        //     exit;
        // }
    }

    /**
     * Redirect with a notice (for backward compatibility).
     *
     * @param string $msg       The notice message.
     * @param string $redirect  Target URL (defaults to admin dashboard).
     * @param string $type      Notice type: success, error, warning, info.
     */
    public static function redirect_with_notice(string $msg, string $redirect = '', string $type = 'error'): void {
        self::display_notice($msg, $type, $redirect, false);
    }

    /**
     * Display stored notices.
     */
    public static function maybe_display_notice(): void {
        $notice = get_transient('thrive_notice');
        if ($notice && is_array($notice)) {
            $msg = esc_html($notice['msg'] ?? '');
            $type = $notice['type'] ?? 'error';
            $wp_class = 'notice-' . (in_array($type, ['success', 'error', 'warning', 'info']) ? $type : 'error');

            echo '<div class="notice ' . esc_attr($wp_class) . ' is-dismissible"><p><strong>' . $msg . '</strong></p></div>';
            delete_transient('thrive_notice');
        }
    }

    /**
     * Initialize front-end notice display.
     */
    public static function init_front_end_notices(): void {
        if (is_admin()) {
            return;
        }
        add_action('wp_footer', [self::class, 'maybe_display_notice']);
    }

    /**
     * Displays all relevant admin notices for plugin requirements and config issues.
     */
    public static function display_admin_notices(): void {
        if(self::is_blocked_admin()) {
            return;
        }
        // Check for configuration issues
        $config = THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        if (empty($config)) {
            printf(
                '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
                esc_html__('Thrive', THRIVE_SECURITY_TEXT_DOMAIN),
                esc_html__('No configuration loaded. Please check API settings.', THRIVE_SECURITY_TEXT_DOMAIN)
            );
        }

        // Check for missing logging classes
        if (!class_exists('THRIVE_SECURITY_LOG_MANAGER') || !class_exists('THRIVE_SECURITY_LOG_TABLE')) {
            printf(
                '<div class="notice notice-error"><p><strong>%s</strong> %s</p></div>',
                esc_html__('Thrive', THRIVE_SECURITY_TEXT_DOMAIN),
                esc_html__('Logging functionality is disabled due to missing classes.', THRIVE_SECURITY_TEXT_DOMAIN)
            );
        }
    }

    /**
     * Render a small status widget showing config version and last sync date.
     */
    public static function render_config_status_widget(): void {
        $version = get_option(THRIVE_SECURITY_CONFIG_VERSION_KEY) ?: esc_html__('Not available', THRIVE_SECURITY_TEXT_DOMAIN);
        $last_fetched = get_option(THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY) ?:esc_html__('Never', THRIVE_SECURITY_TEXT_DOMAIN);

        $formatted_date = is_string($last_fetched) && strpos($last_fetched, '<em>') === false
            ? date_i18n('F j, Y \a\t g:i A', strtotime($last_fetched))
            : $last_fetched;

        echo '<ul style="margin-left:1em;">';
        echo '<li><strong>' . esc_html__('Config Version:', THRIVE_SECURITY_TEXT_DOMAIN) . '</strong> <code>' . esc_html($version) . '</code></li>';
        echo '<li><strong>' . esc_html__('Last Fetched:', THRIVE_SECURITY_TEXT_DOMAIN) . '</strong> ' . $formatted_date . '</li>';
        echo '</ul>';

        $refresh_url = esc_url(add_query_arg('thrive_force_sync', '1'));

        echo '<div style="display: flex; gap: 20px; justify-content: space-between;">';

        echo '<a href="' . $refresh_url . '" class="button" style="background: #0a7d24;color: #fff;border-color: #10a331;">' .
            esc_html__('Force Sync Config', THRIVE_SECURITY_TEXT_DOMAIN) . '</a>';

        echo '<a href="#" class="button button-primary show_config">' .
            esc_html__('Show Config', THRIVE_SECURITY_TEXT_DOMAIN) . '</a>';

        echo '</div>';

        if (strpos($last_fetched, '<em>') !== false) {
            echo '<div class="notice notice-error inline"><p>' .
                esc_html__('Remote configuration has not been fetched yet. Please check your connection or token.', THRIVE_SECURITY_TEXT_DOMAIN) .
                '</p></div>';
        }

        echo '<div class="thrive_config" style="display:none;">';
        $remote_config = THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        if (empty($remote_config)) {
            echo '<div class="notice notice-error inline"><p>' .
                esc_html__('Remote configuration is empty. Please check your connection or token.', THRIVE_SECURITY_TEXT_DOMAIN) .
                '</p></div>';
            return;
        }

        $cached = get_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
        if (empty($cached)) {
            $cached = [];
        }

        if ($cached && is_array($cached)) {
            echo '<pre style="background:#f9f9f9;border:1px solid #ddd;padding:10px;overflow:auto;">';
            echo esc_html(json_encode($cached, JSON_PRETTY_PRINT));
            echo '</pre>';
            echo '</div>';
        }

        // Add jQuery script for toggle functionality
        echo '<script>
            jQuery(document).ready(function($) {
                $(".show_config").on("click", function(e) {
                    e.preventDefault();
                    $(".thrive_config").toggle();
                });
            });
        </script>';
    }
    
    /**
     * Validates IP address or CIDR notation.
     *
     * @param string $entry
     * @return bool
     */
    public static function is_valid_ip_or_cidr($entry) {
        $entry = trim($entry);

        if (filter_var($entry, FILTER_VALIDATE_IP)) {
            return true;
        }

        if (strpos($entry, '/') !== false) {
            [$ip, $mask] = explode('/', $entry);
            return (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
                && is_numeric($mask) && $mask >= 0 && $mask <= 128;
        }

        return false;
    }

    /**
     * Validates a plugin/theme slug (alphanumeric, dashes, underscores).
     *
     * @param string $slug
     * @return bool
     */
    public static function is_valid_slug($slug) {
        return is_string($slug) && preg_match('/^[a-z0-9\-_]+$/i', $slug);
    }

    /**
     * Validates a WordPress admin page slug.
     *
     * @param string $page
     * @return bool
     */
    public static function is_valid_admin_page($page) {
        // Basic validation for WordPress admin page slugs
        return is_string($page) && 
               preg_match('/^[a-z0-9\-_\.]+\.php$/', $page) && // Must end with .php
               strlen($page) <= 50 && // Reasonable length limit
               !preg_match('/[^a-z0-9\-_\.]/', $page); // Only allow alphanumeric, dash, underscore, and dot
    }

    /**
     * Log a message if WP_DEBUG is enabled.
     *
     * @param string $message
     */
    public static function maybe_debug_log($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Thrive: ' . $message);
        }
    }

    /**
     * Normalize the configuration data.
     *
     * @param array $data
     * @return array
     */
    public static function normalize_config(array $data): array {
        // Normalize blacklist IPs
        $data['blacklist_ips'] = array_filter(array_map('trim', $data['blacklist_ips'] ?? []), [self::class, 'is_valid_ip_or_cidr']);

        // Normalize blocked plugins
        $blocked_plugins = [];
        foreach ($data['blocked_plugins'] ?? [] as $plugin) {
            if (is_array($plugin)) {
                if (isset($plugin['slug']) && self::is_valid_slug($plugin['slug'])) {
                    $blocked_plugins[] = [
                        'slug' => $plugin['slug'],
                        'name' => $plugin['name'] ?? $plugin['slug'],
                        'force_delete' => isset($plugin['force_delete']) ? (bool)$plugin['force_delete'] : false
                    ];
                }
            } elseif (is_string($plugin) && self::is_valid_slug($plugin)) {
                $blocked_plugins[] = [
                    'slug' => $plugin,
                    'name' => $plugin,
                    'force_delete' => false
                ];
            }
        }
        $data['blocked_plugins'] = $blocked_plugins;

        // Normalize blocked themes
        $blocked_themes = [];
        foreach ($data['blocked_themes'] ?? [] as $theme) {
            if (is_array($theme)) {
                if (isset($theme['slug']) && self::is_valid_slug($theme['slug'])) {
                    $blocked_themes[] = [
                        'slug' => $theme['slug'],
                        'name' => $theme['name'] ?? $theme['slug'],
                        'force_delete' => isset($theme['force_delete']) ? (bool)$theme['force_delete'] : false
                    ];
                }
            } elseif (is_string($theme) && self::is_valid_slug($theme)) {
                $blocked_themes[] = [
                    'slug' => $theme,
                    'name' => $theme,
                    'force_delete' => false
                ];
            }
        }
        $data['blocked_themes'] = $blocked_themes;

        // Normalize required plugins
        $required_plugins = [];
        foreach ($data['required_plugins'] ?? [] as $plugin) {
            if (is_array($plugin)) {
                if (isset($plugin['slug']) && self::is_valid_slug($plugin['slug'])) {
                    $required_plugins[] = [
                        'slug' => $plugin['slug'],
                        'name' => $plugin['name'] ?? $plugin['slug'],
                        'required' => isset($plugin['required']) ? (bool)$plugin['required'] : false,
                        'force_activation' => isset($plugin['force_activation']) ? (bool)$plugin['force_activation'] : false
                    ];
                }
            } elseif (is_string($plugin) && self::is_valid_slug($plugin)) {
                $required_plugins[] = [
                    'slug' => $plugin,
                    'name' => $plugin,
                    'required' => false,
                    'force_activation' => false
                ];
            }
        }
        $data['required_plugins'] = $required_plugins;

        // Normalize restricted pages
        $data['restricted_pages'] = array_values(array_filter($data['restricted_pages'] ?? [], [self::class, 'is_valid_admin_page']));

        return $data;
    }
}