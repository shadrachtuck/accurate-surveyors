<?php
defined('ABSPATH') || exit;

class THRIVE_SECURITY_SETTINGS {
    
    // Define cron job configurations
    private static $cron_jobs = [
        'log_send' => [
            'name' => 'Send Log',
            'description' => 'Enable to automatically send logs to the server.',
            'default_enabled' => false,
            'default_interval' => 'daily'
        ],
        'config_refresh' => [
            'name' => 'Config Refresh',
            'description' => 'Enable to automatically refresh the config.',
            'default_enabled' => true,
            'default_interval' => 'twicedaily'
        ],
        'block_plugins' => [
            'name' => 'Block Plugins & Themes',
            'description' => 'Enable to automatically block plugins and themes.',
            'default_enabled' => true,
            'default_interval' => 'twicedaily'
        ],
        'install_plugins' => [
            'name' => 'Install Required Plugins',
            'description' => 'Enable to automatically install required plugins.',
            'default_enabled' => true,
            'default_interval' => 'twicedaily'
        ]
    ];

    public static function init() {
        // Always initialize settings so users can access the page to re-enable blocking
       add_action('admin_init', [self::class, 'register_settings']);
       add_action('admin_enqueue_scripts', [self::class, 'enqueue_styles']);
       add_action('admin_enqueue_scripts', [self::class, 'enqueue_scripts']);
       add_action('admin_init', [self::class, 'process_settings']);
    }

    public static function register_settings() {
        // Blocking Module Settings
        register_setting('thrive_settings', 'thrive_blocking_enabled', [
            'type' => 'boolean',
            'default' => true
        ]);
        
        register_setting('thrive_settings', 'thrive_blocking_was_disabled', [
            'type' => 'boolean',
            'default' => false
        ]);
        
        register_setting('thrive_settings', 'thrive_stored_values_for_comparison', [
            'type' => 'array',
            'default' => []
        ]);

        // General Settings
        register_setting('thrive_settings', 'thrive_config_api_url', [
            'type' => 'string',
            'default' => THRIVE_SECURITY_CONFIG_API_URL
        ]);
        
        register_setting('thrive_settings', 'thrive_disable_core_file_mod', [
            'type' => 'boolean',
            'default' => true
        ]);
        
        register_setting('thrive_settings', 'thrive_disable_file_edit', [
            'type' => 'boolean',
            'default' => true
        ]);

        // Register settings for each cron job
        foreach (self::$cron_jobs as $job_key => $job_config) {
            register_setting('thrive_settings', "thrive_enable_{$job_key}", [
                'type' => 'boolean',
                'default' => $job_config['default_enabled']
            ]);
            
            register_setting('thrive_settings', "thrive_{$job_key}_interval", [
                'type' => 'string',
                'default' => $job_config['default_interval']
            ]);
        }
    }

