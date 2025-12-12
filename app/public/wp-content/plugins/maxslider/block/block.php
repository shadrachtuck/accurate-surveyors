<?php
	add_action( 'init', 'maxslider_block_init' );
	function maxslider_block_init() {
		register_block_type( 'maxslider/slider', array(
			'attributes'      => array(
				'uniqueId'  => array(
					'type' => 'string',
				),
				'id'        => array(
					'type' => 'string',
				),
				'align'     => array(
					'type' => 'string',
				),
				'template'  => array(
					'type' => 'string',
				),
				'className' => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'render_callback' => 'maxslider_block_render_callback',
		) );
	}

	function maxslider_block_defaults() {
		return array(
			'uniqueId'  => false,
			'id'        => false,
			'align'     => false,
			'template'  => false,
			'className' => false,
		);
	}

	function maxslider_block_render_callback( $attributes ) {
		$attributes = wp_parse_args( $attributes, maxslider_block_defaults() );

		$unique_id  = $attributes['uniqueId'];
		$slider_id  = $attributes['id'];
		$align      = $attributes['align'];
		$template   = $attributes['template'];
		$class_name = $attributes['className'];

		$dom_id = 'maxslider-block-' . $unique_id;

		$block_classes = array_merge( array(
			'wp-block-maxslider-slider',
			$dom_id,
		), explode( ' ', $class_name ) );

		if ( $align ) {
			$block_classes[] = 'align' . $align;
		}

		if ( empty( $slider_id ) ) {
			return '';
		}

		ob_start();

		?>
		<div id="<?php echo esc_attr( $dom_id ); ?>" class="<?php echo esc_attr( implode( ' ', $block_classes ) ); ?>">
			<?php echo do_shortcode( sprintf( '[maxslider id="%s" template="%s"]', esc_attr( $slider_id ), esc_attr( $template ) ) ); ?>
		</div>
		<?php

		$response = ob_get_clean();

		return $response;
	}
