<?php
add_action( 'admin_init', 'blockchain_plugin_cpt_post_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_post_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_post_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_post_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_post_hero_meta_box', 'post', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-sidebar', esc_html__( 'Sidebar', 'blockchain-plugin' ), 'blockchain_plugin_add_post_sidebar_meta_box', 'post', 'side', 'low' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_post_update_meta' ) ) :
	function blockchain_plugin_cpt_post_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'post' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );

		blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_post_hero_meta_box' ) ) :
	function blockchain_plugin_add_post_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'post' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_post_sidebar_meta_box' ) ) :
	function blockchain_plugin_add_post_sidebar_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'post' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sidebar( $object, $box );

		?></div><?php
	}
endif;
