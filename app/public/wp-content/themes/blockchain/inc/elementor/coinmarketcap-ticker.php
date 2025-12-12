<?php
namespace Elementor;

class Widget_Coinmarketcap_Ticker extends Widget_Base {

	public function get_name() {
		return 'coinmarketcap_ticker';
	}

	public function get_title() {
		return __( 'Coinmarketcap Ticker', 'blockchain' );
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
				'label' => __( 'Coinmarketcap Ticker', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Displays a ticker of the top cryptocurrencies.', 'blockchain' ),
				'content_classes' => 'ci-description',
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => __( 'Number of top cryptocurrencies:', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '3',
				'options' => [
					'0'   => esc_html__( 'All', 'blockchain' ),
					'3'   => '3',
					'5'   => '5',
					'10'  => '10',
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
			'interval',
			[
				'label'   => __( 'Show change percentage for the last:', 'blockchain' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '1h',
				'options' => [
					'1h'  => esc_html__( '1 Hour', 'blockchain' ),
					'24h' => esc_html__( '1 Day', 'blockchain' ),
					'7d'  => esc_html__( '1 Week', 'blockchain' ),
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
				'label' => __( 'Ticker Styles', 'blockchain' ),
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
		$interval = $settings['interval'];
		$start    = $settings['start']['size'];

		$params = array(
			'limit'   => $limit,
			'start'   => $start,
			'convert' => $fiat,
		);

		$response = blockchain_plugin_coinmarketcap_get_data( 'ticker', $params );

		if ( false === $response['error'] ) {
			$data = $response['data'];
			if ( is_array( $data ) ) {
				?>
				<div
					class="widget-crypto widget-crypto-multi loaded"
					data-currency="<?php echo esc_attr( $fiat ); ?>"
					data-limit="<?php echo esc_attr( $limit ); ?>"
					data-change-period="<?php echo esc_attr( $interval ); ?>"
					data-start="<?php echo esc_attr( $start ); ?>"
				>
				<div class="widget-crypto-placeholder">
					<div class="data-item-wrap data-item-list">
						<?php
							foreach ( $data as $coin ) {
								$price_field  = 'price_' . strtolower( $fiat );
								$change_field = 'percent_change_' . strtolower( $interval );
								$change_class = '';
								if ( floatval( $coin->$change_field ) < 0 ) {
									$change_class = 'text-danger symbol-desc';
								} elseif ( floatval( $coin->$change_field ) > 0 ) {
									$change_class = 'text-success symbol-asc';
								}
								?>
								<div class="data-item-row">
									<div class="data-item">
										<span class="data-item-eyebrow js-symbol-name"><?php echo esc_html( $coin->name ); ?></span>
										<span class="data-item-value js-symbol"><?php echo esc_html( $coin->symbol ); ?></span>
									</div>

									<div class="data-item data-item-right">
										<span class="data-item-eyebrow">
											<span class="js-symbol-change-interval"><?php echo esc_html( blockchain_get_change_interval_short_text( $interval ) ); ?></span>
											<span class="js-symbol-change <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $coin->$change_field ) ) ); ?></span>
										</span>
										<span class="data-item-value js-symbol-price"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $coin->$price_field, $fiat ) ); ?></span>
									</div>
								</div>
								<?php
							}
						?>
					</div>
				</div>
			</div>
			<?php
			}
		}
	}
}
