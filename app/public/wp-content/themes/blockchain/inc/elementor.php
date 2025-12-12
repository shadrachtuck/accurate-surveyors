<?php
namespace Elementor;

/**
 * Blockchain Elementor related code.
 */

add_action( 'elementor/theme/register_locations', 'Elementor\blockchain_register_elementor_locations' );
function blockchain_register_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_location( 'header' );
	$elementor_theme_manager->register_location( 'footer' );
	$elementor_theme_manager->register_location( 'single' );
	$elementor_theme_manager->register_location( 'archive' );
}

add_action( 'elementor/init', 'Elementor\blockchain_elementor_init' );
function blockchain_elementor_init() {
	Plugin::instance()->elements_manager->add_category(
		'blockchain-elements',
		[
			'title' => __( 'Blockchain Elements', 'blockchain' ),
			'icon'  => 'font',
		],
		1
	);
}

add_action( 'elementor/widgets/widgets_registered', 'Elementor\blockchain_elementor_add_elements' );
function blockchain_elementor_add_elements() {

	require_once get_theme_file_path( '/inc/elementor/post-type.php' );
	Plugin::instance()->widgets_manager->register_widget_type( new Widget_Post_Type() );

	require_once get_theme_file_path( '/inc/elementor/latest-post-type.php' );
	Plugin::instance()->widgets_manager->register_widget_type( new Widget_Latest_Post_Type() );

	require_once get_theme_file_path( '/inc/elementor/post-type-items.php' );
	Plugin::instance()->widgets_manager->register_widget_type( new Widget_Post_Type_Items() );

	// The following elements require the theme-specific plugin to be active.
	if ( function_exists( 'blockchain_plugin_setup' ) ) {
		require_once get_theme_file_path( '/inc/elementor/coinmarketcap-ticker.php' );
		Plugin::instance()->widgets_manager->register_widget_type( new Widget_Coinmarketcap_Ticker() );

		require_once get_theme_file_path( '/inc/elementor/crypto-table.php' );
		Plugin::instance()->widgets_manager->register_widget_type( new Widget_Crypto_Table() );

		require_once get_theme_file_path( '/inc/elementor/coinmarketcap-single.php' );
		Plugin::instance()->widgets_manager->register_widget_type( new Widget_Coinmarketcap_Single() );

		require_once get_theme_file_path( '/inc/elementor/coinmarketcap-global.php' );
		Plugin::instance()->widgets_manager->register_widget_type( new Widget_Coinmarketcap_Global() );
	}
}


add_action( 'elementor/editor/before_enqueue_scripts', 'Elementor\blockchain_elementor_enqueue_scripts' );
function blockchain_elementor_enqueue_scripts() {
	blockchain_register_scripts();
	blockchain_admin_scripts( '' );

	wp_enqueue_media();
	wp_enqueue_style( 'blockchain-widgets' );
	wp_enqueue_script( 'blockchain-widgets' );

	wp_enqueue_script( 'blockchain-elementor-ajax', get_template_directory_uri() . '/js/admin/elementor-ajax.js' );

	$params = array(
		'ajaxurl'         => admin_url( 'admin-ajax.php' ),
		'no_posts_found'  => esc_html__( 'No posts found.', 'blockchain' ),
		'get_posts_nonce' => wp_create_nonce( 'blockchain_get_posts_nonce' ),
	);

	wp_localize_script( 'blockchain-elementor-ajax', 'blockchain_elementor_ajax', $params );
}

add_action( 'wp_ajax_blockchain_elementor_get_posts', 'Elementor\blockchain_ajax_elementor_get_posts' );
function blockchain_ajax_elementor_get_posts() {

	// Verify nonce.
	if ( ! isset( $_POST['get_posts_nonce'] ) || ! wp_verify_nonce( $_POST['get_posts_nonce'], 'blockchain_get_posts_nonce' ) ) {
		die( 'Permission denied' );
	}

	$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : 'post' ;

	$q = new \WP_Query( array(
		'post_type'      => $post_type,
		'posts_per_page' => - 1,
	) );

	?><option><?php esc_html_e( 'Select an item', 'blockchain' ); ?></option><?php
	while ( $q->have_posts() ) : $q->the_post();
		?><option value="<?php echo esc_attr( get_the_ID() ); ?>"><?php the_title(); ?></option><?php
	endwhile;
	wp_reset_postdata();
	wp_die();
}

function blockchain_get_change_interval_short_text( $interval ) {
	$intervals = array(
		'1h'  => esc_html__( '1h', 'blockchain' ),
		'24h' => esc_html__( '24h', 'blockchain' ),
		'7d'  => esc_html__( '7d', 'blockchain' ),
	);

	if ( ! array_key_exists( $interval, $intervals ) ) {
		$interval = $this->defaults['interval'];
	}

	return $intervals[ $interval ];
}

function blockchain_get_available_post_types() {

	$post_types = get_post_types( array(
		'public' => true,
	), 'objects' );

	$testimonials = get_post_types( array(
		'name' => 'blockchain_testimon',
	), 'objects' );

	$post_types = array_merge( $post_types, $testimonials );

	unset( $post_types['attachment'] );
	unset( $post_types['elementor_library'] );

	$post_types = apply_filters( 'blockchain_widget_post_types_dropdown', $post_types, __CLASS__ );

	$labels = [];

	foreach ( $post_types as $key => $type ) {
		$labels[ $type->name ] = $type->labels->name;
	}

	return $labels;
}
