<?php
function blockchain_plugin_coinmarketcap_get_cryptocurrency_choices() {

	$choices = array(
		'1' => __( 'Bitcoin (BTC)', 'blockchain-plugin' ),
	);

	$coins = blockchain_plugin_coinmarketcap_get_cryptocurrencies();

	if ( ! empty( $coins ) ) {
		$choices = array();
		$coins   = wp_list_sort( $coins, 'name' );

		foreach ( $coins as $coin ) {
			/* translators: %1$s is the coin's name, e.g. Bitcoin. %2$s is the coin's symbol, e.g. BTC. */
			$choices[ (string) $coin['id'] ] = sprintf( _x( '%1$s (%2$s)', 'cryptocoin label', 'blockchain-plugin' ),
				$coin['name'],
				$coin['symbol']
			);
		}
	}

	return $choices;
}

function blockchain_plugin_coinmarketcap_get_cryptocurrencies() {

	$trans_name = 'blockchain_plugin_coinmarketcap_cryptocurrencies';
	$coins      = get_transient( $trans_name );

	if ( false === $coins ) {

		$api_key  = get_theme_mod( 'title_coinmarketcap_api_key' );
		$endpoint = 'listings';

		if ( $api_key ) {
			$endpoint = 'cryptocurrency/map';
		}

		$response = blockchain_plugin_coinmarketcap_get_data( $endpoint );

		$coins = array();

		if ( ! $response['error'] && ! empty( $response['data']['data'] ) ) {
			$coins = $response['data']['data'];
		}

		if ( empty( $coins ) ) {
			// There was a problem, so don't cache anything. Fall back to the blockchain_plugin_coinmarketcap_do_api_call() transients.
			delete_transient( $trans_name );
		} else {
			set_transient( $trans_name, $coins, 24 * HOUR_IN_SECONDS );
		}
	}

	return $coins;
}

function blockchain_plugin_coinmarketcap_get_fiat_currencies() {
	return apply_filters( 'blockchain_plugin_coinmarketcap_fiat_currencies', array(
		'AUD' => esc_html_x( 'AUD', 'currency code', 'blockchain-plugin' ),
		'BRL' => esc_html_x( 'BRL', 'currency code', 'blockchain-plugin' ),
		'CAD' => esc_html_x( 'CAD', 'currency code', 'blockchain-plugin' ),
		'CHF' => esc_html_x( 'CHF', 'currency code', 'blockchain-plugin' ),
		'CLP' => esc_html_x( 'CLP', 'currency code', 'blockchain-plugin' ),
		'CNY' => esc_html_x( 'CNY', 'currency code', 'blockchain-plugin' ),
		'CZK' => esc_html_x( 'CZK', 'currency code', 'blockchain-plugin' ),
		'DKK' => esc_html_x( 'DKK', 'currency code', 'blockchain-plugin' ),
		'EUR' => esc_html_x( 'EUR', 'currency code', 'blockchain-plugin' ),
		'GBP' => esc_html_x( 'GBP', 'currency code', 'blockchain-plugin' ),
		'HKD' => esc_html_x( 'HKD', 'currency code', 'blockchain-plugin' ),
		'HUF' => esc_html_x( 'HUF', 'currency code', 'blockchain-plugin' ),
		'IDR' => esc_html_x( 'IDR', 'currency code', 'blockchain-plugin' ),
		'ILS' => esc_html_x( 'ILS', 'currency code', 'blockchain-plugin' ),
		'INR' => esc_html_x( 'INR', 'currency code', 'blockchain-plugin' ),
		'JPY' => esc_html_x( 'JPY', 'currency code', 'blockchain-plugin' ),
		'KRW' => esc_html_x( 'KRW', 'currency code', 'blockchain-plugin' ),
		'MXN' => esc_html_x( 'MXN', 'currency code', 'blockchain-plugin' ),
		'MYR' => esc_html_x( 'MYR', 'currency code', 'blockchain-plugin' ),
		'NOK' => esc_html_x( 'NOK', 'currency code', 'blockchain-plugin' ),
		'NZD' => esc_html_x( 'NZD', 'currency code', 'blockchain-plugin' ),
		'PHP' => esc_html_x( 'PHP', 'currency code', 'blockchain-plugin' ),
		'PKR' => esc_html_x( 'PKR', 'currency code', 'blockchain-plugin' ),
		'PLN' => esc_html_x( 'PLN', 'currency code', 'blockchain-plugin' ),
		'RUB' => esc_html_x( 'RUB', 'currency code', 'blockchain-plugin' ),
		'SEK' => esc_html_x( 'SEK', 'currency code', 'blockchain-plugin' ),
		'SGD' => esc_html_x( 'SGD', 'currency code', 'blockchain-plugin' ),
		'THB' => esc_html_x( 'THB', 'currency code', 'blockchain-plugin' ),
		'TRY' => esc_html_x( 'TRY', 'currency code', 'blockchain-plugin' ),
		'TWD' => esc_html_x( 'TWD', 'currency code', 'blockchain-plugin' ),
		'USD' => esc_html_x( 'USD', 'currency code', 'blockchain-plugin' ),
		'ZAR' => esc_html_x( 'ZAR', 'currency code', 'blockchain-plugin' ),
	) );
}

