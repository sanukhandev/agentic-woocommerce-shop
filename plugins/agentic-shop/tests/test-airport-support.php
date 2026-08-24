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
}
