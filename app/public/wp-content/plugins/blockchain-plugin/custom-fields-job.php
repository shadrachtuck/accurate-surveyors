<?php
add_action( 'init', 'blockchain_plugin_create_cpt_job' );

if ( ! function_exists( 'blockchain_plugin_create_cpt_job' ) ) :
	function blockchain_plugin_create_cpt_job() {
		$labels = array(
			'name'               => esc_html_x( 'Jobs', 'post type general name', 'blockchain-plugin' ),
			'singular_name'      => esc_html_x( 'Job', 'post type singular name', 'blockchain-plugin' ),
			'menu_name'          => esc_html_x( 'Jobs', 'admin menu', 'blockchain-plugin' ),
			'name_admin_bar'     => esc_html_x( 'Job', 'add new on admin bar', 'blockchain-plugin' ),
			'add_new'            => esc_html_x( 'Add New', 'Job', 'blockchain-plugin' ),
			'add_new_item'       => esc_html__( 'Add New Job', 'blockchain-plugin' ),
			'edit_item'          => esc_html__( 'Edit Job', 'blockchain-plugin' ),
			'new_item'           => esc_html__( 'New Job', 'blockchain-plugin' ),
			'view_item'          => esc_html__( 'View Job', 'blockchain-plugin' ),
			'search_items'       => esc_html__( 'Search Jobs', 'blockchain-plugin' ),
			'not_found'          => esc_html__( 'No Jobs found', 'blockchain-plugin' ),
			'not_found_in_trash' => esc_html__( 'No Jobs found in the trash', 'blockchain-plugin' ),
			'parent_item_colon'  => esc_html__( 'Parent Job:', 'blockchain-plugin' ),
		);

		$args = array(
			'labels'          => $labels,
			'singular_label'  => esc_html_x( 'Job', 'post type singular name', 'blockchain-plugin' ),
			'public'          => true,
			'show_ui'         => true,
			'capability_type' => 'post',
			'hierarchical'    => false,
			'has_archive'     => _x( 'job-archive', 'post type archive slug', 'blockchain-plugin' ),
			'rewrite'         => array( 'slug' => esc_html_x( 'job', 'post type slug', 'blockchain-plugin' ) ),
			'menu_position'   => 10,
			'supports'        => array( 'title', 'editor', 'excerpt' ),
			'menu_icon'       => 'dashicons-sticky',
		);

		register_post_type( 'blockchain_job', $args );

		$labels = array(
			'name'              => esc_html_x( 'Job Categories', 'taxonomy general name', 'blockchain-plugin' ),
			'singular_name'     => esc_html_x( 'Job Category', 'taxonomy singular name', 'blockchain-plugin' ),
			'search_items'      => esc_html__( 'Search Job Categories', 'blockchain-plugin' ),
			'all_items'         => esc_html__( 'All Job Categories', 'blockchain-plugin' ),
			'parent_item'       => esc_html__( 'Parent Job Category', 'blockchain-plugin' ),
			'parent_item_colon' => esc_html__( 'Parent Job Category:', 'blockchain-plugin' ),
			'edit_item'         => esc_html__( 'Edit Job Category', 'blockchain-plugin' ),
			'update_item'       => esc_html__( 'Update Job Category', 'blockchain-plugin' ),
			'add_new_item'      => esc_html__( 'Add New Job Category', 'blockchain-plugin' ),
			'new_item_name'     => esc_html__( 'New Job Category Name', 'blockchain-plugin' ),
			'menu_name'         => esc_html__( 'Categories', 'blockchain-plugin' ),
			'view_item'         => esc_html__( 'View Job Category', 'blockchain-plugin' ),
			'popular_items'     => esc_html__( 'Popular Job Categories', 'blockchain-plugin' ),
		);

		register_taxonomy( 'blockchain_job_category', array( 'blockchain_job' ), array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => esc_html_x( 'job-category', 'taxonomy slug', 'blockchain-plugin' ) ),
		) );
	}
endif;

add_action( 'admin_init', 'blockchain_plugin_cpt_job_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_job_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_job_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_job_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-job-info', esc_html__( 'Job information', 'blockchain-plugin' ), 'blockchain_plugin_add_job_info_meta_box', 'blockchain_job', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_job_hero_meta_box', 'blockchain_job', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-sidebar', esc_html__( 'Sidebar', 'blockchain-plugin' ), 'blockchain_plugin_add_job_sidebar_meta_box', 'blockchain_job', 'side', 'low' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_job_update_meta' ) ) :
	function blockchain_plugin_cpt_job_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'blockchain_job' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );

		blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id );

		update_post_meta( $post_id, 'blockchain_job_location', ! isset( $_POST['blockchain_job_location'] ) ? '' : sanitize_text_field( $_POST['blockchain_job_location'] ) );
		update_post_meta( $post_id, 'blockchain_job_department', ! isset( $_POST['blockchain_job_department'] ) ? '' : sanitize_text_field( $_POST['blockchain_job_department'] ) );
		update_post_meta( $post_id, 'blockchain_job_date', ! isset( $_POST['blockchain_job_date'] ) ? '' : sanitize_text_field( $_POST['blockchain_job_date'] ) );
		update_post_meta( $post_id, 'blockchain_job_salary', ! isset( $_POST['blockchain_job_salary'] ) ? '' : sanitize_text_field( $_POST['blockchain_job_salary'] ) );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_job_info_meta_box' ) ) :
	function blockchain_plugin_add_job_info_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_job' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_metabox_open_tab( esc_html__( 'Job Details', 'blockchain-plugin' ) );

				do_action( 'blockchain_plugin_add_job_info_meta_box_before' );

				blockchain_plugin_metabox_input( 'blockchain_job_location', esc_html__( 'Location:', 'blockchain-plugin' ) );
				blockchain_plugin_metabox_input( 'blockchain_job_department', esc_html__( 'Department:', 'blockchain-plugin' ) );
				blockchain_plugin_metabox_input( 'blockchain_job_date', esc_html__( 'Starting date:', 'blockchain-plugin' ) );
				blockchain_plugin_metabox_input( 'blockchain_job_salary', esc_html__( 'Salary:', 'blockchain-plugin' ) );

				do_action( 'blockchain_plugin_add_job_info_meta_box_after' );

			blockchain_plugin_metabox_close_tab();

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_job_hero_meta_box' ) ) :
	function blockchain_plugin_add_job_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_job' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_job_sidebar_meta_box' ) ) :
	function blockchain_plugin_add_job_sidebar_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'blockchain_job' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sidebar( $object, $box );

		?></div><?php
	}
endif;
