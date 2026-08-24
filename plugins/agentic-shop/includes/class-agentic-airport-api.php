<?php
/**
 * Aviation Edge API client.
 *
 * @package AgenticShop
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch flight and route data without exposing the API key to browsers.
 */
final class Agentic_Airport_API {
	private const BASE_URL = 'https://aviation-edge.com/v2/public/';

	private string $api_key;

	/**
	 * @param string $api_key Aviation Edge API key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = trim( $api_key );
	}

	/**
	 * @return array<int, mixed>|WP_Error
	 */
	public function get_flight( string $flight_iata ) {
		return $this->request( 'flights', array( 'flightIata' => strtoupper( $flight_iata ) ) );
	}

	/**
	 * @return array<int, mixed>|WP_Error
	 */
	public function get_routes( string $airline_iata ) {
		return $this->request( 'routes', array( 'airlineIata' => strtoupper( $airline_iata ) ) );
	}

	/**
	 * @param array<string, string> $parameters Query parameters.
	 * @return array<int, mixed>|WP_Error
	 */
	private function request( string $endpoint, array $parameters ) {
		if ( '' === $this->api_key ) {
			return new WP_Error( 'agentic_airport_missing_key', __( 'Flight information is temporarily unavailable.', 'agentic-shop' ) );
		}

		$url = add_query_arg(
			array_merge( array( 'key' => $this->api_key ), $parameters ),
			self::BASE_URL . $endpoint
		);

		try {
			$response = wp_safe_remote_get(
				$url,
				array(
					'headers' => array( 'Accept' => 'application/json' ),
					'timeout' => 15,
				)
			);
		} catch ( Throwable $error ) {
			return new WP_Error( 'agentic_airport_request_failed', __( 'The flight service could not be reached.', 'agentic-shop' ) );
		}

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'agentic_airport_request_failed', __( 'The flight service could not be reached.', 'agentic-shop' ) );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( 'agentic_airport_bad_response', __( 'The flight service returned an error.', 'agentic-shop' ) );
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			return new WP_Error( 'agentic_airport_invalid_response', __( 'The flight service returned invalid data.', 'agentic-shop' ) );
		}

		return $decoded;
	}
}
