<?php
if ( ! class_exists( 'CI_Widget_Home_Hero_Callout' ) ) :
	class CI_Widget_Home_Hero_Callout extends WP_Widget {

		protected $defaults = array(
			'title'             => '',
			'subtitle'          => '',
			'button_text'       => '',
			'button_url'        => '',
			'align'             => 'center',
			'text_color'        => '',
			'overlay_color'     => '',
			'background_color'  => '',
			'background_image'  => '',
			'background_repeat' => 'repeat',
			'background_size'   => 1,
			'parallax'          => '',
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Homepage widget. Hero callout widget with custom call to action button.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-home-hero-callout', esc_html__( 'Theme (home) - Hero Callout', 'blockchain' ), $widget_ops, $control_ops );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_custom_css' ) );
		}


		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$subtitle    = $instance['subtitle'];
			$button_text = $instance['button_text'];
			$button_url  = $instance['button_url'];
			$align       = $instance['align'];

			// WPML
			$subtitle  = apply_filters( 'wpml_translate_single_string', $subtitle, 'Widgets', 'Theme (home) - Hero Callout - Subtitle' );
			$button_text  = apply_filters( 'wpml_translate_single_string', $button_text, 'Widgets', 'Theme (home) - Hero Callout - Button Text' );

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

			?><div class="widget-section-hero <?php echo esc_attr( $this->get_align_classes( $align ) ); ?>"><?php

			if ( $title || $subtitle ) {
				?>
				<div class="section-heading"><?php

				if ( $title ) {
					echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
				}

				if ( $subtitle ) {
					?><p class="section-subtitle"><?php echo wp_kses( $subtitle, blockchain_get_allowed_tags( 'guide' ) ); ?></p><?php
				}

				?></div><?php
			}

			if ( ! empty( $button_text ) && ! empty( $button_url ) ) {
				?><a href="<?php echo esc_url( $button_url ); ?>" class="btn btn-transparent btn-lg"><?php echo wp_kses( $button_text, blockchain_get_allowed_tags() ); ?></a><?php
			}

			?></div><?php

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

			$instance['title']       = sanitize_text_field( $new_instance['title'] );
			$instance['subtitle']    = wp_kses( $new_instance['subtitle'], blockchain_get_allowed_tags( 'guide' ) );
			$instance['button_text'] = wp_kses( $new_instance['button_text'], blockchain_get_allowed_tags() );
			$instance['button_url']  = esc_url_raw( $new_instance['button_url'] );
			$instance['align']       = $this->sanitize_align( $new_instance['align'] );

			$instance['text_color']        = sanitize_hex_color( $new_instance['text_color'] );
			$instance['overlay_color']     = blockchain_sanitize_rgba_color( $new_instance['overlay_color'] );
			$instance['background_color']  = sanitize_hex_color( $new_instance['background_color'] );
			$instance['background_image']  = esc_url_raw( $new_instance['background_image'] );
			$instance['background_repeat'] = blockchain_sanitize_image_repeat( $new_instance['background_repeat'] );
			$instance['background_size']   = isset( $new_instance['background_size'] );
			$instance['parallax']          = isset( $new_instance['parallax'] );

			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme (home) - Hero Callout - Subtitle', $instance['subtitle'] );
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme (home) - Hero Callout - Button Text', $instance['button_text'] );

			return $instance;
		} // save

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title       = $instance['title'];
			$subtitle    = $instance['subtitle'];
			$button_text = $instance['button_text'];
			$button_url  = $instance['button_url'];
			$align       = $instance['align'];

			$text_color        = $instance['text_color'];
			$overlay_color     = $instance['overlay_color'];
			$background_color  = $instance['background_color'];
			$background_image  = $instance['background_image'];
			$background_repeat = $instance['background_repeat'];
			$background_size   = $instance['background_size'];
			$parallax          = $instance['parallax'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat"/></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"><?php esc_html_e( 'Subtitle:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" type="text" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat"/></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php esc_html_e( 'Button Text:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" type="text" value="<?php echo esc_attr( $button_text ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>"><?php esc_html_e( 'Button URL:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_url' ) ); ?>" type="text" value="<?php echo esc_attr( $button_url ); ?>" class="widefat" /></p>

			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>"><?php esc_html_e( 'Align text:', 'blockchain' ); ?></label>
				<select id="<?php echo esc_attr( $this->get_field_id( 'align' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'align' ) ); ?>" class="widefat">
					<?php foreach ( $this->get_align_options() as $key => $value ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $align, $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<fieldset class="ci-collapsible">
				<legend><?php esc_html_e( 'Customize', 'blockchain' ); ?> <i class="dashicons dashicons-arrow-down"></i></legend>
				<div class="elements">
					<p><label for="<?php echo esc_attr( $this->get_field_id( 'text_color' ) ); ?>"><?php esc_html_e( 'Text Color:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'text_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text_color' ) ); ?>" type="text" value="<?php echo esc_attr( $text_color ); ?>" class="blockchain-color-picker widefat"/></p>
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

		public function get_align_options() {
			return array(
				'left'   => __( 'Left', 'blockchain' ),
				'center' => __( 'Center', 'blockchain' ),
				'right'  => __( 'Right', 'blockchain' ),
			);
		}

		public function sanitize_align( $value ) {
			$choices = $this->get_align_options();
			if ( array_key_exists( $value, $choices ) ) {
				return $value;
			}

			return $this->defaults['align'];
		}

		public function get_align_classes( $align = 'center' ) {
			$classes = array(
				'left'   => 'widget-section-hero-left',
				'center' => '',
				'right'  => 'widget-section-hero-right',
			);

			if ( ! array_key_exists( $align, $classes ) ) {
				$align = $this->defaults['align'];
			}

			return $classes[ $align ];
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

				$text_color        = $instance['text_color'];
				$background_color  = $instance['background_color'];
				$background_image  = $instance['background_image'];
				$background_repeat = $instance['background_repeat'];
				$background_size   = $instance['background_size'] ? '' : 'auto'; // Assumes that background-size: cover; is applied by default.

				$css = '';

				if ( ! empty( $text_color ) ) {
					$css .= 'color: ' . $text_color . '; ';
				}
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
