<?php
if ( ! class_exists( 'CI_Widget_Buttons' ) ) :
	class CI_Widget_Buttons extends WP_Widget {

		protected $defaults = array(
			'title' => '',
			'rows'  => array(),
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'A list of buttons.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-buttons', esc_html__( 'Theme - Buttons', 'blockchain' ), $widget_ops, $control_ops );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$rows = $instance['rows'];

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			if ( ! empty( $rows ) ) {
				?><div class="widget-button-list"><?php
					foreach ( $rows as $row ) {
						$item_classes = array( 'item-btn' );
						if ( empty( $row['title'] ) || empty( $row['subtitle'] ) ) {
							$item_classes[] = 'item-btn-sm';
						}

						//WPML
						apply_filters( 'wpml_translate_single_string', $row['title'], 'Widgets', 'Theme - Buttons - Title' );
						apply_filters( 'wpml_translate_single_string', $row['subtitle'], 'Widgets', 'Theme - Buttons - Subtitle' );
						apply_filters( 'wpml_translate_single_string', $row['url'], 'Widgets', 'Theme - Buttons - URL' );
						?>
						<a href="<?php echo esc_url( $row['url'] ); ?>" class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
							<?php if ( ! empty( $row['icon'] ) ) : ?>
								<span class="item-btn-icon">
									<i class="fa <?php echo esc_attr( $row['icon'] ); ?>"></i>
								</span>
							<?php endif; ?>

							<div class="item-btn-content">
								<?php if ( ! empty( $row['title'] ) ) : ?>
									<span class="item-btn-title"><?php echo esc_html( $row['title'] ); ?></span>
								<?php endif; ?>

								<?php if ( ! empty( $row['subtitle'] ) ) : ?>
									<span class="item-btn-subtitle"><?php echo esc_html( $row['subtitle'] ); ?></span>
								<?php endif; ?>
							</div>
						</a>
						<?php
					}
				?></div><?php
			}

			echo $after_widget;

		} // widget

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			$instance['rows']  = $this->sanitize_instance_rows( $new_instance );

			return $instance;
		}

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title = $instance['title'];
			$rows  = $instance['rows'];

			$row_title_name    = $this->get_field_name( 'row_title' ) . '[]';
			$row_subtitle_name = $this->get_field_name( 'row_subtitle' ) . '[]';
			$row_icon_name     = $this->get_field_name( 'row_icon' ) . '[]';
			$row_url_name      = $this->get_field_name( 'row_url' ) . '[]';
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>

			<p><?php esc_html_e( 'Add as many items as you want by pressing the "Add Item" button. Remove any item by selecting "Remove me".', 'blockchain' ); ?></p>
			<p><?php echo wp_kses( sprintf( __( 'In the Icon code field, type the icon code that best describes your item, including the <em>fa-</em> prefix. E.g. <code>fa-file-pdf-o</code>. <a href="%s" target="_blank">Reference</a>', 'blockchain' ), 'http://fontawesome.io/icons/' ), blockchain_get_allowed_tags( 'guide' ) ); ?></p>
			<fieldset class="ci-repeating-fields">
				<div class="inner">
					<?php
						if ( ! empty( $rows ) ) {
							$count = count( $rows );
							for ( $i = 0; $i < $count; $i ++ ) {
								?>
								<div class="post-field">
									<label class="post-field-item"><?php esc_html_e( 'Title:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $row_title_name ); ?>" value="<?php echo esc_attr( $rows[ $i ]['title'] ); ?>" class="widefat" />
									</label>

									<label class="post-field-item"><?php esc_html_e( 'Subtitle:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $row_subtitle_name ); ?>" value="<?php echo esc_attr( $rows[ $i ]['subtitle'] ); ?>" class="widefat" />
									</label>

									<label class="post-field-item"><?php esc_html_e( 'Icon code:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $row_icon_name ); ?>" value="<?php echo esc_attr( $rows[ $i ]['icon'] ); ?>" class="widefat" />
									</label>

									<label class="post-field-item"><?php esc_html_e( 'Link URL:', 'blockchain' ); ?>
										<input type="text" name="<?php echo esc_attr( $row_url_name ); ?>" value="<?php echo esc_attr( $rows[ $i ]['url'] ); ?>" class="widefat" />
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
						<label class="post-field-item"><?php esc_html_e( 'Title:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $row_title_name ); ?>" value="" class="widefat" />
						</label>

						<label class="post-field-item"><?php esc_html_e( 'Subtitle:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $row_subtitle_name ); ?>" value="" class="widefat" />
						</label>

						<label class="post-field-item"><?php esc_html_e( 'Icon code:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $row_icon_name ); ?>" value="" class="widefat" />
						</label>

						<label class="post-field-item"><?php esc_html_e( 'Link URL:', 'blockchain' ); ?>
							<input type="text" name="<?php echo esc_attr( $row_url_name ); ?>" value="" class="widefat" />
						</label>

						<p class="ci-repeating-remove-action"><a href="#" class="button ci-repeating-remove-field"><i class="dashicons dashicons-dismiss"></i><?php esc_html_e( 'Remove me', 'blockchain' ); ?></a></p>
					</div>
				</div>
				<a href="#" class="ci-repeating-add-field button"><i class="dashicons dashicons-plus-alt"></i><?php esc_html_e( 'Add Item', 'blockchain' ); ?></a>
			</fieldset>
			<?php
		} // form

		protected function sanitize_instance_rows( $instance ) {
			if ( empty( $instance ) || ! is_array( $instance ) ) {
				return array();
			}

			$titles    = $instance['row_title'];
			$subtitles = $instance['row_subtitle'];
			$icons     = $instance['row_icon'];
			$urls      = $instance['row_url'];

			$count = max(
				count( $titles ),
				count( $subtitles ),
				count( $icons ),
				count( $urls )
			);

			$new_fields = array();

			$records_count = 0;

			for ( $i = 0; $i < $count; $i++ ) {
				if ( empty( $titles[ $i ] )
				     && empty( $subtitles[ $i ] )
				     && empty( $icons[ $i ] )
				     && empty( $urls[ $i ] )
				) {
					continue;
				}

				$new_fields[ $records_count ]['title']    = sanitize_text_field( $titles[ $i ] );
				$new_fields[ $records_count ]['subtitle'] = sanitize_text_field( $subtitles[ $i ] );
				$new_fields[ $records_count ]['icon']     = sanitize_html_class( $icons[ $i ] );
				$new_fields[ $records_count ]['url']      = esc_url_raw( $urls[ $i ] );

				//WPML
				do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Buttons - Title', $new_fields[ $records_count ]['title'] );
				do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Buttons - Subtitle', $new_fields[ $records_count ]['subtitle'] );
				do_action( 'wpml_register_single_string', 'Widgets', 'Theme - Buttons - URL', $new_fields[ $records_count ]['url'] );

				$records_count++;
			}
			return $new_fields;
		}

	} // class

endif;
