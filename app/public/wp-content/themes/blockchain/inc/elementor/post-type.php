<?php
namespace Elementor;

class Widget_Post_Type extends Widget_Base {

	public function get_name() {
		return 'post_type_widget';
	}

	public function get_title() {
		return __( 'Blockchain Post Type', 'blockchain' );
	}

	public function get_icon() {
		return 'fa fa-window-maximize';
	}

	public function get_categories() {
		return [ 'blockchain-elements' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Blockchain Post Type', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Display any post type item from Blockchain by first selecting the post type and then the item itself.', 'blockchain' ),
				'content_classes' => 'ci-description',
			]
		);

		$this->add_control(
			'post_types',
			[
				'label'   => __( 'Post Type', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => blockchain_get_available_post_types(),
			]
		);

		$this->add_control(
			'selected_post',
			[
				'label'   => __( 'Post', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => '',
			]
		);

		$this->add_control(
			'view',
			[
				'label'   => __( 'View', 'blockchain' ),
				'type'    => Controls_Manager::HIDDEN,
				'default' => 'traditional',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Post Type Element Styles', 'blockchain' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Text Color', 'blockchain' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}};',
				],
				'scheme'    => [
					'type'  => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'   => 'typography',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
			]
		);

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings();

		if ( empty( $settings['selected_post'] ) ) {
			return;
		}

		$post_id = $settings['selected_post'];

		$q = new \WP_Query( array(
			'post_type' => get_post_type( $post_id ),
			'p'         => $post_id,
		) );

		while ( $q->have_posts() ) : $q->the_post();
			get_template_part( 'template-parts/widgets/home-item', get_post_type() );
		endwhile;

		wp_reset_postdata();
	}

}
