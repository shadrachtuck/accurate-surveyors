<?php
defined('ABSPATH') || exit;

/**
 * Class THRIVE_SECURITY_ACCESS_CONTROLLER
 *
 * Handles admin access restrictions based on IP and user role.
 * Applies DISALLOW_FILE_MODS and logs unauthorized access attempts.
 */
class THRIVE_SECURITY_ACCESS_CONTROLLER {
    /**
     * Initialize access control hooks
     */
    public static function init() {
        // Always initialize so access control can work
       add_action('admin_init', [self::class, 'enforce'], 1);
    }

    /**
     * Enforce access restrictions for wp-admin.
     */
    public static function enforce() {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            return; // Exit early if blocking is disabled
        }
        
        // Skip for AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }

        $remote_config = THRIVE_SECURITY_CONFIG_MANAGER::get_config_if_needed();
        
        if (empty($remote_config)) {
            error_log('Thrive: Empty config in access controller');
            return;
        }

        // Get current page
        $current_page = THRIVE_SECURITY_HELPER::get_current_admin_page();

        // Restrict access to sensitive admin pages
        if(THRIVE_SECURITY_HELPER::is_blocked_admin()) {
            // Check if current page is restricted
            $is_restricted = in_array($current_page, $remote_config['restricted_pages'] ?? [], true);
            
            if ($is_restricted) {
                // Log the access attempt
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('access-denied', $current_page);
                }
                
                // Redirect with notice
                THRIVE_SECURITY_HELPER::display_notice(
                    sprintf(__('Access denied to %s – for blacklisted administrators.', THRIVE_SECURITY_TEXT_DOMAIN), $current_page),
                    'error',
                    admin_url()
                );
            }

            // Special handling for log page
            if (isset($_GET['page']) && $_GET['page'] === 'thrive-log') {
                // Log access denied to the log page
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('log-page-access-denied', 'thrive-log');
                }
                THRIVE_SECURITY_HELPER::display_notice(
                    __('Access denied to Thrive Block Log – for blacklisted administrators.', THRIVE_SECURITY_TEXT_DOMAIN),
                    'error',
                    admin_url()
                );
            }

            // Special handling for settings page
            if (isset($_GET['page']) && $_GET['page'] === 'thrive-settings') {
                // Log access denied to the settings page
                if (class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
                    THRIVE_SECURITY_LOG_MANAGER::log('settings-page-access-denied', 'thrive-settings');
                }
                THRIVE_SECURITY_HELPER::display_notice(
                    __('Access denied to Thrive Settings – for blacklisted administrators.', THRIVE_SECURITY_TEXT_DOMAIN),
                    'error',
                    admin_url()
                );
            }
        }
    }
}