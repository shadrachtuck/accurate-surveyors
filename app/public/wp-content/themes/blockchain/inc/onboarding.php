<?php
/**
 * Blockchain onboarding related code.
 */

if ( ! defined( 'BLOCKCHAIN_WHITELABEL' ) || false === (bool) BLOCKCHAIN_WHITELABEL ) {
	add_filter( 'pt-ocdi/import_files', 'blockchain_ocdi_import_files' );
	add_action( 'pt-ocdi/after_import', 'blockchain_ocdi_after_import_setup' );
}

add_filter( 'pt-ocdi/timeout_for_downloading_import_file', 'blockchain_ocdi_download_timeout' );
function blockchain_ocdi_download_timeout( $timeout ) {
	return 60;
}

function blockchain_ocdi_import_files( $files ) {
	if ( ! defined( 'BLOCKCHAIN_NAME' ) ) {
		return $files;
	}

	$demo_dir_url = untrailingslashit( apply_filters( 'blockchain_ocdi_demo_dir_url', 'https://www.cssigniter.com/sample_content/' . BLOCKCHAIN_NAME ) );

	// When having more that one predefined imports, set a preview image, preview URL, and categories for isotope-style filtering.
	$new_files = array(
		array(
			'import_file_name'           => esc_html__( 'Demo Import', 'blockchain' ),
			'import_file_url'            => $demo_dir_url . '/content.xml',
			'import_widget_file_url'     => $demo_dir_url . '/widgets.wie',
			'import_customizer_file_url' => $demo_dir_url . '/customizer.dat',
		),
	);

	return array_merge( $files, $new_files );
}

function blockchain_ocdi_after_import_setup() {
	// Set up nav menus.
	$main_menu = get_term_by( 'name', 'Main', 'nav_menu' );

	set_theme_mod( 'nav_menu_locations', array(
		'menu-1' => $main_menu->term_id,
	) );

	// Set up home and blog pages.
	$front_page_id = get_page_by_title( 'Home' );
	$blog_page_id  = get_page_by_title( 'News' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id->ID );
	update_option( 'page_for_posts', $blog_page_id->ID );
}

