<?php
/**
 * Airport support tests.
 *
 * @package AgenticShop
 */

declare(strict_types=1);

/**
 * Tests input handling for airport support.
 */
final class Agentic_Airport_Support_Test extends WP_UnitTestCase {
	public function test_api_key_is_sanitized(): void {
		$this->assertSame( 'safe_KEY-123', Agentic_Airport_Support::sanitize_api_key( 'safe_KEY-123<script>' ) );
	}

	public function test_shortcode_renders_both_lookup_types(): void {
		$output = Agentic_Airport_Support::render_shortcode();

		$this->assertStringContainsString( 'Flight status', $output );
		$this->assertStringContainsString( 'Airline routes', $output );
		$this->assertStringContainsString( 'agentic_airport_nonce', $output );
	}

	public function test_missing_api_key_returns_an_error(): void {
		$api    = new Agentic_Airport_API( '' );
		$result = $api->get_flight( 'AA100' );

		$this->assertWPError( $result );
		$this->assertSame( 'agentic_airport_missing_key', $result->get_error_code() );
	}

	public function test_aviationstack_flight_response_is_unwrapped(): void {
		$url  = '';
		$mock = static function ( $response, $args, $request_url ) use ( &$url ): array {
			$url = $request_url;
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => '{"data":[{"flight_status":"active","flight":{"iata":"AA100"}}]}',
			);
		};

		add_filter( 'pre_http_request', $mock, 10, 3 );
		$result = ( new Agentic_Airport_API( 'test-key' ) )->get_flight( 'aa100' );
		remove_filter( 'pre_http_request', $mock );

		$this->assertStringContainsString( 'https://api.aviationstack.com/v1/flights', $url );
		$this->assertStringContainsString( 'access_key=test-key', $url );
		$this->assertStringContainsString( 'flight_iata=AA100', $url );
		$this->assertSame( 'AA100', $result[0]['flight']['iata'] );
	}

	public function test_aviationstack_plan_restriction_returns_clear_error(): void {
		$mock = static function ( $preempt, $args, $url ): array {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => '{"success":false,"error":{"type":"function_access_restricted"}}',
			);
		};

		add_filter( 'pre_http_request', $mock, 10, 3 );
		$result = ( new Agentic_Airport_API( 'test-key' ) )->get_routes( 'AA' );
		remove_filter( 'pre_http_request', $mock, 10 );

		$this->assertWPError( $result );
		$this->assertSame( 'agentic_airport_plan_restricted', $result->get_error_code() );
	}

	public function test_uncached_lookups_are_rate_limited(): void {
		$rate_key = 'agentic_airport_rate_' . hash( 'sha256', '127.0.0.1' );
		$requests = 0;
		$mock     = static function ( $preempt, $args, $url ) use ( &$requests ): array {
			++$requests;
			return array( 'response' => array( 'code' => 200 ), 'body' => '{"data":[]}' );
		};

		update_option( 'agentic_airport_api_key', 'test-key' );
		delete_transient( $rate_key );
		delete_transient( 'agentic_aviationstack_flight_aa100' );
		delete_transient( 'agentic_aviationstack_flight_bb200' );
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_POST                  = array(
			'agentic_airport_action' => 'flight',
			'agentic_airport_nonce'  => wp_create_nonce( 'agentic_airport_lookup' ),
			'agentic_airport_query'  => 'AA100',
		);
		add_filter( 'pre_http_request', $mock, 10, 3 );

		Agentic_Airport_Support::render_shortcode();
		$_POST['agentic_airport_query'] = 'BB200';
		$output                          = Agentic_Airport_Support::render_shortcode();

		remove_filter( 'pre_http_request', $mock, 10 );
		delete_transient( $rate_key );
		delete_transient( 'agentic_aviationstack_flight_aa100' );
		delete_transient( 'agentic_aviationstack_flight_bb200' );
		delete_option( 'agentic_airport_api_key' );
		$_POST = array();

		$this->assertSame( 1, $requests );
		$this->assertStringContainsString( 'Please wait before trying another search.', $output );
	}
}
