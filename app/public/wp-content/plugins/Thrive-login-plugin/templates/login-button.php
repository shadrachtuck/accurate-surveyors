<?php
/**
 * Template for the Thrive login button
 */
?>
<div class="thrive-login-container">
    <p class="thrive-login-or"><?php _e('- OR -', 'thrive-login'); ?></p>
    <a href="<?php echo esc_url(site_url('wp-login.php?thrive_login=start')); ?>" class="button button-primary thrive-login-button">
         <span class="thrive-login-icon"></span>
        <span class="thrive-login-text"><?php _e('Login with Thrive', 'thrive-login'); ?></span>
       
    </a>
    
    <?php if (isset($_GET['thrive_login_error'])): ?>
        <p class="thrive-login-error">
            <?php 
            $error_code = sanitize_text_field($_GET['thrive_login_error']);
            switch ($error_code) {
                case 'invalid_token':
                    _e('Invalid or expired authentication token. Please try again.', 'thrive-login');
                    break;
                case 'sync_failed':
                    _e('Failed to create or update user account. Please contact support.', 'thrive-login');
                    break;
                case 'invalid_user_data':
                    _e('Invalid user data received from Thrive. Please try again.', 'thrive-login');
                    break;
                case 'no_token':
                    _e('No authentication token received. Please try again.', 'thrive-login');
                    break;
                default:
                    _e('Error logging in with Thrive. Please try again.', 'thrive-login');
            }
            ?>
        </p>
    <?php endif; ?>
</div>