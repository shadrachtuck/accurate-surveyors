<?php
add_action( 'init', 'blockchain_plugin_create_cpt_testimonial' );

if ( ! function_exists( 'blockchain_plugin_create_cpt_testimonial' ) ) :
	function blockchain_plugin_create_cpt_testimonial() {
		$labels = array(
			'name'               => esc_html_x( 'Testimonials', 'post type general name', 'blockchain-plugin' ),
			'singular_name'      => esc_html_x( 'Testimonial', 'post type singular name', 'blockchain-plugin' ),
			'menu_name'          => esc_html_x( 'Testimonials', 'admin menu', 'blockchain-plugin' ),
			'name_admin_bar'     => esc_html_x( 'Testimonial', 'add new on admin bar', 'blockchain-plugin' ),
			'add_new'            => esc_html_x( 'Add New', 'testimonial', 'blockchain-plugin' ),
			'add_new_item'       => esc_html__( 'Add New Testimonial', 'blockchain-plugin' ),
			'edit_item'          => esc_html__( 'Edit Testimonial', 'blockchain-plugin' ),
			'new_item'           => esc_html__( 'New Testimonial', 'blockchain-plugin' ),
			'view_item'          => esc_html__( 'View Testimonial', 'blockchain-plugin' ),
			'search_items'       => esc_html__( 'Search Testimonials', 'blockchain-plugin' ),
			'not_found'          => esc_html__( 'No Testimonials found', 'blockchain-plugin' ),
			'not_found_in_trash' => esc_html__( 'No Testimonials found in the trash', 'blockchain-plugin' ),
			'parent_item_colon'  => esc_html__( 'Parent Testimonial:', 'blockchain-plugin' ),
		);

		$args = array(
			'labels'          => $labels,
			'singular_label'  => esc_html_x( 'Testimonial', 'post type singular name', 'blockchain-plugin' ),
			'public'          => false,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'has_archive'     => false,
			'rewrite'         => array( 'slug' => esc_html_x( 'testimonial', 'post type slug', 'blockchain-plugin' ) ),
			'menu_position'   => 10,
			'supports'        => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon'       => 'dashicons-format-quote',
		);

		register_post_type( 'blockchain_testimon', $args );
	}
endif;

add_action( 'admin_init', 'blockchain_plugin_cpt_testimonial_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_testimonial_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_testimonial_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_testimonial_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-sub-title', esc_html__( 'Title / Subtitle', 'blockchain-plugin' ), 'blockchain_plugin_add_testimonial_sub_title_meta_box', 'blockchain_testimon', 'normal', 'high' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_testimonial_update_meta' ) ) :
	function blockchain_plugin_cpt_testimonial_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'blockchain_testimon' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_testimonial_sub_title_meta_box' ) ) :
	function blockchain_plugin_add_testimonial_sub_title_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_testimon' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

		?></div><?php
	}
endif;
