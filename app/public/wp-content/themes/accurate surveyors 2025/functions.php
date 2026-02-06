<?php
// Enqueue child theme styles with proper dependencies
function accurate_child_enqueue_styles() {
    // Get parent theme stylesheet handle
    $parent_style = 'blockchain-style';
    
    // Enqueue parent theme stylesheet first (if not already enqueued)
    wp_enqueue_style($parent_style, 
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue child theme stylesheet with parent as dependency and higher priority
    $child_style_path = get_stylesheet_directory() . '/css/style.css';
    $child_style_version = file_exists($child_style_path) ? filemtime($child_style_path) : wp_get_theme()->get('Version');
    
    wp_enqueue_style('accurate-child-style',
        get_stylesheet_directory_uri() . '/css/style.css',
        array($parent_style), // Depend on parent theme styles
        $child_style_version // Use file modification time for cache busting
    );
    
    // Google Fonts
    wp_enqueue_style('google-fonts', 
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap',
        array(), // No dependencies
        null // No version for external resources
    );
}
add_action('wp_enqueue_scripts', 'accurate_child_enqueue_styles', 15); // Priority 15 to run after parent theme but ensure our styles load last

// Register navigation menus
function accurate_register_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu'),
        'footer' => __('Footer Menu')
    ));
}
add_action('init', 'accurate_register_menus');

// Add custom class to footer element
function accurate_add_footer_class($classes) {
    $classes[] = 'as26-footer-display-override';
    return $classes;
}
add_filter('blockchain_footer_classes', 'accurate_add_footer_class');

// Add top contact bar
function accurate_top_contact_bar() {
    // Email link HTML
    $email_link = '<a href="mailto:info@accurate-surveyors.com">EMAIL US!</a>';
    
    // Default static text if customizer setting is not set
    $default_text = '1520 W. Washington St., Boise, ID 83702  |  (208) 488-4227  | ' . $email_link;
    
    // Get customizer setting, fallback to default if not set
    $contact_text = get_theme_mod('accurate_top_contact_text', $default_text);
    
    // Only display if there's content
    if ($contact_text) {
        ?>
        <div class="top-contact-bar">
            <div class="container">
                <span><?php echo wp_kses_post($contact_text); ?></span>
            </div>
        </div>
        <?php
    }
}
add_action('blockchain_before_header', 'accurate_top_contact_bar', 10);

// Set default logo from theme images folder
function accurate_set_default_logo() {
    // Only set if no custom logo is already configured
    if (!has_custom_logo()) {
        $logo_path = get_stylesheet_directory() . '/assets/images/AS_LogoOL_CLR_P-1920x850.png';
        $logo_url = get_stylesheet_directory_uri() . '/assets/images/AS_LogoOL_CLR_P-1920x850.png';
        
        // Check if file exists
        if (file_exists($logo_path)) {
            // Check if image is already in media library
            $attachment_id = attachment_url_to_postid($logo_url);
            
            // If not in media library, add it
            if (!$attachment_id) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                
                $upload = wp_upload_bits(basename($logo_path), null, file_get_contents($logo_path));
                
                if (!$upload['error']) {
                    $attachment = array(
                        'post_mime_type' => 'image/png',
                        'post_title' => 'Accurate Surveyors Logo',
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    
                    $attachment_id = wp_insert_attachment($attachment, $upload['file']);
                    $attach_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                    wp_update_attachment_metadata($attachment_id, $attach_data);
                }
            }
            
            // Set as custom logo if we have an attachment ID
            if ($attachment_id) {
                set_theme_mod('custom_logo', $attachment_id);
            }
        }
    }
}
add_action('after_setup_theme', 'accurate_set_default_logo');

