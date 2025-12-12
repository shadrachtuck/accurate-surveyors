<?php
namespace Elementor;

class Widget_Latest_Post_Type extends Widget_Base {

	public function get_name() {
		return 'latest_post_type';
	}

	public function get_title() {
		return __( 'Latest Post Type', 'blockchain' );
	}

	public function get_icon() {
		return 'eicon-wordpress';
	}

	public function get_categories() {
		return [ 'blockchain-elements' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Latest Posts', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Displays a number of the latest (or random) posts from a specific post type.', 'blockchain' ),
				'content_classes' => 'ci-description',
			]
		);

		$this->add_control(
			'post_type',
			[
				'label'   => __( 'Post Type', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => blockchain_get_available_post_types(),
			]
		);

		$this->add_control(
			'random',
			[
				'label'        => __( 'Display in random order', 'blockchain' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'label_on'     => __( 'Yes', 'blockchain' ),
				'label_off'    => __( 'No', 'blockchain' ),
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'count',
			[
				'label'   => __( 'Number of posts to show:', 'blockchain' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 1,
				],
				'range'   => [
					'min'  => 1,
					'max'  => 250,
					'step' => 1,
				],
			]
		);

		$this->add_control(
			'columns',
			[
				'label'   => __( 'Columns', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '2',
				'options' => [
					'2' => __( 'Two', 'blockchain' ),
					'3' => __( 'Three', 'blockchain' ),
					'4' => __( 'Four', 'blockchain' ),
				],
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
				'label' => __( 'Latest Posts Element Styles', 'blockchain' ),
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

		$post_type = $settings['post_type'];
		$random    = 'yes' === $settings['random'] ? true : false;
		$count     = $settings['count']['size'];
		$columns   = $settings['columns'];

		echo do_shortcode( sprintf( '[latest-post-type post_type="%1$s" count="%2$s" columns="%3$s" random="%4$s"]',
			$post_type,
			$count,
			$columns,
			$random
		) );

	}

}