add_action( 'init', 'blockchain_onboarding_page_init' );
function blockchain_onboarding_page_init() {

	$data = array(
		'show_page'                => true,
		'description'              => __( 'Blockchain is a powerful business theme for WordPress. The following post types are available: Portfolio, Services, Case Studies, Jobs, Events & Testimonials. <strong>WooCommerce</strong> is also supported by Blockchain.', 'blockchain' ),
		'required_plugins_page'    => array(
			'plugins' => array(
				'blockchain-plugin' => array(
					'title'       => __( 'Blockchain Essentials', 'blockchain' ),
					'description' => __( "Blockchain's core functions.", 'blockchain' ),
					'bundled'     => true,
					'is_callable' => 'blockchain_plugin_setup',
				),
			),
		),
		'recommended_plugins_page' => array(
			'plugins' => array(
				'maxslider'             => array(
					'title'       => __( 'MaxSlider', 'blockchain' ),
					'description' => __( 'Add a custom responsive slider to any page of your website.', 'blockchain' ),
				),
				'one-click-demo-import' => array(
					'title'       => __( 'One Click Demo Import', 'blockchain' ),
					'description' => __( 'Import your demo content, widgets and theme settings with one click.', 'blockchain' ),
				),
				'elementor'             => array(
					'title'       => __( 'Elementor', 'blockchain' ),
					'description' => __( 'Elementor is a front-end drag & drop page builder for WordPress. ', 'blockchain' ),
				),
				'woocommerce'           => array(
					'title'       => __( 'WooCommerce', 'blockchain' ),
					'description' => __( 'A fully customizable, open source eCommerce platform built for WordPress. ', 'blockchain' ),
				),
			),
		),

	);

	$onboarding = new Blockchain_Onboarding_Page();
	$onboarding->init( apply_filters( 'blockchain_onboarding_page_array', $data ) );

//	// Full parameters list. Only use what's required or needs to be overridden.
//	$theme = wp_get_theme();
//	$data  = array(
//		// Required. Turns the onboarding page on/off.
//		'show_page'                => true,
//		// Optional. The text to be used for the admin menu. If empty, defaults to "About theme_name"
//		/* translators: %s is the theme name. */
//		'menu_title'               => sprintf( __( 'About %s', 'blockchain' ), $theme->get( 'Name' ) ),
//		// Optional. The text to be displayed in the page's title tag. If empty, defaults to "About theme_name"
//		/* translators: %s is the theme name. */
//		'page_title'               => sprintf( __( 'About %s', 'blockchain' ), $theme->get( 'Name' ) ),
//		// Optional. The onboarding page's title, placeholders available :theme_name:, :theme_version:. If empty, defaults to "Welcome to :theme_name:! - Version :theme_version:"
//		'title'                    => __( 'Welcome to :theme_name:! - Version :theme_version:', 'blockchain' ),
//		// Optional. The theme's description. Some HTML is allowed (no p).
//		'description'              => '',
//		// Optional. Boolean. Whether to show the logo. Default: true in normal mode. false if whitelabel.
//		'logo_show'                => defined( 'BLOCKCHAIN_WHITELABEL' ) && BLOCKCHAIN_WHITELABEL ? false : true,
//		// Optional. The logo's image source URL. Defaults to the bundled logo.
//		'logo_src'                 => get_template_directory_uri() . '/inc/onboarding/assets/cssigniter_logo.svg',
//		// Optional. The logo's link URL. Defaults to 'https://www.cssigniter.com/themes/' . BLOCKCHAIN_NAME . '/'
//		'logo_url'                 => 'https://www.cssigniter.com/themes/' . BLOCKCHAIN_NAME . '/',
//		// Optional. The default active tab. Default 'required_plugins'. Must be one of the keys in the tabs[] array.
//		'default_tab'              => 'required_plugins',
//		// Optional. slug => label pairs for each tab. Default are as follows:
//		'tabs'                     => array(
//			'required_plugins'    => __( 'Required Plugins', 'blockchain' ),
//			'recommended_plugins' => __( 'Recommended Plugins', 'blockchain' ),
//			'sample_content'      => __( 'Sample Content', 'blockchain' ),
//			'support'             => __( 'Support', 'blockchain' ),
//		),
//		'required_plugins_page'    => array(
//			'plugins' => array(
//				// Each plugin is registered as 'slug' => array(). The slug must match the plugin's directory.
//				'plugin-slug' => array(
//					// Required. The plugin's title.
//					'title'       => __( 'Plugin Title', 'blockchain' ),
//					// Optional. The plugin's description, or why the plugin is required.
//					'description' => '',
//					// Optional. If true, the plugin zip will be searched in the theme's plugins/ directory, named "plugin-slug.zip". Default false.
//					'bundled'     => false,
//					// Optional. If passed string or array is callable, then the plugin will appear as activated.
//					'is_callable' => '',
//					// Optional. If not passed, it's assumed to be "plugin-slug.php". Only pass a filename. It gets combined with the plugin slug as needed.
//					'plugin_file' => '',
//				),
//			),
//		),
//		'recommended_plugins_page' => array(
//			'plugins' => array(
//				// Each plugin is registered as 'slug' => array()
//				'plugin-slug' => array(
//					// Required. The plugin's title.
//					'title'       => __( 'Plugin Title', 'blockchain' ),
//					// Optional. The plugin's description, or why the plugin is required.
//					'description' => '',
//					// Optional. If true, the plugin zip will be searched in the theme's plugins/ directory, named "plugin-slug.zip". Default false.
//					'bundled'     => false,
//					// Optional. If passed string or array is callable, then the plugin will appear as activated.
//					'is_callable' => '',
//					// Optional. If not passed, it's assumed to be "plugin-slug.php". Only pass a filename. It gets combined with the plugin slug as needed.
//					'plugin_file' => '',
//				),
//			),
//		),
//		'support_page'             => array(
//			'sections' => array(
//				'documentation' => array(
//					'title'       => __( 'Theme Documentation', 'blockchain' ),
//					'description' => __( "If you don't want to import our demo sample content, just visit this page and learn how to set things up individually.", 'blockchain' ),
//					'link_url'    => 'https://www.cssigniter.com/docs/' . BLOCKCHAIN_NAME . '/',
//				),
//				'kb'            => array(
//					'title'       => __( 'Knowledge Base', 'blockchain' ),
//					'description' => __( 'Browse our library of step by step how-to articles, tutorials, and guides to get quick answers.', 'blockchain' ),
//					'link_url'    => 'https://www.cssigniter.com/docs/knowledgebase/',
//				),
//				'support'       => array(
//					'title'       => __( 'Request Support', 'blockchain' ),
//					'description' => __( 'Got stuck? No worries, just visit our support page, submit your ticket and we will be there for you within 24 hours.', 'blockchain' ),
//					'link_url'    => 'https://www.cssigniter.com/support/',
//				),
//			),
//		),
//	);

}

/**
 * User onboarding.
 */
require_once get_theme_file_path( '/inc/onboarding/onboarding-page.php' );