/*
// #region agent log - Debug upload directory issue (uncomment to re-enable)
function accurate_debug_get_log_path() {
    $workspace_log = '/Users/shadrachtuck/Local Sites/accurate-surveying-mapping/.cursor/debug.log';
    $workspace_dir = dirname($workspace_log);
    if (is_dir($workspace_dir) || @mkdir($workspace_dir, 0755, true)) {
        return $workspace_log;
    }
    $theme_log_dir = get_stylesheet_directory() . '/.cursor';
    if (!is_dir($theme_log_dir)) {
        @mkdir($theme_log_dir, 0755, true);
    }
    return $theme_log_dir . '/debug.log';
}

function accurate_debug_upload_dir($upload_dir) {
    $log_path = accurate_debug_get_log_path();
    $log_dir = dirname($log_path);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $current_time = current_time('mysql');
    $current_timestamp = current_time('timestamp');
    $wp_date = date('Y/m', $current_timestamp);
    $uploads_use_yearmonth = get_option('uploads_use_yearmonth_folders');
    $basedir_exists = isset($upload_dir['basedir']) ? is_dir($upload_dir['basedir']) : false;
    $basedir_writable = isset($upload_dir['basedir']) ? is_writable($upload_dir['basedir']) : false;
    $path_exists = isset($upload_dir['path']) ? is_dir($upload_dir['path']) : false;
    $path_writable = isset($upload_dir['path']) ? is_writable($upload_dir['path']) : false;
    $path_perms = isset($upload_dir['path']) && file_exists($upload_dir['path']) ? substr(sprintf('%o', fileperms($upload_dir['path'])), -4) : 'N/A';
    $basedir_perms = isset($upload_dir['basedir']) && file_exists($upload_dir['basedir']) ? substr(sprintf('%o', fileperms($upload_dir['basedir'])), -4) : 'N/A';
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'A',
        'location' => 'functions.php:upload_dir_filter',
        'message' => 'upload_dir filter called',
        'data' => [
            'upload_path' => $upload_dir['path'] ?? 'N/A',
            'upload_url' => $upload_dir['url'] ?? 'N/A',
            'subdir' => $upload_dir['subdir'] ?? 'N/A',
            'basedir' => $upload_dir['basedir'] ?? 'N/A',
            'baseurl' => $upload_dir['baseurl'] ?? 'N/A',
            'wp_current_time' => $current_time,
            'wp_current_timestamp' => $current_timestamp,
            'wp_expected_date_folder' => $wp_date,
            'uploads_use_yearmonth_folders' => $uploads_use_yearmonth,
            'timezone_string' => get_option('timezone_string'),
            'gmt_offset' => get_option('gmt_offset'),
            'basedir_exists' => $basedir_exists,
            'basedir_writable' => $basedir_writable,
            'basedir_perms' => $basedir_perms,
            'path_exists' => $path_exists,
            'path_writable' => $path_writable,
            'path_perms' => $path_perms,
            'error' => $upload_dir['error'] ?? false
        ],
        'timestamp' => round(microtime(true) * 1000)
    ]) . "\n";
    @file_put_contents($log_path, $log_entry, FILE_APPEND);
    return $upload_dir;
}
add_filter('upload_dir', 'accurate_debug_upload_dir', 999);

function accurate_debug_wp_upload_dir() {
    $log_path = accurate_debug_get_log_path();
    $log_dir = dirname($log_path);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $upload_dir = wp_upload_dir();
    $current_time = current_time('mysql');
    $current_timestamp = current_time('timestamp');
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'B',
        'location' => 'functions.php:wp_upload_dir_check',
        'message' => 'wp_upload_dir() result',
        'data' => [
            'upload_path' => $upload_dir['path'] ?? 'N/A',
            'upload_url' => $upload_dir['url'] ?? 'N/A',
            'subdir' => $upload_dir['subdir'] ?? 'N/A',
            'basedir' => $upload_dir['basedir'] ?? 'N/A',
            'baseurl' => $upload_dir['baseurl'] ?? 'N/A',
            'wp_current_time' => $current_time,
            'wp_current_timestamp' => $current_timestamp,
            'error' => $upload_dir['error'] ?? false
        ],
        'timestamp' => round(microtime(true) * 1000)
    ]) . "\n";
    @file_put_contents($log_path, $log_entry, FILE_APPEND);
}
add_action('admin_init', 'accurate_debug_wp_upload_dir');

function accurate_debug_upload_handler($file) {
    $log_path = accurate_debug_get_log_path();
    $log_dir = dirname($log_path);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $upload_dir = wp_upload_dir();
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'C',
        'location' => 'functions.php:upload_handler',
        'message' => 'File upload handler called',
        'data' => [
            'file_name' => $file['name'] ?? 'N/A',
            'file_type' => $file['type'] ?? 'N/A',
            'upload_path' => $upload_dir['path'] ?? 'N/A',
            'upload_subdir' => $upload_dir['subdir'] ?? 'N/A',
            'target_path' => isset($upload_dir['path']) && isset($file['name']) ? $upload_dir['path'] . '/' . $file['name'] : 'N/A',
            'upload_error' => $upload_dir['error'] ?? false
        ],
        'timestamp' => round(microtime(true) * 1000)
    ]) . "\n";
    @file_put_contents($log_path, $log_entry, FILE_APPEND);
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'accurate_debug_upload_handler', 999);

function accurate_debug_upload_error($movefile, $file) {
    $log_path = accurate_debug_get_log_path();
    $log_dir = dirname($log_path);
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $log_entry = json_encode([
        'sessionId' => 'debug-session',
        'runId' => 'run1',
        'hypothesisId' => 'D',
        'location' => 'functions.php:upload_error',
        'message' => 'Upload error detected',
        'data' => [
            'file_name' => $file['name'] ?? 'N/A',
            'error' => $movefile['error'] ?? 'N/A',
            'file_path' => $movefile['file'] ?? 'N/A'
        ],
        'timestamp' => round(microtime(true) * 1000)
    ]) . "\n";
    @file_put_contents($log_path, $log_entry, FILE_APPEND);
    return $movefile;
}
add_filter('wp_handle_upload', 'accurate_debug_upload_error', 999, 2);
// #endregion
*/
