<?php
if ( ! class_exists( 'CI_Widget_Home_Post_Type_Items' ) ) :
	class CI_Widget_Home_Post_Type_Items extends WP_Widget {

		public $ajax_posts = 'blockchain_home_post_type_items_widget_post_type_ajax_get_posts';

		protected $defaults = array(
			'title'             => '',
			'subtitle'          => '',
			'post_type'         => 'post',
			'rows'              => array(),
			'columns'           => 3,
			'overlay_color'     => '',
			'background_color'  => '',
			'background_image'  => '',
			'background_repeat' => 'repeat',
			'background_size'   => 1,
			'parallax'          => '',
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Homepage widget. Displays a hand-picked selection of posts from a selected post type.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-home-post-type-items', esc_html__( 'Theme (home) - Post Type Items', 'blockchain' ), $widget_ops, $control_ops );

			if ( is_admin() === true ) {
				add_action( 'wp_ajax_' . $this->ajax_posts, 'CI_Widget_Home_Post_Type_Items::_ajax_get_posts' );
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_custom_css' ) );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$subtitle  = $instance['subtitle'];
			$post_type = $instance['post_type'];
			$rows      = $instance['rows'];
			$columns   = $instance['columns'];

			// WPML
			$subtitle  = apply_filters( 'wpml_translate_single_string', $subtitle, 'Widgets', 'Theme (home) - Post Type Items - Subtitle' );

			if ( empty( $post_type ) || empty( $rows ) ) {
				return;
			}

			$ids = wp_list_pluck( $rows, 'post_id' );
			$ids = array_filter( $ids );

			$q = new WP_Query( array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post__in'       => $ids,
				'orderby'        => 'post__in',
			) );

			$background_color = $instance['background_color'];
			$background_image = $instance['background_image'];
			$parallax         = $instance['parallax'];

			if ( ! empty( $background_color ) || ! empty( $background_image ) || ! empty( $overlay_color ) ) {
				preg_match( '/class=(["\']).*?widget-section.*?\1/', $before_widget, $match );
				if ( ! empty( $match ) ) {
					$classes = array( 'widget-section-padded' );
					if ( $parallax ) {
						$classes[] = 'widget-section-parallax';
					}

					$attr_class    = preg_replace( '/\bwidget-section\b/', 'widget-section ' . implode( ' ', $classes ), $match[0], 1 );
					$before_widget = str_replace( $match[0], $attr_class, $before_widget );
				}
			}

			echo $before_widget;

			if ( in_array( $id, blockchain_get_fullwidth_sidebars(), true ) ) {
				?>
				<div class="container">
					<div class="row">
						<div class="col-12">
				<?php
			}

			if ( $title || $subtitle ) {
			 	?><div class="section-heading"><?php

				if ( $title ) {
					echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
				}

				if ( $subtitle ) {
					?><p class="section-subtitle"><?php echo esc_html( $subtitle ); ?></p><?php
				}

			 	?></div><?php
			}

			if ( $q->have_posts() ) {
				?><div class="row row-items"><?php

					while ( $q->have_posts() ) {
						$q->the_post();

						?><div class="<?php echo esc_attr( blockchain_get_columns_classes( $columns ) ); ?>"><?php

						get_template_part( 'template-parts/widgets/home-item', get_post_type() );

						?></div><?php
					}
					wp_reset_postdata();

				?></div><?php
			}

			if ( in_array( $id, blockchain_get_fullwidth_sidebars(), true ) ) {
				?>
						</div>
					</div>
				</div>
				<?php
			}

			echo $after_widget;
		}

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']     = sanitize_text_field( $new_instance['title'] );
			$instance['subtitle']  = sanitize_text_field( $new_instance['subtitle'] );
			$instance['post_type'] = in_array( $new_instance['post_type'], $this->get_available_post_types( 'names' ), true ) ? $new_instance['post_type'] : $this->defaults['post_type'];
			$instance['rows']      = $this->sanitize_instance_rows( $new_instance );
			$instance['columns']   = absint( $new_instance['columns'] );

			$instance['overlay_color']     = blockchain_sanitize_rgba_color( $new_instance['overlay_color'] );
			$instance['background_color']  = sanitize_hex_color( $new_instance['background_color'] );
			$instance['background_image']  = esc_url_raw( $new_instance['background_image'] );
			$instance['background_repeat'] = blockchain_sanitize_image_repeat( $new_instance['background_repeat'] );
			$instance['background_size']   = isset( $new_instance['background_size'] );
			$instance['parallax']          = isset( $new_instance['parallax'] );


			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme (home) - Post Type Items - Subtitle', $instance['subtitle'] );

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title     = $instance['title'];
			$subtitle  = $instance['subtitle'];
			$post_type = $instance['post_type'];
			$rows      = $instance['rows'];
			$columns   = $instance['columns'];

			$overlay_color     = $instance['overlay_color'];
			$background_color  = $instance['background_color'];
			$background_image  = $instance['background_image'];
			$background_repeat = $instance['background_repeat'];
			$background_size   = $instance['background_size'];
			$parallax          = $instance['parallax'];

			$post_types       = $this->get_available_post_types();
			$row_post_id_name = $this->get_field_name( 'row_post_id' ) . '[]';
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"><?php esc_html_e( 'Subtitle:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" type="text" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat" /></p>

			<p data-ajaxposts="<?php echo esc_attr( $this->ajax_posts ); ?>">
				<label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php esc_html_e( 'Post type:', 'blockchain' ); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>" class="widefat blockchain-post-type-select">
					<?php foreach ( $post_types as $key => $pt ) {
						?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $post_type ); ?>><?php echo esc_html( $pt->labels->name ); ?></option><?php
					} ?>
				</select>
			</p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php esc_html_e( 'Output Columns:', 'blockchain' ); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>">
					<?php
						$col_options = blockchain_post_type_listing_get_valid_columns_options();
						foreach ( $col_options['range'] as $col ) {
							echo sprintf( '<option value="%s" %s>%s</option>',
								esc_attr( $col ),
								selected( $columns, $col, false ),
								/* translators: %d is a number of columns. */
								esc_html( sprintf( _n( '%d Column', '%d Columns', $col, 'blockchain' ), $col ) )
							);
						}
					?>
				</select>
			</p>

			<p><?php esc_html_e( 'Add as many items as you want by pressing the "Add Item" button. Remove any item by selecting "Remove me".', 'blockchain' ); ?></p>
			<fieldset class="ci-repeating-fields">
				<div class="inner">
					<?php
						if ( ! empty( $rows ) ) {
							$count = count( $rows );
							for ( $i = 0; $i < $count; $i ++ ) {
								?>
								<div class="post-field">
									<label class="post-field-item" data-value="<?php echo esc_attr( $rows[ $i ]['post_id'] ); ?>"><?php esc_html_e( 'Item:', 'blockchain' ); ?>
										<?php
											blockchain_dropdown_posts( array(
												'post_type'            => $post_type,
												'selected'             => $rows[ $i ]['post_id'],
												'class'                => 'widefat posts_dropdown',
												'show_option_none'     => '&nbsp;',
												'select_even_if_empty' => true,
											), $row_post_id_name );
										?>
									</label>

									<p class="ci-repeating-remove-action"><a href="#" class="button ci-repeating-remove-field"><i class="dashicons dashicons-dismiss"></i><?php esc_html_e( 'Remove me', 'blockchain' ); ?></a></p>
								</div>
								<?php
							}
						}
					?>
					<?php
					//
					// Add an empty and hidden set for jQuery
					//
					?>
					<div class="post-field field-prototype" style="display: none;">
						<label class="post-field-item"><?php esc_html_e( 'Item:', 'blockchain' ); ?>
							<?php
								blockchain_dropdown_posts( array(
									'post_type'            => $post_type,
									'class'                => 'widefat posts_dropdown',
									'show_option_none'     => '&nbsp;',
									'select_even_if_empty' => true,
								), $row_post_id_name );
							?>
						</label>

						<p class="ci-repeating-remove-action"><a href="#" class="button ci-repeating-remove-field"><i class="dashicons dashicons-dismiss"></i><?php esc_html_e( 'Remove me', 'blockchain' ); ?></a></p>
					</div>
				</div>
				<a href="#" class="ci-repeating-add-field button"><i class="dashicons dashicons-plus-alt"></i><?php esc_html_e( 'Add Item', 'blockchain' ); ?></a>
			</fieldset>

			<fieldset class="ci-collapsible">
				<legend><?php esc_html_e( 'Customize', 'blockchain' ); ?> <i class="dashicons dashicons-arrow-down"></i></legend>
				<div class="elements">
					<p><label for="<?php echo esc_attr( $this->get_field_id( 'overlay_color' ) ); ?>"><?php esc_html_e( 'Overlay Color:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'overlay_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'overlay_color' ) ); ?>" type="text" value="<?php echo esc_attr( $overlay_color ); ?>" class="widefat blockchain-alpha-color-picker" /></p>
					<p><label for="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>"><?php esc_html_e( 'Background Color:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" type="text" value="<?php echo esc_attr( $background_color ); ?>" class="blockchain-color-picker widefat"/></p>

					<p class="ci-collapsible-media"><label for="<?php echo esc_attr( $this->get_field_id( 'background_image' ) ); ?>"><?php esc_html_e( 'Background Image:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'background_image' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_image' ) ); ?>" type="text" value="<?php echo esc_attr( $background_image ); ?>" class="ci-uploaded-url widefat"/><a href="#" class="button ci-media-button"><?php esc_html_e( 'Select', 'blockchain' ); ?></a></p>
					<p>
						<label for="<?php echo esc_attr( $this->get_field_id( 'background_repeat' ) ); ?>"><?php esc_html_e( 'Background Repeat:', 'blockchain' ); ?></label>
						<select id="<?php echo esc_attr( $this->get_field_id( 'background_repeat' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'background_repeat' ) ); ?>">
							<option value="repeat" <?php selected( 'repeat', $background_repeat ); ?>><?php esc_html_e( 'Repeat', 'blockchain' ); ?></option>
							<option value="repeat-x" <?php selected( 'repeat-x', $background_repeat ); ?>><?php esc_html_e( 'Repeat Horizontally', 'blockchain' ); ?></option>
							<option value="repeat-y" <?php selected( 'repeat-y', $background_repeat ); ?>><?php esc_html_e( 'Repeat Vertically', 'blockchain' ); ?></option>
							<option value="no-repeat" <?php selected( 'no-repeat', $background_repeat ); ?>><?php esc_html_e( 'No Repeat', 'blockchain' ); ?></option>
						</select>
					</p>
					<p><label for="<?php echo esc_attr( $this->get_field_id( 'background_size' ) ); ?>"><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'background_size' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'background_size' ) ); ?>" value="1" <?php checked( $background_size, 1 ); ?> /><?php esc_html_e( 'Stretch background image to cover the entire width (requires a background image).', 'blockchain' ); ?></label></p>

					<p><label for="<?php echo esc_attr( $this->get_field_id( 'parallax' ) ); ?>"><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'parallax' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'parallax' ) ); ?>" value="1" <?php checked( $parallax, 1 ); ?> /><?php esc_html_e( 'Parallax effect (requires a background image).', 'blockchain' ); ?></label></p>
				</div>
			</fieldset>
			<?php
		}

		protected function sanitize_instance_rows( $instance ) {
			if ( empty( $instance ) || ! is_array( $instance ) ) {
				return array();
			}

			$ids = $instance['row_post_id'];

			$count = count( $ids );

			$new_fields = array();

			$records_count = 0;

			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $ids[ $i ] ) ) {
					continue;
				}

				$new_fields[ $records_count ]['post_id'] = ! empty( $ids[ $i ] ) ? intval( $ids[ $i ] ) : '';

				$records_count++;
			}
			return $new_fields;
		}


		protected function get_available_post_types( $return = 'objects' ) {
			$return = in_array( $return, array( 'objects', 'names' ), true ) ? $return : 'objects';

			$post_types = get_post_types( array(
				'public' => true,
			), $return );

			$testimonials = get_post_types( array(
				'name' => 'blockchain_testimon',
			), $return );

			$post_types = array_merge( $post_types, $testimonials );

			unset( $post_types['attachment'] );
			unset( $post_types['elementor_library'] );

			$post_types = apply_filters( 'blockchain_widget_post_types_dropdown', $post_types, __CLASS__ );

			return $post_types;
		}

		static function _ajax_get_posts() {
			$post_type_name = isset( $_POST['post_type_name'] ) ? sanitize_key( $_POST['post_type_name'] ) : 'post'; // Input var okay.
			$name_field     = isset( $_POST['name_field'] ) ? esc_attr( $_POST['name_field'] ) : ''; // Input var okay.

			$str = blockchain_dropdown_posts( array(
				'echo'                 => false,
				'post_type'            => $post_type_name,
				'class'                => 'widefat posts_dropdown',
				'show_option_none'     => '&nbsp;',
				'select_even_if_empty' => true,
			), $name_field );

			echo $str;
			die;
		}

		function enqueue_custom_css() {
			$settings = $this->get_settings();

			if ( empty( $settings ) ) {
				return;
			}

			foreach ( $settings as $instance_id => $instance ) {
				$id = $this->id_base . '-' . $instance_id;

				if ( ! is_active_widget( false, $id, $this->id_base ) ) {
					continue;
				}

				$instance = wp_parse_args( (array) $instance, $this->defaults );

				$sidebar_id      = false; // Holds the sidebar id that the widget is assigned to.
				$sidebar_widgets = wp_get_sidebars_widgets();
				if ( ! empty( $sidebar_widgets ) ) {
					foreach ( $sidebar_widgets as $sidebar => $widgets ) {
						// We need to check $widgets for emptiness due to https://core.trac.wordpress.org/ticket/14876
						if ( ! empty( $widgets ) && array_search( $id, $widgets ) !== false ) {
							$sidebar_id = $sidebar;
						}
					}
				}

				$background_color  = $instance['background_color'];
				$background_image  = $instance['background_image'];
				$background_repeat = $instance['background_repeat'];
				$background_size   = $instance['background_size'] ? '' : 'auto'; // Assumes that background-size: cover; is applied by default.

				$css = '';

				if ( ! empty( $background_color ) ) {
					$css .= 'background-color: ' . $background_color . '; ';
				}
				if ( ! empty( $background_image ) ) {
					$css .= 'background-image: url(' . esc_url( $background_image ) . '); ';
					$css .= 'background-repeat: ' . $background_repeat . '; ';
				}

				if ( ! empty( $background_size ) ) {
					$css .= 'background-size: ' . $background_size . '; ';
				}

				if ( ! empty( $css ) ) {
					$css = '#' . $id . ' { ' . $css . ' } ' . PHP_EOL;
					wp_add_inline_style( 'blockchain-style', $css );
				}

				$overlay_color = $instance['overlay_color'];

				$css = '';

				if ( ! empty( $overlay_color ) ) {
					$css .= 'background-color: ' . $overlay_color . '; ';
				}

				if ( ! empty( $css ) ) {
					$css = '#' . $id . '::before { ' . $css . ' } ' . PHP_EOL;
					wp_add_inline_style( 'blockchain-style', $css );
				}

			}

		}

	} // class

endif;
