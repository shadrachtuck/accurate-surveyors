<?php
defined('ABSPATH') || exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class THRIVE_SECURITY_LOG_TABLE
 *
 * Displays parsed logs in a sortable, searchable admin table.
 */
class THRIVE_SECURITY_LOG_TABLE extends WP_List_Table {

    /**
     * @var array Parsed log entries.
     */
    private array $all_logs = [];

    /**
     * Constructor.
     *
     * @param array $args ['logs' => array[]]
     */
    public function __construct(array $args = []) {
        parent::__construct([
            'singular' => 'log_entry',
            'plural'   => 'log_entries',
            'ajax'     => false,
        ]);

        $this->all_logs = $args['logs'] ?? [];
    }

    /**
     * Define columns.
     *
     * @return array
     */
    public function get_columns(): array {
        return [
            'date'   => esc_html__('Date & Time', THRIVE_SECURITY_TEXT_DOMAIN),
            'type'   => esc_html__('Event Type', THRIVE_SECURITY_TEXT_DOMAIN),
            'target' => esc_html__('Target', THRIVE_SECURITY_TEXT_DOMAIN),
            'ip'     => esc_html__('IP Address', THRIVE_SECURITY_TEXT_DOMAIN),
            'user'   => esc_html__('User', THRIVE_SECURITY_TEXT_DOMAIN),
        ];
    }

    /**
     * Add filter views (e.g., All, Plugin-Blocked, Theme-Install).
     *
     * @return array
     */
    protected function get_views(): array {
        $types = array_unique(array_column($this->all_logs, 'type'));
        $views = [];

        $current  = sanitize_text_field($_GET['type'] ?? '');
        $base_url = admin_url('admin.php?page=thrive-log');

        $views['all'] = sprintf(
            '<a href="%s"%s>%s <span class="thrive-view-count">(%d)</span></a>',
            esc_url($base_url),
            $current === '' ? ' class="current"' : '',
            esc_html__('All Events', THRIVE_SECURITY_TEXT_DOMAIN),
            count($this->all_logs)
        );

        foreach ($types as $type) {
            $type_count = count(array_filter($this->all_logs, function($log) use ($type) {
                return isset($log['type']) && $log['type'] === $type;
            }));
            
            $views[$type] = sprintf(
                '<a href="%s"%s>%s <span class="thrive-view-count">(%d)</span></a>',
                esc_url(add_query_arg('type', urlencode($type), $base_url)),
                $current === $type ? ' class="current"' : '',
                esc_html(ucfirst(str_replace('-', ' ', $type))),
                $type_count
            );
        }

        return $views;
    }

    /**
     * Renders the search box.
     */
    public function get_search_box($text, $input_id) {
        echo '<div class="thrive-search-container">';
        echo '<label class="thrive-search-label" for="' . esc_attr($input_id) . '">' . esc_html($text) . ':</label>';
        echo '<input type="search" id="' . esc_attr($input_id) . '" name="s" value="' . esc_attr($_GET['s'] ?? '') . '" placeholder="Search by IP, user, target, or type..." />';
        submit_button($text, 'button thrive-search-btn', false, false, ['id' => 'search-submit']);
        echo '</div>';
    }

    /**
     * Prepare log items with pagination and filtering.
     */
    public function prepare_items() {
        try {
            $logs = $this->all_logs;

            // Filter by type
            if (!empty($_GET['type'])) {
                $type = sanitize_text_field($_GET['type']);
                $logs = array_filter($logs, function($log) use ($type) {
                    return isset($log['type']) && $log['type'] === $type;
                });
            }

            // Search filter
            if (!empty($_GET['s'])) {
                $search = strtolower(sanitize_text_field($_GET['s']));
                $logs = array_filter($logs, function ($log) use ($search) {
                    return (isset($log['target']) && strpos(strtolower($log['target']), $search) !== false)
                        || (isset($log['ip']) && strpos(strtolower($log['ip']), $search) !== false)
                        || (isset($log['user']) && strpos(strtolower($log['user']), $search) !== false)
                        || (isset($log['type']) && strpos(strtolower($log['type']), $search) !== false)
                        || (isset($log['date']) && strpos(strtolower($log['date']), $search) !== false);
                });
            }

            $per_page     = 25; // Increased for better UX
            $current_page = $this->get_pagenum();
            $total_items  = count($logs);

            $this->items = array_slice($logs, ($current_page - 1) * $per_page, $per_page);

            $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page,
            ]);

