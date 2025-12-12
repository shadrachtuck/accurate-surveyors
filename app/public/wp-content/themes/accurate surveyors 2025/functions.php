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

