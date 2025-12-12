<?php
function blockchain_plugin_post_type_listing_get_valid_columns_options( $post_type = false ) {
	if ( function_exists( 'blockchain_post_type_listing_get_valid_columns_options' ) ) {
		return blockchain_post_type_listing_get_valid_columns_options( $post_type );
	} else {
		return array(
			'min'   => 3,
			'max'   => 3,
			'range' => array( 3 ),
		);
	}
}

/**
 * Returns the taxonomy that should be used for listing template, for a given post type.
 *
 * @param $post_type
 *
 * @return bool|string Returns false for no supported taxonomy, and a taxonomy name otherwise.
 */
function blockchain_plugin_post_type_listing_taxonomy( $post_type ) {
	$taxonomy = false;
	switch ( $post_type ) {
		case 'post':
			$taxonomy = 'category';
			break;
		case 'page':
			$taxonomy = false;
			break;
		default:
			$taxonomy = "{$post_type}_category";
	}

	return apply_filters( 'blockchain_plugin_post_type_listing_taxonomy', $taxonomy, $post_type );
}

/**
 * Sanitize post meta, as displayed by blockchain_plugin_print_metabox_tab_post_type_listing()
 *
 * @param $post_type string
 * @param $post_id int
 */
function blockchain_plugin_sanitize_metabox_tab_post_type_listing( $post_type, $post_id ) {
	// Ignore phpcs issues. nonce validation happens inside blockchain_plugin_can_save_meta(), from the caller of this function.
	// @codingStandardsIgnoreStart

	$taxonomy = blockchain_plugin_post_type_listing_taxonomy( $post_type );
	$base_category = isset( $_POST["{$post_type}_listing_base_category"] ) ? intval( $_POST["{$post_type}_listing_base_category"] ) : 0 ;
	if ( $taxonomy && $base_category > 0 && term_exists( $base_category, $taxonomy ) ) {
		update_post_meta( $post_id, "{$post_type}_listing_base_category", $base_category );
	} else {
		update_post_meta( $post_id, "{$post_type}_listing_base_category", 0 );
	}

	update_post_meta( $post_id, "{$post_type}_listing_loading_effect", blockchain_plugin_sanitize_grid_loading_effect( $_POST["{$post_type}_listing_loading_effect"] ) );
	if ( apply_filters( 'blockchain_post_type_listing_spacing_support', true, $post_type ) ) {
		update_post_meta( $post_id, "{$post_type}_listing_spacing", blockchain_plugin_sanitize_grid_spacing( $_POST["{$post_type}_listing_spacing"] ) );
	}
	update_post_meta( $post_id, "{$post_type}_listing_columns", intval( $_POST["{$post_type}_listing_columns"] ) );
	update_post_meta( $post_id, "{$post_type}_listing_masonry", isset( $_POST["{$post_type}_listing_masonry"] ) ? 1 : 0 );
	update_post_meta( $post_id, "{$post_type}_listing_isotope", isset( $_POST["{$post_type}_listing_isotope"] ) ? 1 : 0 );
	update_post_meta( $post_id, "{$post_type}_listing_posts_per_page", intval( $_POST["{$post_type}_listing_posts_per_page"] ) );
	// @codingStandardsIgnoreEnd
}

