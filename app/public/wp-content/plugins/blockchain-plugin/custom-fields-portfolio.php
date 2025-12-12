<?php
add_action( 'init', 'blockchain_plugin_create_cpt_portfolio' );

if ( ! function_exists( 'blockchain_plugin_create_cpt_portfolio' ) ) :
	function blockchain_plugin_create_cpt_portfolio() {
		$labels = array(
			'name'               => esc_html_x( 'Portfolio', 'post type general name', 'blockchain-plugin' ),
			'singular_name'      => esc_html_x( 'Portfolio Item', 'post type singular name', 'blockchain-plugin' ),
			'menu_name'          => esc_html_x( 'Portfolio', 'admin menu', 'blockchain-plugin' ),
			'name_admin_bar'     => esc_html_x( 'Portfolio', 'add new on admin bar', 'blockchain-plugin' ),
			'add_new'            => esc_html_x( 'Add New', 'portfolio', 'blockchain-plugin' ),
			'add_new_item'       => esc_html__( 'Add New Portfolio Item', 'blockchain-plugin' ),
			'edit_item'          => esc_html__( 'Edit Portfolio Item', 'blockchain-plugin' ),
			'new_item'           => esc_html__( 'New Portfolio Item', 'blockchain-plugin' ),
			'view_item'          => esc_html__( 'View Portfolio Item', 'blockchain-plugin' ),
			'search_items'       => esc_html__( 'Search Portfolio Items', 'blockchain-plugin' ),
			'not_found'          => esc_html__( 'No Portfolio Items found', 'blockchain-plugin' ),
			'not_found_in_trash' => esc_html__( 'No Portfolio Items found in the trash', 'blockchain-plugin' ),
			'parent_item_colon'  => esc_html__( 'Parent Portfolio Item:', 'blockchain-plugin' ),
		);

		$args = array(
			'labels'          => $labels,
			'singular_label'  => esc_html_x( 'Portfolio Item', 'post type singular name', 'blockchain-plugin' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'has_archive'     => _x( 'portfolio-archive', 'post type archive slug', 'blockchain-plugin' ),
			'rewrite'         => array( 'slug' => esc_html_x( 'portfolio', 'post type slug', 'blockchain-plugin' ) ),
			'menu_position'   => 10,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'       => 'dashicons-portfolio',
		);

		register_post_type( 'blockchain_portfolio', $args );

		$labels = array(
			'name'              => esc_html_x( 'Portfolio Categories', 'taxonomy general name', 'blockchain-plugin' ),
			'singular_name'     => esc_html_x( 'Portfolio Category', 'taxonomy singular name', 'blockchain-plugin' ),
			'search_items'      => esc_html__( 'Search Portfolio Categories', 'blockchain-plugin' ),
			'all_items'         => esc_html__( 'All Portfolio Categories', 'blockchain-plugin' ),
			'parent_item'       => esc_html__( 'Parent Portfolio Category', 'blockchain-plugin' ),
			'parent_item_colon' => esc_html__( 'Parent Portfolio Category:', 'blockchain-plugin' ),
			'edit_item'         => esc_html__( 'Edit Portfolio Category', 'blockchain-plugin' ),
			'update_item'       => esc_html__( 'Update Portfolio Category', 'blockchain-plugin' ),
			'add_new_item'      => esc_html__( 'Add New Portfolio Category', 'blockchain-plugin' ),
			'new_item_name'     => esc_html__( 'New Portfolio Category Name', 'blockchain-plugin' ),
			'menu_name'         => esc_html__( 'Categories', 'blockchain-plugin' ),
			'view_item'         => esc_html__( 'View Portfolio Category', 'blockchain-plugin' ),
			'popular_items'     => esc_html__( 'Popular Portfolio Categories', 'blockchain-plugin' ),
		);

		register_taxonomy( 'blockchain_portfolio_category', array( 'blockchain_portfolio' ), array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => esc_html_x( 'portfolio-category', 'taxonomy slug', 'blockchain-plugin' ) ),
		) );
	}
endif;

add_action( 'admin_init', 'blockchain_plugin_cpt_portfolio_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_portfolio_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_portfolio_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_portfolio_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_portfolio_hero_meta_box', 'blockchain_portfolio', 'normal', 'high' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_portfolio_update_meta' ) ) :
	function blockchain_plugin_cpt_portfolio_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'blockchain_portfolio' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_portfolio_hero_meta_box' ) ) :
	function blockchain_plugin_add_portfolio_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_portfolio' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;
