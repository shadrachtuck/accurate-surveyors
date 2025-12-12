<?php
namespace Elementor;

class Widget_Coinmarketcap_Single extends Widget_Base {

	public function get_name() {
		return 'coinmarketcap_single';
	}

	public function get_title() {
		return __( 'Single Crypto Info', 'blockchain' );
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
				'label' => __( 'Single Crypto Info', 'blockchain' ),
			]
		);

		$this->add_control(
			'html_msg',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => __( 'Displays information about a specific cryptocurrency.', 'blockchain' ),
				'content_classes' => 'ci-description',
			]
		);

		$this->add_control(
			'crypto_id',
			[
				'label'       => __( 'Currency ID (e.g. bitcoin, ethereum, bitcoin-cash):', 'blockchain' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'bitcoin',
				'placeholder' => __( 'bitcoin', 'blockchain' ),
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
				'label' => __( 'Single Crypto Info Styles', 'blockchain' ),
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
		$currency = $settings['crypto_id'];

		$params = array(
			'convert' => $fiat,
		);

		$response = blockchain_plugin_coinmarketcap_get_data( "ticker/{$currency}", $params );

		if ( false === $response['error'] ) {
			$data = $response['data'];
			if ( is_array( $data ) ) {
				foreach ( $data as $coin ) {
					$price_field = 'price_' . strtolower( $fiat );
					?>
					<div
						class="widget-crypto widget-crypto-single loaded"
						data-currency="<?php echo esc_attr( $fiat ); ?>"
						data-symbol-id="<?php echo esc_attr( $currency ); ?>"
					>
						<div class="widget-crypto-placeholder">
							<div class="data-item-wrap">
								<div class="data-item-row">
									<div class="data-item">
										<span class="data-item-eyebrow js-symbol-name"><?php echo esc_html( $coin->name ); ?></span>
										<span class="data-item-value js-symbol"><?php echo esc_html( $coin->symbol ); ?></span>
									</div>

									<div class="data-item data-item-right">
										<span class="data-item-eyebrow"><?php esc_html_e( 'Price', 'blockchain' ); ?></span>
										<span class="data-item-value js-symbol-price"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $coin->$price_field, $fiat ) ); ?></span>
									</div>
								</div>

								<div class="row">
									<?php $intervals = array( '1h', '24h', '7d' ); ?>
									<?php foreach ( $intervals as $interval ) : ?>
										<?php
											$change_field   = 'percent_change_' . strtolower( $interval );
											$change_class   = '';
											$velocity_class = '';
											if ( floatval( $coin->$change_field ) < 0 ) {
												$velocity_class = 'symbol-desc';
												$change_class   = 'text-danger';
											} elseif ( floatval( $coin->$change_field ) > 0 ) {
												$velocity_class = 'symbol-asc';
												$change_class   = 'text-success';
											}
										?>
										<div class="col-4">
											<div class="data-item js-velocity" data-interval="<?php echo esc_attr( $interval ); ?>">
												<span class="data-item-eyebrow <?php echo esc_attr( $velocity_class ); ?>"><?php echo esc_html( blockchain_get_change_interval_short_text( $interval ) ); ?></span>
												<span class="data-item-value <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $coin->$change_field ) ) ); ?></span>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							</div>

							<p class="data-last-updated">
								<?php esc_html_e( 'Last update:', 'blockchain' ); ?> <span class="data-last-updated-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $coin->last_updated ) ); ?></span>
							</p>
						</div>
					</div>
					<?php
				}
			}
		}
	}
}
