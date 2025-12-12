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

// Modify hero styles to ensure gradient fallback only shows when no image is set
function accurate_hero_styles_fallback($style, $hero) {
    // Check for image in both 'image' (URL) and 'image_id' (for singular pages)
    $has_image = !empty($hero['image']) || !empty($hero['image_id']);
    
    // Only add gradient fallback if no image is set in WordPress customizer
    if (!$has_image) {
        $styles_selector = '.page-hero';
        
        // Build gradient background (replaces background-color fallback)
        $gradient_bg = 'background-image: url(\'' . get_stylesheet_directory_uri() . '/assets/images/nav-star.svg\'), linear-gradient(to right, #000 15%, #191a1b 40%, var(--primary-blue), var(--primary-green)); background-size: cover, cover; background-position: bottom center, center center; background-repeat: no-repeat, no-repeat; ';
        
        // If style already exists (has bg_color or text_color), prepend gradient to it
        if (!empty($style) && strpos($style, $styles_selector) !== false) {
            // Insert gradient background-image before the closing brace
            $style = str_replace($styles_selector . ' { ', $styles_selector . ' { ' . $gradient_bg, $style);
        } else {
            // No existing style, create new one with gradient
            $style = $styles_selector . ' { ';
            $style .= $gradient_bg;
            
            // Add bg_color and text_color if they exist
            if (!empty($hero['bg_color'])) {
                $style .= sprintf('background-color: %s; ', esc_attr($hero['bg_color']));
            }
            if (!empty($hero['text_color'])) {
                $style .= sprintf('color: %s; ', esc_attr($hero['text_color']));
            }
            $style .= '} ' . PHP_EOL;
        }
    }
    
    // If image IS set, return original style unchanged (customizer handles the image)
    return $style;
}
add_filter('blockchain_hero_styles', 'accurate_hero_styles_fallback', 10, 2);

// Add class to hero element when image is set (for CSS targeting)
// This works for all page types (homepage, singular pages, archives, etc.)
function accurate_hero_classes($classes) {
    $hero = blockchain_get_hero_data();
    // Check for image in hero data (works for both global and per-page images)
    // Check both 'image' (URL) and 'image_id' (attachment ID)
    if (!empty($hero['image']) || !empty($hero['image_id'])) {
        $classes[] = 'has-custom-hero-image';
    }
    return $classes;
}
add_filter('blockchain_hero_classes', 'accurate_hero_classes', 20); // Higher priority to ensure it runs after other filters

// Modify hero overlay to use ::after instead of ::before (since we disabled ::before)
function accurate_hero_overlay_selector($selector) {
    // Change overlay selector from ::before to ::after so our CSS overlay works
    return '.page-hero::after';
}
add_filter('blockchain_hero_styles_overlay_selector', 'accurate_hero_overlay_selector');

