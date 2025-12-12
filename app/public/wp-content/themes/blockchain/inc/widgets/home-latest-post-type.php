<?php
if ( ! class_exists( 'CI_Widget_Home_Latest_Post_Type' ) ) :
	class CI_Widget_Home_Latest_Post_Type extends WP_Widget {

		protected $defaults = array(
			'title'             => '',
			'subtitle'          => '',
			'post_type'         => 'post',
			'random'            => false,
			'count'             => 3,
			'columns'           => 3,
			'overlay_color'     => '',
			'background_color'  => '',
			'background_image'  => '',
			'background_repeat' => 'repeat',
			'background_size'   => 1,
			'parallax'          => '',
		);

		function __construct() {
			$widget_ops  = array( 'description' => __( 'Homepage widget. Displays a number of the latest (or random) posts from a specific post type.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-home-latest-post-type', esc_html__( 'Theme (home) - Latest Post Type', 'blockchain' ), $widget_ops, $control_ops );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_custom_css' ) );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title     = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$subtitle  = $instance['subtitle'];
			$post_type = $instance['post_type'];
			$random    = $instance['random'];
			$count     = $instance['count'];
			$columns   = $instance['columns'];

			// WPML
			$subtitle  = apply_filters( 'wpml_translate_single_string', $subtitle, 'Widgets', 'Theme (home) - Latest Post Type - Subtitle' );

			if ( 0 === $count ) {
				return;
			}

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

			echo do_shortcode( sprintf( '[latest-post-type post_type="%1$s" count="%2$s" columns="%3$s" random="%4$s"]',
				$post_type,
				$count,
				$columns,
				$random
			) );

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
			$instance['post_type'] = sanitize_key( $new_instance['post_type'] );
			$instance['random']    = isset( $new_instance['random'] );
			$instance['count']     = absint( $new_instance['count'] );
			$instance['columns']   = absint( $new_instance['columns'] );

			$instance['overlay_color']     = blockchain_sanitize_rgba_color( $new_instance['overlay_color'] );
			$instance['background_color']  = sanitize_hex_color( $new_instance['background_color'] );
			$instance['background_image']  = esc_url_raw( $new_instance['background_image'] );
			$instance['background_repeat'] = blockchain_sanitize_image_repeat( $new_instance['background_repeat'] );
			$instance['background_size']   = isset( $new_instance['background_size'] );
			$instance['parallax']          = isset( $new_instance['parallax'] );

			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme (home) - Latest Post Type - Subtitle', $instance['subtitle'] );

			return $instance;
		} // save

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title     = $instance['title'];
			$subtitle  = $instance['subtitle'];
			$post_type = $instance['post_type'];
			$random    = $instance['random'];
			$count     = $instance['count'];
			$columns   = $instance['columns'];

			$overlay_color     = $instance['overlay_color'];
			$background_color  = $instance['background_color'];
			$background_image  = $instance['background_image'];
			$background_repeat = $instance['background_repeat'];
			$background_size   = $instance['background_size'];
			$parallax          = $instance['parallax'];

			$post_types = $this->get_available_post_types();

			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"><?php esc_html_e( 'Subtitle:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" type="text" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>"><?php esc_html_e( 'Select a post type to display the latest posts from:', 'blockchain' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'post_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>" class="widefat">
				<?php foreach ( $post_types as $key => $type ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $post_type, $key ); ?>>
						<?php echo esc_html( $type->labels->name ); ?>
					</option>
				<?php endforeach; ?>
			</select></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>"><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'random' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'random' ) ); ?>" value="1" <?php checked( $random, 1 ); ?> /><?php esc_html_e( 'Show random posts.', 'blockchain' ); ?></label></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>"><?php esc_html_e( 'Number of posts to show:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" type="number" min="1" step="1" value="<?php echo esc_attr( $count ); ?>" class="widefat"/></p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>"><?php esc_html_e( 'Output Columns:', 'blockchain' ); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'columns' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'columns' ) ); ?>" class="widefat">
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

		} // form

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

	}

endif;
