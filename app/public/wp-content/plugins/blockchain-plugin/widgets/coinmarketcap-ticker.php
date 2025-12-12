<?php
if ( ! class_exists( 'CI_Widget_Coinmarketcap_Ticker' ) ) :
	class CI_Widget_Coinmarketcap_Ticker extends WP_Widget {

		protected $defaults = array(
			'title'    => '',
			'limit'    => 3,
			'start'    => 0,
			'fiat'     => 'USD',
			'interval' => '1h',
		);

		function __construct() {
			$widget_ops  = array( 'description' => esc_html__( 'Displays a ticker of the top cryptocurrencies.', 'blockchain-plugin' ) );
			$control_ops = array();
			parent::__construct( 'ci-coinmarketcap-ticker', esc_html__( 'Theme - Top Crypto Ticker', 'blockchain-plugin' ), $widget_ops, $control_ops );

		}


		function widget( $args, $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$id            = isset( $args['id'] ) ? $args['id'] : '';
			$before_widget = $args['before_widget'];
			$after_widget  = $args['after_widget'];

			$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			$limit = $instance['limit'];
			// API v1 used 0-based start. API v2 uses 1-based start. For back-compat, we retain 0-based options but add 1 automatically.
			$start    = intval( $instance['start'] ) + 1;
			$fiat     = $instance['fiat'];
			$interval = $instance['interval'];

			echo $before_widget;

			if ( $title ) {
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}

			$params = array(
				'limit'   => $limit,
				'start'   => $start,
				'convert' => $fiat,
			);

			$api_key  = get_theme_mod( 'title_coinmarketcap_api_key' );
			$endpoint = 'ticker';

			if ( $api_key ) {
				$endpoint = 'cryptocurrency/listings/latest';
			}

			$response = blockchain_plugin_coinmarketcap_get_data( $endpoint, $params );

			$last_update = false;

			$p = $params;
			unset( $p['convert'] );
			$query_hash = blockchain_plugin_coinmarketcap_get_query_hash( 'ticker', $p );
			$noexp_name = blockchain_plugin_coinmarketcap_get_nonexpiring_transient_name( $query_hash );
			$noexp      = (array) get_transient( $noexp_name );
			if ( isset( $noexp['last_good_timestamp'] ) ) {
				$last_update = $noexp['last_good_timestamp'];
				$last_update = blockchain_plugin_convert_gmt_to_local_timestamp( $last_update );
			}
			unset( $p );

			if ( false === $response['error'] ) {
				$response_data = $response['data'];
				if ( ! empty( $response_data['data'] ) && is_array( $response_data['data'] ) ) {

					if ( $api_key ) {
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
										foreach ( $response_data['data'] as $coin ) {
											$price  = $coin['quote'][ $fiat ]['price'];
											$change = $coin['quote'][ $fiat ][ 'percent_change_' . strtolower( $interval ) ];

											$change_class = '';
											if ( floatval( $change ) < 0 ) {
												$change_class = 'text-danger symbol-desc';
											} elseif ( floatval( $change ) > 0 ) {
												$change_class = 'text-success symbol-asc';
											}
											?>
											<div class="data-item-row">
												<div class="data-item">
													<span class="data-item-eyebrow js-symbol-name"><?php echo esc_html( $coin['name'] ); ?></span>
													<span class="data-item-value js-symbol"><?php echo esc_html( $coin['symbol'] ); ?></span>
												</div>

												<div class="data-item data-item-right">
													<span class="data-item-eyebrow">
														<span class="js-symbol-change-interval"><?php echo esc_html( $this->get_change_interval_short_text( $interval ) ); ?></span>
														<span class="js-symbol-change <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $change ) ) ); ?></span>
													</span>
													<span class="data-item-value js-symbol-price"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $price, $fiat ) ); ?></span>
												</div>
											</div>
											<?php
										}
									?>
								</div>

								<?php if ( ! empty( $last_update ) ) : ?>
									<p class="data-last-updated">
										<?php esc_html_e( 'Last update:', 'blockchain-plugin' ); ?> <span class="data-last-updated-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $last_update ) ); ?></span>
									</p>
								<?php endif; ?>

							</div>
						</div>
						<?php
					} else {
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
										foreach ( $response_data['data'] as $coin ) {
											$price  = $coin['quotes'][ $fiat ]['price'];
											$change = $coin['quotes'][ $fiat ][ 'percent_change_' . strtolower( $interval ) ];

											$change_class = '';
											if ( floatval( $change ) < 0 ) {
												$change_class = 'text-danger symbol-desc';
											} elseif ( floatval( $change ) > 0 ) {
												$change_class = 'text-success symbol-asc';
											}
											?>
											<div class="data-item-row">
												<div class="data-item">
													<span class="data-item-eyebrow js-symbol-name"><?php echo esc_html( $coin['name'] ); ?></span>
													<span class="data-item-value js-symbol"><?php echo esc_html( $coin['symbol'] ); ?></span>
												</div>

												<div class="data-item data-item-right">
													<span class="data-item-eyebrow">
														<span class="js-symbol-change-interval"><?php echo esc_html( $this->get_change_interval_short_text( $interval ) ); ?></span>
														<span class="js-symbol-change <?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $change ) ) ); ?></span>
													</span>
													<span class="data-item-value js-symbol-price"><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $price, $fiat ) ); ?></span>
												</div>
											</div>
											<?php
										}
									?>
								</div>

								<?php if ( ! empty( $last_update ) ) : ?>
									<p class="data-last-updated">
										<?php esc_html_e( 'Last update:', 'blockchain-plugin' ); ?> <span class="data-last-updated-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $last_update ) ); ?></span>
									</p>
								<?php endif; ?>

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

			$instance['title']    = sanitize_text_field( $new_instance['title'] );
			$instance['limit']    = absint( $new_instance['limit'] );
			$instance['start']    = absint( $new_instance['start'] );
			$instance['fiat']     = blockchain_plugin_sanitize_coinmarketcap_fiat_currency( $new_instance['fiat'] );
			$instance['interval'] = $this->sanitize_change_interval( $new_instance['interval'] );

			return $instance;
		} // save

		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, $this->defaults );

			$title    = $instance['title'];
			$limit    = $instance['limit'];
			$start    = $instance['start'];
			$fiat     = $instance['fiat'];
			$interval = $instance['interval'];
			?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'blockchain-plugin' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of top cryptocurrencies:', 'blockchain-plugin' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" class="widefat">
				<?php foreach ( $this->get_limit_options() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $limit, $key ); ?>>
						<?php echo esc_html( $value ); ?>
					</option>
				<?php endforeach; ?>
			</select></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'start' ) ); ?>"><?php esc_html_e( 'Number of top currencies to skip (0 for none):', 'blockchain-plugin' ); ?></label><input id="<?php echo esc_attr( $this->get_field_id( 'start' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'start' ) ); ?>" type="number" value="<?php echo esc_attr( $start ); ?>" class="widefat" /></p>

			<p><label for="<?php echo esc_attr( $this->get_field_id( 'interval' ) ); ?>"><?php esc_html_e( 'Show change percentage for the last:', 'blockchain-plugin' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'interval' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'interval' ) ); ?>" class="widefat">
				<?php foreach ( $this->get_change_interval_options() as $key => $value ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $interval, $key ); ?>>
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

		protected function get_limit_options() {
			return array(
				'0'   => esc_html__( 'All', 'blockchain-plugin' ),
				'3'   => '3',
				'5'   => '5',
				'10'  => '10',
				'25'  => '25',
				'50'  => '50',
				'100' => '100',
			);
		}

		protected function get_change_interval_options() {
			return array(
				'1h'  => esc_html__( '1 Hour', 'blockchain-plugin' ),
				'24h' => esc_html__( '1 Day', 'blockchain-plugin' ),
				'7d'  => esc_html__( '1 Week', 'blockchain-plugin' ),
			);
		}

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

		protected function sanitize_change_interval( $value ) {
			$choices = $this->get_change_interval_options();
			if ( array_key_exists( $value, $choices ) ) {
				return $value;
			}

			return $this->defaults['interval'];
		}
	}

endif;
