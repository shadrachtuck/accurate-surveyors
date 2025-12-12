<?php
/**
 * Plugin Name: Thrive 2FA
 * Plugin URI: https://github.com/glossyit/Thrive2FAPlugin
 * Description: Adds two-factor authentication for WordPress admin logins using email OTP.
 * Version: 1.0.10
 * Author: Thrive
 * Author URI: https://example.com
 * Update URI: https://github.com/glossyit/Thrive2FAPlugin
 * Text Domain: wp-2fa
 * Domain Path: /languages
 * License: GPL v2 or later
 * Requires PHP: 7.4
 * Requires at least: 5.0
 * Tested up to: 6.4
 */


// Check PHP version
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Thrive 2FA Plugin Error:</strong> ';
        echo 'This plugin requires PHP version 7.4 or higher. Your current PHP version is ' . PHP_VERSION . '. ';
        echo 'Please contact your hosting provider to upgrade PHP.';
        echo '</p></div>';
    });
    return;
}

// Check WordPress version
if (version_compare(get_bloginfo('version'), '5.0', '<')) {
    add_action('admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>Thrive 2FA Plugin Error:</strong> ';
        echo 'This plugin requires WordPress version 5.0 or higher. Your current WordPress version is ' . get_bloginfo('version') . '. ';
        echo 'Please update WordPress to use this plugin.';
        echo '</p></div>';
    });
    return;
}

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WP_2FA_VERSION', '1.0.10');
define('WP_2FA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_2FA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WP_2FA_PLUGIN_DIR . 'includes/class-wp-2fa.php';
require_once WP_2FA_PLUGIN_DIR . 'includes/class-wp-2fa-updater.php';
require_once WP_2FA_PLUGIN_DIR . 'templates/email-2fa-verification.php';

// Initialize the plugin
function run_wp_2fa()
{
    $plugin = new WP_2FA();
    $plugin->run();
    // Initialize the updater
    $thrive_updater = new Thrive_2FA_Updater();
}

run_wp_2fa();

// Activation hook
register_activation_hook(__FILE__, 'thrive_2FA_activate');
function thrive_2FA_activate()
{
    if (get_option('thrive_2FA_settings') === false) {
        add_option('thrive_2FA_settings', [
            'github_username' => 'glossyit',
            'github_repo' => 'Thrive2FAPlugin',
            'auto_update' => 1,
            'version' => WP_2FA_VERSION,
            'github_token' => '',
            'wp_2fa_enabled' => true,
            'wp_2fa_otp_expiry' => 10,
        ]);
    }
    if (get_option('wp_2fa_user_roles') === false) {
        add_option('wp_2fa_user_roles', array('administrator'));
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'thrive_2FA_deactivate');
function thrive_2FA_deactivate()
{
    // Clean up if needed
    Thrive_2FA_Updater::deactivate();
}