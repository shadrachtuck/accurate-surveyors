<?php
add_action( 'init', 'blockchain_plugin_create_cpt_case_study' );

if ( ! function_exists( 'blockchain_plugin_create_cpt_case_study' ) ) :
	function blockchain_plugin_create_cpt_case_study() {
		$labels = array(
			'name'               => esc_html_x( 'Case Studies', 'post type general name', 'blockchain-plugin' ),
			'singular_name'      => esc_html_x( 'Case Study', 'post type singular name', 'blockchain-plugin' ),
			'menu_name'          => esc_html_x( 'Case Studies', 'admin menu', 'blockchain-plugin' ),
			'name_admin_bar'     => esc_html_x( 'Case Study', 'add new on admin bar', 'blockchain-plugin' ),
			'add_new'            => esc_html_x( 'Add New', 'Case Study', 'blockchain-plugin' ),
			'add_new_item'       => esc_html__( 'Add New Case Study', 'blockchain-plugin' ),
			'edit_item'          => esc_html__( 'Edit Case Study', 'blockchain-plugin' ),
			'new_item'           => esc_html__( 'New Case Study', 'blockchain-plugin' ),
			'view_item'          => esc_html__( 'View Case Study', 'blockchain-plugin' ),
			'search_items'       => esc_html__( 'Search Case Studies', 'blockchain-plugin' ),
			'not_found'          => esc_html__( 'No Case Studies found', 'blockchain-plugin' ),
			'not_found_in_trash' => esc_html__( 'No Case Studies found in the trash', 'blockchain-plugin' ),
			'parent_item_colon'  => esc_html__( 'Parent Case Study:', 'blockchain-plugin' ),
		);

		$args = array(
			'labels'          => $labels,
			'singular_label'  => esc_html_x( 'Case Study', 'post type singular name', 'blockchain-plugin' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'has_archive'     => _x( 'case-study-archive', 'post type archive slug', 'blockchain-plugin' ),
			'rewrite'         => array( 'slug' => esc_html_x( 'case-study', 'post type slug', 'blockchain-plugin' ) ),
			'menu_position'   => 10,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'       => 'dashicons-book',
		);

		register_post_type( 'blockchain_cstudy', $args );

		$labels = array(
			'name'              => esc_html_x( 'Case Study Categories', 'taxonomy general name', 'blockchain-plugin' ),
			'singular_name'     => esc_html_x( 'Case Study Category', 'taxonomy singular name', 'blockchain-plugin' ),
			'search_items'      => esc_html__( 'Search Case Study Categories', 'blockchain-plugin' ),
			'all_items'         => esc_html__( 'All Case Study Categories', 'blockchain-plugin' ),
			'parent_item'       => esc_html__( 'Parent Case Study Category', 'blockchain-plugin' ),
			'parent_item_colon' => esc_html__( 'Parent Case Study Category:', 'blockchain-plugin' ),
			'edit_item'         => esc_html__( 'Edit Case Study Category', 'blockchain-plugin' ),
			'update_item'       => esc_html__( 'Update Case Study Category', 'blockchain-plugin' ),
			'add_new_item'      => esc_html__( 'Add New Case Study Category', 'blockchain-plugin' ),
			'new_item_name'     => esc_html__( 'New Case Study Category Name', 'blockchain-plugin' ),
			'menu_name'         => esc_html__( 'Categories', 'blockchain-plugin' ),
			'view_item'         => esc_html__( 'View Case Study Category', 'blockchain-plugin' ),
			'popular_items'     => esc_html__( 'Popular Case Study Categories', 'blockchain-plugin' ),
		);

		register_taxonomy( 'blockchain_cstudy_category', array( 'blockchain_cstudy' ), array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => esc_html_x( 'case-study-category', 'taxonomy slug', 'blockchain-plugin' ) ),
		) );
	}
endif;

add_action( 'admin_init', 'blockchain_plugin_cpt_case_study_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_case_study_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_case_study_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_case_study_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_case_study_hero_meta_box', 'blockchain_cstudy', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-sidebar', esc_html__( 'Sidebar', 'blockchain-plugin' ), 'blockchain_plugin_add_case_study_sidebar_meta_box', 'blockchain_cstudy', 'side', 'low' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_case_study_update_meta' ) ) :
	function blockchain_plugin_cpt_case_study_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'blockchain_cstudy' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );

		blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_case_study_hero_meta_box' ) ) :
	function blockchain_plugin_add_case_study_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_cstudy' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_case_study_sidebar_meta_box' ) ) :
	function blockchain_plugin_add_case_study_sidebar_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_cstudy' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sidebar( $object, $box );

		?></div><?php
	}
endif;
