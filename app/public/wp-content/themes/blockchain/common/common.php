<?php
/**
 * Common theme features.
 */

/**
 * Common assets registration
 */
function blockchain_register_common_assets() {
	$theme = wp_get_theme();
	wp_register_style( 'blockchain-common', get_template_directory_uri() . '/common/css/global.css', array(), $theme->get( 'Version' ) );
}
add_action( 'init', 'blockchain_register_common_assets', 8 );
