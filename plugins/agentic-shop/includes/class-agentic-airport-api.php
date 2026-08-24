<?php
/**
 * Aviationstack API client.
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
	private const BASE_URL = 'https://api.aviationstack.com/v1/';

	private string $api_key;

	/**
	 * @param string $api_key Aviationstack API access key.
	 */
	public function __construct( string $api_key ) {
		$this->api_key = trim( $api_key );
	}

	/**
	 * @return array<int, mixed>|WP_Error
	 */
	public function get_flight( string $flight_iata ) {
		return $this->request(
			'flights',
			array(
				'flight_iata' => strtoupper( $flight_iata ),
				'limit'       => '25',
			)
		);
	}

	/**
	 * @return array<int, mixed>|WP_Error
	 */
	public function get_routes( string $airline_iata ) {
		return $this->request(
			'routes',
			array(
				'airline_iata' => strtoupper( $airline_iata ),
				'limit'        => '25',
			)
		);
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
			array_merge( array( 'access_key' => $this->api_key ), $parameters ),
			self::BASE_URL . $endpoint
		);

		try {
			$response = wp_safe_remote_get(
				$url,
				array(
					'headers' => array( 'Accept' => 'application/json' ),
					'timeout' => 5,
				)
			);
		} catch ( Throwable $error ) {
			return new WP_Error( 'agentic_airport_request_failed', __( 'The flight service could not be reached.', 'agentic-shop' ) );
		}

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'agentic_airport_request_failed', __( 'The flight service could not be reached.', 'agentic-shop' ) );
		}

		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! is_array( $decoded ) ) {
			return new WP_Error( 'agentic_airport_invalid_response', __( 'The flight service returned invalid data.', 'agentic-shop' ) );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$error_type = isset( $decoded['error']['type'] ) ? (string) $decoded['error']['type'] : '';
			if ( 'function_access_restricted' === $error_type ) {
				return new WP_Error( 'agentic_airport_plan_restricted', __( 'This lookup is not available on your Aviationstack plan.', 'agentic-shop' ) );
			}
			if ( 'invalid_access_key' === $error_type || 'missing_access_key' === $error_type ) {
				return new WP_Error( 'agentic_airport_invalid_key', __( 'The Aviationstack API key is invalid.', 'agentic-shop' ) );
			}
			return new WP_Error( 'agentic_airport_bad_response', __( 'The flight service returned an error.', 'agentic-shop' ) );
		}

		if ( ! isset( $decoded['data'] ) || ! is_array( $decoded['data'] ) ) {
			return new WP_Error( 'agentic_airport_invalid_response', __( 'The flight service returned invalid data.', 'agentic-shop' ) );
		}

		return $decoded['data'];
	}
}