    public static function process_settings() {
        // Only process if this is our form submission
        if (!isset($_POST['thrive_settings_submit'])) {
            return;
        }

        // Verify nonce for security
        if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'thrive_settings_nonce')) {
            wp_die(__('Security check failed. Please try again.', THRIVE_SECURITY_TEXT_DOMAIN));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', THRIVE_SECURITY_TEXT_DOMAIN));
        }

        // Get previous blocking state
        $previous_blocking_state = get_option('thrive_blocking_enabled', true);
        $new_blocking_state = isset($_POST['thrive_blocking_enabled']);

        // Process Blocking Module Settings first
        update_option('thrive_blocking_enabled', $new_blocking_state);

        // If blocking was disabled and is now enabled, restore default values
        if (!$previous_blocking_state && $new_blocking_state) {
            // Restore default values for all settings
            self::restore_default_values();
            
            add_settings_error(
                'thrive_settings',
                'settings_updated',
                __('Blocking module enabled. Default values have been restored.', THRIVE_SECURITY_TEXT_DOMAIN),
                'updated'
            );
            return;
        }

        // If blocking is disabled, set a flag and store current values for comparison
        if (!$new_blocking_state) {
            update_option('thrive_blocking_was_disabled', true);
            self::store_current_values_for_comparison();

            add_settings_error(
                'thrive_settings',
                'settings_updated',
                __('Blocking module disabled. All security features are now inactive.', THRIVE_SECURITY_TEXT_DOMAIN),
                'error'
            );
            return;
        }

        // If blocking is enabled and was previously disabled, intelligently restore defaults
        if ($new_blocking_state && get_option('thrive_blocking_was_disabled', false)) {
            // Intelligently restore values - keep user changes, restore defaults for unchanged fields
            self::intelligently_restore_values($_POST);
            
            // Clear the disabled flag
            delete_option('thrive_blocking_was_disabled');
            
            add_settings_error(
                'thrive_settings',
                'settings_updated',
                __('Settings saved successfully.', THRIVE_SECURITY_TEXT_DOMAIN),
                'updated'
            );
            return;
        }

        // Process General Settings (only when blocking is enabled and not just re-enabled)
        update_option('thrive_config_api_url', esc_url_raw($_POST['thrive_config_api_url'] ?? THRIVE_SECURITY_CONFIG_API_URL));
        update_option('thrive_disable_core_file_mod', isset($_POST['thrive_disable_core_file_mod']));
        update_option('thrive_disable_file_edit', isset($_POST['thrive_disable_file_edit']));

        // Process cron job settings (only when blocking is enabled and not just re-enabled)
        foreach (self::$cron_jobs as $job_key => $job_config) {
            $enable_option = "thrive_enable_{$job_key}";
            $interval_option = "thrive_{$job_key}_interval";
            
            $enabled = isset($_POST[$enable_option]);
            update_option($enable_option, $enabled);
            
            if ($enabled) {
                $interval = sanitize_text_field($_POST[$interval_option] ?? $job_config['default_interval']);
                update_option($interval_option, $interval);
            } else {
                // Reset to default when disabled
                update_option($interval_option, $job_config['default_interval']);
            }
        }

        // Add success message
        add_settings_error(
            'thrive_settings',
            'settings_updated',
            __('Settings saved successfully.', THRIVE_SECURITY_TEXT_DOMAIN),
            'updated'
        );
    }

    /**
     * Restore default values for all settings
     */
    private static function restore_default_values() {
        // Restore general settings defaults
        update_option('thrive_config_api_url', THRIVE_SECURITY_CONFIG_API_URL);
        update_option('thrive_disable_core_file_mod', true);
        update_option('thrive_disable_file_edit', true);

        // Restore cron job settings defaults
        foreach (self::$cron_jobs as $job_key => $job_config) {
            $enable_option = "thrive_enable_{$job_key}";
            $interval_option = "thrive_{$job_key}_interval";
            
            update_option($enable_option, $job_config['default_enabled']);
            update_option($interval_option, $job_config['default_interval']);
        }
    }

    /**
     * Clear the disabled flag (called on plugin activation)
     */
    public static function clear_disabled_flag() {
        delete_option('thrive_blocking_was_disabled');
        delete_option('thrive_stored_values_for_comparison');
    }

    /**
     * Store current values for comparison when blocking is disabled
     */
    private static function store_current_values_for_comparison() {
        $stored_values = [];
        
        // Store general settings
        $stored_values['thrive_config_api_url'] = get_option('thrive_config_api_url', THRIVE_SECURITY_CONFIG_API_URL);
        $stored_values['thrive_disable_core_file_mod'] = get_option('thrive_disable_core_file_mod', true);
        $stored_values['thrive_disable_file_edit'] = get_option('thrive_disable_file_edit', true);
        
        // Store cron job settings
        foreach (self::$cron_jobs as $job_key => $job_config) {
            $enable_option = "thrive_enable_{$job_key}";
            $interval_option = "thrive_{$job_key}_interval";
            
            $stored_values[$enable_option] = get_option($enable_option, $job_config['default_enabled']);
            $stored_values[$interval_option] = get_option($interval_option, $job_config['default_interval']);
        }
        
        update_option('thrive_stored_values_for_comparison', $stored_values);
    }

    /**
     * Intelligently restore values - keep user changes, restore defaults for unchanged fields
     */
    private static function intelligently_restore_values($post_data) {
        $stored_values = get_option('thrive_stored_values_for_comparison', []);
        $changed = false;

        // General settings
        // API URL
        $api_url_post = isset($post_data['thrive_config_api_url']) ? esc_url_raw($post_data['thrive_config_api_url']) : THRIVE_SECURITY_CONFIG_API_URL;
        $api_url_stored = $stored_values['thrive_config_api_url'] ?? THRIVE_SECURITY_CONFIG_API_URL;
        if ($api_url_post !== $api_url_stored) {
            update_option('thrive_config_api_url', $api_url_post);
            $changed = true;
        } else {
            update_option('thrive_config_api_url', THRIVE_SECURITY_CONFIG_API_URL);
        }
        // Core file mod
        $core_file_mod_post = array_key_exists('thrive_disable_core_file_mod', $post_data) ? (bool)$post_data['thrive_disable_core_file_mod'] : false;
        $core_file_mod_stored = (bool)($stored_values['thrive_disable_core_file_mod'] ?? true);
        if ($core_file_mod_post !== $core_file_mod_stored) {
            update_option('thrive_disable_core_file_mod', $core_file_mod_post);
            $changed = true;
        } else {
            update_option('thrive_disable_core_file_mod', true);
        }
        // File edit
        $file_edit_post = array_key_exists('thrive_disable_file_edit', $post_data) ? (bool)$post_data['thrive_disable_file_edit'] : false;
        $file_edit_stored = (bool)($stored_values['thrive_disable_file_edit'] ?? true);
        if ($file_edit_post !== $file_edit_stored) {
            update_option('thrive_disable_file_edit', $file_edit_post);
            $changed = true;
        } else {
            update_option('thrive_disable_file_edit', true);
        }

        // Cron job settings
        foreach (self::$cron_jobs as $job_key => $job_config) {
            $enable_option = "thrive_enable_{$job_key}";
            $interval_option = "thrive_{$job_key}_interval";

            // Enable/disable
            $enable_post = array_key_exists($enable_option, $post_data) ? (bool)$post_data[$enable_option] : false;
            $enable_stored = (bool)($stored_values[$enable_option] ?? $job_config['default_enabled']);
            if ($enable_post !== $enable_stored) {
                update_option($enable_option, $enable_post);
                $changed = true;
            } else {
                update_option($enable_option, $job_config['default_enabled']);
            }
            // Interval
            $interval_post = array_key_exists($interval_option, $post_data) ? sanitize_text_field($post_data[$interval_option]) : $job_config['default_interval'];
            $interval_stored = $stored_values[$interval_option] ?? $job_config['default_interval'];
            if ($interval_post !== $interval_stored) {
                update_option($interval_option, $interval_post);
                $changed = true;
            } else {
                update_option($interval_option, $job_config['default_interval']);
            }
        }
        // Clean up stored values
        delete_option('thrive_stored_values_for_comparison');

        // After restoring/applying, activate cron jobs
        if (class_exists('THRIVE_SECURITY_BOOTSTRAP')) {
            THRIVE_SECURITY_BOOTSTRAP::ensure_cron_scheduled();
        }
    }

    public static function render() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Show settings errors/messages
        settings_errors('thrive_settings');
        $enabled_blocking = (bool) get_option('thrive_blocking_enabled', true);

        $status_class_blocking = $enabled_blocking ? 'thrive-status-enabled' : 'thrive-status-disabled';
        $status_text_blocking = $enabled_blocking
            ? esc_html__('Enabled', THRIVE_SECURITY_TEXT_DOMAIN)
            : esc_html__('Disabled', THRIVE_SECURITY_TEXT_DOMAIN);
        ?>
        <div class="wrap thrive-admin-wrap">
            <div class="thrive-admin-header">
                <h1><?php echo esc_html__('Thrive Security Settings', THRIVE_SECURITY_TEXT_DOMAIN); ?></h1>
            </div>
            
            <div class="thrive-admin-content">

            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=thrive-settings')); ?>" class="thrive-settings-form">
                <?php wp_nonce_field('thrive_settings_nonce'); ?>
                <input type="hidden" name="thrive_settings_submit" value="1">

                <!-- Blocking Module Card -->
                <div class="thrive-card">
                    <div class="thrive-card-header">
                        <h2><?php echo esc_html__('Blocking Module', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                        <span class="thrive-status <?php echo esc_attr($status_class_blocking); ?>"><?php echo esc_html($status_text_blocking); ?></span>
                        </h2>
                    </div>

                    <div class="thrive-card-body">
                        <table class="thrive-form-table">
                            <tr>
                                <th scope="row"><?php echo esc_html__('Enable Blocking Module', THRIVE_SECURITY_TEXT_DOMAIN); ?></th>
                                <td>
                                    <label class="thrive-toggle">
                                        <input type="checkbox" name="thrive_blocking_enabled" value="1" 
                                            <?php checked(get_option('thrive_blocking_enabled', true)); ?>>
                                        <span class="thrive-toggle-slider"></span>
                                    </label>
                                </td>
                            </tr>
                        </table>

                        <div class="thrive-blocking-options" id="thrive-settings-content" style="display: <?php echo $enabled_blocking ? 'block' : 'none'; ?>;">
                            <h3><?php echo esc_html__('General Settings', THRIVE_SECURITY_TEXT_DOMAIN); ?></h3>
                            
                            <div class="thrive-blocking-section">
                                <h4><?php echo esc_html__('API URL', THRIVE_SECURITY_TEXT_DOMAIN); ?></h4>
                                <input type="url" name="thrive_config_api_url" 
                                           value="<?php echo esc_attr(get_option('thrive_config_api_url', THRIVE_SECURITY_CONFIG_API_URL)); ?>" 
                                           class="regular-text" required>
                                <p class="thrive-description">
                                    <?php echo esc_html__('Enter the Base API URL', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                                </p>
                            </div>

                            <div class="thrive-blocking-section">
                                <h4><?php echo esc_html__('Disable Core File Modification', THRIVE_SECURITY_TEXT_DOMAIN); ?></h4>
                                <label class="thrive-toggle">
                                    <input type="checkbox" name="thrive_disable_core_file_mod" value="1" 
                                            <?php checked(get_option('thrive_disable_core_file_mod', true)); ?>>
                                    <span class="thrive-toggle-slider"></span>
                                </label>
                                <p class="thrive-description">
                                    <?php echo esc_html__('Enable to prevent Core File Modification such as Upgrade Wordpress, Update Plugins/Themes', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                                </p>
                            </div>

                            <div class="thrive-blocking-section">
                                <h4><?php echo esc_html__('Disable File Editing', THRIVE_SECURITY_TEXT_DOMAIN); ?></h4>
                                <label class="thrive-toggle">
                                    <input type="checkbox" name="thrive_disable_file_edit" value="1" 
                                            <?php checked(get_option('thrive_disable_file_edit', true)); ?>>
                                    <span class="thrive-toggle-slider"></span>
                                </label>
                                <p class="thrive-description">
                                    <?php echo esc_html__('Enable to prevent File Editing such as Plugins/Themes File Editing.', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                                </p>
                            </div>

                            <h3><?php echo esc_html__('Cron Jobs', THRIVE_SECURITY_TEXT_DOMAIN); ?></h3>
                            <?php self::render_cron_jobs_section(); ?>

                            <h3><?php echo esc_html__('.htaccess Protection', THRIVE_SECURITY_TEXT_DOMAIN); ?></h3>
                            
                            <?php self::render_htaccess_section(); ?>
                        </div>

                        <div class="notice notice-warning" id="thrive-disabled-notice" style="margin: 15px 0; display: <?php echo $enabled_blocking ? 'none' : 'block'; ?>;">
                            <p><strong><?php echo esc_html__('Thrive Security is currently disabled.', THRIVE_SECURITY_TEXT_DOMAIN); ?></strong></p>
                            <p><?php echo esc_html__('Enable the blocking module above to activate all security features.', THRIVE_SECURITY_TEXT_DOMAIN); ?></p>
                        </div>
                    </div>
                </div>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary thrive-submit-btn" 
                           value="<?php echo esc_attr__('Save Changes', THRIVE_SECURITY_TEXT_DOMAIN); ?>">
                </p>
            </form>
            </div>
        </div>
        <?php
    }

    private static function render_cron_jobs_section() {
        foreach (self::$cron_jobs as $job_key => $job_config) {
            $enable_option = "thrive_enable_{$job_key}";
            $interval_option = "thrive_{$job_key}_interval";
            $section_id = "thrive-{$job_key}-interval-section";
            
            $enabled = get_option($enable_option, $job_config['default_enabled']);
            $current_interval = get_option($interval_option, $job_config['default_interval']);
            ?>
            <div class="thrive-blocking-section">
                <h4><?php echo esc_html__('Enable ' . $job_config['name'], THRIVE_SECURITY_TEXT_DOMAIN); ?></h4>
                <label class="thrive-toggle">
                    <input type="checkbox" name="<?php echo esc_attr($enable_option); ?>" value="1" 
                            <?php checked($enabled); ?>>
                    <span class="thrive-toggle-slider"></span>
                </label>
                <p class="thrive-description">
                    <?php echo esc_html__($job_config['description'], THRIVE_SECURITY_TEXT_DOMAIN); ?>
                </p>
            </div>

            <div class="thrive-blocking-section" id="<?php echo esc_attr($section_id); ?>" 
                 style="display: <?php echo $enabled ? 'block' : 'none'; ?>;">
                <h4><?php echo esc_html__($job_config['name'] . ' Interval', THRIVE_SECURITY_TEXT_DOMAIN); ?></h4>
                <select name="<?php echo esc_attr($interval_option); ?>" class="regular-text">
                    <?php self::render_interval_options($current_interval); ?>
                </select>
                <p class="thrive-description">
                    <?php echo esc_html__('Select how often this task should run.', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                </p>
            </div>
            <?php
        }
    }

    private static function render_interval_options($selected_interval) {
        $intervals = [
            'every_minute' => __('Every Minute', THRIVE_SECURITY_TEXT_DOMAIN),
            'hourly' => __('Hourly', THRIVE_SECURITY_TEXT_DOMAIN),
            'twicedaily' => __('Twice Daily', THRIVE_SECURITY_TEXT_DOMAIN),
            'daily' => __('Daily', THRIVE_SECURITY_TEXT_DOMAIN),
            'weekly' => __('Weekly', THRIVE_SECURITY_TEXT_DOMAIN),
            'monthly' => __('Monthly', THRIVE_SECURITY_TEXT_DOMAIN)
        ];

        foreach ($intervals as $value => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($value),
                selected($selected_interval, $value, false),
                esc_html($label)
            );
        }
    }

    private static function render_htaccess_section() {
        $htaccess_status = class_exists('THRIVE_SECURITY_BOOTSTRAP') ? THRIVE_SECURITY_BOOTSTRAP::check_htaccess_protection() : false;
        $status_class = $htaccess_status ? 'thrive-status-enabled' : 'thrive-status-disabled';
        $status_text = $htaccess_status ? esc_html__('Protected', THRIVE_SECURITY_TEXT_DOMAIN) : esc_html__('Not Protected', THRIVE_SECURITY_TEXT_DOMAIN);
        ?>
        <div class="thrive-blocking-section">
            <p><strong><?php echo esc_html__('Status:', THRIVE_SECURITY_TEXT_DOMAIN); ?></strong> 
               <span class="thrive-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></span>
            </p>
            
            <?php if (!$htaccess_status): ?>
                <p class="thrive-description">
                    <?php echo esc_html__('The log file is not protected by .htaccess rules. Click the button below to create protection.', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                </p>
                <p>
                    <a href="<?php echo esc_url(add_query_arg('thrive_force_create_htaccess', '1', admin_url('admin.php?page=thrive-settings'))); ?>" 
                       class="button button-secondary">
                        <?php echo esc_html__('Create .htaccess Protection', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('thrive_debug_htaccess', '1', admin_url('admin.php?page=thrive-settings'))); ?>" 
                       class="button button-secondary">
                        <?php echo esc_html__('Debug .htaccess Issues', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                    </a>
                </p>
            <?php else: ?>
                <p class="thrive-description">
                    <?php echo esc_html__('The log file is protected by .htaccess rules.', THRIVE_SECURITY_TEXT_DOMAIN); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function enqueue_styles($hook) {
        if ('thrive_page_thrive-settings' !== $hook && 'toplevel_page_thrive-log' !== $hook) {
           return;
        }
        $css_version = defined('THRIVE_SECURITY_VERSION') ? THRIVE_SECURITY_VERSION : '1.0.0';
        // Enqueue admin styles for settings page
        if ('thrive_page_thrive-settings' === $hook) {
            wp_enqueue_style('thrive-admin', THRIVE_SECURITY_URL . 'assets/css/thrive-admin.css', [], $css_version);
        }
        // Enqueue log page styles with admin CSS as dependency
        if ('toplevel_page_thrive-log' === $hook) {
            wp_enqueue_style('thrive-admin', THRIVE_SECURITY_URL . 'assets/css/thrive-admin.css', [], $css_version);
            wp_enqueue_style('thrive-log', THRIVE_SECURITY_URL . 'assets/css/thrive-log.css', ['thrive-admin'], $css_version);
        }
    }

    public static function enqueue_scripts($hook) {
        if ('thrive_page_thrive-settings' !== $hook && 'toplevel_page_thrive-log' !== $hook) {
            return;
        }
        
        // Enqueue admin scripts for settings page
        if ('thrive_page_thrive-settings' === $hook) {
            wp_enqueue_script('thrive-admin', THRIVE_SECURITY_URL . 'assets/js/thrive-admin.js', ['jquery'], THRIVE_SECURITY_VERSION, true);
            wp_add_inline_script('thrive-admin', self::get_toggle_script());
        }
        
        // Enqueue log page scripts if needed
        if ('toplevel_page_thrive-log' === $hook) {
            wp_enqueue_script('thrive-log', THRIVE_SECURITY_URL . 'assets/js/thrive-script.js', ['jquery'], THRIVE_SECURITY_VERSION, true);
        }
    }

    private static function get_toggle_script() {
        $script = 'jQuery(document).ready(function($) {';
        
        // Handle blocking module toggle
        $script .= '
            $("input[name=\'thrive_blocking_enabled\']").on("change", function() {
                var $settingsContent = $("#thrive-settings-content");
                var $disabledNotice = $("#thrive-disabled-notice");
                
                if ($(this).is(":checked")) {
                    $settingsContent.show();
                    $disabledNotice.hide();
                } else {
                    $settingsContent.hide();
                    $disabledNotice.show();
                }
            });
        ';
        
        // Handle all cron job toggles
        foreach (self::$cron_jobs as $job_key => $job_config) {
            $section_id = "thrive-{$job_key}-interval-section";
            $script .= "
                $(\"input[name='thrive_enable_{$job_key}']\").on(\"change\", function() {
                    var \$intervalSection = \$(\"#{$section_id}\");
                    var \$intervalSelect = \$(\"select[name='thrive_{$job_key}_interval']\");
                    
                    if (\$(this).is(\":checked\")) {
                        \$intervalSection.show();
                    } else {
                        \$intervalSection.hide();
                        \$intervalSelect.val(\"{$job_config['default_interval']}\"); // Reset to default value
                    }
                });
            ";
        }
        
        $script .= '});';
        
        return $script;
    }
}