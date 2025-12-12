<?php
if ( ! class_exists( 'CI_Widget_Callout' ) ) :
	class CI_Widget_Callout extends WP_Widget {

		protected $defaults = array(
			'title'         => '',
			'content_title' => '',
			'text'          => '',
			'button_text'   => '',
			'button_url'    => '',
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Callout widget with custom call to action button.', 'blockchain' ) );
			$control_ops = array();
			parent::__construct( 'ci-callout', esc_html__( 'Theme - Callout', 'blockchain' ), $widget_ops, $control_ops );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

			$content_title = $instance['content_title'];
			$text          = $instance['text'];
			$button_text   = $instance['button_text'];
			$button_url    = $instance['button_url'];

			// WPML
			$content_title = apply_filters( 'wpml_translate_single_string', $content_title, 'Widgets', 'Callout - Content Title' );
			$text          = apply_filters( 'wpml_translate_single_string', $text, 'Widgets', 'Callout - Text' );
			$button_text   = apply_filters( 'wpml_translate_single_string', $button_text, 'Widgets', 'Callout - Button Text' );

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			?><div class="ci-box-callout"><?php

			if ( $content_title ) {
				?><strong class="ci-box-callout-title"><?php echo esc_html( $content_title ); ?></strong><?php
			}

			if ( $text ) {
				echo wpautop( $text );
			}

			if ( ! empty( $button_text ) && ! empty( $button_url ) ) {
				?><a href="<?php echo esc_url( $button_url ); ?>" class="btn btn-block"><?php echo wp_kses( $button_text, blockchain_get_allowed_tags() ); ?></a><?php
			}

			?></div><?php

			echo $after_widget;

		} // widget

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title']         = sanitize_text_field( $new_instance['title'] );
			$instance['content_title'] = sanitize_text_field( $new_instance['content_title'] );
			$instance['text']          = wp_kses( $new_instance['text'], blockchain_get_allowed_tags() );
			$instance['button_text']   = wp_kses( $new_instance['button_text'], blockchain_get_allowed_tags() );
			$instance['button_url']    = esc_url_raw( $new_instance['button_url'] );

			//WPML
			do_action( 'wpml_register_single_string', 'Widgets', 'Callout - Content Title', $instance['content_title'] );
			do_action( 'wpml_register_single_string', 'Widgets', 'Callout - Text', $instance['text'] );
			do_action( 'wpml_register_single_string', 'Widgets', 'Callout - Button Text', $instance['button_text'] );

			return $instance;
		}

		function form( $instance ) {

			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title         = $instance['title'];
			$content_title = $instance['content_title'];
			$text          = $instance['text'];
			$button_text   = $instance['button_text'];
			$button_url    = $instance['button_url'];

			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Widget Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'content_title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'content_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content_title' ) ); ?>" type="text" value="<?php echo esc_attr( $content_title ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Text:', 'blockchain' ); ?></label><textarea id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" class="widefat"><?php echo esc_textarea( $text ); ?></textarea></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>"><?php esc_html_e( 'Button Text:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'button_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_text' ) ); ?>" type="text" value="<?php echo esc_attr( $button_text ); ?>" class="widefat" /></p>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>"><?php esc_html_e( 'Button URL:', 'blockchain' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'button_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'button_url' ) ); ?>" type="text" value="<?php echo esc_attr( $button_url ); ?>" class="widefat" /></p>
			<?php
		} // form

	} // class

endif;
