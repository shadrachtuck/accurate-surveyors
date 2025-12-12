<?php
add_shortcode( 'latest-post-type', 'blockchain_plugin_shortcode_latest_post_type' );
if ( ! function_exists( 'blockchain_plugin_shortcode_latest_post_type' ) ) :
	function blockchain_plugin_shortcode_latest_post_type( $params, $content = null, $shortcode ) {

		$params = shortcode_atts( array(
			'post_type' => 'post',
			'random'    => false,
			'count'     => 3,
			'columns'   => 3,
		), $params, $shortcode );

		$post_type = $params['post_type'];
		$random    = $params['random'];
		$count     = intval( $params['count'] );
		$columns   = intval( $params['columns'] );

		if ( 0 === $count ) {
			return '';
		}

		if ( empty( $post_type ) ) {
			$post_type = 'post';
		}

		if ( empty( $random ) || 'false' === $random || 'FALSE' === $random || '0' === $random || false === (bool) $random ) {
			$random = false;
		} else {
			true;
		}

		$col_options = blockchain_plugin_post_type_listing_get_valid_columns_options();

		if ( $columns < $col_options['min'] ) {
			$columns = $col_options['min'];
		}

		if ( $columns > $col_options['max'] ) {
			$columns = $col_options['max'];
		}

		$query_args = array(
			'post_type'           => $post_type,
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'posts_per_page'      => $count,
		);

		if ( $random ) {
			$query_args['orderby'] = 'rand';
			unset( $query_args['order'] );
		}

		$q = new WP_Query( $query_args );

		ob_start();

		if ( $q->have_posts() ) {
			?><div class="row row-items"><?php

				while ( $q->have_posts() ) {
					$q->the_post();

					?><div class="<?php echo esc_attr( blockchain_plugin_get_columns_classes( $columns ) ); ?>"><?php

					get_template_part( 'template-parts/widgets/home-item', get_post_type() );

					?></div><?php
				}
				wp_reset_postdata();

			?></div><?php
		}

		$output = ob_get_clean();

		return $output;
	}
endif;

add_shortcode( 'crypto-table', 'blockchain_plugin_shortcode_crypto_table' );
if ( ! function_exists( 'blockchain_plugin_shortcode_crypto_table' ) ) :
	function blockchain_plugin_shortcode_crypto_table( $params, $content = null, $shortcode ) {

		$params = shortcode_atts( array(
			'limit' => '15', // 0 for unlimited
			'start' => '', // Skip that many currencies, e.g. 10 = start from the 11th
			'fiat'  => 'USD',
		), $params, $shortcode );

		$limit = $params['limit'];
		// API v1 used 0-based start. API v2 uses 1-based start. For back-compat, we retain 0-based options but add 1 automatically.
		$start = intval( $params['start'] ) + 1;
		$fiat  = $params['fiat'];

		$q_params = array(
			'limit'   => $limit,
			'start'   => $start,
			'convert' => $fiat,
		);

		$response = blockchain_plugin_coinmarketcap_get_data( 'ticker', $q_params );

		ob_start();

		if ( false === $response['error'] ) {
			$response_data = $response['data'];
			if ( ! empty( $response_data['data'] ) && is_array( $response_data['data'] ) ) {
				?>
				<table
					class="table-list-crypto table-styled"
					data-currency="<?php echo esc_attr( $fiat ); ?>"
					data-limit="<?php echo esc_attr( $limit ); ?>"
				>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Rank', 'blockchain-plugin' ); ?></th>
							<th><?php esc_html_e( '(Symbol) Name', 'blockchain-plugin' ); ?></th>
							<th><?php esc_html_e( 'Market Cap', 'blockchain-plugin' ); ?></th>
							<th><?php esc_html_e( 'Price', 'blockchain-plugin' ); ?></th>
							<th><?php esc_html_e( 'Volume (24h)', 'blockchain-plugin' ); ?></th>
							<th><?php esc_html_e( 'Change (24h)', 'blockchain-plugin' ); ?></th>
						</tr>
					</thead>

					<tbody>
						<?php
							$i = 0;
							foreach ( $response_data['data'] as $coin ) {
								$i++;
								$even_odd = $i % 2 ? 'odd' : 'even';

								$price      = $coin['quotes'][ $fiat ]['price'];
								$market_cap = $coin['quotes'][ $fiat ]['market_cap'];
								$volume     = $coin['quotes'][ $fiat ]['volume_24h'];
								$change_24h = $coin['quotes'][ $fiat ]['percent_change_24h'];

								$change_class = '';
								if ( floatval( $change_24h ) < 0 ) {
									$change_class = 'text-danger symbol-desc';
								} elseif ( floatval( $change_24h ) > 0 ) {
									$change_class = 'text-success symbol-asc';
								}
								?>
								<tr role="row" class="<?php echo esc_attr( $even_odd ); ?>">
									<td><?php echo esc_html( $coin['rank'] ); ?></td>
									<td>(<strong><?php echo esc_html( $coin['symbol'] ); ?></strong>) <?php echo esc_html( $coin['name'] ); ?></td>
									<td><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $market_cap, $fiat, 0 ) ); ?></td>
									<td><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $price, $fiat ) ); ?></td>
									<td><?php echo esc_html( blockchain_plugin_coinmarketcap_format_currency_number( $volume, $fiat ) ); ?></td>
									<td data-order="<?php echo floatval( $change_24h ); ?>"><span class="<?php echo esc_attr( $change_class ); ?>"><?php echo esc_html( sprintf( '%.2f%%', abs( $change_24h ) ) ); ?></span></td>
								</tr>
								<?php
							}
						?>
					</tbody>
				</table>
			<?php
			}
		}

		$output = ob_get_clean();

		return $output;
	}
endif;
