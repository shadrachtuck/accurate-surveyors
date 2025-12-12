<?php
/**
 * The core plugin class.
 */
class WP_2FA {

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        // Start session on 'init' hook with high priority
        add_action('init', array($this, 'maybe_start_session'), 1);
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_auth_hooks();
        $this->define_login_hooks();
        $this->define_security_hooks();
    }

    /**
     * Start session if not already started, on the correct hook.
     */
    public function maybe_start_session() {
        // Prevent session during WP Cron
        if (defined('DOING_CRON') && DOING_CRON) {
            return;
        }

        // Avoid warning if headers already sent
        if (headers_sent($file, $line)) {
            error_log("2FA Session not started. Headers already sent in $file on line $line");
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies(): void {
        require_once WP_2FA_PLUGIN_DIR . 'includes/class-wp-2fa-admin.php';
        require_once WP_2FA_PLUGIN_DIR . 'includes/class-wp-2fa-auth.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks(): void {
        $plugin_admin = new WP_2FA_Admin();
        add_action('admin_menu', array($plugin_admin, 'add_settings_page'));
        add_action('admin_init', array($plugin_admin, 'register_settings'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
    }

    /**
     * Register all of the hooks related to authentication.
     */
    private function define_auth_hooks(): void {
        $plugin_auth = new WP_2FA_Auth();
        add_action('wp_login', array($plugin_auth, 'start_2fa_verification'), 10, 2);
        add_action('wp_authenticate', array($plugin_auth, 'check_2fa_status'), 10, 2);
        add_action('login_form_wp_2fa_verify', array($plugin_auth, 'verify_otp'));
    }

    /**
     * Register hooks related to the login page.
     */
    private function define_login_hooks(): void {
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_styles'));
    }

    /**
     * Register security hooks to prevent admin access without 2FA completion.
     */
    private function define_security_hooks(): void {
        $plugin_auth = new WP_2FA_Auth();
        add_action('admin_init', array($plugin_auth, 'check_admin_access'));
        add_action('wp_ajax_nopriv_wp_2fa_verify', array($plugin_auth, 'verify_otp'));
        add_action('wp_logout', array($plugin_auth, 'clear_2fa_status'));
    }

    /**
     * Enqueue styles for the login page.
     */
    public function enqueue_login_styles(): void {
        // Only load on the 2FA verification page
        if (isset($_GET['action']) && 'wp_2fa_verify' === $_GET['action']) {
            wp_enqueue_style('wp-2fa-login', WP_2FA_PLUGIN_URL . 'assets/css/wp-2fa-login.css', array(), WP_2FA_VERSION);
            wp_enqueue_script('wp-2fa-login', WP_2FA_PLUGIN_URL . 'assets/js/wp-2fa-login.js', array(), WP_2FA_VERSION, true);
        }
    }

    /**
     * Run the plugin.
     */
    public function run(): void {
        // Plugin is now running
    }
}