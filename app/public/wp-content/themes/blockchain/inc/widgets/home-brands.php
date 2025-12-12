<?php
if ( ! class_exists( 'CI_Widget_Home_Brands' ) ) :
	class CI_Widget_Home_Brands extends WP_Widget {

		protected $defaults = array(
			'title'             => '',
			'subtitle'          => '',
			'brands'            => array(),
			'overlay_color'     => '',
			'background_color'  => '',
			'background_image'  => '',
			'background_repeat' => 'repeat',
			'background_size'   => 1,
			'parallax'          => '',
		);

		function __construct() {
			$control_ops = array();
			$widget_ops  = array( 'description' => esc_html__( 'Homepage widget. Brand logos.', 'blockchain' ) );
			parent::__construct( 'ci-home-brands', esc_html__( 'Theme (home) - Brands', 'blockchain' ), $widget_ops, $control_ops );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_custom_css' ) );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$subtitle = $instance['subtitle'];
			$brands   = $instance['brands'];

			// WPML
			$subtitle  = apply_filters( 'wpml_translate_single_string', $subtitle, 'Widgets', 'Theme (home) - Brands - Subtitle' );

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

			?><div class="list-brand-logos"><?php

			foreach ( $brands as $brand ) {
				if ( ! empty( $brand['url'] ) ) {
					?><a href="<?php echo esc_url( $brand['url'] ); ?>" target="_blank"><?php
				}

				$image_url = wp_get_attachment_image_url( $brand['image_id'], 'blockchain_brand_logo' );
				if ( ! empty( $image_url ) ) {
					$attachment = wp_prepare_attachment_for_js( $brand['image_id'] );
					?><span class="brand-logo"><img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $attachment['alt'] ); ?>"></span><?php
				}

				if ( ! empty( $brand['url'] ) ) {
					?></a><?php
				}
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

		} // widget

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']    = sanitize_text_field( $new_instance['title'] );
			$instance['subtitle'] = sanitize_text_field( $new_instance['subtitle'] );
			$instance['brands']   = $this->sanitize_instance_brands( $new_instance );

			$instance['overlay_color']     = blockchain_sanitize_rgba_color( $new_instance['overlay_color'] );
			$instance['background_color']  = sanitize_hex_color( $new_instance['background_color'] );
			$instance['background_image']  = esc_url_raw( $new_instance['background_image'] );
			$instance['background_repeat'] = blockchain_sanitize_image_repeat( $new_instance['background_repeat'] );
			$instance['background_size']   = isset( $new_instance['background_size'] );
			$instance['parallax']          = isset( $new_instance['parallax'] );

			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme (home) - Brands - Subtitle', $instance['subtitle'] );

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title    = $instance['title'];
			$subtitle = $instance['subtitle'];
			$brands   = $instance['brands'];

			$overlay_color     = $instance['overlay_color'];
			$background_color  = $instance['background_color'];
			$background_image  = $instance['background_image'];
			$background_repeat = $instance['background_repeat'];
			$background_size   = $instance['background_size'];
			$parallax          = $instance['parallax'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>"><?php esc_html_e( 'Subtitle:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" type="text" value="<?php echo esc_attr( $subtitle ); ?>" class="widefat" /></p>

			<p><?php esc_html_e( 'Add as many items as you want by pressing the "Add Item" button. Remove any item by selecting "Remove me".', 'blockchain' ); ?></p>
			<fieldset class="ci-repeating-fields">
				<div class="inner">
					<?php
						if ( ! empty( $brands ) ) {
							$count = count( $brands );
							for ( $i = 0; $i < $count; $i++ ) {
								?>
								<div class="post-field">
									<label class="post-field-item"><?php esc_html_e( 'Logo:', 'blockchain' ); ?>
										<div class="ci-upload-preview">
											<div class="upload-preview">
												<?php if ( ! empty( $brands[ $i ]['image_id'] ) ) : ?>
													<?php
														$image_url = wp_get_attachment_image_url( $brands[ $i ]['image_id'], 'blockchain_featgal_small_thumb' );
														echo sprintf( '<img src="%s" /><a href="#" class="close media-modal-icon" title="%s"></a>',
															esc_url( $image_url ),
															esc_attr__( 'Remove image', 'blockchain' )
														);
													?>
												<?php endif; ?>
											</div>
											<input type="hidden" class="ci-uploaded-id" name="<?php echo esc_attr( $this->get_field_name( 'brand_image_id' ) . '[]' ); ?>" value="<?php echo esc_attr( $brands[ $i ]['image_id'] ); ?>" />
											<input type="button" class="button ci-media-button" value="<?php esc_attr_e( 'Select Image', 'blockchain' ); ?>" />
										</div>
									</label>

									<label class="post-field-item"><?php esc_html_e( 'URL:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'brand_url' ) . '[]' ); ?>" value="<?php echo esc_attr( $brands[ $i ]['url'] ); ?>" class="widefat" />
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
						<label class="post-field-item"><?php esc_html_e( 'Logo:', 'blockchain' ); ?>
							<div class="ci-upload-preview">
								<div class="upload-preview"></div>
								<input type="hidden" class="ci-uploaded-id" name="<?php echo esc_attr( $this->get_field_name( 'brand_image_id' ) . '[]' ); ?>" value="" />
								<input type="button" class="button ci-media-button" value="<?php esc_attr_e( 'Select Image', 'blockchain' ); ?>" />
							</div>
						</label>

						<label class="post-field-item"><?php esc_html_e( 'URL:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'brand_url' ) . '[]' ); ?>" value="" class="widefat" />
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
		} // form

		protected function sanitize_instance_brands( $instance ) {
			if ( empty( $instance ) || ! is_array( $instance ) ) {
				return array();
			}

			$image_ids = $instance['brand_image_id'];
			$urls      = $instance['brand_url'];

			$count = max(
				count( $image_ids ),
				count( $urls )
			);

			$new_fields = array();

			$records_count = 0;

			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $image_ids[ $i ] ) ) {
					continue;
				}

				$new_fields[ $records_count ]['image_id'] = blockchain_sanitize_intval_or_empty( $image_ids[ $i ] );
				$new_fields[ $records_count ]['url']      = esc_url_raw( $urls[ $i ] );

				$records_count++;
			}
			return $new_fields;
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
