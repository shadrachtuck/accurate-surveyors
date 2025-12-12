<?php
namespace Elementor;

class Widget_Coinmarketcap_Global extends Widget_Base {

	public function get_name() {
		return 'coinmarketcap_global';
	}

	public function get_title() {
		return __( 'Global Crypto Info', 'blockchain' );
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
				'label' => __( 'Global Crypto Info', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Displays global cryptocurrency information.', 'blockchain' ),
				'content_classes' => 'ci-description',
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
				'label' => __( 'Global Crypto Info Styles', 'blockchain' ),
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

		$params = array(
			'convert' => $fiat,
		);

		$response = blockchain_plugin_coinmarketcap_get_data( 'global', $params );

		if ( false === $response['error'] ) {
			if ( is_object( $response['data'] ) ) {
				$data = $response['data'];

				$lfiat = strtolower( $fiat );

				$metrics = array(
					"total_market_cap_{$lfiat}"        => __( 'Market Cap', 'blockchain' ),
					"total_24h_volume_{$lfiat}"        => __( '24h Volume', 'blockchain' ),
					'bitcoin_percentage_of_market_cap' => __( 'BTC Coverage', 'blockchain' ),
					'active_currencies'                => __( 'Total Active Currencies', 'blockchain' ),
					'active_assets'                    => __( 'Active Assets', 'blockchain' ),
					'active_markets'                   => __( 'Active Markets', 'blockchain' ),
				);

				?>
				<div class="widget-crypto widget-crypto-data loaded" data-currency="<?php echo esc_attr( $fiat ); ?>"
					data-metrics="total_market_cap,total_24h_volume,bitcoin_percentage_of_market_cap,active_currencies,active_assets,active_markets">
					<div class="widget-crypto-placeholder">
						<div class="data-item-wrap">

							<div class="data-item">
								<?php $metric = "total_market_cap_{$lfiat}"; ?>
								<span class="data-item-eyebrow"><?php esc_html_e( 'Market Cap', 'blockchain' ); ?></span>
								<span class="data-item-value"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $data->$metric, $fiat ) ); ?></span>
							</div>

							<div class="data-item">
								<?php $metric = "total_24h_volume_{$lfiat}"; ?>
								<span class="data-item-eyebrow"><?php esc_html_e( '24h Volume', 'blockchain' ); ?></span>
								<span class="data-item-value"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $data->$metric, $fiat ) ); ?></span>
							</div>

							<div class="data-item">
								<?php $metric = 'bitcoin_percentage_of_market_cap'; ?>
								<span class="data-item-eyebrow"><?php esc_html_e( 'BTC Coverage', 'blockchain' ); ?></span>
								<span class="data-item-value"><?php echo esc_html( $data->$metric . '%' ); ?></span>
							</div>

							<div class="data-item">
								<?php $metric = 'active_currencies'; ?>
								<span class="data-item-eyebrow"><?php esc_html_e( 'Total Active Currencies', 'blockchain' ); ?></span>
								<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data->$metric ) ); ?></span>
							</div>

							<div class="data-item">
								<?php $metric = 'active_assets'; ?>
								<span class="data-item-eyebrow"><?php esc_html_e( 'Active Assets', 'blockchain' ); ?></span>
								<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data->$metric ) ); ?></span>
							</div>

							<div class="data-item">
								<?php $metric = 'active_markets'; ?>
								<span class="data-item-eyebrow"><?php esc_html_e( 'Active Markets', 'blockchain' ); ?></span>
								<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data->$metric ) ); ?></span>
							</div>

						</div>

						<p class="data-last-updated">
							<?php esc_html_e( 'Last update:', 'blockchain' ); ?> <span class="data-last-updated-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $data->last_updated ) ); ?></span>
						</p>
					</div>
				</div>
				<?php
			}
		}
	}
}
