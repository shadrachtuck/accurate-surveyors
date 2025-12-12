<?php
/*
Plugin Name: Blockchain theme-specific plugin
Description: Features required by the Blockchain theme.
Plugin URI: https://www.cssigniter.com/themes/blockchain/
Version: 1.3
License: GNU General Public License v2 or later
Author: The CSSIgniter Team
Author URI: https://www.cssigniter.com/
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: blockchain-plugin
*/

if ( ! defined( 'BLOCKCHAIN_PLUGIN_VERSION' ) ) {
	define( 'BLOCKCHAIN_PLUGIN_VERSION', '1.3' );
}

if ( ! defined( 'BLOCKCHAIN_PLUGIN_DIR' ) ) {
	define( 'BLOCKCHAIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'BLOCKCHAIN_PLUGIN_DIR_URL' ) ) {
	define( 'BLOCKCHAIN_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
}

add_action( 'after_setup_theme', 'blockchain_plugin_setup' );
function blockchain_plugin_setup() {
	load_plugin_textdomain( 'blockchain-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}

/**
 * Enqueue admin scripts and styles.
 */
function blockchain_plugin_admin_scripts( $hook ) {

	if ( ! wp_script_is( 'alpha-color-picker', 'enqueued' ) && ! wp_script_is( 'alpha-color-picker', 'registered' ) ) {
		wp_register_style( 'alpha-color-picker', untrailingslashit( BLOCKCHAIN_PLUGIN_DIR_URL ) . '/assets/vendor/alpha-color-picker/alpha-color-picker.css', array(
			'wp-color-picker',
		), '1.0.0' );
		wp_register_script( 'alpha-color-picker', untrailingslashit( BLOCKCHAIN_PLUGIN_DIR_URL ) . '/assets/vendor/alpha-color-picker/alpha-color-picker.js', array(
			'jquery',
			'wp-color-picker',
		), '1.0.0', true );
	}

	wp_register_style( 'blockchain-plugin-post-meta', untrailingslashit( BLOCKCHAIN_PLUGIN_DIR_URL ) . '/assets/css/post-meta.css', array(
		'alpha-color-picker',
	), BLOCKCHAIN_PLUGIN_VERSION );
	wp_register_script( 'blockchain-plugin-post-meta', untrailingslashit( BLOCKCHAIN_PLUGIN_DIR_URL ) . '/assets/js/post-meta.js', array(
		'media-editor',
		'jquery',
		'jquery-ui-sortable',
		'alpha-color-picker',
	), BLOCKCHAIN_PLUGIN_VERSION, true );

	$settings = array(
		'ajaxurl'             => admin_url( 'admin-ajax.php' ),
		'tSelectFile'         => esc_html__( 'Select file', 'blockchain-plugin' ),
		'tSelectFiles'        => esc_html__( 'Select files', 'blockchain-plugin' ),
		'tUseThisFile'        => esc_html__( 'Use this file', 'blockchain-plugin' ),
		'tUseTheseFiles'      => esc_html__( 'Use these files', 'blockchain-plugin' ),
		'tUpdateGallery'      => esc_html__( 'Update gallery', 'blockchain-plugin' ),
		'tLoading'            => esc_html__( 'Loading...', 'blockchain-plugin' ),
		'tPreviewUnavailable' => esc_html__( 'Gallery preview not available.', 'blockchain-plugin' ),
		'tRemoveImage'        => esc_html__( 'Remove image', 'blockchain-plugin' ),
		'tRemoveFromGallery'  => esc_html__( 'Remove from gallery', 'blockchain-plugin' ),
	);
	wp_localize_script( 'blockchain-plugin-post-meta', 'blockchain_plugin_PostMeta', $settings );

	wp_register_style( 'jquery-ui-style', untrailingslashit( BLOCKCHAIN_PLUGIN_DIR_URL ) . '/assets/vendor/jquery-ui/jquery-ui.css', array(), '1.11.4' );

	wp_register_script( 'blockchain-plugin-post-edit', untrailingslashit( BLOCKCHAIN_PLUGIN_DIR_URL ) . '/assets/js/post-edit.js', array(
		'jquery-ui-datepicker',
	), BLOCKCHAIN_PLUGIN_VERSION, true );

	//
	// Enqueue
	//
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_style( 'blockchain-plugin-post-meta' );
		wp_enqueue_script( 'blockchain-plugin-post-meta' );

		wp_enqueue_style( 'jquery-ui-style' );
		wp_enqueue_script( 'blockchain-plugin-post-edit' );
	}

}
add_action( 'admin_enqueue_scripts', 'blockchain_plugin_admin_scripts' );


function blockchain_plugin_get_columns_classes( $columns ) {
	if ( function_exists( 'blockchain_get_columns_classes' ) ) {
		return blockchain_get_columns_classes( $columns );
	}

	switch ( intval( $columns ) ) {
		case 1:
			$classes = 'col-12';
			break;
		case 2:
			$classes = 'col-sm-6 col-12';
			break;
		case 3:
			$classes = 'col-lg-4 col-sm-6 col-12';
			break;
		case 4:
		default:
			$classes = 'col-xl-3 col-sm-6 col-12';
			break;
	}

	// Filter name intentionally same to blockchain_get_columns_classes()
	return apply_filters( 'blockchain_get_columns_classes', $classes, $columns );
}


function blockchain_plugin_load_widgets() {
	require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/widgets/coinmarketcap-ticker.php';
	register_widget( 'CI_Widget_Coinmarketcap_Ticker' );

	require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/widgets/coinmarketcap-single.php';
	register_widget( 'CI_Widget_Coinmarketcap_Single' );

	require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/widgets/coinmarketcap-global.php';
	register_widget( 'CI_Widget_Coinmarketcap_Global' );
}
add_action( 'widgets_init', 'blockchain_plugin_load_widgets' );


function blockchain_plugin_activated() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	blockchain_plugin_create_cpt_portfolio();

	blockchain_plugin_create_cpt_service();

	blockchain_plugin_create_cpt_case_study();

	blockchain_plugin_create_cpt_team();

	blockchain_plugin_create_cpt_job();

	blockchain_plugin_create_cpt_event();

	blockchain_plugin_create_cpt_testimonial();

	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'blockchain_plugin_activated' );

function blockchain_plugin_deactivated() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	unregister_post_type( 'blockchain_portfolio' );
	unregister_taxonomy( 'blockchain_portfolio_category' );

	unregister_post_type( 'blockchain_service' );
	unregister_taxonomy( 'blockchain_service_category' );

	unregister_post_type( 'blockchain_cstudy' );
	unregister_taxonomy( 'blockchain_cstudy_category' );

	unregister_post_type( 'blockchain_team' );
	unregister_taxonomy( 'blockchain_team_category' );

	unregister_post_type( 'blockchain_job' );
	unregister_taxonomy( 'blockchain_job_category' );

	unregister_post_type( 'blockchain_event' );
	unregister_taxonomy( 'blockchain_event_category' );

	unregister_post_type( 'blockchain_testimon' );

	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'blockchain_plugin_deactivated' );



/**
 * Custom fields / post types / taxonomies.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-post.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-page.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-portfolio.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-service.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-case-study.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-team.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-job.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-event.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/custom-fields-testimonial.php';

/**
 * Standard helper functions.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/helpers.php';

/**
 * Standard sanitization functions.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/sanitization.php';

/**
 * Post meta helpers.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/post-meta.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/post-meta-title-subtitle.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/post-meta-hero.php';
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/post-meta-sidebar.php';

/**
 * Post types listing related functions.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/inc/items-listing.php';

/**
 * User fields.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/user-meta.php';

/**
 * Shortcodes.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/shortcodes.php';

/**
 * CoinMarkerCap.com related code.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/coinmarketcap.php';

/**
 * Customizer controls.
 */
require untrailingslashit( BLOCKCHAIN_PLUGIN_DIR ) . '/customizer.php';
