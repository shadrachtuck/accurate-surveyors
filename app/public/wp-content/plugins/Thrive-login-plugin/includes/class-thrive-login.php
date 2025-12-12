<?php

/**
 * Main Thrive Login class
 */
class Thrive_Login
{
    /**
     * Thrive API handler
     *
     * @var Thrive_API
     */
    private $api;

    /**
     * User sync handler
     *
     * @var Thrive_User_Sync
     */
    private $user_sync;

    /**
     * Initialize the class
     */
    public function init()
    {
        $this->api = new Thrive_API();
        $this->user_sync = new Thrive_User_Sync();

        // Add login button to WordPress login form
        add_action('login_footer', array($this, 'add_login_button_via_js'));

        // Add settings page
        add_action('admin_menu', array($this, 'add_settings_page'));

        // Register settings
        add_action('admin_init', array($this, 'register_settings'));

        // Handle login callback
        add_action('init', array($this, 'handle_login_callback'));

        // Enqueue scripts and styles
        add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Add Thrive login button to WordPress login form
     */
    public function add_login_button()
    {
        include THRIVE_LOGIN_PLUGIN_DIR . 'templates/login-button.php';
    }

    /**
     * Handle login callback from Thrive
     */
    public function handle_login_callback()
    {

        if (isset($_GET['thrive_login']) && $_GET['thrive_login'] === 'callback') {

            if (isset($_GET['token'])) {
                $token = sanitize_text_field($_GET['token']);
                $user_data = $this->api->get_user_data($token);

                //die(print_r($user_data));

                if ($user_data) {
                    // Create or update WordPress user
                    $wp_user_id = $this->user_sync->sync_user($user_data);

                    if ($wp_user_id) {
                        // Set 8-hour expiration
                        add_filter('auth_cookie_expiration', 'set_8_hour_login_expiration', 10, 3);
                        function set_8_hour_login_expiration($expiration, $user_id, $remember)
                        {
                            return 8 * HOUR_IN_SECONDS;
                        }

                        wp_set_auth_cookie($wp_user_id, true);
                        remove_filter('auth_cookie_expiration', 'set_8_hour_login_expiration', 10);

                        wp_redirect(admin_url());
                        exit;
                    }
                }
            }

            // If we get here, something went wrong
            wp_redirect(wp_login_url() . '?thrive_login_error=1');
            exit;
        }

        if (isset($_GET['thrive_login']) && $_GET['thrive_login'] === 'start') {
            $redirect_url = urlencode(site_url('wp-login.php?thrive_login=callback'));
            $auth_url = THRIVE_API_URL . '/login?redirect_url=' . $redirect_url;

            wp_redirect($auth_url);
            exit;
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('thrive-login', THRIVE_LOGIN_PLUGIN_URL . 'assets/css/thrive-login.css', array(), THRIVE_LOGIN_VERSION);
        wp_enqueue_script('thrive-login', THRIVE_LOGIN_PLUGIN_URL . 'assets/js/thrive-login.js', array('jquery'), THRIVE_LOGIN_VERSION, true);
    }

    /**
     * Add settings page
     */
    public function add_settings_page()
    {
        add_options_page(
            __('Thrive Login Settings', 'thrive-login'),
            __('Thrive Login', 'thrive-login'),
            'manage_options',
            'thrive-login',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings()
    {
        register_setting('thrive_login_settings', 'thrive_login_settings');

        add_settings_section(
            'thrive_login_settings_section',
            __('Thrive API Settings', 'thrive-login'),
            array($this, 'settings_section_callback'),
            'thrive-login'
        );

        add_settings_field(
            'thrive_api_url',
            __('Thrive API URL', 'thrive-login'),
            array($this, 'api_url_callback'),
            'thrive-login',
            'thrive_login_settings_section'
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback()
    {
        echo '<p>' . __('Configure your Thrive API settings below:', 'thrive-login') . '</p>';
    }

    /**
     * API URL field callback
     */
    public function api_url_callback()
    {
        $options = get_option('thrive_login_settings');
        $api_url = isset($options['api_url']) ? $options['api_url'] : THRIVE_API_URL;

        echo '<input type="text" id="api_url" name="thrive_login_settings[api_url]" value="' . esc_attr($api_url) . '" class="regular-text">';
    }

    /**
     * Render settings page
     */
    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('thrive_login_settings');
                do_settings_sections('thrive-login');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }


    public function add_login_button_via_js()
    {
        ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
                var loginForm = document.getElementById('loginform');
                if (loginForm) {
                    var submitDiv = loginForm.querySelector('p.submit');
                    if (submitDiv) {
                        var thriveContainer = document.createElement('div');
                        thriveContainer.className = 'thrive-login-container';
                        thriveContainer.innerHTML = `
                    <p class="thrive-login-or"><?php _e('- OR -', 'thrive-login'); ?></p>
                    <a href="<?php echo esc_url(site_url('wp-login.php?thrive_login=start')); ?>" class="button button-primary thrive-login-button">
                     <span class="thrive-login-icon"></span>
                        <span class="thrive-login-text"><?php _e('Login with Thrive', 'thrive-login'); ?></span>
                       
                    </a>
                `;

                        // Insert after the submit button
                        submitDiv.parentNode.insertBefore(thriveContainer, submitDiv.nextSibling);
                    }
                }
            });
        </script>
        <?php
    }
}