<?php
/**
 * Blockchain scripts and styles related functions.
 */

/**
 * Register Google Fonts
 */
function blockchain_fonts_url() {
	$fonts_url = '';
	$fonts     = array();
	$subsets   = 'latin,latin-ext';

	/* translators: If there are characters in your language that are not supported by Roboto, translate this to 'off'. Do not translate into your own language. */
	if ( 'off' !== _x( 'on', 'Roboto font: on or off', 'blockchain' ) ) {
		$fonts[] = 'Roboto:400,400i,500,700';
	}

	if ( $fonts ) {
		$fonts_url = add_query_arg( array(
			'family' => urlencode( implode( '|', $fonts ) ),
			'subset' => urlencode( $subsets ),
		), 'https://fonts.googleapis.com/css' );
	}

	return $fonts_url;
}

/**
 * Register scripts and styles unconditionally.
 */
function blockchain_register_scripts() {
	$theme = wp_get_theme();

	if ( ! wp_script_is( 'alpha-color-picker', 'enqueued' ) && ! wp_script_is( 'alpha-color-picker', 'registered' ) ) {
		wp_register_style( 'alpha-color-picker', get_template_directory_uri() . '/assets/vendor/alpha-color-picker/alpha-color-picker.css', array(
			'wp-color-picker',
		), '1.0.0' );
		wp_register_script( 'alpha-color-picker', get_template_directory_uri() . '/assets/vendor/alpha-color-picker/alpha-color-picker.js', array(
			'jquery',
			'wp-color-picker',
		), '1.0.0', true );
	}

	if ( ! wp_script_is( 'slick', 'enqueued' ) && ! wp_script_is( 'slick', 'registered' ) ) {
		wp_register_style( 'slick', get_template_directory_uri() . '/assets/vendor/slick/slick.css', array(), '1.6.0' );
		wp_register_script( 'slick', get_template_directory_uri() . '/assets/vendor/slick/slick.js', array(
			'jquery',
		), '1.6.0', true );
	}

	if ( ! wp_script_is( 'blockchain-plugin-post-meta', 'enqueued' ) && ! wp_script_is( 'blockchain-plugin-post-meta', 'registered' ) ) {
		wp_register_style( 'blockchain-plugin-post-meta', get_template_directory_uri() . '/css/admin/post-meta.css', array(
			'alpha-color-picker',
		), $theme->get( 'Version' ) );
		wp_register_script( 'blockchain-plugin-post-meta', get_template_directory_uri() . '/js/admin/post-meta.js', array(
			'media-editor',
			'jquery',
			'jquery-ui-sortable',
			'alpha-color-picker',
		), $theme->get( 'Version' ), true );

		$settings = array(
			'ajaxurl'             => admin_url( 'admin-ajax.php' ),
			'tSelectFile'         => esc_html__( 'Select file', 'blockchain' ),
			'tSelectFiles'        => esc_html__( 'Select files', 'blockchain' ),
			'tUseThisFile'        => esc_html__( 'Use this file', 'blockchain' ),
			'tUseTheseFiles'      => esc_html__( 'Use these files', 'blockchain' ),
			'tUpdateGallery'      => esc_html__( 'Update gallery', 'blockchain' ),
			'tLoading'            => esc_html__( 'Loading...', 'blockchain' ),
			'tPreviewUnavailable' => esc_html__( 'Gallery preview not available.', 'blockchain' ),
			'tRemoveImage'        => esc_html__( 'Remove image', 'blockchain' ),
			'tRemoveFromGallery'  => esc_html__( 'Remove from gallery', 'blockchain' ),
		);
		wp_localize_script( 'blockchain-plugin-post-meta', 'blockchain_plugin_PostMeta', $settings );
	}

	wp_register_style( 'blockchain-repeating-fields', get_template_directory_uri() . '/css/admin/repeating-fields.css', array(), $theme->get( 'Version' ) );
	wp_register_script( 'blockchain-repeating-fields', get_template_directory_uri() . '/js/admin/repeating-fields.js', array(
		'jquery',
		'jquery-ui-sortable',
	), $theme->get( 'Version' ), true );

	wp_register_style( 'font-awesome', get_template_directory_uri() . '/assets/vendor/fontawesome/css/font-awesome.css', array(), '4.7.0' );

	wp_register_script( 'imagesLoaded', get_template_directory_uri() . '/js/imagesloaded.pkgd.min.js', array( 'jquery' ), '4.1.3', true );
	wp_register_script( 'anim-on-scroll', get_template_directory_uri() . '/js/anim-on-scroll.js', array(
		'jquery',
		'imagesLoaded',
	), '1.0.1', true );

	wp_register_style( 'jquery-magnific-popup', get_template_directory_uri() . '/assets/vendor/magnific-popup/magnific.css', array(), '1.0.0' );
	wp_register_script( 'jquery-magnific-popup', get_template_directory_uri() . '/assets/vendor/magnific-popup/jquery.magnific-popup.js', array( 'jquery' ), '1.0.0', true );
	wp_register_script( 'blockchain-magnific-init', get_template_directory_uri() . '/js/magnific-init.js', array( 'jquery' ), $theme->get( 'Version' ), true );

	wp_register_style( 'blockchain-google-font', blockchain_fonts_url(), array(), null );
	wp_register_style( 'blockchain-base', get_template_directory_uri() . '/css/base.css', array(), $theme->get( 'Version' ) );
	wp_register_style( 'mmenu', get_template_directory_uri() . '/css/mmenu.css', array(), '5.5.3' );

	wp_register_style( 'datatables', get_template_directory_uri() . '/css/datatables.min.css', array(), '1.10.16' );

	wp_register_style( 'blockchain-dependencies', false, array(
		'blockchain-google-font',
		'blockchain-base',
		'blockchain-common',
		'mmenu',
		'font-awesome',
		'slick',
		'datatables',
	), $theme->get( 'Version' ) );

	if ( is_child_theme() ) {
		wp_register_style( 'blockchain-style-parent', get_template_directory_uri() . '/style.css', array(
			'blockchain-dependencies',
		), $theme->get( 'Version' ) );
	}

	wp_register_style( 'blockchain-style', get_stylesheet_uri(), array(
		'blockchain-dependencies',
	), $theme->get( 'Version' ) );


	wp_register_script( 'mmenu', get_template_directory_uri() . '/js/jquery.mmenu.min.all.js', array( 'jquery' ), '5.5.3', true );
	wp_register_script( 'fitVids', get_template_directory_uri() . '/js/jquery.fitvids.js', array( 'jquery' ), '1.1', true );
	wp_register_script( 'isotope', get_template_directory_uri() . '/js/isotope.pkgd.min.js', array( 'jquery' ), '3.0.2', true );
	wp_register_script( 'sticky-kit', get_template_directory_uri() . '/js/jquery.sticky-kit.min.js', array( 'jquery' ), '1.1.4', true );

	wp_register_script( 'datatables', get_template_directory_uri() . '/js/datatables.min.js', array( 'jquery' ), '1.10.16', true );

	wp_register_script( 'blockchain-dependencies', false, array(
		'jquery',
		'mmenu',
		'fitVids',
		'slick',
		'isotope',
		'sticky-kit',
		'anim-on-scroll',
		'datatables',
	), $theme->get( 'Version' ), true );

	wp_register_script( 'blockchain-front-scripts', get_template_directory_uri() . '/js/scripts.js', array(
		'blockchain-dependencies',
	), $theme->get( 'Version' ), true );

}
add_action( 'init', 'blockchain_register_scripts' );

