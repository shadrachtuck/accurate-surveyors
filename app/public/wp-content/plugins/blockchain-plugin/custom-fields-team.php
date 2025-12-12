<?php
add_action( 'init', 'blockchain_plugin_create_cpt_team' );

if ( ! function_exists( 'blockchain_plugin_create_cpt_team' ) ) :
	function blockchain_plugin_create_cpt_team() {
		$labels = array(
			'name'               => esc_html_x( 'Team Members', 'post type general name', 'blockchain-plugin' ),
			'singular_name'      => esc_html_x( 'Team Member', 'post type singular name', 'blockchain-plugin' ),
			'menu_name'          => esc_html_x( 'Team Members', 'admin menu', 'blockchain-plugin' ),
			'name_admin_bar'     => esc_html_x( 'Team Member', 'add new on admin bar', 'blockchain-plugin' ),
			'add_new'            => esc_html_x( 'Add New', 'Team Member', 'blockchain-plugin' ),
			'add_new_item'       => esc_html__( 'Add New Team Member', 'blockchain-plugin' ),
			'edit_item'          => esc_html__( 'Edit Team Member', 'blockchain-plugin' ),
			'new_item'           => esc_html__( 'New Team Member', 'blockchain-plugin' ),
			'view_item'          => esc_html__( 'View Team Member', 'blockchain-plugin' ),
			'search_items'       => esc_html__( 'Search Team Members', 'blockchain-plugin' ),
			'not_found'          => esc_html__( 'No Team Members found', 'blockchain-plugin' ),
			'not_found_in_trash' => esc_html__( 'No Team Members found in the trash', 'blockchain-plugin' ),
			'parent_item_colon'  => esc_html__( 'Parent Team Member:', 'blockchain-plugin' ),
		);

		$args = array(
			'labels'          => $labels,
			'singular_label'  => esc_html_x( 'Team Member', 'post type singular name', 'blockchain-plugin' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'has_archive'     => _x( 'team-archive', 'post type archive slug', 'blockchain-plugin' ),
			'rewrite'         => array( 'slug' => esc_html_x( 'team', 'post type slug', 'blockchain-plugin' ) ),
			'menu_position'   => 10,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'       => 'dashicons-admin-users',
		);

		register_post_type( 'blockchain_team', $args );

		$labels = array(
			'name'              => esc_html_x( 'Team Member Categories', 'taxonomy general name', 'blockchain-plugin' ),
			'singular_name'     => esc_html_x( 'Team Member Category', 'taxonomy singular name', 'blockchain-plugin' ),
			'search_items'      => esc_html__( 'Search Team Member Categories', 'blockchain-plugin' ),
			'all_items'         => esc_html__( 'All Team Member Categories', 'blockchain-plugin' ),
			'parent_item'       => esc_html__( 'Parent Team Member Category', 'blockchain-plugin' ),
			'parent_item_colon' => esc_html__( 'Parent Team Member Category:', 'blockchain-plugin' ),
			'edit_item'         => esc_html__( 'Edit Team Member Category', 'blockchain-plugin' ),
			'update_item'       => esc_html__( 'Update Team Member Category', 'blockchain-plugin' ),
			'add_new_item'      => esc_html__( 'Add New Team Member Category', 'blockchain-plugin' ),
			'new_item_name'     => esc_html__( 'New Team Member Category Name', 'blockchain-plugin' ),
			'menu_name'         => esc_html__( 'Categories', 'blockchain-plugin' ),
			'view_item'         => esc_html__( 'View Team Member Category', 'blockchain-plugin' ),
			'popular_items'     => esc_html__( 'Popular Team Member Categories', 'blockchain-plugin' ),
		);

		register_taxonomy( 'blockchain_team_category', array( 'blockchain_team' ), array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => esc_html_x( 'team-category', 'taxonomy slug', 'blockchain-plugin' ) ),
		) );
	}
endif;

add_action( 'admin_init', 'blockchain_plugin_cpt_team_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_team_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_team_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_team_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_team_hero_meta_box', 'blockchain_team', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-sidebar', esc_html__( 'Sidebar', 'blockchain-plugin' ), 'blockchain_plugin_add_team_sidebar_meta_box', 'blockchain_team', 'side', 'low' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_team_update_meta' ) ) :
	function blockchain_plugin_cpt_team_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'blockchain_team' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );

		blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_team_hero_meta_box' ) ) :
	function blockchain_plugin_add_team_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_team' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_team_sidebar_meta_box' ) ) :
	function blockchain_plugin_add_team_sidebar_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_team' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sidebar( $object, $box );

		?></div><?php
	}
endif;
