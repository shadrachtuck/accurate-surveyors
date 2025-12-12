<?php
/**
 * Thrive API handler class
 */
class Thrive_API {
    /**
     * API base URL
     *
     * @var string
     */
    private $api_url;

    /**
     * Constructor
     */
    public function __construct() {
        $options = get_option('thrive_login_settings');
        $this->api_url = isset($options['api_url']) ? $options['api_url'] : THRIVE_API_URL;
    }

    /**
     * Get user data from Thrive API
     *
     * @param string $token The authentication token
     * @return array|false User data or false on failure
     */
    public function get_user_data($token) {
        $response = wp_remote_get($this->api_url . '/api/user', [
            'headers' => [
              'Authorization' => 'Bearer ' . trim($token, "\"'\\"),
                'Accept' => 'application/json',
            ],
        ]);


        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        

        if (!$data || !isset($data['user_id'])) {
            return false;
        }

        return $data;
    }

    /**
     * Verify token with Thrive API
     *
     * @param string $token The authentication token
     * @return bool Whether the token is valid
     */
    public function verify_token($token) {
        $response = wp_remote_post($this->api_url . '/api/verify-token', [
            'headers' => [
               'Authorization' => 'Bearer ' . trim($token, "\"'\\"),
                'Accept' => 'application/json',
            ],
        ]);



        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return isset($data['valid']) && $data['valid'] === true;
    }

    /**
     * Get authorization URL
     *
     * @param string $redirect_uri The redirect URI after authorization
     * @return string The authorization URL
     */
    public function get_auth_url($redirect_uri) {
        return $this->api_url . '/login?redirect=' . urlencode($redirect_uri);
    }
}