/**
 * Enqueue scripts and styles.
 */
function blockchain_enqueue_scripts() {
	$theme = wp_get_theme();

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( get_theme_mod( 'theme_lightbox', 1 ) ) {
		wp_enqueue_style( 'jquery-magnific-popup' );
		wp_enqueue_script( 'jquery-magnific-popup' );
		wp_enqueue_script( 'blockchain-magnific-init' );
	}

	if ( is_child_theme() ) {
		wp_enqueue_style( 'blockchain-style-parent' );
	}

	wp_enqueue_style( 'blockchain-style' );
	wp_add_inline_style( 'blockchain-style', blockchain_get_all_customizer_css() );

	wp_enqueue_script( 'blockchain-front-scripts' );

}
add_action( 'wp_enqueue_scripts', 'blockchain_enqueue_scripts' );


/**
 * Enqueue admin scripts and styles.
 */
function blockchain_admin_scripts( $hook ) {
	$theme = wp_get_theme();

	wp_register_style( 'blockchain-widgets', get_template_directory_uri() . '/css/admin/widgets.css', array(
		'blockchain-repeating-fields',
		'blockchain-plugin-post-meta',
		'alpha-color-picker',
	), $theme->get( 'Version' ) );

	wp_register_script( 'blockchain-widgets', get_template_directory_uri() . '/js/admin/widgets.js', array(
		'jquery',
		'blockchain-repeating-fields',
		'blockchain-plugin-post-meta',
		'alpha-color-picker',
	), $theme->get( 'Version' ), true );
	$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
	);
	wp_localize_script( 'blockchain-widgets', 'ThemeWidget', $params );


	//
	// Enqueue
	//
	if ( in_array( $hook, array( 'widgets.php', 'customize.php' ), true ) ) {
		wp_enqueue_style( 'blockchain-repeating-fields' );
		wp_enqueue_script( 'blockchain-repeating-fields' );

		wp_enqueue_media();
		wp_enqueue_style( 'blockchain-widgets' );
		wp_enqueue_script( 'blockchain-widgets' );
	}

}
add_action( 'admin_enqueue_scripts', 'blockchain_admin_scripts' );