            $this->_column_headers = [$this->get_columns(), [], []];
        } catch (Exception $e) {
            error_log('Thrive Security: Error preparing log items - ' . $e->getMessage());
            $this->items = [];
            $this->set_pagination_args([
                'total_items' => 0,
                'per_page'    => 25,
            ]);
        }
    }

    /**
     * Render date column with modern formatting.
     */
    public function column_date($item): string {
        $date = $item['date'] ?? '';
        if (empty($date)) return '-';
        
        // Try to parse the date and format it nicely
        $timestamp = strtotime($date);
        if ($timestamp) {
            $formatted_date = date('M j, Y', $timestamp);
            $formatted_time = date('g:i A', $timestamp);
            return sprintf(
                '<div class="thrive-date-cell">
                    <div class="thrive-date-main">%s</div>
                    <div class="thrive-date-time">%s</div>
                </div>',
                esc_html($formatted_date),
                esc_html($formatted_time)
            );
        }
        
        return esc_html($date);
    }

    /**
     * Render type column with status indicators.
     */
    public function column_type($item): string {
        $type = $item['type'] ?? '';
        if (empty($type)) return '-';
        
        $type_class = 'thrive-event-type-' . str_replace('-', '-', $type);
        $type_icon = $this->get_event_icon($type);
        $type_label = ucfirst(str_replace('-', ' ', $type));
        
        return sprintf(
            '<div class="thrive-event-type %s">
                <span class="thrive-event-icon">%s</span>
                <span class="thrive-event-label">%s</span>
            </div>',
            esc_attr($type_class),
            $type_icon,
            esc_html($type_label)
        );
    }

    /**
     * Render target column with better formatting.
     */
    public function column_target($item): string {
        $target = $item['target'] ?? '';
        if (empty($target)) return '-';
        
        return sprintf(
            '<div class="thrive-target-cell">
                <code class="thrive-target-code">%s</code>
            </div>',
            esc_html($target)
        );
    }

    /**
     * Render IP column with geolocation info.
     */
    public function column_ip($item): string {
        $ip = $item['ip'] ?? '';
        if (empty($ip)) return '-';
        
        $ip_class = $this->get_ip_class($ip);
        
        return sprintf(
            '<div class="thrive-ip-cell">
                <span class="thrive-ip-address %s">%s</span>
            </div>',
            esc_attr($ip_class),
            esc_html($ip)
        );
    }

    /**
     * Render user column with user info.
     */
    public function column_user($item): string {
        $user = $item['user'] ?? '';
        if (empty($user)) return '-';
        
        return sprintf(
            '<div class="thrive-user-cell">
                <span class="thrive-user-name">%s</span>
            </div>',
            esc_html($user)
        );
    }

    /**
     * Default column renderer.
     *
     * @param array  $item
     * @param string $column_name
     * @return string
     */
    public function column_default($item, $column_name): string {
        return esc_html($item[$column_name] ?? '');
    }

    /**
     * Get event icon based on type.
     */
    private function get_event_icon($type): string {
        $icons = [
            'plugin-blocked' => '🔌',
            'plugin-activated' => '🔌',
            'plugin-deactivated' => '🔌',
            'theme-blocked' => '🎨',
            'theme-activated' => '🎨',
            'theme-deactivated' => '🎨',
            'access-blocked' => '🚫',
            'login-attempt' => '🔐',
            'file-access' => '📁',
            'admin-access' => '👤',
            'config-update' => '⚙️',
            'cron-execution' => '⏰',
            'default' => '📝'
        ];
        
        return $icons[$type] ?? $icons['default'];
    }

    /**
     * Get IP address class for styling.
     */
    private function get_ip_class($ip): string {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return 'thrive-ip-external';
        }
        return 'thrive-ip-internal';
    }

    /**
     * Display the table with modern styling.
     */
    public function display() {
        if (empty($this->items)) {
            echo '<div class="thrive-empty-state">';
            echo '<div class="thrive-empty-icon">📊</div>';
            echo '<h3>' . esc_html__('No Log Entries Found', THRIVE_SECURITY_TEXT_DOMAIN) . '</h3>';
            echo '<p>' . esc_html__('No security events have been logged yet. Events will appear here as they occur.', THRIVE_SECURITY_TEXT_DOMAIN) . '</p>';
            echo '</div>';
            return;
        }
        
        parent::display();
    }
}