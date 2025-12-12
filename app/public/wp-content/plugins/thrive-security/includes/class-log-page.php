<?php
defined('ABSPATH') || exit;

/**
 * Class THRIVE_SECURITY_LOG_PAGE
 *
 * Displays and controls the admin log viewer page.
 */
class THRIVE_SECURITY_LOG_PAGE
{
    /**
     * Register menu, actions, and dashboard widget.
     */
    public static function init(): void
    {
       // Check if blocking module is enabled
       if (!get_option('thrive_blocking_enabled', false)) {
           return; // Exit early if blocking is disabled
       }
       add_action('admin_init', [self::class, 'check_access']);
       add_action('admin_init', [self::class, 'handle_download']);
    }

    /**
     * Check access to the log page.
     */
    public static function check_access(): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'thrive-log') {
            return;
        }

        // Let the access controller handle the check
        THRIVE_SECURITY_ACCESS_CONTROLLER::enforce();
    }

    /**
     * Handle log file download request.
     */
    public static function handle_download(): void
    {
        if (
            isset($_GET['thrive_log_download']) &&
            current_user_can('manage_options') &&
            class_exists('THRIVE_SECURITY_LOG_MANAGER')
        ) {
            THRIVE_SECURITY_LOG_MANAGER::download_log();
        }
    }

    /**
     * Renders the actual admin log viewer page.
     */
    public static function render(): void
    {
        // Check if blocking module is enabled
        if (!get_option('thrive_blocking_enabled', false)) {
            echo '<div class="wrap thrive-admin-wrap">';
            echo '<div class="thrive-admin-header">';
            echo '<h1>📊 ' . esc_html__('Thrive Security Logs', THRIVE_SECURITY_TEXT_DOMAIN) . '</h1>';
            echo '</div>';
            echo '<div class="thrive-admin-content">';
            echo '<div class="thrive-card">';
            echo '<div class="thrive-card-body">';
            echo '<div class="thrive-disabled-notice">';
            echo '<div class="thrive-notice-icon">⚠️</div>';
            echo '<div class="thrive-notice-content">';
            echo '<h3>' . esc_html__('Log Viewer Unavailable', THRIVE_SECURITY_TEXT_DOMAIN) . '</h3>';
            echo '<p>' . esc_html__('This page is not available because the Thrive blocking module is disabled.', THRIVE_SECURITY_TEXT_DOMAIN) . '</p>';
            echo '<p>' . esc_html__('Please enable the blocking module in Thrive settings to access this page.', THRIVE_SECURITY_TEXT_DOMAIN) . '</p>';
            echo '<a href="' . esc_url(admin_url('admin.php?page=thrive-settings')) . '" class="button button-primary">' .
                esc_html__('Go to Settings', THRIVE_SECURITY_TEXT_DOMAIN) . '</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }

        $entries = class_exists('THRIVE_SECURITY_LOG_MANAGER') ? THRIVE_SECURITY_LOG_MANAGER::get_parsed_entries() : [];
        $table = class_exists('THRIVE_SECURITY_LOG_TABLE') ? new THRIVE_SECURITY_LOG_TABLE(['logs' => $entries]) : null;
        $refresh_url = esc_url(add_query_arg('thrive_force_sync', '1'));
        $clear_url = esc_url(add_query_arg('thrive_force_clear_log', '1'));
        $download_url = esc_url(add_query_arg('thrive_log_download', '1'));
        $current_ver = get_option(THRIVE_SECURITY_CONFIG_VERSION_KEY) ?: __('N/A', THRIVE_SECURITY_TEXT_DOMAIN);

        // Calculate statistics
        $total_entries = count($entries);
        $blocked_plugins = count(array_filter($entries, function ($entry) {
            return isset($entry['type']) && strpos($entry['type'], 'plugin') !== false;
        }));
        $blocked_themes = count(array_filter($entries, function ($entry) {
            return isset($entry['type']) && strpos($entry['type'], 'theme') !== false;
        }));
        $unique_ips = count(array_unique(array_column($entries, 'ip')));
        $unique_users = count(array_unique(array_column($entries, 'user')));

        echo '<div class="wrap thrive-admin-wrap">';
        echo '<div class="thrive-admin-header">';
        echo '<h1>📊 ' . esc_html__('Thrive Security Logs', THRIVE_SECURITY_TEXT_DOMAIN) . '</h1>';
        echo '<p>' . esc_html__('Monitor and analyze security events, blocked plugins, themes, and access attempts.', THRIVE_SECURITY_TEXT_DOMAIN) . '</p>';
        echo '</div>';

        echo '<div class="thrive-admin-content">';

        if (!class_exists('THRIVE_SECURITY_LOG_TABLE') || !class_exists('THRIVE_SECURITY_LOG_MANAGER')) {
            echo '<div class="thrive-card">';
            echo '<div class="thrive-card-body">';
            echo '<div class="thrive-error-notice">';
            echo '<div class="thrive-notice-icon">❌</div>';
            echo '<div class="thrive-notice-content">';
            echo '<h3>' . esc_html__('Log Viewer Error', THRIVE_SECURITY_TEXT_DOMAIN) . '</h3>';
            echo '<p>' . esc_html__('Log viewer is disabled due to missing logging classes.', THRIVE_SECURITY_TEXT_DOMAIN) . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            return;
        }

        // Statistics Cards
        echo '<div class="thrive-stats-grid">';
        echo '<div class="thrive-stat-card">';
        echo '<div class="thrive-stat-icon">📈</div>';
        echo '<div class="thrive-stat-content">';
        echo '<div class="thrive-stat-number">' . number_format($total_entries) . '</div>';
        echo '<div class="thrive-stat-label">' . esc_html__('Total Events', THRIVE_SECURITY_TEXT_DOMAIN) . '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="thrive-stat-card">';
        echo '<div class="thrive-stat-icon">🔌</div>';
        echo '<div class="thrive-stat-content">';
        echo '<div class="thrive-stat-number">' . number_format($blocked_plugins) . '</div>';
        echo '<div class="thrive-stat-label">' . esc_html__('Blocked Plugins', THRIVE_SECURITY_TEXT_DOMAIN) . '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="thrive-stat-card">';
        echo '<div class="thrive-stat-icon">🎨</div>';
        echo '<div class="thrive-stat-content">';
        echo '<div class="thrive-stat-number">' . number_format($blocked_themes) . '</div>';
        echo '<div class="thrive-stat-label">' . esc_html__('Blocked Themes', THRIVE_SECURITY_TEXT_DOMAIN) . '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div class="thrive-stat-card">';
        echo '<div class="thrive-stat-icon">🌐</div>';
        echo '<div class="thrive-stat-content">';
        echo '<div class="thrive-stat-number">' . number_format($unique_ips) . '</div>';
        echo '<div class="thrive-stat-label">' . esc_html__('Unique IPs', THRIVE_SECURITY_TEXT_DOMAIN) . '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Configuration Status Card
        echo '<div class="thrive-card">';
        echo '<div class="thrive-card-header">';
        echo '<h2>⚙️ ' . esc_html__('Configuration Status', THRIVE_SECURITY_TEXT_DOMAIN) . '</h2>';
        echo '</div>';
        echo '<div class="thrive-card-body">';
        echo '<div class="thrive-config-info">';
        echo '<div class="thrive-config-item">';
        echo '<span class="thrive-config-label">' . esc_html__('Current Config Version:', THRIVE_SECURITY_TEXT_DOMAIN) . '</span>';
        echo '<span class="thrive-config-value">' . esc_html($current_ver) . '</span>';
        echo '</div>';
        echo '<div class="thrive-config-actions">';
        echo '<a href="' . $refresh_url . '" class="button button-primary thrive-action-btn">';
        echo '<span class="thrive-btn-icon">🔄</span>';
        echo esc_html__('Force Sync Config', THRIVE_SECURITY_TEXT_DOMAIN);
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // Log Table Card
        echo '<div class="thrive-card">';
        echo '<div class="thrive-card-header">';
        echo '<h2>📋 ' . esc_html__('Security Events Log', THRIVE_SECURITY_TEXT_DOMAIN) . '</h2>';
        echo '<div class="thrive-log-actions">';
        echo '<a href="' . $clear_url . '" class="button button-danger thrive-action-btn" ';
        echo 'onclick="return confirm(\'' . esc_js(__('Are you sure you want to clear the log? This action cannot be undone.', THRIVE_SECURITY_TEXT_DOMAIN)) . '\')">';
        echo '<span class="thrive-btn-icon">🗑️</span>';
        echo esc_html__('Clear Log', THRIVE_SECURITY_TEXT_DOMAIN);
        echo '</a>';
        echo '<a href="' . $download_url . '" class="button button-secondary thrive-action-btn">';
        echo '<span class="thrive-btn-icon">⬇️</span>';
        echo esc_html__('Download Log', THRIVE_SECURITY_TEXT_DOMAIN);
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="thrive-card-body">';

        echo '<form method="get" class="thrive-log-form">';
        echo '<input type="hidden" name="page" value="thrive-log">';

        $table->prepare_items();

        // Modern search and filter controls
        echo '<div class="thrive-log-controls">';
        echo '<div class="thrive-search-filter">';
        $table->views();
        echo '</div>';
        echo '<div class="thrive-search-box">';
        $table->search_box(__('Search Logs', THRIVE_SECURITY_TEXT_DOMAIN), 'thrive-log-search');
        echo '</div>';
        echo '</div>';

        $table->display();
        echo '</form>';

        echo '</div>';
        echo '</div>';

        // Quick Actions Card
        echo '<div class="thrive-card">';
        echo '<div class="thrive-card-header">';
        echo '<h2>⚡ ' . esc_html__('Quick Actions', THRIVE_SECURITY_TEXT_DOMAIN) . '</h2>';
        echo '</div>';
        echo '<div class="thrive-card-body">';
        echo '<div class="thrive-quick-actions">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=thrive-settings')) . '" class="thrive-quick-action">';
        echo '<span class="thrive-quick-icon">⚙️</span>';
        echo '<span class="thrive-quick-text">' . esc_html__('Security Settings', THRIVE_SECURITY_TEXT_DOMAIN) . '</span>';
        echo '</a>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=thrive-plugins')) . '" class="thrive-quick-action">';
        echo '<span class="thrive-quick-icon">🔌</span>';
        echo '<span class="thrive-quick-text">' . esc_html__('Plugin Management', THRIVE_SECURITY_TEXT_DOMAIN) . '</span>';
        echo '</a>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=thrive-log')) . '" class="thrive-quick-action">';
        echo '<span class="thrive-quick-icon">📊</span>';
        echo '<span class="thrive-quick-text">' . esc_html__('Refresh Logs', THRIVE_SECURITY_TEXT_DOMAIN) . '</span>';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '</div>'; // .thrive-admin-content
        echo '</div>'; // .wrap
    }
}