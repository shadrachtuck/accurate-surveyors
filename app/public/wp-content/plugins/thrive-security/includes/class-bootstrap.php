<?php
defined('ABSPATH') || exit;

/**
 * Class THRIVE_SECURITY_BOOTSTRAP
 *
 * Handles activation, deactivation, uninstall protection, and CRON setup.
 */
class THRIVE_SECURITY_BOOTSTRAP {
    /**
     * Initialize hooks that should always run.
     */
  

   public static function init() {
       // Ensure CRON is scheduled
       add_action('init', [self::class, 'ensure_cron_scheduled']);

       // Check if blocking module is enabled
       if (!get_option('thrive_blocking_enabled', false)) {
           // Clear all scheduled cron hooks when blocking is disabled
           $cron_hooks = [
               'thrive_security_cron_send_log'
           ];

           foreach ($cron_hooks as $hook) {
               wp_clear_scheduled_hook($hook);
           }
       }

       // Protect against deactivation in admin
//        add_filter('plugin_action_links_' . plugin_basename(THRIVE_SECURITY_PLUGIN_FILE), [self::class, 'prevent_deactivation']);
       // Add settings link on plugins page.
       add_filter('plugin_action_links_' . plugin_basename(THRIVE_SECURITY_PLUGIN_FILE), [self::class, 'add_settings_link']);
       // Prevent uninstall
//        register_uninstall_hook(THRIVE_SECURITY_PLUGIN_FILE, [self::class, 'prevent_uninstall']);

       add_action('admin_menu', [self::class, 'add_thrive_menu']);

       add_action('admin_notices', [THRIVE_SECURITY_HELPER::class, 'display_admin_notices']);
       add_action('admin_notices', [THRIVE_SECURITY_HELPER::class, 'maybe_display_notice']);

//       add_action('wp_dashboard_setup', function () {
//           if(THRIVE_SECURITY_HELPER::is_blocked_admin()) {
//               return;
//           }
//
//           // Add a dashboard widget
//           wp_add_dashboard_widget(
//               'thrive_config_status',
//               esc_html__('Thrive Config Status', THRIVE_SECURITY_TEXT_DOMAIN),
//               [THRIVE_SECURITY_HELPER::class, 'render_config_status_widget']
//           );
//
//           // Ensure the widget is at the top position
//           global $wp_meta_boxes;
//           $dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
//           $widget = ['thrive_config_status' => $dashboard['thrive_config_status']];
//           unset($dashboard['thrive_config_status']);
//           $wp_meta_boxes['dashboard']['normal']['core'] = $widget + $dashboard;
//       });



       // Handle force sync
       add_action('admin_init', function () {
           $core_file_mod_disabled = get_option('thrive_disable_core_file_mod', 0);
           $file_editing_disabled = get_option('thrive_disable_file_edit', 0);

           // Disable plugin/theme installs, updates, and edits if option enabled
           if ($core_file_mod_disabled && !defined('DISALLOW_FILE_MODS')) {
               define('DISALLOW_FILE_MODS', true);
           }

           if ($file_editing_disabled && !defined('DISALLOW_FILE_EDIT')) {
               define('DISALLOW_FILE_EDIT', true);
           }

           if(!THRIVE_SECURITY_HELPER::is_blocked_admin()) {
               if (isset($_GET['thrive_force_sync'])) {
                   THRIVE_SECURITY_CONFIG_MANAGER::refresh();
                   THRIVE_SECURITY_HELPER::display_notice(
                       __('Thrive site config was forcefully refreshed.', THRIVE_SECURITY_TEXT_DOMAIN),
                       'success',
                       '',
                       true
                   );
                   wp_safe_redirect(remove_query_arg('thrive_force_sync'));
                   exit;
               }

               if (isset($_GET['thrive_force_clear_log']) && class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                   THRIVE_SECURITY_LOG_MANAGER::clear_log();
                   THRIVE_SECURITY_HELPER::display_notice(
                       __('Log file force-cleared.', THRIVE_SECURITY_TEXT_DOMAIN),
                       'success',
                       '',
                       true
                   );
                   wp_safe_redirect(remove_query_arg('thrive_force_clear_log'));
                   exit;
               }

               if (isset($_GET['thrive_force_create_htaccess'])) {
                   $result = self::thrive_security_protect_log_htaccess();
                   if ($result) {
                       THRIVE_SECURITY_HELPER::display_notice(
                           __('Thrive .htaccess protection created successfully.', THRIVE_SECURITY_TEXT_DOMAIN),
                           'success',
                           '',
                           true
                       );
                   } else {
                       THRIVE_SECURITY_HELPER::display_notice(
                           __('Failed to create Thrive .htaccess protection. Check debug log for details.', THRIVE_SECURITY_TEXT_DOMAIN),
                           'error',
                           '',
                           true
                       );
                   }
                   wp_safe_redirect(remove_query_arg('thrive_force_create_htaccess'));
                   exit;
               }

               if (isset($_GET['thrive_debug_htaccess'])) {
                   $debug_info = self::debug_htaccess_creation();
                   THRIVE_SECURITY_HELPER::display_notice(
                       __('Debug information logged. Check debug log for details.', THRIVE_SECURITY_TEXT_DOMAIN),
                       'info',
                       '',
                       true
                   );
                   wp_safe_redirect(remove_query_arg('thrive_debug_htaccess'));
                   exit;
               }
           }
       });

       // CRON handler
       if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
           add_action('thrive_security_cron_send_log', [THRIVE_SECURITY_LOG_MANAGER::class, 'send_log']);
       }
   }

    /**
     * Add Thrive menu and submenus
     */
    public static function add_thrive_menu(): void {
        // Always show menu so users can access settings to enable/disable blocking
        if(THRIVE_SECURITY_HELPER::is_blocked_admin()) {
            return;
        }
        // Add parent menu "Thrive"
        add_menu_page(
            esc_html__('Thrive', THRIVE_SECURITY_TEXT_DOMAIN),  // Page title
            esc_html__('Thrive', THRIVE_SECURITY_TEXT_DOMAIN), // Menu title
            'manage_options',                                 // Capability
            'thrive-log',                                    // Menu slug (unique)
            null,                                           // No callback because we override submenu below
            'dashicons-lock',                              // Icon (choose any dashicon)
            65                                            // Position (optional)
        );
        
        // Override default first submenu (same slug as parent) with Thrive Block Log
        add_submenu_page(
            'thrive-log',                                                    // Parent slug matches parent menu slug
            esc_html__('Thrive Block Log', THRIVE_SECURITY_TEXT_DOMAIN),    // Page title
            esc_html__('Thrive Block Log', THRIVE_SECURITY_TEXT_DOMAIN),   // Menu title
            'manage_options',                                             // Capability
            'thrive-log',                                                // SAME slug as parent menu slug to override
            [THRIVE_SECURITY_LOG_PAGE::class, 'render']                 // Callback function to render the page
        );

        // Add second submenu: Thrive Dependencies
        add_submenu_page(
            'thrive-log',                                                        // Parent slug
            esc_html__('Thrive Dependencies', THRIVE_SECURITY_TEXT_DOMAIN),     // Page title
            esc_html__('Thrive Dependencies', THRIVE_SECURITY_TEXT_DOMAIN),    // Menu title
            'manage_options',                                                 // Capability
            'thrive-plugins',                                                // Submenu slug
            [THRIVE_SECURITY_PLUGIN_THEMES_MANAGER::class, 'render']
        );

        // Override default first submenu (same slug as parent) with Thrive Block Log
        add_submenu_page(
            'thrive-log',                                                   // Parent slug matches parent menu slug
            esc_html__('Thrive Settings', THRIVE_SECURITY_TEXT_DOMAIN),    // Page title
            esc_html__('Thrive Settings', THRIVE_SECURITY_TEXT_DOMAIN),   // Menu title
            'manage_options',                                            // Capability
            'thrive-settings',                                          // SAME slug as parent menu slug to override
            [THRIVE_SECURITY_SETTINGS::class, 'render']                // Callback function to render the page
        );
    }

    /**
     * On plugin activation
     */
    public static function on_activation() {
        // Clear the disabled flag on activation
        if (class_exists('THRIVE_SECURITY_SETTINGS')) {
            THRIVE_SECURITY_SETTINGS::clear_disabled_flag();
        }
        
        $config = THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        self::ensure_cron_scheduled();
        
        // Attempt to create .htaccess protection
        $htaccess_result = self::thrive_security_protect_log_htaccess();
        if (!$htaccess_result) {
            // Run debug function to get more information
            self::debug_htaccess_creation();
        }
    }

    /**
     * Ensure CRON is scheduled
     */
    public static function ensure_cron_scheduled() {
        // If blocking is disabled, clear all cron jobs and return
        if (!get_option('thrive_blocking_enabled', false)) {
            $cron_hooks = [
                'thrive_security_cron_send_log'
            ];
            foreach ($cron_hooks as $hook) {
                wp_clear_scheduled_hook($hook);
            }
            return;
        }

        // Define cron job configurations (matching the settings structure)
        $cron_jobs = [
            'log_send' => [
                'hook' => 'thrive_security_cron_send_log',
                'default_enabled' => false,
                'default_interval' => 'daily'
            ]
        ];

        // Process each cron job
        foreach ($cron_jobs as $job_key => $job_config) {
            $enable_option = "thrive_enable_{$job_key}";
            $interval_option = "thrive_{$job_key}_interval";
            
            $enabled = get_option($enable_option, $job_config['default_enabled']);
            $interval = get_option($interval_option, $job_config['default_interval']);
            
            if (!$enabled) {
                wp_clear_scheduled_hook($job_config['hook']);
            } else {
                $current_schedule = wp_get_schedule($job_config['hook']);
                if ($current_schedule !== $interval) {
                    wp_clear_scheduled_hook($job_config['hook']);
                    // Schedule first run after the interval, not immediately
                    $interval_seconds = 0;
                    if ($interval === 'hourly') {
                        $interval_seconds = HOUR_IN_SECONDS;
                    } elseif ($interval === 'twicedaily') {
                        $interval_seconds = 12 * HOUR_IN_SECONDS;
                    } elseif ($interval === 'daily') {
                        $interval_seconds = DAY_IN_SECONDS;
                    } elseif ($interval === 'every_minute') {
                        $interval_seconds = MINUTE_IN_SECONDS;
                    } elseif ($interval === 'monthly') {
                        $interval_seconds = 30 * DAY_IN_SECONDS;
                    } else {
                        // fallback: try to get from cron_schedules
                        $schedules = wp_get_schedules();
                        if (isset($schedules[$interval]['interval'])) {
                            $interval_seconds = (int)$schedules[$interval]['interval'];
                        } else {
                            $interval_seconds = 0;
                        }
                    }
                    $first_run = time() + max(1, $interval_seconds);
                    wp_schedule_event($first_run, $interval, $job_config['hook']);
                }
            }
        }
    }

    /**
     * On plugin deactivation
     */
    public static function on_deactivation() {
        // Clear all scheduled cron hooks
        $cron_hooks = [
            'thrive_security_cron_send_log'
        ];
        
        foreach ($cron_hooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Prevent plugin deactivation
     */
    public static function prevent_deactivation($actions) {
        unset($actions['deactivate']);
        return $actions;
    }

    /**
     * Block uninstall
     */
    public static function prevent_uninstall() {
        THRIVE_SECURITY_HELPER::redirect_with_notice(
            __('Uninstall via admin is disabled.', THRIVE_SECURITY_TEXT_DOMAIN),
            'error',
            admin_url()
        );
    }

    /**
	 * Add settings link on plugins page.
	 *
	 * @param array $links Plugin action links.
	 * @return array Modified links.
	 */
	public static function add_settings_link( $links ) {
		$settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=thrive-settings' ) ),
            esc_html__( 'Settings', THRIVE_SECURITY_TEXT_DOMAIN )
        );

        $log_page = sprintf(
            '<a href="%s">%s</a>',
            esc_url( admin_url( 'admin.php?page=thrive-log' ) ),
            esc_html__( 'Log', THRIVE_SECURITY_TEXT_DOMAIN )
        );

        // Add links to the beginning (unshift) or end (push)
        array_unshift( $links, $log_page );
        array_unshift( $links, $settings_link );

        return $links;
	}

    /**
     * Check if .htaccess protection exists
     */
    public static function check_htaccess_protection() {
        if (!defined('WP_CONTENT_DIR')) {
            return false;
        }

        $htaccess_path = WP_CONTENT_DIR . '/.htaccess';
        
        if (!file_exists($htaccess_path)) {
            return false;
        }

        $contents = file_get_contents($htaccess_path);
        if ($contents === false) {
            return false;
        }

        return strpos($contents, 'thrive-security-block-log.txt') !== false;
    }

    /**
     * Debug .htaccess creation issues
     */
    public static function debug_htaccess_creation() {
        $debug_info = [];
        
        // Check if WP_CONTENT_DIR is defined
        $debug_info['wp_content_dir_defined'] = defined('WP_CONTENT_DIR');
        if (defined('WP_CONTENT_DIR')) {
            $debug_info['wp_content_dir'] = WP_CONTENT_DIR;
            $debug_info['wp_content_dir_exists'] = is_dir(WP_CONTENT_DIR);
            $debug_info['wp_content_dir_writable'] = is_writable(WP_CONTENT_DIR);
        }
        
        // Check .htaccess path
        if (defined('WP_CONTENT_DIR')) {
            $htaccess_path = WP_CONTENT_DIR . '/.htaccess';
            $debug_info['htaccess_path'] = $htaccess_path;
            $debug_info['htaccess_exists'] = file_exists($htaccess_path);
            
            if (file_exists($htaccess_path)) {
                $debug_info['htaccess_readable'] = is_readable($htaccess_path);
                $debug_info['htaccess_writable'] = is_writable($htaccess_path);
                $debug_info['htaccess_size'] = filesize($htaccess_path);
            }
        }
        
        // Check file permissions
        $debug_info['php_user'] = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : 'unknown';
        $debug_info['php_group'] = function_exists('posix_getgrgid') ? posix_getgrgid(posix_getegid())['name'] : 'unknown';
        
        // Log debug info
        THRIVE_SECURITY_HELPER::maybe_debug_log('Thrive: .htaccess debug info: ' . print_r($debug_info, true));
        
        return $debug_info;
    }

    /**
     * Ensure .htaccess is protected
     */
    public static function thrive_security_protect_log_htaccess() {
        // Check if WP_CONTENT_DIR is defined
        if (!defined('WP_CONTENT_DIR')) {
            return false;
        }

        $htaccess_path = WP_CONTENT_DIR . '/.htaccess';
        $rule = "# Protect thrive-security-block-log.txt from public access\n<Files \"thrive-security-block-log.txt\">\n  Order allow,deny\n  Deny from all\n</Files>\n";

        // Check if directory is writable
        if (!is_writable(WP_CONTENT_DIR)) {
            return false;
        }

        try {
            if (file_exists($htaccess_path)) {
                // Check if .htaccess is writable
                if (!is_writable($htaccess_path)) {
                    return false;
                }

                $contents = file_get_contents($htaccess_path);
                if ($contents === false) {
                    return false;
                }

                if (strpos($contents, 'thrive-security-block-log.txt') === false) {
                    // Append the rule if not present
                    $result = file_put_contents($htaccess_path, "\n" . $rule, FILE_APPEND | LOCK_EX);
                    if ($result === false) {
                        return false;
                    }
                }
            } else {
                // Create new .htaccess with the rule
                $result = file_put_contents($htaccess_path, $rule, LOCK_EX);
                if ($result === false) {
                    return false;
                }
            }

            // Verify the file was created/updated
            if (file_exists($htaccess_path)) {
                $final_contents = file_get_contents($htaccess_path);
                if (strpos($final_contents, 'thrive-security-block-log.txt') !== false) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }

        } catch (Exception $e) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Thrive: Exception during .htaccess creation: ' . $e->getMessage());
            return false;
        }
    }
}