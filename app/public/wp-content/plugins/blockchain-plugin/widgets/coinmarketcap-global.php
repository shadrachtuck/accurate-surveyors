<?php
if ( ! class_exists( 'CI_Widget_Coinmarketcap_Global' ) ) :
	class CI_Widget_Coinmarketcap_Global extends WP_Widget {

		protected $defaults = array(
			'title' => '',
			'fiat'  => 'USD',
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Displays global cryptocurrency information.', 'blockchain-plugin' ) );
			$control_ops = array();
			parent::__construct( 'ci-coinmarketcap-global', esc_html__( 'Theme - Global Crypto Info', 'blockchain-plugin' ), $widget_ops, $control_ops );
		}

		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$fiat  = $instance['fiat'];

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}

			$params = array(
				'convert' => $fiat,
			);

			$api_key  = get_theme_mod( 'title_coinmarketcap_api_key' );
			$endpoint = 'global';

			if ( $api_key ) {
				$endpoint = 'global-metrics/quotes/latest';
			}

			$response = blockchain_plugin_coinmarketcap_get_data( $endpoint, $params );

			if ( false === $response['error'] ) {
				if ( is_array( $response['data']['data'] ) ) {
					$data = $response['data']['data'];

					if ( $api_key ) {
						?>
						<div class="widget-crypto widget-crypto-data loaded" data-currency="<?php echo esc_attr( $fiat ); ?>"
							data-metrics="total_market_cap,total_volume_24h,btc_dominance,active_cryptocurrencies,active_market_pairs">
							<div class="widget-crypto-placeholder">
								<div class="data-item-wrap">

									<div class="data-item">
										<?php $metric = 'total_market_cap'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'Market Cap', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $data['quote'][ $fiat ][ $metric ], $fiat, 0 ) ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'total_volume_24h'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( '24h Volume', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $data['quote'][ $fiat ][ $metric ], $fiat, 0 ) ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'btc_dominance'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'BTC Coverage', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( $data[ $metric ] . '%' ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'active_cryptocurrencies'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'Total Active Currencies', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data[ $metric ] ) ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'active_market_pairs'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'Active Markets', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data[ $metric ] ) ); ?></span>
									</div>

								</div>

								<p class="data-last-updated">
									<?php esc_html_e( 'Last update:', 'blockchain-plugin' ); ?>
									<span class="data-last-updated-value">
										<?php
											$timestamp = strtotime( $data['last_updated'] );
											echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), blockchain_plugin_convert_gmt_to_local_timestamp( $timestamp ) ) );
										?>
									</span>
								</p>
							</div>
						</div>
						<?php
					} else {
						?>
						<div class="widget-crypto widget-crypto-data loaded" data-currency="<?php echo esc_attr( $fiat ); ?>"
							data-metrics="total_market_cap,total_volume_24h,bitcoin_percentage_of_market_cap,active_cryptocurrencies,active_markets">
							<div class="widget-crypto-placeholder">
								<div class="data-item-wrap">

									<div class="data-item">
										<?php $metric = 'total_market_cap'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'Market Cap', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $data['quotes'][ $fiat ][ $metric ], $fiat, 0 ) ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'total_volume_24h'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( '24h Volume', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $data['quotes'][ $fiat ][ $metric ], $fiat, 0 ) ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'bitcoin_percentage_of_market_cap'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'BTC Coverage', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( $data[ $metric ] . '%' ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'active_cryptocurrencies'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'Total Active Currencies', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data[ $metric ] ) ); ?></span>
									</div>

									<div class="data-item">
										<?php $metric = 'active_markets'; ?>
										<span class="data-item-eyebrow"><?php esc_html_e( 'Active Markets', 'blockchain-plugin' ); ?></span>
										<span class="data-item-value"><?php echo esc_html( number_format_i18n( $data[ $metric ] ) ); ?></span>
									</div>

								</div>

								<p class="data-last-updated">
									<?php esc_html_e( 'Last update:', 'blockchain-plugin' ); ?> <span class="data-last-updated-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), blockchain_plugin_convert_gmt_to_local_timestamp( $data['last_updated'] ) ) ); ?></span>
								</p>
							</div>
						</div>
						<?php
					}

				}
			}

			echo $after_widget;

		}

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;

			$instance['title'] = sanitize_text_field( $new_instance['title'] );
			$instance['fiat']  = blockchain_plugin_sanitize_coinmarketcap_fiat_currency( $new_instance['fiat'] );

			return $instance;
		} // save

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title = $instance['title'];
			$fiat  = $instance['fiat'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain-plugin' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'fiat' ) ); ?>"><?php esc_html_e( 'Show values expressed in:', 'blockchain-plugin' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'fiat' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'fiat' ) ); ?>" class="widefat">
				<?php foreach ( blockchain_plugin_coinmarketcap_get_fiat_currencies() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $fiat, $key ); ?>>
						<?php echo esc_html( $value ); ?>
					</option>
				<?php endforeach; ?>
			</select></p>
			<?php

		} // form

		protected function get_change_interval_short_text( $interval ) {
			$intervals = array(
				'1h'  => esc_html__( '1h', 'blockchain-plugin' ),
				'24h' => esc_html__( '24h', 'blockchain-plugin' ),
				'7d'  => esc_html__( '7d', 'blockchain-plugin' ),
			);

			if ( ! array_key_exists( $interval, $intervals ) ) {
				$interval = $this->defaults['interval'];
			}

			return $intervals[ $interval ];
		}

	}

endif;
