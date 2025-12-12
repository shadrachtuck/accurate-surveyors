<?php
if ( ! class_exists( 'CI_Widget_Coinmarketcap_Single' ) ) :
	class CI_Widget_Coinmarketcap_Single extends WP_Widget {

		protected $defaults = array(
			'title'       => '',
			'currency_id' => '1',
			'fiat'        => 'USD',
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Displays information about a specific cryptocurrency.', 'blockchain-plugin' ) );
			$control_ops = array();
			parent::__construct( 'ci-coinmarketcap-single', esc_html__( 'Theme - Single Crypto Symbol', 'blockchain-plugin' ), $widget_ops, $control_ops );

			// These are needed for compatibility with widgets set up for the v1 of the CoinMarketCap API (Blockchain <= 1.2)
			add_filter( 'widget_display_callback', array( $this, '_convert_coin_slug_to_id' ), 10, 2 );
			add_filter( 'widget_form_callback', array( $this, '_convert_coin_slug_to_id' ), 10, 2 );
		}


		// This is needed for compatibility with widgets set up for the v1 of the CoinMarketCap API (Blockchain <= 1.2)
		function _convert_coin_slug_to_id( $instance, $_this ) {
			$coins = blockchain_plugin_coinmarketcap_get_cryptocurrencies();
			$class = get_class( $this );

			if ( get_class( $_this ) === $class && ! isset( $instance['currency_id'] ) && isset( $instance['currency'] ) ) {
				$instance['currency_id'] = '';

				$api_key = get_theme_mod( 'title_coinmarketcap_api_key' );

				if ( $api_key ) {
					$result = wp_list_filter( $coins, array( 'slug' => $instance['currency'] ) );
				} else {
					$result = wp_list_filter( $coins, array( 'website_slug' => $instance['currency'] ) );
				}


				if ( ! empty( $result ) ) {
					foreach ( $result as $coin ) {
						$instance['currency_id'] = $coin['id'];
						break;
					}
				}

				unset( $instance['currency'] );
			}

			return $instance;
		}


		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title       = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$currency_id = $instance['currency_id'];
			$fiat        = $instance['fiat'];

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}

			$params = array(
				'convert' => $fiat,
			);

			$api_key  = get_theme_mod( 'title_coinmarketcap_api_key' );
			$endpoint = "ticker/{$currency_id}";

			if ( $api_key ) {
				$endpoint     = 'cryptocurrency/quotes/latest';
				$params['id'] = $currency_id;
			}

			$response = blockchain_plugin_coinmarketcap_get_data( $endpoint, $params );

			if ( false === $response['error'] ) {
				$response_data = $response['data'];
				if ( ! empty( $response_data['data'] ) && is_array( $response_data['data'] ) ) {

					$coin = $response_data['data'];

					if ( $api_key ) {
						// In the pro API, the data are wrapped into an array, so, let's get the first element.
						$coin = reset( $response_data['data'] );

						$price = $coin['quote'][ $fiat ]['price'];
						?>
						<div
							class="widget-crypto widget-crypto-single loaded"
							data-currency="<?php echo esc_attr( $fiat ); ?>"
							data-symbol-id="<?php echo esc_attr( $currency_id ); ?>"
						>
							<div class="widget-crypto-placeholder">
								<div class="data-item-wrap">
									<div class="data-item-row">
										<div class="data-item">
											<span class="data-item-eyebrow js-symbol-name"><?php echo esc_html( $coin['name'] ); ?></span>
											<span class="data-item-value js-symbol"><?php echo esc_html( $coin['symbol'] ); ?></span>
										</div>

										<div class="data-item data-item-right">
											<span class="data-item-eyebrow"><?php esc_html_e( 'Price', 'blockchain-plugin' ); ?></span>
											<span class="data-item-value js-symbol-price"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $price, $fiat ) ); ?></span>
										</div>
									</div>

									<div class="row">
										<?php $intervals = array( '1h', '24h', '7d' ); ?>
										<?php foreach ( $intervals as $interval ) : ?>
											<?php
												$change         = $coin['quote'][ $fiat ][ 'percent_change_' . strtolower( $interval ) ];
												$change_class   = '';
												$velocity_class = '';
												if ( floatval( $change ) < 0 ) {
													$velocity_class = 'symbol-desc';
													$change_class   = 'text-danger';
												} elseif ( floatval( $change ) > 0 ) {
													$velocity_class = 'symbol-asc';
													$change_class   = 'text-success';
												}
											?>
											<div class="col-4">
												<div class="data-item js-velocity" data-interval="<?php echo esc_attr( $interval ); ?>">
													<span class="data-item-eyebrow <?php echo esc_attr( $velocity_class ); ?>"><?php echo esc_html( $this->get_change_interval_short_text( $interval ) ); ?></span>
													<span class="data-item-value <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $change ) ) ); ?></span>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>

								<p class="data-last-updated">
									<?php esc_html_e( 'Last update:', 'blockchain-plugin' ); ?>
									<span class="data-last-updated-value">
										<?php
											$timestamp = strtotime( $coin['last_updated'] );
											echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), blockchain_plugin_convert_gmt_to_local_timestamp( $timestamp ) ) );
										?>
									</span>
								</p>
							</div>
						</div>
						<?php
					} else {
						$price = $coin['quotes'][ $fiat ]['price'];
						?>
						<div
							class="widget-crypto widget-crypto-single loaded"
							data-currency="<?php echo esc_attr( $fiat ); ?>"
							data-symbol-id="<?php echo esc_attr( $currency_id ); ?>"
						>
							<div class="widget-crypto-placeholder">
								<div class="data-item-wrap">
									<div class="data-item-row">
										<div class="data-item">
											<span class="data-item-eyebrow js-symbol-name"><?php echo esc_html( $coin['name'] ); ?></span>
											<span class="data-item-value js-symbol"><?php echo esc_html( $coin['symbol'] ); ?></span>
										</div>

										<div class="data-item data-item-right">
											<span class="data-item-eyebrow"><?php esc_html_e( 'Price', 'blockchain-plugin' ); ?></span>
											<span class="data-item-value js-symbol-price"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $price, $fiat ) ); ?></span>
										</div>
									</div>

									<div class="row">
										<?php $intervals = array( '1h', '24h', '7d' ); ?>
										<?php foreach ( $intervals as $interval ) : ?>
											<?php
												$change         = $coin['quotes'][ $fiat ][ 'percent_change_' . strtolower( $interval ) ];
												$change_class   = '';
												$velocity_class = '';
												if ( floatval( $change ) < 0 ) {
													$velocity_class = 'symbol-desc';
													$change_class   = 'text-danger';
												} elseif ( floatval( $change ) > 0 ) {
													$velocity_class = 'symbol-asc';
													$change_class   = 'text-success';
												}
											?>
											<div class="col-4">
												<div class="data-item js-velocity" data-interval="<?php echo esc_attr( $interval ); ?>">
													<span class="data-item-eyebrow <?php echo esc_attr( $velocity_class ); ?>"><?php echo esc_html( $this->get_change_interval_short_text( $interval ) ); ?></span>
													<span class="data-item-value <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $change ) ) ); ?></span>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>

								<p class="data-last-updated">
									<?php esc_html_e( 'Last update:', 'blockchain-plugin' ); ?> <span class="data-last-updated-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), blockchain_plugin_convert_gmt_to_local_timestamp( $coin['last_updated'] ) ) ); ?></span>
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

			$instance['title']       = sanitize_text_field( $new_instance['title'] );
			$instance['currency']    = sanitize_text_field( $new_instance['currency'] );
			$instance['currency_id'] = absint( $new_instance['currency_id'] );
			$instance['fiat']        = blockchain_plugin_sanitize_coinmarketcap_fiat_currency( $new_instance['fiat'] );

			return $instance;
		} // save

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title       = $instance['title'];
			$currency_id = $instance['currency_id'];
			$fiat        = $instance['fiat'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain-plugin' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'currency_id' ) ); ?>"><?php esc_html_e( 'Crypto Currency:', 'blockchain-plugin' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'currency_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'currency_id' ) ); ?>" class="widefat">
				<?php foreach ( blockchain_plugin_coinmarketcap_get_cryptocurrency_choices() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $currency_id, $key ); ?>>
						<?php echo esc_html( $value ); ?>
					</option>
				<?php endforeach; ?>
			</select></p>

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
