<?php
/**
 * Thrive User Sync class
 */
class Thrive_User_Sync {
    /**
     * Map Thrive roles to WordPress roles
     *
     * @var array
     */
    private $role_map = [
        'admin' => 'administrator',
        'editor' => 'editor',
        'author' => 'author',
        'contributor' => 'contributor',
        'subscriber' => 'subscriber',
    ];

    /**
     * Sync user from Thrive to WordPress
     *
     * @param array $user_data User data from Thrive API
     * @return int|false WordPress user ID or false on failure
     */
    public function sync_user($user_data) {
        if (!isset($user_data['email'])) {
            return false;
        }

        // Check if user exists
        $user = get_user_by('email', $user_data['email']);

        if ($user) {
            // Update existing user
            return $this->update_user($user->ID, $user_data);
        } else {
            // Create new user
            return $this->create_user($user_data);
        }
    }

    /**
     * Create a new WordPress user from Thrive data
     *
     * @param array $user_data User data from Thrive API
     * @return int|false WordPress user ID or false on failure
     */
    private function create_user($user_data) {
        $username = $this->generate_username($user_data);
        $password = wp_generate_password(16);
        $role = $this->map_role($user_data['role']);

        $user_id = wp_insert_user([
            'user_login' => $username,
            'user_pass' => $password,
            'user_email' => $user_data['email'],
            'display_name' => $user_data['name'],
            'role' => $role,
        ]);

        if (is_wp_error($user_id)) {
            return false;
        }

        // Store Thrive user ID in user meta
        update_user_meta($user_id, 'thrive_user_id', $user_data['user_id']);
        update_user_meta($user_id, 'thrive_token', $user_data['token']);
        update_user_meta($user_id, 'thrive_role', $user_data['role']);

        return $user_id;
    }

    /**
     * Update an existing WordPress user with Thrive data
     *
     * @param int $user_id WordPress user ID
     * @param array $user_data User data from Thrive API
     * @return int|false WordPress user ID or false on failure
     */
    private function update_user($user_id, $user_data) {
        $role = $this->map_role($user_data['role']);

        //die($role);
        
        // Get the user object
        $user = get_userdata($user_id);
        
        // Remove existing roles
        $user->set_role('');
        
        // Add new role
        $user->add_role($role);
        
        // Update user meta
        update_user_meta($user_id, 'thrive_user_id', $user_data['user_id']);
        update_user_meta($user_id, 'thrive_token', $user_data['token']);
        update_user_meta($user_id, 'thrive_role', $user_data['role']);
        
        return $user_id;
    }

    /**
     * Generate a username from user data
     *
     * @param array $user_data User data from Thrive API
     * @return string Generated username
     */
    private function generate_username($user_data) {
        $base_username = sanitize_user(strtolower(str_replace(' ', '', $user_data['name'])), true);
        
        // Check if username exists
        $username = $base_username;
        $i = 1;
        
        while (username_exists($username)) {
            $username = $base_username . $i;
            $i++;
        }
        
        return $username;
    }

    /**
     * Map Thrive role to WordPress role
     *
     * @param string $thrive_role Role from Thrive
     * @return string WordPress role
     */
private function map_role($thrive_role) {
    $thrive_role = strtolower($thrive_role); // normalize input

    if (isset($this->role_map[$thrive_role])) {
        return $this->role_map[$thrive_role];
    }

    // Default to subscriber if role not found
    return 'subscriber';
}
}