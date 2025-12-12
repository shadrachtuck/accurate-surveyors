<?php
defined('ABSPATH') || exit;

/**
 * Class THRIVE_SECURITY_CONFIG_MANAGER
 *
 * Manages remote configuration including:
 * - Blacklisted IPs
 * - Blocked plugins and themes
 * - Restricted admin pages
 * - Version tracking and fallback
 */
class THRIVE_SECURITY_CONFIG_MANAGER {
    /**
     * Static property to track if a request is already in progress
     * @var bool
     */
    private static $request_in_progress = false;
    
    /**
     * Static property to store the last successful config
     * @var array|null
     */
    private static $last_config = null;
    
    /**
     * Retrieves and caches the remote config.
     * Falls back to last known good config on failure.
     *
     * @return array
     */
    public static function get_config() {
        // If we already have a config in memory, return it immediately
        if (self::$last_config !== null) {
            return THRIVE_SECURITY_HELPER::normalize_config(self::$last_config);
        }
        
        // Check if a request is already in progress to prevent multiple simultaneous calls
        if (self::$request_in_progress) {
            // Wait a bit and return cached data if available
            $cached = get_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
            if ($cached && is_array($cached)) {
                return THRIVE_SECURITY_HELPER::normalize_config($cached);
            }
            
            // If no cache, return fallback
            $fallback = get_option(THRIVE_SECURITY_CONFIG_FALLBACK_KEY);
            if (is_array($fallback)) {
                return THRIVE_SECURITY_HELPER::normalize_config($fallback);
            }
            
            return [];
        }
        
        $cached = get_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
        if ($cached && is_array($cached)) {
            // Store in memory for this request
            self::$last_config = $cached;
            // Always normalize before returning
            return THRIVE_SECURITY_HELPER::normalize_config($cached);
        }

        // Set request in progress flag
        self::$request_in_progress = true;
        $api_url = get_option('thrive_config_api_url', THRIVE_SECURITY_CONFIG_API_URL);

            // Log API request for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Thrive: Making API request to ' . $api_url);
            }

        // Basic API config validation
        if (empty($api_url)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('API URL is missing');
            THRIVE_SECURITY_HELPER::display_notice(
                __('Thrive: Configuration could not be loaded due to missing API URL.', THRIVE_SECURITY_TEXT_DOMAIN),
                'error',
                admin_url()
            );
            self::$request_in_progress = false;
            return [];
        }

        if (!filter_var($api_url, FILTER_VALIDATE_URL)) {
            error_log('Thrive: Invalid API URL: ' . $api_url);
            THRIVE_SECURITY_HELPER::maybe_debug_log('Invalid API URL: ' . $api_url);
            THRIVE_SECURITY_HELPER::maybe_display_notice(
                __('Thrive: Invalid API URL configured.', THRIVE_SECURITY_TEXT_DOMAIN),
                'error',
                admin_url()
            );
            self::$request_in_progress = false;
            return [];
        }

        // Remote request
        $response = wp_remote_get($api_url, [
            'timeout'    => apply_filters('thrive_admin_config_timeout', 20),
            'sslverify'  => true,
            'headers'    => [
                'Accept' => 'application/json',
                'User-Agent' => 'Thrive Security/' . THRIVE_SECURITY_VERSION,
                'X-Site-URL' => get_site_url(), // Add this custom header
            ]
        ]);

        // Check for timeout or connection errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $error_code = $response->get_error_code();

            // Log the specific error
            if ($error_code === 'http_request_failed') {
                THRIVE_SECURITY_HELPER::maybe_debug_log('API request failed: Connection timeout or network error');
            } else {
                THRIVE_SECURITY_HELPER::maybe_debug_log('API request failed with code ' . $error_code . ': ' . $error_message);
            }

            $fallback = get_option(THRIVE_SECURITY_CONFIG_FALLBACK_KEY);
            if (is_array($fallback)) {
                // Store in memory for this request
                self::$last_config = $fallback;
                self::$request_in_progress = false;
                // Always normalize before returning
                return THRIVE_SECURITY_HELPER::normalize_config($fallback);
            }

