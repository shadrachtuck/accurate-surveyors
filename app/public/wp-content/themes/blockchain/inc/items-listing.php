<?php
function blockchain_post_type_listing_get_valid_columns_options( $post_type = false ) {
	$array = array(
		'min'   => 1,
		'max'   => 4,
		'range' => range( 1, 4 ),
	);

	return apply_filters( 'blockchain_post_type_listing_valid_columns_options', $array, $post_type );
}

function blockchain_post_type_listing_get_post_terms_classes( $post_id, $taxonomy = false ) {
	$terms_classes = array();
	if ( $taxonomy ) {
		$terms         = get_the_terms( $post_id, $taxonomy );
		$terms         = ! empty( $terms ) ? $terms : array();
		$terms_classes = array_map( 'urldecode', wp_list_pluck( $terms, 'slug' ) );
		foreach ( wp_list_pluck( $terms, 'term_id' ) as $term_id ) {
			$terms_classes[] = 'term-' . $term_id;
		}
	}

	return apply_filters( 'blockchain_post_type_listing_post_terms_classes', $terms_classes, $post_id, $taxonomy );
}



/**
 * Retrieves listing post meta and generates data for use on the template files.
 *
 * @param $post_id int The post ID of the page to get listing data from.
 * @param $post_type string The post type for which the listing is about.
 * @return array
 */
function blockchain_post_type_listing_get_template_data( $post_id, $post_type ) {

	$taxonomy = false;
	if ( function_exists( 'blockchain_plugin_post_type_listing_taxonomy' ) ) {
		$taxonomy = blockchain_plugin_post_type_listing_taxonomy( $post_type );
	}

	$base_category  = get_post_meta( get_the_ID(), "{$post_type}_listing_base_category", true );
	$isotope        = get_post_meta( get_the_ID(), "{$post_type}_listing_isotope", true );
	$columns        = get_post_meta( get_the_ID(), "{$post_type}_listing_columns", true );
	$masonry        = get_post_meta( get_the_ID(), "{$post_type}_listing_masonry", true );
	$posts_per_page = get_post_meta( get_the_ID(), "{$post_type}_listing_posts_per_page", true );
	$loading_effect = get_post_meta( get_the_ID(), "{$post_type}_listing_loading_effect", true );

	$spacing = '';
	if ( apply_filters( 'blockchain_post_type_listing_spacing_support', true, $post_type ) ) {
		$spacing = get_post_meta( get_the_ID(), "{$post_type}_listing_spacing", true );
	}

	$base_category  = intval( $base_category );
	$isotope        = (bool) $isotope;
	$columns        = intval( $columns );
	$masonry        = (bool) $masonry;
	$posts_per_page = intval( $posts_per_page );

	$args = array(
		'paged'     => blockchain_get_page_var(),
		'post_type' => $post_type,
	);

	if ( $isotope ) {
		$args['posts_per_page'] = - 1;
	} else {
		if ( $posts_per_page >= 1 ) {
			$args['posts_per_page'] = $posts_per_page;
		} elseif ( $posts_per_page <= - 1 ) {
			$args['posts_per_page'] = - 1;
		}
	}

	$container_classes = array();

	if ( $masonry || $isotope ) {
		$container_classes[] = 'row-isotope';
	}

	if ( $loading_effect ) {
		$container_classes[] = 'row-effect';
		$container_classes[] = sprintf( 'row-effect-%s', $loading_effect );
	}

	if ( $spacing ) {
		$container_classes[] = $spacing;
	}

	$container_classes = array_unique( array_filter( $container_classes ) );

	$query_args_tax = array(
		'tax_query' => array(
			array(
				'taxonomy'         => $taxonomy,
				'field'            => 'term_id',
				'terms'            => $base_category,
				'include_children' => true,
			),
		)
	);

	$get_terms_args = array(
		'hide_empty' => 1,
	);

	if ( $taxonomy && $base_category > 0 ) {
		$query_args = array_merge( $args, $query_args_tax );

		$get_terms_args['child_of'] = $base_category;
	} else {
		$query_args = $args;
	}

	$data = array(
		'post_type'         => $post_type,
		'taxonomy'          => $taxonomy,
		'base_category'     => $base_category,
		'isotope'           => $isotope,
		'columns'           => $columns,
		'masonry'           => $masonry,
		'posts_per_page'    => $posts_per_page,
		'loading_effect'    => $loading_effect,
		'spacing'           => $spacing,
		'container_classes' => $container_classes,
		'get_terms_args'    => $get_terms_args,
		'query_args'        => $query_args,
	);

	$data = apply_filters( 'blockchain_post_type_listing_template_data', $data, $post_id, $post_type );

	return $data;
}
