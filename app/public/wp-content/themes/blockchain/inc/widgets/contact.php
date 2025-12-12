<?php
if ( ! class_exists( 'CI_Widget_Contact' ) ) :
	class CI_Widget_Contact extends WP_Widget {

		protected $defaults = array(
			'title'          => '',
			'map_code'       => '',
			'contact_title'  => '',
			'contact_fields' => array(),
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Display a map and contact information.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-contact', esc_html__( 'Theme - Contact', 'blockchain' ), $widget_ops, $control_ops );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$map_code      = $instance['map_code'];
			$contact_title = $instance['contact_title'];
			$fields        = $instance['contact_fields'];

			// WPML
			$content_title = apply_filters( 'wpml_translate_single_string', $contact_title, 'Widgets', 'Theme - Contact - Contact Title' );

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			if ( $map_code ) {
				echo do_shortcode( $this->sanitize_map_code( $map_code ) );
			}

			if ( $contact_title ) {
				echo '<p class="ci-contact-widget-title">' . esc_html( $contact_title ) . '</p>';
			}

			if ( $fields ) {
				echo '<ul class="ci-contact-widget-items">';
				foreach ( $fields as $field ) {
					echo sprintf( '<li class="ci-contact-widget-item"><i class="fa %1$s"></i> %2$s</li>', $field['icon'], $field['title'] );
				}
				echo '</ul>';
			}

			echo $after_widget;

		} // widget

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			if ( current_user_can( 'unfiltered_html' ) ) {
				$instance['map_code'] = $new_instance['map_code'];
			} else {
				$instance['map_code'] = $this->sanitize_map_code( $new_instance['map_code'] );
			}
			$instance['contact_title']  = sanitize_text_field( $new_instance['contact_title'] );
			$instance['contact_fields'] = $this->sanitize_contact_fields( $new_instance );

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title         = $instance['title'];
			$map_code      = $instance['map_code'];
			$contact_title = $instance['contact_title'];
			$fields        = $instance['contact_fields'];

			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Contact - Contact Title', $instance['contact_title'] );

			$field_title_name = $this->get_field_name( 'contact_field_title' ) . '[]';
			$field_icon_name  = $this->get_field_name( 'contact_field_icon' ) . '[]';
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'map_code' ) ); ?>"><?php esc_html_e( 'Map code (accepts HTML):', 'blockchain' ); ?></label><textarea id="<?php echo esc_attr( $this->get_field_id( 'map_code' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'map_code' ) ); ?>" class="widefat"><?php echo esc_textarea( $map_code ); ?></textarea></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'contact_title' ) ); ?>"><?php esc_html_e( 'Contact title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'contact_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'contact_title' ) ); ?>" type="text" value="<?php echo esc_attr( $contact_title ); ?>" class="widefat" /></p>

			<p><?php esc_html_e( 'Add as many items as you want by pressing the "Add Item" button. Remove any item by selecting "Remove me".', 'blockchain' ); ?></p>
			<fieldset class="ci-repeating-fields">
				<div class="inner">
					<?php
						if ( ! empty( $fields ) ) {
							$count = count( $fields );
							for ( $i = 0; $i < $count; $i++ ) {
								?>
								<div class="post-field">
									<label class="post-field-item"><?php echo wp_kses( sprintf( __( 'Icon (e.g. <code>fa-check</code> <a href="%s" target="_blank">Reference</a>):', 'blockchain' ), 'http://fontawesome.io/icons/' ), array( 'code' => array(), 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?>
										<input type="text" name="<?php echo esc_attr( $field_icon_name ); ?>" value="<?php echo esc_attr( $fields[ $i ]['icon'] ); ?>" class="widefat" />
									</label>

									<label class="post-field-item"><?php esc_html_e( 'Title:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $field_title_name ); ?>" value="<?php echo esc_attr( $fields[ $i ]['title'] ); ?>" class="widefat" />
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
						<label class="post-field-item"><?php echo wp_kses( sprintf( __( 'Icon (e.g. <code>fa-check</code> <a href="%s" target="_blank">Reference</a>):', 'blockchain' ), 'http://fontawesome.io/icons/' ), array( 'code' => array(), 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?>
							<input type="text" name="<?php echo esc_attr( $field_icon_name ); ?>" value="" class="widefat" />
						</label>

						<label class="post-field-item"><?php esc_html_e( 'Title:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $field_title_name ); ?>" value="" class="widefat" />
						</label>

						<p class="ci-repeating-remove-action"><a href="#" class="button ci-repeating-remove-field"><i class="dashicons dashicons-dismiss"></i><?php esc_html_e( 'Remove me', 'blockchain' ); ?></a></p>
					</div>
				</div>
				<a href="#" class="ci-repeating-add-field button"><i class="dashicons dashicons-plus-alt"></i><?php esc_html_e( 'Add Item', 'blockchain' ); ?></a>
			</fieldset>

			<?php
		} // form

		protected function sanitize_contact_fields( $instance ) {
			if ( empty( $instance ) || ! is_array( $instance ) ) {
				return array();
			}

			$icons  = $instance['contact_field_icon'];
			$titles = $instance['contact_field_title'];

			$count = max( count( $icons ), count( $titles ) );

			$new_fields = array();

			$records_count = 0;

			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $titles[ $i ] ) && empty( $icons[ $i ] ) ) {
					continue;
				}

				$new_fields[ $records_count ]['icon']  = sanitize_key( $icons[ $i ] );
				$new_fields[ $records_count ]['title'] = sanitize_text_field( $titles[ $i ] );

				$records_count++;
			}
			return $new_fields;
		}

		protected function sanitize_map_code( $map_code ) {
			$allowed = wp_kses_allowed_html( 'post' );
			$allowed['iframe'] = array(
				'src'             => array(),
				'width'           => array(),
				'height'          => array(),
				'style'           => array(),
				'frameborder'     => array(),
				'allowfullscreen' => array(),
				'scrolling'       => array(),
				'marginwidth'     => array(),
				'marginheight'    => array(),
			);

			return wp_kses( $map_code, $allowed );
		}
	} // class

endif;
