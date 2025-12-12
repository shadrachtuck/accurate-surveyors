<?php
add_action( 'admin_init', 'blockchain_plugin_cpt_page_add_metaboxes' );
add_action( 'save_post', 'blockchain_plugin_cpt_page_update_meta' );

if ( ! function_exists( 'blockchain_plugin_cpt_page_add_metaboxes' ) ) :
	function blockchain_plugin_cpt_page_add_metaboxes() {
		add_meta_box( 'blockchain-plugin-header', esc_html__( 'Header Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_header_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-hero', esc_html__( 'Hero section', 'blockchain-plugin' ), 'blockchain_plugin_add_page_hero_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-sidebar', esc_html__( 'Sidebar', 'blockchain-plugin' ), 'blockchain_plugin_add_page_sidebar_meta_box', 'page', 'side', 'low' );
		add_meta_box( 'blockchain-plugin-tpl-front-page', esc_html__( 'Front Page Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_front_page_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-tpl-portfolio-listing', esc_html__( 'Portfolio Listing Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_portfolio_listing_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-tpl-service-listing', esc_html__( 'Services Listing Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_service_listing_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-tpl-case-study-listing', esc_html__( 'Case Studies Listing Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_case_study_listing_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-tpl-team-listing', esc_html__( 'Team Listing Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_team_listing_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-tpl-event-listing', esc_html__( 'Events Listing Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_event_listing_meta_box', 'page', 'normal', 'high' );
		add_meta_box( 'blockchain-plugin-tpl-testimonial-listing', esc_html__( 'Testimonials Listing Options', 'blockchain-plugin' ), 'blockchain_plugin_add_page_testimonial_listing_meta_box', 'page', 'normal', 'high' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_cpt_page_update_meta' ) ) :
	function blockchain_plugin_cpt_page_update_meta( $post_id ) {

		if ( ! blockchain_plugin_can_save_meta( 'page' ) ) {
			return;
		}

		blockchain_plugin_sanitize_metabox_tab_sub_title( $post_id );

		blockchain_plugin_sanitize_metabox_tab_hero( $post_id );

		blockchain_plugin_sanitize_metabox_tab_sidebar( $post_id );

		update_post_meta( $post_id, 'blockchain_front_slider_id', blockchain_plugin_sanitize_intval_or_empty( $_POST['blockchain_front_slider_id'] ) );

		blockchain_plugin_sanitize_metabox_tab_post_type_listing( 'blockchain_portfolio', $post_id );
		blockchain_plugin_sanitize_metabox_tab_post_type_listing( 'blockchain_service', $post_id );
		blockchain_plugin_sanitize_metabox_tab_post_type_listing( 'blockchain_cstudy', $post_id );
		blockchain_plugin_sanitize_metabox_tab_post_type_listing( 'blockchain_team', $post_id );
		blockchain_plugin_sanitize_metabox_tab_post_type_listing( 'blockchain_event', $post_id );

		$post_type = 'blockchain_testimon';
		update_post_meta( $post_id, "{$post_type}_listing_loading_effect", blockchain_plugin_sanitize_grid_loading_effect( $_POST["{$post_type}_listing_loading_effect"] ) );
		if ( apply_filters( 'blockchain_post_type_listing_spacing_support', true, $post_type ) ) {
			update_post_meta( $post_id, "{$post_type}_listing_spacing", blockchain_plugin_sanitize_grid_spacing( $_POST["{$post_type}_listing_spacing"] ) );
		}
		update_post_meta( $post_id, "{$post_type}_listing_columns", intval( $_POST["{$post_type}_listing_columns"] ) );
		update_post_meta( $post_id, "{$post_type}_listing_posts_per_page", intval( $_POST["{$post_type}_listing_posts_per_page"] ) );

		update_post_meta( $post_id, 'blockchain_header_overlaid', isset( $_POST['blockchain_header_overlaid'] ) ? 1 : 0 );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_header_meta_box' ) ) :
	function blockchain_plugin_add_page_header_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_metabox_open_tab( '' );
				blockchain_plugin_metabox_guide( wp_kses( __( "You can make the header appear overlaid over the content of this page. This is especially useful on front pages, where it's often desirable for the header to appear over the slider.", 'blockchain-plugin' ), blockchain_plugin_get_allowed_tags( 'guide' ) ) );
				blockchain_plugin_metabox_checkbox( 'blockchain_header_overlaid', 1, esc_html__( 'Make header appear over the content in this page.', 'blockchain-plugin' ) );
			blockchain_plugin_metabox_close_tab();

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_hero_meta_box' ) ) :
	function blockchain_plugin_add_page_hero_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sub_title( $object, $box );

			blockchain_plugin_print_metabox_tab_hero( $object, $box );

		?></div><?php
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_sidebar_meta_box' ) ) :
	function blockchain_plugin_add_page_sidebar_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_sidebar( $object, $box );

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-sidebar', array(
			'default',
			'templates/listing-blockchain_portfolio.php',
			'templates/listing-blockchain_service.php',
			'templates/listing-blockchain_cstudy.php',
			'templates/listing-blockchain_team.php',
			'templates/listing-blockchain_job.php',
		), 'blockchain_sidebar_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_front_page_meta_box' ) ) :
	function blockchain_plugin_add_page_front_page_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_metabox_open_tab( '' );
				blockchain_plugin_metabox_guide( esc_html__( 'You can select a MaxSlider slideshow to display on your front page. If you choose a slideshow, it will be displayed instead of the image and/or video that you have set on "Hero section".', 'blockchain-plugin' ) );
				?>
				<p class="ci-field-group ci-field-dropdown">
					<label for="background_slider_id"><?php esc_html_e( 'MaxSlider Slideshow', 'blockchain-plugin' ); ?></label>
					<?php
						$post_type = 'maxslider_slide';
						if ( function_exists( 'MaxSlider' ) ) {
							$post_type = MaxSlider()->post_type;
						}
						blockchain_plugin_dropdown_posts( array(
							'post_type'            => $post_type,
							'selected'             => get_post_meta( $object->ID, 'blockchain_front_slider_id', true ),
							'class'                => 'posts_dropdown',
							'show_option_none'     => esc_html__( 'Disable Slideshow', 'blockchain-plugin' ),
							'select_even_if_empty' => true,
						), 'blockchain_front_slider_id' );
					?>
				</p>
				<?php
			blockchain_plugin_metabox_close_tab();

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-front-page', 'templates/front-page.php', 'blockchain_front_page_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_portfolio_listing_meta_box' ) ) :
	function blockchain_plugin_add_page_portfolio_listing_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_post_type_listing( 'blockchain_portfolio', $object, $box, esc_html__( 'Portfolio listing', 'blockchain-plugin' ) );

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-portfolio-listing', 'templates/listing-blockchain_portfolio.php', 'blockchain_plugin_portfolio_listing_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_service_listing_meta_box' ) ) :
	function blockchain_plugin_add_page_service_listing_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_post_type_listing( 'blockchain_service', $object, $box, esc_html__( 'Services listing', 'blockchain-plugin' ) );

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-service-listing', 'templates/listing-blockchain_service.php', 'blockchain_plugin_service_listing_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_case_study_listing_meta_box' ) ) :
	function blockchain_plugin_add_page_case_study_listing_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_post_type_listing( 'blockchain_cstudy', $object, $box, esc_html__( 'Case Studies listing', 'blockchain-plugin' ) );

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-case-study-listing', 'templates/listing-blockchain_cstudy.php', 'blockchain_plugin_case_study_listing_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_team_listing_meta_box' ) ) :
	function blockchain_plugin_add_page_team_listing_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_post_type_listing( 'blockchain_team', $object, $box, esc_html__( 'Teams listing', 'blockchain-plugin' ) );

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-team-listing', 'templates/listing-blockchain_team.php', 'blockchain_plugin_team_listing_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_event_listing_meta_box' ) ) :
	function blockchain_plugin_add_page_event_listing_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			blockchain_plugin_print_metabox_tab_post_type_listing( 'blockchain_event', $object, $box, esc_html__( 'Events listing', 'blockchain-plugin' ) );

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-event-listing', 'templates/listing-blockchain_event.php', 'blockchain_plugin_event_listing_metabox_tpl' );
	}
endif;

if ( ! function_exists( 'blockchain_plugin_add_page_testimonial_listing_meta_box' ) ) :
	function blockchain_plugin_add_page_testimonial_listing_meta_box( $object, $box ) {
		blockchain_plugin_prepare_metabox( 'page' );

		?><div class="ci-cf-wrap"><?php

			$post_type = 'blockchain_testimon';

			blockchain_plugin_metabox_open_tab( esc_html__( 'Testimonials listing', 'blockchain-plugin' ) );
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
				/* translators: %d is the current number of posts per page option. */
				blockchain_plugin_metabox_guide( wp_kses( sprintf( __( 'Set the number of items per page that you want to display. Setting this to <strong>-1</strong> will show <strong>all items</strong>, while setting it to zero or leaving it empty, will follow the global option set from <em>Settings -> Reading</em>, currently set to <strong>%d items per page</strong>.', 'blockchain-plugin' ), get_option( 'posts_per_page' ) ), blockchain_plugin_get_allowed_tags( 'guide' ) ) );
				blockchain_plugin_metabox_input( "{$post_type}_listing_posts_per_page", esc_html__( 'Items per page:', 'blockchain-plugin' ), array( 'input_type' => 'number' ) );
			blockchain_plugin_metabox_close_tab();

		?></div><?php

		blockchain_plugin_bind_metabox_to_page_template( 'blockchain-plugin-tpl-testimonial-listing', 'templates/listing-blockchain_testimon.php', 'blockchain_plugin_testimonial_listing_metabox_tpl' );
	}
endif;
