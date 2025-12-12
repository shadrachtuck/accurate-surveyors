<?php
/**
 * Plugin Name: Login with Thrive
 * Plugin URI: https://thrive.com
 * Description: Allows users to login to WordPress using their Thrive credentials
 * Version: 1.0.16
 * Author: Thrive
 * Author URI: https://thrive.com
 * Text Domain: thrive-login
 * Domain Path: /languages
 */


// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('THRIVE_LOGIN_VERSION', '1.0.16');
define('THRIVE_LOGIN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('THRIVE_LOGIN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('THRIVE_API_URL', 'https://accounts.thrivedevs.com');

// Include required files
require_once THRIVE_LOGIN_PLUGIN_DIR . 'includes/class-thrive-login.php';
require_once THRIVE_LOGIN_PLUGIN_DIR . 'includes/class-thrive-api.php';
require_once THRIVE_LOGIN_PLUGIN_DIR . 'includes/class-thrive-user-sync.php';
require_once THRIVE_LOGIN_PLUGIN_DIR . 'includes/class-thrive-updater.php';

// Initialize the plugin
function thrive_login_init()
{
    $thrive_login = new Thrive_Login();
    $thrive_login->init();

    // Initialize the updater
    $thrive_updater = new Thrive_Updater();

}

add_action('plugins_loaded', 'thrive_login_init');

// Activation hook
register_activation_hook(__FILE__, 'thrive_login_activate');
function thrive_login_activate()
{
    // Create necessary database tables or options
    add_option('thrive_login_settings', [
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => wp_login_url() . '?thrive_login=callback',
        'github_username' => 'glossyit',
        'github_repo' => 'LoginWithThrives_plugin',
        'auto_update' => 1, // Enable auto-updates by default
        'api_url' => THRIVE_API_URL,
        'version' => THRIVE_LOGIN_VERSION,
        'github_token' => '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',
    ]);
}


function thrive_login_deactivate()
{
    // Clean up if needed
    Thrive_Updater::deactivate();
}