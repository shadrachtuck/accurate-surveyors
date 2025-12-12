<?php
function blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id ) {
	// Ignore phpcs issues. nonce validation happens inside blockchain_plugin_can_save_meta(), from the caller of this function.
	// @codingStandardsIgnoreStart
	update_post_meta( $post_id, 'blockchain_sidebar', blockchain_plugin_sanitize_sidebar( $_POST['blockchain_sidebar'] ) );
	// @codingStandardsIgnoreEnd
}

function blockchain_plugin_print_metabox_tab_sidebar( $object, $box ) {

	blockchain_plugin_metabox_open_tab( esc_html__( 'Sidebar', 'blockchain-plugin' ) );

		$options = blockchain_plugin_get_sidebar_choices();
		foreach ( $options as $key => $value ) {
			blockchain_plugin_metabox_radio( 'blockchain_sidebar', "sidebar-$key", $key, $value, array( 'default' => apply_filters( 'blockchain_plugin_sanitize_sidebar_default', 'right' ) ) );
		}

	blockchain_plugin_metabox_close_tab();
}

function blockchain_plugin_get_sidebar_choices() {
	return apply_filters( 'blockchain_plugin_sidebar_choices', array(
		'left'  => esc_html__( 'Left sidebar', 'blockchain-plugin' ),
		'right' => esc_html__( 'Right sidebar', 'blockchain-plugin' ),
		'none'  => esc_html__( 'No sidebar', 'blockchain-plugin' ),
	) );
}

function blockchain_plugin_sanitize_sidebar( $value ) {
	$choices = blockchain_plugin_get_sidebar_choices();
	if ( array_key_exists( $value, $choices ) ) {
		return $value;
	}

	return apply_filters( 'blockchain_plugin_sanitize_sidebar_default', 'right' );
}