function blockchain_plugin_print_metabox_tab_post_type_listing( $post_type, $object, $box, $title = '' ) {
	blockchain_plugin_metabox_open_tab( $title );
		$taxonomy = blockchain_plugin_post_type_listing_taxonomy( $post_type );

		if ( $taxonomy ) {
			blockchain_plugin_metabox_guide( wp_kses( __( "Select a base category. Only items from the selected category will be displayed. If you don't select one (i.e. empty) items from all categories will be shown.", 'blockchain-plugin' ), blockchain_plugin_get_allowed_tags( 'guide' ) ) );
			?><p class="ci-field-group ci-field-dropdown"><label for="<?php echo esc_attr( "{$post_type}_listing_base_category" ); ?>"><?php esc_html_e( 'Base category:', 'blockchain-plugin' ); ?></label> <?php
			$category = get_post_meta( $object->ID, "{$post_type}_listing_base_category", true );
			wp_dropdown_categories( array(
				'selected'          => $category,
				'id'                => "{$post_type}_listing_base_category",
				'name'              => "{$post_type}_listing_base_category",
				'show_option_none'  => ' ',
				'option_none_value' => 0,
				'taxonomy'          => $taxonomy,
				'hierarchical'      => 1,
				'show_count'        => 1,
				'hide_empty'        => 0,
			) );
			?></p><?php
		}

		blockchain_plugin_metabox_dropdown( "{$post_type}_listing_loading_effect", blockchain_plugin_get_grid_loading_effect_choices(), esc_html__( 'Grid loading effect:', 'blockchain-plugin' ) );

		if ( apply_filters( 'blockchain_post_type_listing_spacing_support', true, $post_type ) ) {
			blockchain_plugin_metabox_dropdown( "{$post_type}_listing_spacing", blockchain_plugin_get_grid_spacing_choices(), esc_html__( 'Grid spacing:', 'blockchain-plugin' ) );
		}

		$options     = array();
		$col_options = blockchain_plugin_post_type_listing_get_valid_columns_options( $post_type );
		foreach ( $col_options['range'] as $col ) {
			/* translators: %d is a number of columns. */
			$options[ $col ] = sprintf( _n( '%d Column', '%d Columns', $col, 'blockchain-plugin' ), $col );
		}
		blockchain_plugin_metabox_dropdown( "{$post_type}_listing_columns", $options, esc_html__( 'Listing columns:', 'blockchain-plugin' ) );
		blockchain_plugin_metabox_checkbox( "{$post_type}_listing_masonry", 1, esc_html__( 'Masonry effect.', 'blockchain-plugin' ) );
		blockchain_plugin_metabox_checkbox( "{$post_type}_listing_isotope", 1, wp_kses( __( 'Enable category filters (ignores <em>Items per page</em> setting).', 'blockchain-plugin' ), blockchain_plugin_get_allowed_tags( 'guide' ) ) );
		/* translators: %d is the current number of posts per page option. */
		blockchain_plugin_metabox_guide( wp_kses( sprintf( __( 'Set the number of items per page that you want to display. Setting this to <strong>-1</strong> will show <strong>all items</strong>, while setting it to zero or leaving it empty, will follow the global option set from <em>Settings -> Reading</em>, currently set to <strong>%d items per page</strong>.', 'blockchain-plugin' ), get_option( 'posts_per_page' ) ), blockchain_plugin_get_allowed_tags( 'guide' ) ) );
		blockchain_plugin_metabox_input( "{$post_type}_listing_posts_per_page", esc_html__( 'Items per page:', 'blockchain-plugin' ), array( 'input_type' => 'number' ) );
	blockchain_plugin_metabox_close_tab();
}

function blockchain_plugin_get_grid_loading_effect_choices() {
	return apply_filters( 'blockchain_plugin_grid_loading_effect_choices', blockchain_plugin_get_loading_effect_choices() );
}

function blockchain_plugin_sanitize_grid_loading_effect( $value ) {
	$choices = blockchain_plugin_get_grid_loading_effect_choices();
	if ( array_key_exists( $value, $choices ) ) {
		return $value;
	}

	return apply_filters( 'blockchain_plugin_sanitize_grid_loading_effect_default', '' );
}

function blockchain_plugin_get_grid_spacing_choices() {
	return apply_filters( 'blockchain_plugin_grid_spacing_choices', array(
		''           => esc_html__( 'With gutters', 'blockchain-plugin' ),
		'no-gutters' => esc_html__( 'No gutters', 'blockchain-plugin' ),
	) );
}

function blockchain_plugin_sanitize_grid_spacing( $value ) {
	$choices = blockchain_plugin_get_grid_spacing_choices();
	if ( array_key_exists( $value, $choices ) ) {
		return $value;
	}

	return apply_filters( 'blockchain_plugin_sanitize_grid_spacing_default', '' );
}