function blockchain_plugin_coinmarketcap_format_currency_number( $number, $currency = 'USD', $decimals = 2 ) {
	$currencies = apply_filters( 'blockchain_plugin_coinmarketcap_currencies_format', array(
		'USD' => '$%s',
		'AUD' => '$%s',
		'BRL' => 'R$%s',
		'CAD' => '$%s',
		'CHF' => 'CHF%s',
		'CLP' => '$%s',
		'CNY' => '¥%s',
		'CZK' => 'Kč%s',
		'DKK' => 'kr.%s',
		'EUR' => '€%s',
		'GBP' => '£%s',
		'HKD' => '$%s',
		'HUF' => 'Ft%s',
		'IDR' => 'Rp%s',
		'ILS' => '₪%s',
		'INR' => '₹%s',
		'JPY' => '¥%s',
		'KRW' => '₩%s',
		'MXN' => '$%s',
		'MYR' => 'RM%s',
		'NOK' => 'kr%s',
		'NZD' => '$%s',
		'PHP' => '₱%s',
		'PKR' => '₨%s',
		'PLN' => 'zł%s',
		'RUB' => '₽%s',
		'SEK' => 'kr%s',
		'SGD' => '$%s',
		'THB' => '฿%s',
		'TRY' => '₺%s',
		'TWD' => 'NT$%s',
		'ZAR' => 'R%s',
	) );

	if ( ! array_key_exists( $currency, $currencies ) ) {
		$currency = 'USD';
	}

	$value = sprintf( $currencies[ $currency ], number_format_i18n( $number, $decimals ) );
	$value = apply_filters( 'blockchain_plugin_coinmarketcap_format_currency_number', $value, $number, $currency, $decimals, $currencies );

	return $value;
}

function blockchain_plugin_sanitize_coinmarketcap_fiat_currency( $value ) {
	$choices = blockchain_plugin_coinmarketcap_get_fiat_currencies();
	if ( array_key_exists( $value, $choices ) ) {
		return $value;
	}

	return blockchain_plugin_coinmarketcap_default_fiat_currency();
}

function blockchain_plugin_coinmarketcap_default_fiat_currency() {
	return apply_filters( 'blockchain_plugin_coinmarketcap_default_fiat_currency', 'USD' );
}

function blockchain_plugin_coinmarketcap_get_data( $endpoint, $params = array() ) {

	$endpoint = trim( $endpoint );

	$params = is_array( $params ) ? $params : array();
	if ( ! empty( $params['convert'] ) && 'USD' === $params['convert'] ) {
		unset( $params['convert'] );
	}

	if ( empty( $endpoint ) ) {
		$response = array(
			'error'  => true,
			'errors' => array( 'No endpoint defined.' ),
			'data'   => new stdClass(),
		);

		return $response;
	}

	$data = blockchain_plugin_coinmarketcap_do_api_call( $endpoint, $params );

	if ( is_wp_error( $data ) ) {
		$response = array(
			'error'  => true,
			'errors' => $data->get_error_messages(),
			'data'   => new stdClass(),
		);
	} elseif ( ! empty( $data ) && isset( $data['response']['code'] ) && 200 === $data['response']['code'] ) {
		$response = array(
			'error'  => false,
			'errors' => array(),
			'data'   => json_decode( $data['body'], true ),
		);
	} else {
		$json = json_decode( $data['body'], true );
		if ( isset( $json['error'] ) ) {
			$response = array(
				'error'  => true,
				'errors' => array( $json['error'] ),
				'data'   => new stdClass(),
			);
		} elseif ( isset( $json['status']['error_message'] ) ) {
			$response = array(
				'error'  => true,
				'errors' => array( $json['status']['error_message'] ),
				'data'   => new stdClass(),
			);
		} else {
			$response = array(
				'error'  => true,
				'errors' => array( 'CoinMarketCap.com Error' ),
				'data'   => new stdClass(),
			);
		}
	}

	return $response;
}

