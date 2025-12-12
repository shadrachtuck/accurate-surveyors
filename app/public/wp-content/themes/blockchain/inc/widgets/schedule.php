<?php
if ( ! class_exists( 'CI_Widget_Schedule' ) ) :
	class CI_Widget_Schedule extends WP_Widget {

		protected $defaults = array(
			'title'           => '',
			'text'            => '',
			'schedule_fields' => array(),
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Display a schedule.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-schedule', esc_html__( 'Theme - Schedule', 'blockchain' ), $widget_ops, $control_ops );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$text   = $instance['text'];
			$fields = $instance['schedule_fields'];

			//WPML
			$text   = apply_filters( 'wpml_translate_single_string', $text, 'Widgets', 'Theme - Callout - Text' );

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			if ( $text ) {
				?><p class="ci-schedule-widget-intro"><?php echo do_shortcode( wp_kses_post( $text ) ); ?></p><?php
			}

			if ( $fields ) {
				?><table class="ci-schedule-widget-table"><tbody><?php

				foreach ( $fields as $field ) {
					$day  = $field['day'] ? $field['day'] : '&nbsp;';
					$time = $field['time'] ? $field['time'] : '&nbsp;';

					//WPML
					apply_filters( 'wpml_translate_single_string', $day, 'Widgets', 'Theme - Schedule - Day' );
					apply_filters( 'wpml_translate_single_string', $time, 'Widgets', 'Theme - Schedule - Time' );
					?>
					<tr>
						<th><?php echo esc_html( $day ); ?></th>
						<td><?php echo esc_html( $time ); ?></td>
					</tr>
					<?php
				}

				?></tbody></table><?php
			}

			echo $after_widget;

		} // widget

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']           = sanitize_text_field( $new_instance['title'] );
			$instance['text']            = wp_kses_post( $new_instance['text'] );
			$instance['schedule_fields'] = $this->sanitize_schedule_fields( $new_instance );

			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Schedule - Text', $instance['text'] );

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title  = $instance['title'];
			$text   = $instance['text'];
			$fields = $instance['schedule_fields'];

			$field_day_name  = $this->get_field_name( 'schedule_field_day' ) . '[]';
			$field_time_name = $this->get_field_name( 'schedule_field_time' ) . '[]';
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Text (accepts HTML):', 'blockchain' ); ?></label><textarea id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" class="widefat"><?php echo esc_textarea( $text ); ?></textarea></p>

			<p><?php esc_html_e( 'Add as many items as you want by pressing the "Add Item" button. Remove any item by selecting "Remove me".', 'blockchain' ); ?></p>
			<fieldset class="ci-repeating-fields">
				<div class="inner">
					<?php
						if ( ! empty( $fields ) ) {
							$count = count( $fields );
							for ( $i = 0; $i < $count; $i++ ) {
								?>
								<div class="post-field">
									<label class="post-field-item"><?php esc_html_e( 'Day:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $field_day_name ); ?>" value="<?php echo esc_attr( $fields[ $i ]['day'] ); ?>" class="widefat" />
									</label>

									<label class="post-field-item"><?php esc_html_e( 'Time:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $field_time_name ); ?>" value="<?php echo esc_attr( $fields[ $i ]['time'] ); ?>" class="widefat" />
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
						<label class="post-field-item"><?php esc_html_e( 'Day:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $field_day_name ); ?>" value="" class="widefat" />
						</label>

						<label class="post-field-item"><?php esc_html_e( 'Time:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $field_time_name ); ?>" value="" class="widefat" />
						</label>

						<p class="ci-repeating-remove-action"><a href="#" class="button ci-repeating-remove-field"><i class="dashicons dashicons-dismiss"></i><?php esc_html_e( 'Remove me', 'blockchain' ); ?></a></p>
					</div>
				</div>
				<a href="#" class="ci-repeating-add-field button"><i class="dashicons dashicons-plus-alt"></i><?php esc_html_e( 'Add Item', 'blockchain' ); ?></a>
			</fieldset>

			<?php
		} // form

		protected function sanitize_schedule_fields( $instance ) {
			if ( empty( $instance ) || ! is_array( $instance ) ) {
				return array();
			}

			$days  = $instance['schedule_field_day'];
			$times = $instance['schedule_field_time'];

			$count = max( count( $days ), count( $times ) );

			$new_fields = array();

			$records_count = 0;

			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $days[ $i ] ) && empty( $times[ $i ] ) ) {
					continue;
				}

				$new_fields[ $records_count ]['day']  = sanitize_text_field( $days[ $i ] );
				$new_fields[ $records_count ]['time'] = sanitize_text_field( $times[ $i ] );

				//WPML
				do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Schedule - Day', $new_fields[ $records_count ]['day'] );
				do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Schedule - Time', $new_fields[ $records_count ]['time'] );

				$records_count++;
			}
			return $new_fields;
		}
	} // class

endif;
