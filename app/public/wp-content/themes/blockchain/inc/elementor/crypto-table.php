<?php
namespace Elementor;

class Widget_Crypto_Table extends Widget_Base {

	public function get_name() {
		return 'crypto_table';
	}

	public function get_title() {
		return __( 'Crypto Table', 'blockchain' );
	}

	public function get_icon() {
		return 'fa fa-desktop';
	}

	public function get_categories() {
		return [ 'blockchain-elements' ];
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_title',
			[
				'label' => __( 'Crypto Table', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Displays cryptocurrency table.', 'blockchain' ),
				'content_classes' => 'ci-description',
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => __( 'Number of top cryptocurrencies:', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '15',
				'options' => [
					'0'   => esc_html__( 'All', 'blockchain' ),
					'3'   => '3',
					'5'   => '5',
					'10'  => '10',
					'15'  => '15',
					'25'  => '25',
					'50'  => '50',
					'100' => '100',
				],
			]
		);

		$this->add_control(
			'start',
			[
				'label'   => __( 'Number of top currencies to skip (0 for none):', 'blockchain' ),
				'type'    => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0,
				],
				'range'   => [
					'min'  => 0,
					'max'  => 100,
					'step' => 1,
				],
			]
		);

		$this->add_control(
			'fiat',
			[
				'label'   => __( 'Show values expressed in:', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'USD',
				'options' => blockchain_plugin_coinmarketcap_get_fiat_currencies(),
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
				'label' => __( 'Crypto Table Styles', 'blockchain' ),
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
		$fiat     = $settings['fiat'];
		$limit    = $settings['limit'];
		$start    = $settings['start']['size'];

		echo do_shortcode( sprintf( '[crypto-table limit="%s" start="%s" fiat="%s"]',
			$limit,
			$start,
			$fiat
		) );
	}
}