function blockchain_plugin_coinmarketcap_do_api_call( $endpoint, $params, $bypass_cache = false ) {
	$query_hash      = blockchain_plugin_coinmarketcap_get_query_hash( $endpoint, $params );
	$trans_name      = blockchain_plugin_coinmarketcap_get_transient_name( $query_hash );
	$cache_time      = apply_filters( 'blockchain_plugin_coinmarketcap_query_cache_time', 30 * MINUTE_IN_SECONDS );
	$retry_time      = apply_filters( 'blockchain_plugin_coinmarketcap_query_retry_time', 2 * MINUTE_IN_SECONDS ); // Retry after failure.
	$request_timeout = apply_filters( 'blockchain_plugin_coinmarketcap_query_timeout', 30 );

	// This transient will never expire. Holds the last known good response for fallback.
	$noexp_name = blockchain_plugin_coinmarketcap_get_nonexpiring_transient_name( $query_hash );

	$api_key = get_theme_mod( 'title_coinmarketcap_api_key' );
	$api_url = 'https://api.coinmarketcap.com/v2/';

	if ( $api_key ) {
		$api_url = 'https://pro-api.coinmarketcap.com/v1/';
	}

	$response = get_transient( $trans_name );
	if ( false === $response || $bypass_cache ) {

		$url = $api_url . $endpoint;

		// The public API needs a trailing forward slash, but the pro needs to NOT have it.
		if ( $api_key ) {
			$url = untrailingslashit( $url );
		} else {
			$url = trailingslashit( $url );
		}

		$url = add_query_arg( $params, $url );
		$url = apply_filters( 'blockchain_plugin_coinmarketcap_request_url', $url, $endpoint, $params, $api_url );

		$request_params = array(
			'timeout' => $request_timeout,
		);

		if ( $api_key ) {
			$request_params['headers'] = array(
				'X-CMC_PRO_API_KEY' => $api_key,
			);
		}

		$response = wp_safe_remote_get( $url, $request_params );

		if ( ! $bypass_cache ) {
			$noexp = (array) get_transient( $noexp_name );

			if ( ! is_wp_error( $response ) && ! empty( $response ) && isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
				$json = json_decode( $response['body'], true );

				if ( ! is_null( $json ) /*&& ! empty( $json['weather'] ) && ! empty( $json['main'] )*/ ) {
					$noexp['last_good_response']  = $response;
					$noexp['last_good_timestamp'] = current_time( 'timestamp', true );
				} else {
					$cache_time = $retry_time;
				}
			}

			if ( is_wp_error( $response ) ) {
				$noexp['last_fail_message']   = $response->get_error_messages();
				$noexp['last_fail_timestamp'] = current_time( 'timestamp', true );

				$cache_time = $retry_time;

				if ( ! empty( $noexp['last_good_response'] ) ) {
					$response = $noexp['last_good_response'];
				}
			}

			// Cache indefinitely, both for a fallback to $trans_name, as well as debugging.
			set_transient( $noexp_name, $noexp, 0 );

			set_transient( $trans_name, $response, $cache_time );
		}
	}

	return $response;
}

function blockchain_plugin_coinmarketcap_get_query_hash( $endpoint, $params ) {
	$hash = md5( $endpoint . serialize( $params ) );

	return apply_filters( 'blockchain_plugin_coinmarketcap_query_hash', $hash, $endpoint, $params );
}

function blockchain_plugin_coinmarketcap_get_transient_name( $query_hash ) {
	$base_name = 'blockchain_plugin_coinmarketcap_%s';
	$name      = sprintf( $base_name, $query_hash );

	return apply_filters( 'blockchain_plugin_coinmarketcap_transient_name', $name, $base_name, $query_hash );
}

function blockchain_plugin_coinmarketcap_get_nonexpiring_transient_name( $query_hash ) {
	$base_name = 'blockchain_plugin_coinmarketcap_noexp_%s';
	$name      = sprintf( $base_name, $query_hash );

	return apply_filters( 'blockchain_plugin_coinmarketcap_nonexpiring_transient_name', $name, $base_name, $query_hash );
}

function blockchain_plugin_convert_gmt_to_local_timestamp( $gmt_timestamp ) {
	$local = get_date_from_gmt( date( 'Y-m-d H:i:s', $gmt_timestamp ) );
	$local = strtotime( $local );

	return $local;
}
