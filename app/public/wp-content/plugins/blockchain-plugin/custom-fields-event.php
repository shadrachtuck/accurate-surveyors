<?php
add_action( 'init', 'blockchain_plugin_create_cpt_event' );

if ( ! function_exists( 'blockchain_plugin_create_cpt_event' ) ) :
	function blockchain_plugin_create_cpt_event() {
		$labels = array(
			'name'               => esc_html_x( 'Events', 'post type general name', 'blockchain-plugin' ),
			'singular_name'      => esc_html_x( 'Event', 'post type singular name', 'blockchain-plugin' ),
			'menu_name'          => esc_html_x( 'Events', 'admin menu', 'blockchain-plugin' ),
			'name_admin_bar'     => esc_html_x( 'Event', 'add new on admin bar', 'blockchain-plugin' ),
			'add_new'            => esc_html_x( 'Add New', 'Event', 'blockchain-plugin' ),
			'add_new_item'       => esc_html__( 'Add New Event', 'blockchain-plugin' ),
			'edit_item'          => esc_html__( 'Edit Event', 'blockchain-plugin' ),
			'new_item'           => esc_html__( 'New Event', 'blockchain-plugin' ),
			'view_item'          => esc_html__( 'View Event', 'blockchain-plugin' ),
			'search_items'       => esc_html__( 'Search Events', 'blockchain-plugin' ),
			'not_found'          => esc_html__( 'No Events found', 'blockchain-plugin' ),
			'not_found_in_trash' => esc_html__( 'No Events found in the trash', 'blockchain-plugin' ),
			'parent_item_colon'  => esc_html__( 'Parent Event:', 'blockchain-plugin' ),
		);

		$args = array(
			'labels'          => $labels,
			'singular_label'  => esc_html_x( 'Event', 'post type singular name', 'blockchain-plugin' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'has_archive'     => _x( 'event-archive', 'post type archive slug', 'blockchain-plugin' ),
			'rewrite'         => array( 'slug' => esc_html_x( 'event', 'post type slug', 'blockchain-plugin' ) ),
			'menu_position'   => 10,
			'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'menu_icon'       => 'dashicons-calendar',
		);

		register_post_type( 'blockchain_event', $args );

		$labels = array(
			'name'              => esc_html_x( 'Event Categories', 'taxonomy general name', 'blockchain-plugin' ),
			'singular_name'     => esc_html_x( 'Event Category', 'taxonomy singular name', 'blockchain-plugin' ),
			'search_items'      => esc_html__( 'Search Event Categories', 'blockchain-plugin' ),
			'all_items'         => esc_html__( 'All Event Categories', 'blockchain-plugin' ),
			'parent_item'       => esc_html__( 'Parent Event Category', 'blockchain-plugin' ),
			'parent_item_colon' => esc_html__( 'Parent Event Category:', 'blockchain-plugin' ),
			'edit_item'         => esc_html__( 'Edit Event Category', 'blockchain-plugin' ),
			'update_item'       => esc_html__( 'Update Event Category', 'blockchain-plugin' ),
			'add_new_item'      => esc_html__( 'Add New Event Category', 'blockchain-plugin' ),
			'new_item_name'     => esc_html__( 'New Event Category Name', 'blockchain-plugin' ),
			'menu_name'         => esc_html__( 'Categories', 'blockchain-plugin' ),
			'view_item'         => esc_html__( 'View Event Category', 'blockchain-plugin' ),
			'popular_items'     => esc_html__( 'Popular Event Categories', 'blockchain-plugin' ),
		);
		register_taxonomy( 'blockchain_event_category', array( 'blockchain_event' ), array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => esc_html_x( 'event-category', 'taxonomy slug', 'blockchain-plugin' ) ),
		) );
	}
endif;

add_action( 'admin_init', 'blockchain_plugin_cpt_event_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_event_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_event_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_event_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-event-info', esc_html__( 'Event information', 'blockchain-plugin' ), 'blockchain_plugin_add_event_info_meta_box', 'blockchain_event', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_event_hero_meta_box', 'blockchain_event', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-sidebar', esc_html__( 'Sidebar', 'blockchain-plugin' ), 'blockchain_plugin_add_event_sidebar_meta_box', 'blockchain_event', 'side', 'low' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_event_update_meta' ) ) :
	function blockchain_plugin_cpt_event_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'blockchain_event' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );

		blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id );

		update_post_meta( $post_id, 'blockchain_event_date', ! isset( $_POST['blockchain_event_date'] ) ? '' : sanitize_text_field( $_POST['blockchain_event_date'] ) );
		update_post_meta( $post_id, 'blockchain_event_time', ! isset( $_POST['blockchain_event_time'] ) ? '' : sanitize_text_field( $_POST['blockchain_event_time'] ) );
		update_post_meta( $post_id, 'blockchain_event_location', ! isset( $_POST['blockchain_event_location'] ) ? '' : sanitize_text_field( $_POST['blockchain_event_location'] ) );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_event_info_meta_box' ) ) :
	function blockchain_plugin_add_event_info_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_event' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_metabox_open_tab( esc_html__( 'Event Details', 'blockchain-plugin' ) );

				do_action( 'blockchain_plugin_add_event_info_meta_box_before' );

				blockchain_plugin_metabox_input( 'blockchain_event_date', esc_html__( 'Event date. Use the date picker (click inside the field):', 'blockchain-plugin' ), array( 'input_class' => 'widefat datepicker' ) );
				blockchain_plugin_metabox_input( 'blockchain_event_time', esc_html__( 'Time:', 'blockchain-plugin' ) );
				blockchain_plugin_metabox_input( 'blockchain_event_location', esc_html__( 'Location:', 'blockchain-plugin' ) );

				do_action( 'blockchain_plugin_add_event_info_meta_box_after' );

			blockchain_plugin_metabox_close_tab();

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_event_hero_meta_box' ) ) :
	function blockchain_plugin_add_event_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_event' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_event_sidebar_meta_box' ) ) :
	function blockchain_plugin_add_event_sidebar_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_event' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sidebar( $object, $box );

		?></div><?php
	}
endif;
