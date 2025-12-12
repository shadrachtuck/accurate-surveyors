<?php
/**
 * The authentication functionality of the plugin.
 */
class WP_2FA_Auth {

    /**
     * Start the 2FA verification process after successful login.
     *
     * @param string $user_login The username.
     * @param WP_User $user The user object.
     */
    public function start_2fa_verification(string $user_login, WP_User $user): void {
        // Check if 2FA is enabled
        if (!$this->is_2fa_enabled_for_user($user)) {
            return;
        }

        // Clear any existing completion timestamp
        delete_user_meta($user->ID, 'wp_2fa_completed');

        // Generate and send OTP
        $otp = $this->generate_otp();
        $this->store_otp($user->ID, $otp);
        $this->send_otp_email($user, $otp);

        // Start the verification session
        $this->start_verification_session($user->ID);

        // Redirect to the verification page
        wp_redirect(add_query_arg('action', 'wp_2fa_verify', wp_login_url()));
        exit;
    }

    /**
     * Check if 2FA is enabled for a user.
     *
     * @param WP_User $user The user object.
     * @return bool Whether 2FA is enabled for the user.
     */
    private function is_2fa_enabled_for_user($user) {
        // Check if 2FA is globally enabled
        $enabled = get_option('wp_2fa_enabled', false);
        if (!$enabled) {
            return false;
        }

        // Check if user has thrive_user_id - if so, exempt from 2FA
        $thrive_user_id = get_user_meta($user->ID, 'thrive_user_id', true);
        if (!empty($thrive_user_id)) {
            return false; // Exempt from 2FA
        }

        // Check if 2FA is enabled for the user's role
        $enabled_roles = get_option('wp_2fa_user_roles', array('administrator'));
        $user_roles = $user->roles;

        foreach ($user_roles as $role) {
            if (in_array($role, $enabled_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a random OTP.
     *
     * @return string The generated OTP.
     */
    private function generate_otp() {
        return sprintf('%06d', wp_rand(0, 999999));
    }

    /**
     * Store the OTP for a user.
     *
     * @param int $user_id The user ID.
     * @param string $otp The OTP.
     */
    private function store_otp($user_id, $otp) {
        $expiry = get_option('wp_2fa_otp_expiry', 10);
        $expiry_time = time() + ($expiry * MINUTE_IN_SECONDS);

        update_user_meta($user_id, 'wp_2fa_otp', $otp);
        update_user_meta($user_id, 'wp_2fa_otp_expiry', $expiry_time);
    }

    /**
     * Send the OTP to the user's email.
     *
     * @param WP_User $user The user object.
     * @param string $otp The OTP.
     */
    private function send_otp_email($user, $otp) {
        $to = $user->user_email;
        $subject = sprintf(__('[%s] Your login verification code', 'wp-2fa'), get_bloginfo('name'));

        // Get the HTML email template
        $html_message = wp_2fa_get_email_template(
            $user->display_name,
            get_bloginfo('name'),
            $otp,
            get_option('wp_2fa_otp_expiry', 10)
        );

        // Create plain text fallback
        $plain_message = sprintf(
            __('Hello %s,', 'wp-2fa') . "\n\n" .
            __('You are logging in to %s.', 'wp-2fa') . "\n\n" .
            __('Your verification code is: %s', 'wp-2fa') . "\n\n" .
            __('This code will expire in %d minutes.', 'wp-2fa') . "\n\n" .
            __('If you did not attempt to log in, please contact your site administrator immediately.', 'wp-2fa'),
            $user->display_name,
            get_bloginfo('name'),
            $otp,
            get_option('wp_2fa_otp_expiry', 10)
        );

        // Set up email headers for HTML email
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );

        // Send the HTML email
        wp_mail($to, $subject, $html_message, $headers);
    }

    /**
     * Start the verification session.
     *
     * @param int $user_id The user ID.
     */
    private function start_verification_session($user_id) {
        // Use a unique session name to avoid conflicts
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }

        $verification_token = wp_generate_password(32, false);

        // Store the verification token in a cookie
        $secure = is_ssl();
        $httponly = true;

        setcookie('wp_2fa_verification', $verification_token, time() + HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, $secure, $httponly);

        // Store the verification token and user ID in the session
        $_SESSION['wp_2fa_verification'] = array(
            'token' => $verification_token,
            'user_id' => $user_id,
            'created' => time(),
        );
    }

    /**
     * Check the 2FA status before authentication.
     *
     * @param string $username The username.
     * @param string $password The password.
     */
    public function check_2fa_status(string $username, string $password): void {
        // This is a hook that runs before authentication
        // We'll use it to set up the session if needed
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
    }

    /**
     * Check admin access and redirect to 2FA verification if needed.
     */
    public function check_admin_access(): void {
        // Only check on admin pages
        if (!is_admin()) {
            return;
        }

        // Skip check for AJAX requests to avoid infinite loops
        if (wp_doing_ajax()) {
            return;
        }

        // Skip check for the 2FA settings page to allow configuration
        if (isset($_GET['page']) && 'wp-2fa-settings' === $_GET['page']) {
            return;
        }

        // Get current user
        $user = wp_get_current_user();

        // If no user is logged in, let WordPress handle the redirect
        if (!$user->exists()) {
            return;
        }

        // Check if 2FA is enabled for this user
        if (!$this->is_2fa_enabled_for_user($user)) {
            return;
        }

        // Check if user has completed 2FA verification
        if (!$this->has_user_completed_2fa($user->ID)) {
            // Start 2FA verification process
            $this->start_2fa_verification_for_admin($user);
        }
    }

    /**
     * Check if a user has completed 2FA verification.
     *
     * @param int $user_id The user ID.
     * @return bool Whether the user has completed 2FA.
     */
    private function has_user_completed_2fa($user_id) {
        // Check if user has a valid 2FA completion timestamp
        $completion_time = get_user_meta($user_id, 'wp_2fa_completed', true);

        if (!$completion_time) {
            return false;
        }

        // Check if the completion is still valid (24 hours)
        $expiry_time = $completion_time + (24 * HOUR_IN_SECONDS);

        return time() < $expiry_time;
    }

    /**
     * Start 2FA verification process for admin access.
     *
     * @param WP_User $user The user object.
     */
    private function start_2fa_verification_for_admin($user) {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
        
        // Check if verification session already exists
        if (isset($_SESSION['wp_2fa_verification']) && isset($_COOKIE['wp_2fa_verification'])) {
            // If session exists, redirect to verification page
            wp_redirect(add_query_arg('action', 'wp_2fa_verify', wp_login_url()));
            exit;
        }

        // Clear any existing completion timestamp
        delete_user_meta($user->ID, 'wp_2fa_completed');

        // Generate and send OTP
        $otp = $this->generate_otp();
        $this->store_otp($user->ID, $otp);
        $this->send_otp_email($user, $otp);

        // Start the verification session
        $this->start_verification_session($user->ID);

        // Redirect to the verification page
        wp_redirect(add_query_arg('action', 'wp_2fa_verify', wp_login_url()));
        exit;
    }

    /**
     * Verify the OTP.
     */
    public function verify_otp(): void {
        // Check if we're on the verification page
        if (!isset($_GET['action']) || 'wp_2fa_verify' !== $_GET['action']) {
            return;
        }

        // If resend was requested
        if (isset($_GET['resend']) && $_GET['resend'] === '1') {
            $this->resend_otp();
            return;
        }

        // If the form was submitted
        if (isset($_POST['wp_2fa_otp'])) {
            $this->process_otp_verification();
            return;
        }

        // Display the OTP verification form
        $this->display_otp_form();
        exit;
    }

    /**
     * Resend the OTP code.
     */
    private function resend_otp() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
        
        // Check if the verification session exists
        if (!isset($_SESSION['wp_2fa_verification']) || !isset($_COOKIE['wp_2fa_verification'])) {
            wp_redirect(wp_login_url() . '?login=failed');
            exit;
        }

        // Check if the verification token matches
        if ($_SESSION['wp_2fa_verification']['token'] !== $_COOKIE['wp_2fa_verification']) {
            wp_redirect(wp_login_url() . '?login=failed');
            exit;
        }

        $user_id = $_SESSION['wp_2fa_verification']['user_id'];
        $user = get_user_by('id', $user_id);

        if (!$user) {
            wp_redirect(wp_login_url() . '?login=failed');
            exit;
        }

        // Generate and send new OTP
        $otp = $this->generate_otp();
        $this->store_otp($user_id, $otp);
        $this->send_otp_email($user, $otp);

        // Redirect back to the verification page with a message
        wp_redirect(add_query_arg(array('action' => 'wp_2fa_verify', 'resent' => '1'), wp_login_url()));
        exit;
    }

    /**
     * Process the OTP verification.
     */
    private function process_otp_verification() {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
        
        // Verify nonce
        if (!isset($_POST['wp_2fa_nonce']) || !wp_verify_nonce($_POST['wp_2fa_nonce'], 'wp_2fa_verify_action')) {
            wp_redirect(wp_login_url() . '?login=failed');
            exit;
        }

        // Check if the verification session exists
        if (!isset($_SESSION['wp_2fa_verification']) || !isset($_COOKIE['wp_2fa_verification'])) {
            $this->display_otp_form('session_expired');
            exit;
        }

        // Check if the verification token matches
        if ($_SESSION['wp_2fa_verification']['token'] !== $_COOKIE['wp_2fa_verification']) {
            $this->display_otp_form('session_expired');
            exit;
        }

        $user_id = $_SESSION['wp_2fa_verification']['user_id'];
        $user = get_user_by('id', $user_id);

        if (!$user) {
            $this->display_otp_form('invalid_user');
            exit;
        }

        $submitted_otp = sanitize_text_field($_POST['wp_2fa_otp']);
        $stored_otp = get_user_meta($user_id, 'wp_2fa_otp', true);
        $expiry_time = get_user_meta($user_id, 'wp_2fa_otp_expiry', true);

        // Check if the OTP is expired
        if (time() >= $expiry_time) {
            $this->display_otp_form('expired');
            exit;
        }

        // Check if the OTP is valid
        if ($submitted_otp !== $stored_otp) {
            $this->display_otp_form('invalid');
            exit;
        }

        // OTP is valid, log the user in
        wp_set_auth_cookie($user_id, true);

        // Set 2FA completion timestamp
        update_user_meta($user_id, 'wp_2fa_completed', time());

        // Clean up
        delete_user_meta($user_id, 'wp_2fa_otp');
        delete_user_meta($user_id, 'wp_2fa_otp_expiry');
        unset($_SESSION['wp_2fa_verification']);
        setcookie('wp_2fa_verification', '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);

        // Redirect to appropriate page based on user capabilities
        $redirect_to = user_can($user_id, 'manage_options') ? admin_url() : home_url();

        // Allow filtering the redirect URL
        $redirect_to = apply_filters('wp_2fa_login_redirect', $redirect_to, $user);

        wp_redirect($redirect_to);
        exit;
    }

    /**
     * Display the OTP verification form with modern UI.
     *
     * @param string $error The error message to display.
     */
    private function display_otp_form($error = '') {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
        
        // Enqueue the login styles and scripts
        wp_enqueue_style('wp-2fa-login', WP_2FA_PLUGIN_URL . 'assets/css/wp-2fa-login.css', array(), WP_2FA_VERSION);
        wp_enqueue_script('wp-2fa-login', WP_2FA_PLUGIN_URL . 'assets/js/wp-2fa-login.js', array(), WP_2FA_VERSION, true);

        // Calculate actual remaining time
        $remaining_seconds = 0;
        $expiry_minutes = get_option('wp_2fa_otp_expiry', 10);

        if (isset($_SESSION['wp_2fa_verification']['user_id'])) {
            $user_id = $_SESSION['wp_2fa_verification']['user_id'];
            $expiry_time = get_user_meta($user_id, 'wp_2fa_otp_expiry', true);

            if ($expiry_time) {
                $remaining_seconds = max(0, $expiry_time - time());
            }
        }

        // If no remaining time or expired, show full time (for new OTP)
        if ($remaining_seconds <= 0) {
            $remaining_seconds = $expiry_minutes * 60;
        }

        $remaining_minutes = floor($remaining_seconds / 60);
        $remaining_secs = $remaining_seconds % 60;
        $timer_display = sprintf('%d:%02d', $remaining_minutes, $remaining_secs);

        $message = '';
        $error_message = '';

        if (isset($_GET['resent']) && $_GET['resent'] === '1') {
            $message = '<div class="wp-2fa-message">' . __('A new verification code has been sent to your email.', 'wp-2fa') . '</div>';
        }

        // Set appropriate error messages
        if ($error === 'invalid') {
            $error_message = __('Invalid verification code. Please try again.', 'wp-2fa');
        } elseif ($error === 'expired') {
            $error_message = __('Your verification code has expired. Please request a new code.', 'wp-2fa');
        } elseif ($error === 'session_expired') {
            $error_message = __('Your verification session has expired. Please log in again.', 'wp-2fa');
        } elseif ($error === 'invalid_user') {
            $error_message = __('Invalid user session. Please log in again.', 'wp-2fa');
        }

        $error_html = $error_message ? '<div class="wp-2fa-error">' . esc_html($error_message) . '</div>' : '';

        login_header(__('Thrive 2FA', 'wp-2fa'), '', $error_html ?: $message);

        ?>
        <div class="wp-2fa-login-wrap">
            <div class="wp-2fa-login-header">
                <h1><?php echo esc_html__('Verification Required', 'wp-2fa'); ?></h1>
                <p><?php echo esc_html__('Please enter the verification code sent to your email.', 'wp-2fa'); ?></p>
            </div>

            <form name="wp_2fa_verification_form" id="wp_2fa_verification_form" class="wp-2fa-login-form" action="<?php echo esc_url(site_url('wp-login.php?action=wp_2fa_verify', 'login_post')); ?>" method="post">
                <?php wp_nonce_field('wp_2fa_verify_action', 'wp_2fa_nonce'); ?>
                <label for="wp_2fa_otp"><?php echo esc_html__('Verification Code', 'wp-2fa'); ?></label>
                <input type="text" name="wp_2fa_otp" id="wp_2fa_otp" class="wp-2fa-otp-input" value="" size="6" maxlength="6" autocomplete="off" autocapitalize="off" />

                <?php if ($error !== 'session_expired' && $error !== 'invalid_user'): ?>
                    <p class="wp-2fa-description">
                        <?php echo esc_html__('Code expires in:', 'wp-2fa'); ?>
                        <span id="wp-2fa-timer" data-expiry="<?php echo esc_attr($remaining_seconds); ?>"><?php echo esc_html($timer_display); ?></span>
                    </p>

                    <button type="submit" name="wp-submit" id="wp-submit" class="wp-2fa-submit-button">
                        <?php echo esc_html__('Verify', 'wp-2fa'); ?>
                    </button>
                <?php else: ?>
                    <button type="button" id="wp-2fa-login-again" class="wp-2fa-submit-button" onclick="window.location.href='<?php echo esc_url(wp_login_url()); ?>'">
                        <?php echo esc_html__('Back to Login', 'wp-2fa'); ?>
                    </button>
                <?php endif; ?>
            </form>

            <div class="wp-2fa-login-footer">
                <?php if ($error !== 'session_expired' && $error !== 'invalid_user'): ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" id="wp-2fa-back-to-login">
                        <?php echo esc_html__('← Back to login', 'wp-2fa'); ?>
                    </a>

                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'wp_2fa_verify', 'resend' => '1'), wp_login_url())); ?>" id="wp-2fa-resend" <?php echo $error === 'expired' ? '' : 'style="display: none; float: right;"'; ?>>
                        <?php echo esc_html__('Resend code', 'wp-2fa'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php

        login_footer();
    }

    /**
     * Clear 2FA status on logout.
     */
    public function clear_2fa_status(): void {
        $user = wp_get_current_user();
        if ($user->exists()) {
            delete_user_meta($user->ID, 'wp_2fa_completed');
            delete_user_meta($user->ID, 'wp_2fa_otp');
            delete_user_meta($user->ID, 'wp_2fa_otp_expiry');
        }

        // Clear session data
        if (session_status() === PHP_SESSION_NONE) {
            session_name('wp_2fa_session');
            session_start();
        }
        
        if (isset($_SESSION['wp_2fa_verification'])) {
            unset($_SESSION['wp_2fa_verification']);
        }

        // Clear cookie
        setcookie('wp_2fa_verification', '', time() - HOUR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
    }
}