<?php
/**
 * Plugin Name: Thrive Security
 * Plugin URI: https://www.thrivewebdesigns.com
 * Description: Secure access control with plugin/theme blocking, alerts, and filtered log viewer.
 * Version: 1.0.24
 * Author: Thrive Web Designs
 * Author URI: https://www.thrivewebdesigns.com
 * Text Domain: thrive-security
 * Domain Path: /languages
 * Requires at least: 5.4
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 *
 * @package THRIVE_SECURITY
 */

defined('ABSPATH') || exit;

// Define constants
define('THRIVE_SECURITY_VERSION', '1.0.24');
define('THRIVE_SECURITY_TEXT_DOMAIN', 'thrive-security');
define('THRIVE_SECURITY_CONFIG_CACHE_KEY', 'thrive_config_cache');
define('THRIVE_SECURITY_CONFIG_VERSION_KEY', 'thrive_config_version');
define('THRIVE_SECURITY_CONFIG_FALLBACK_KEY', 'thrive_last_good_config');
define('THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY', 'thrive_last_time_fetch');
define('THRIVE_SECURITY_CONFIG_API_URL', 'https://accounts.thrivedevs.com/api/all-in-one');
define('THRIVE_SECURITY_PATH', plugin_dir_path(__FILE__));
define('THRIVE_SECURITY_URL', plugin_dir_url(__FILE__));
define('THRIVE_SECURITY_PLUGIN_FILE', __FILE__);

// Require files with existence checks
$required_files = [
    'class-bootstrap.php',
    'class-config-manager.php',
    'class-settings.php',
    'class-access-controller.php',
    'class-plugin-theme-manager.php',
    'class-alert-manager.php',
    'class-log-manager.php',
    'class-helper.php',
    'class-log-table.php',
    'class-log-page.php',
    'class-thrive-security-updater.php'
];

foreach ($required_files as $file) {
    $file_path = THRIVE_SECURITY_PATH . 'includes/' . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } elseif (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('Thrive: Missing file ' . $file_path);
    }
}

// Main Class
final class THRIVE_SECURITY_ADMIN_ACCESS
{
    public static function init()
    {
        // Load text domain
        add_action('plugins_loaded', function () {
            load_plugin_textdomain(THRIVE_SECURITY_TEXT_DOMAIN, false, THRIVE_SECURITY_PATH . 'languages');
        });


        // Register custom cron schedules
        add_filter('cron_schedules', function ($schedules) {
            if (!isset($schedules['every_minute'])) {
                $schedules['every_minute'] = [
                    'interval' => 60,
                    'display' => __('Every Minute')
                ];
            }

            // Register a custom schedule for monthly if not already present
            if (!isset($schedules['monthly'])) {
                $schedules['monthly'] = [
                    'interval' => 30 * DAY_IN_SECONDS, // Approximately 30 days
                    'display' => __('Monthly')
                ];
            }

            // Register thrice daily schedule for background plugin management
            if (!isset($schedules['thrice_daily'])) {
                $schedules['thrice_daily'] = [
                    'interval' => 8 * HOUR_IN_SECONDS, // Every 8 hours (3 times per day)
                    'display' => __('Three Times Daily')
                ];
            }

            return $schedules;
        });

        // Initialize core classes only
        if (class_exists('THRIVE_SECURITY_BOOTSTRAP')) {
            THRIVE_SECURITY_BOOTSTRAP::init();
        }

        if (class_exists('THRIVE_SECURITY_ACCESS_CONTROLLER')) {
            THRIVE_SECURITY_ACCESS_CONTROLLER::init();
        }

        // Initialize admin-only classes only when needed

        if (class_exists('THRIVE_SECURITY_PLUGIN_THEMES_MANAGER')) {
            THRIVE_SECURITY_PLUGIN_THEMES_MANAGER::init();
        }

        if (class_exists('THRIVE_SECURITY_LOG_PAGE')) {
            THRIVE_SECURITY_LOG_PAGE::init();
        }

        if (class_exists('THRIVE_SECURITY_SETTINGS')) {
            THRIVE_SECURITY_SETTINGS::init();
        }

        // Initialize updater if class exists
        if (class_exists('Thrive_Security_Updater')) {
            new Thrive_Security_Updater();
        }


        // Display activation notice
        add_action('admin_notices', function () {
            if (get_option('thrive_activation_notice')) {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo '<strong>Thrive Security</strong> has been activated successfully! ';
                echo 'The plugin is now protecting your WordPress installation.';
                echo '</p></div>';
                delete_option('thrive_activation_notice');
            }
        });
    }
}

// Initiate
THRIVE_SECURITY_ADMIN_ACCESS::init();

// On Plugin Activate
register_activation_hook(__FILE__, function () {
    try {
        // Initialize default settings
        if (!get_option('thrive_blocking_enabled')) {
            update_option('thrive_blocking_enabled', true);
        }

        if (!get_option('thrive_config_api_url')) {
            // You should replace this with your actual API endpoint
            update_option('thrive_config_api_url', THRIVE_SECURITY_CONFIG_API_URL);
        }

        // Set default intervals for cron jobs
        update_option('thrive_config_refresh_interval', 'twicedaily');
        update_option('thrive_block_plugins_interval', 'twicedaily');
        update_option('thrive_install_plugins_interval', 'twicedaily');

        // Add activation notice
        add_option('thrive_activation_notice', true);
        add_option('thrive_security_settings', [
            'auto_update' => 1,
            'github_username' => 'glossyit',
            'github_repo' => 'ThriveSecurityPlugin',
        ]);

        // Schedule background plugin management
        if (class_exists('THRIVE_SECURITY_PLUGIN_THEMES_MANAGER')) {
            THRIVE_SECURITY_PLUGIN_THEMES_MANAGER::schedule_background_management();
        }

        // Trigger custom activation action for other components
        do_action('thrive_security_activated');

        if (class_exists('THRIVE_SECURITY_BOOTSTRAP')) {
            THRIVE_SECURITY_BOOTSTRAP::on_activation();
        }

    } catch (Exception $e) {
        if (function_exists('error_log')) {
            error_log('Thrive: Activation error - ' . $e->getMessage());
        }
    }
});

// On Plugin deactivate
register_deactivation_hook(__FILE__, function () {
    if (class_exists('THRIVE_SECURITY_BOOTSTRAP')) {
        THRIVE_SECURITY_BOOTSTRAP::on_deactivation();
    }

    // Clean up background management cron job
    if (class_exists('THRIVE_SECURITY_PLUGIN_THEMES_MANAGER')) {
        THRIVE_SECURITY_PLUGIN_THEMES_MANAGER::unschedule_background_management();
    }

    // Trigger custom deactivation action for other components
    do_action('thrive_security_deactivated');

    // Clean up updater if class exists
    if (class_exists('Thrive_Security_Updater')) {
        Thrive_Security_Updater::deactivate();
    }
});