            THRIVE_SECURITY_HELPER::maybe_display_notice(
                __('Thrive: Failed to fetch configuration from API. Using fallback config.', THRIVE_SECURITY_TEXT_DOMAIN),
                'warning',
                admin_url()
            );
            self::$request_in_progress = false;
            return [];
        }

        // HTTP response code check
        $http_code = wp_remote_retrieve_response_code($response);
        if ($http_code !== 200) {
            THRIVE_SECURITY_HELPER::maybe_debug_log("API returned non-200 status: $http_code");
            THRIVE_SECURITY_HELPER::maybe_display_notice(
                __('Thrive: Invalid response code from API.', THRIVE_SECURITY_TEXT_DOMAIN),
                'error',
                admin_url()
            );
            self::$request_in_progress = false;
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        if (!is_array($data)) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('Invalid JSON response from API');
            THRIVE_SECURITY_HELPER::maybe_display_notice(
                __('Thrive: Invalid API response and no fallback config available.', THRIVE_SECURITY_TEXT_DOMAIN),
                'error',
                admin_url()
            );
            self::$request_in_progress = false;
            return [];
        }

        // Normalize the data structure
        $normalized_data = [
            'version' => $data['version'] ?? '',
            'restricted_pages' => $data['restricted_pages'] ?? [],
            'blocked_plugins' => $data['blocked_plugins'] ?? [],
            'blocked_themes' => $data['blocked_themes'] ?? [],
            'required_plugins' => $data['required_plugins'] ?? [],
            'recommended_plugins' => $data['recommended_plugins'] ?? [],
            'blacklist_ips' => $data['blacklist_ips'] ?? []
        ];

        // Validate and normalize
        $data = apply_filters('thrive_admin_filter_remote_config', THRIVE_SECURITY_HELPER::normalize_config($normalized_data));

        // Warn if empty or missing restricted pages
        if (empty($data['restricted_pages'])) {
            THRIVE_SECURITY_HELPER::maybe_display_notice(
                __('Thrive: Configuration loaded but no restricted pages defined.', THRIVE_SECURITY_TEXT_DOMAIN),
                'warning',
                admin_url()
            );
            //return [];
        }

        // Update cache, version, fallback
        $remote_version = $data['version'] ?? '';
        if ($remote_version !== get_option(THRIVE_SECURITY_CONFIG_VERSION_KEY)) {
            update_option(THRIVE_SECURITY_CONFIG_FALLBACK_KEY, $data);
            update_option(THRIVE_SECURITY_CONFIG_VERSION_KEY, $remote_version);
        }
        
        // Update last fetch time
        update_option(THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY, time());
        
        // Cache the result for 1 hour (3600 seconds) instead of default transient time
        set_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY, $data, 3600);
        
        // Store in memory for this request
        self::$last_config = $data;
        self::$request_in_progress = false;
        
        // Always normalize before returning
        return THRIVE_SECURITY_HELPER::normalize_config($data);
    }

    /**
     * Clears the cache and forces a config refresh.
     *
     * @return array The new config
     */
    public static function refresh() {
        delete_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
        delete_option(THRIVE_SECURITY_CONFIG_VERSION_KEY);
        // Clear memory cache
        self::$last_config = null;
        self::$request_in_progress = false;
        return self::get_config();
    }
    
    /**
     * Clears the in-memory cache
     */
    public static function clear_memory_cache() {
        self::$last_config = null;
        self::$request_in_progress = false;
    }
    
    /**
     * Checks if the config needs to be refreshed based on time intervals
     * 
     * @return bool
     */
    public static function needs_refresh() {
        $last_fetch = (int) get_option(THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY, 0);
        $current_time = time();
        
        // Ensure we have valid numeric values
        if (!is_numeric($last_fetch) || !is_numeric($current_time)) {
            return true; // Force refresh if we have invalid values
        }
        
        // Check if we have a cached version that's still valid
        $cached = get_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
        if ($cached && is_array($cached)) {
            // If we have valid cache, only refresh every hour
            return ($current_time - $last_fetch) > 3600; // 1 hour
        }
        
        // If no cache, refresh if last fetch was more than 5 minutes ago
        return ($current_time - $last_fetch) > 300; // 5 minutes
    }
    
    /**
     * Gets config only if refresh is needed, otherwise returns cached version
     * 
     * @return array
     */
    public static function get_config_if_needed() {
        if (self::needs_refresh()) {
            return self::get_config();
        }
        
        // Return cached version
        $cached = get_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
        if ($cached && is_array($cached)) {
            return THRIVE_SECURITY_HELPER::normalize_config($cached);
        }
        
        // If no cache, get fresh config
        return self::get_config();
    }
    
    /**
     * Gets API usage statistics
     * 
     * @return array
     */
    public static function get_api_stats() {
        $last_fetch = (int) get_option(THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY, 0);
        $cached = get_transient(THRIVE_SECURITY_CONFIG_CACHE_KEY);
        $fallback = get_option(THRIVE_SECURITY_CONFIG_FALLBACK_KEY);
        
        return [
            'last_fetch' => $last_fetch,
            'last_fetch_human' => $last_fetch ? date('Y-m-d H:i:s', $last_fetch) : 'Never',
            'has_cache' => !empty($cached),
            'has_fallback' => !empty($fallback),
            'request_in_progress' => self::$request_in_progress,
            'memory_cache_active' => self::$last_config !== null,
            'cache_expires_in' => $cached ? (int) get_option('_transient_timeout_' . THRIVE_SECURITY_CONFIG_CACHE_KEY, 0) - time() : 0
        ];
    }
    
    /**
     * Clean up corrupted data and ensure proper numeric values
     * 
     * @return bool
     */
    public static function cleanup_corrupted_data() {
        $last_fetch = get_option(THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY);
        
        // If last_fetch is not numeric, reset it
        if (!is_numeric($last_fetch)) {
            update_option(THRIVE_SECURITY_CONFIG_LAST_FETCH_KEY, time());
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Thrive: Cleaned up corrupted last_fetch data');
            }
        }
        
        // Clean up any other potential corrupted data
        $version = get_option(THRIVE_SECURITY_CONFIG_VERSION_KEY);
        if (!is_string($version) && !is_numeric($version)) {
            delete_option(THRIVE_SECURITY_CONFIG_VERSION_KEY);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Thrive: Cleaned up corrupted version data');
            }
        }
        
        return true;
    }
}