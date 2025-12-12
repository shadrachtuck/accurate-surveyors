<?php
/**
 * The admin-specific functionality of the plugin.
 */
class WP_2FA_Admin {

    /**
     * Add the settings page to the admin menu.
     */
    public function add_settings_page(): void {
        add_options_page(
            __('Thrive 2FA', 'wp-2fa'),
            __('Thrive 2FA', 'wp-2fa'),
            'manage_options',
            'wp-2fa-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Register the settings for the plugin.
     */
    public function register_settings(): void {
        register_setting('wp_2fa_settings', 'wp_2fa_enabled', array(
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));
        
        register_setting('wp_2fa_settings', 'wp_2fa_user_roles', array(
            'type' => 'array',
            'default' => array('administrator'),
            'sanitize_callback' => array($this, 'sanitize_user_roles'),
        ));
        
        register_setting('wp_2fa_settings', 'wp_2fa_otp_expiry', array(
            'type' => 'integer',
            'default' => 10, // 10 minutes
            'sanitize_callback' => array($this, 'sanitize_otp_expiry'),
        ));

        // Register updater settings
        register_setting('wp_2fa_settings', 'thrive_2FA_settings', array(
            'type' => 'array',
            'default' => array(
                'github_username' => 'glossyit',
                'github_repo' => 'Thrive2FAPlugin',
                'github_token' => '',
                'auto_update' => 1,
            ),
            'sanitize_callback' => array($this, 'sanitize_updater_settings'),
        ));
        
        add_settings_section(
            'wp_2fa_general_section',
            __('General Settings', 'wp-2fa'),
            array($this, 'general_section_callback'),
            'wp-2fa-settings'
        );
        
        add_settings_field(
            'wp_2fa_enabled',
            __('Enable Thrive 2FA', 'wp-2fa'),
            array($this, 'enabled_field_callback'),
            'wp-2fa-settings',
            'wp_2fa_general_section'
        );
        
        add_settings_field(
            'wp_2fa_user_roles',
            __('User Roles', 'wp-2fa'),
            array($this, 'user_roles_field_callback'),
            'wp-2fa-settings',
            'wp_2fa_general_section'
        );
        
        add_settings_field(
            'wp_2fa_otp_expiry',
            __('OTP Expiry (minutes)', 'wp-2fa'),
            array($this, 'otp_expiry_field_callback'),
            'wp-2fa-settings',
            'wp_2fa_general_section'
        );
    }

    /**
     * Sanitize user roles.
     */
    public function sanitize_user_roles($roles) {
        if (!is_array($roles)) {
            return array('administrator');
        }
        
        $valid_roles = array_keys(wp_roles()->roles);
        return array_intersect($roles, $valid_roles);
    }

    /**
     * Display the settings page with modern UI.
     */
    public function display_settings_page(): void {
        $enabled = get_option('wp_2fa_enabled', false);
        $status_class = $enabled ? 'wp-2fa-status-enabled' : 'wp-2fa-status-disabled';
        $status_text = $enabled ? __('Enabled', 'wp-2fa') : __('Disabled', 'wp-2fa');
        ?>
        <div class="wrap wp-2fa-admin-wrap">
            <div class="wp-2fa-admin-header">
                <h1><?php echo esc_html__('Thrive 2FA', 'wp-2fa'); ?></h1>
                <p><?php echo esc_html__('Secure your WordPress admin login with email-based two-factor authentication.', 'wp-2fa'); ?></p>
            </div>
            
            <form method="post" action="options.php" class="wp-2fa-settings-form">
                <?php settings_fields('wp_2fa_settings'); ?>
                
                <div class="wp-2fa-card">
                    <div class="wp-2fa-card-header">
                        <h2><?php echo esc_html__('General Settings', 'wp-2fa'); ?> <span class="wp-2fa-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span></h2>
                    </div>
                    <div class="wp-2fa-card-body">
                        <table class="wp-2fa-form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Thrive 2FA', 'wp-2fa'); ?></th>
                                <td>
                                    <label class="wp-2fa-toggle">
                                        <input type="checkbox" name="wp_2fa_enabled" value="1" <?php checked($enabled); ?> />
                                        <span class="wp-2fa-toggle-slider"></span>
                                    </label>
                                    <p class="wp-2fa-description"><?php echo esc_html__('Enable two-factor authentication for admin logins', 'wp-2fa'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('User Roles', 'wp-2fa'); ?></th>
                                <td>
                                    <?php $this->user_roles_field_callback(); ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo esc_html__('OTP Expiry Time', 'wp-2fa'); ?></th>
                                <td>
                                    <?php $this->otp_expiry_field_callback(); ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary wp-2fa-submit-btn" value="<?php echo esc_attr__('Save Changes', 'wp-2fa'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Enqueue admin styles.
     */
    public function enqueue_styles(string $hook): void {
        if ('settings_page_wp-2fa-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wp-2fa-admin', WP_2FA_PLUGIN_URL . 'assets/css/wp-2fa-admin.css', array(), WP_2FA_VERSION);
    }

    /**
     * Enqueue admin scripts.
     */
    public function enqueue_scripts(string $hook): void {
        if ('settings_page_wp-2fa-settings' !== $hook) {
            return;
        }
        
        wp_enqueue_script('wp-2fa-admin', WP_2FA_PLUGIN_URL . 'assets/js/wp-2fa-admin.js', array('jquery'), WP_2FA_VERSION, true);
    }

    /**
     * Sanitize OTP expiry time.
     */
    public function sanitize_otp_expiry($input) {
        $input = absint($input);
        
        // Ensure the value is between 1 and 60
        if ($input < 1) {
            $input = 1;
        } elseif ($input > 60) {
            $input = 60;
        }
        
        return $input;
    }

    /**
     * Sanitize updater settings.
     */
    public function sanitize_updater_settings($input) {
        if (!is_array($input)) {
            return array(
                'github_username' => 'glossyit',
                'github_repo' => 'Thrive2FAPlugin',
                'github_token' => '',
                'auto_update' => 0,
            );
        }

        $sanitized = array();
        
        // Sanitize GitHub username
        $sanitized['github_username'] = sanitize_text_field($input['github_username'] ?? 'glossyit');
        if (empty($sanitized['github_username'])) {
            $sanitized['github_username'] = 'glossyit';
        }
        
        // Sanitize GitHub repository
        $sanitized['github_repo'] = sanitize_text_field($input['github_repo'] ?? 'Thrive2FAPlugin');
        if (empty($sanitized['github_repo'])) {
            $sanitized['github_repo'] = 'Thrive2FAPlugin';
        }
        
        // Sanitize GitHub token (optional)
        $sanitized['github_token'] = sanitize_text_field($input['github_token'] ?? '');
        
        // Sanitize auto update setting
        $sanitized['auto_update'] = isset($input['auto_update']) ? 1 : 0;
        
        return $sanitized;
    }

    /**
     * General section callback.
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure the general settings for Thrive 2FA.', 'wp-2fa') . '</p>';
    }

    /**
     * Enabled field callback.
     */
    public function enabled_field_callback() {
        $enabled = get_option('wp_2fa_enabled', false);
        ?>
        <label class="wp-2fa-toggle">
            <input type="checkbox" name="wp_2fa_enabled" value="1" <?php checked($enabled); ?> />
            <span class="wp-2fa-toggle-slider"></span>
        </label>
        <p class="wp-2fa-description"><?php echo esc_html__('Enable two-factor authentication for admin logins', 'wp-2fa'); ?></p>
        <?php
    }

    /**
     * User roles field callback.
     */
    public function user_roles_field_callback() {
        $selected_roles = get_option('wp_2fa_user_roles', array('administrator'));
        $roles = wp_roles()->roles;
        
        echo '<div class="wp-2fa-role-list">';
        foreach ($roles as $role_id => $role) {
            ?>
            <label>
                <input type="checkbox" name="wp_2fa_user_roles[]" value="<?php echo esc_attr($role_id); ?>" <?php checked(in_array($role_id, $selected_roles)); ?> />
                <?php echo esc_html($role['name']); ?>
            </label>
            <?php
        }
        echo '</div>';
        echo '<p class="wp-2fa-description">' . esc_html__('Select which user roles will be required to use two-factor authentication.', 'wp-2fa') . '</p>';
    }

    /**
     * OTP expiry field callback.
     */
    public function otp_expiry_field_callback() {
        $expiry = get_option('wp_2fa_otp_expiry', 10);
        ?>
        <input type="number" name="wp_2fa_otp_expiry" value="<?php echo esc_attr($expiry); ?>" min="1" max="60" class="wp-2fa-number-input" />
        <p class="wp-2fa-description"><?php echo esc_html__('Number of minutes before an OTP expires (1-60).', 'wp-2fa'); ?></p>
        <?php
    }
}