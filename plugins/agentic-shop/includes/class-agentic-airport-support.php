<?php
/**
 * Airport support settings and front-end shortcode.
 *
 * @package AgenticShop
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the airport support feature.
 */
final class Agentic_Airport_Support {
	private const OPTION_KEY = 'agentic_airport_api_key';

	public static function init(): void {
		add_action( 'admin_menu', array( self::class, 'add_settings_page' ) );
		add_action( 'admin_init', array( self::class, 'register_settings' ) );
		add_shortcode( 'agentic_airport_support', array( self::class, 'render_shortcode' ) );
	}

	public static function add_settings_page(): void {
		add_options_page(
			__( 'Airport Support', 'agentic-shop' ),
			__( 'Airport Support', 'agentic-shop' ),
			'manage_options',
			'agentic-airport-support',
			array( self::class, 'render_settings_page' )
		);
	}

	public static function register_settings(): void {
		register_setting(
			'agentic_airport_settings',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( self::class, 'sanitize_api_key' ),
				'type'              => 'string',
			)
		);
	}

	public static function sanitize_api_key( $api_key ): string {
		if ( ! is_string( $api_key ) ) {
			return '';
		}
		return preg_replace( '/[^A-Za-z0-9_-]/', '', $api_key ) ?? '';
	}

	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to manage these settings.', 'agentic-shop' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Airport Support', 'agentic-shop' ); ?></h1>
			<form action="options.php" method="post">
				<?php settings_fields( 'agentic_airport_settings' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="agentic-airport-api-key"><?php echo esc_html__( 'Aviationstack API access key', 'agentic-shop' ); ?></label></th>
						<td><input class="regular-text" id="agentic-airport-api-key" name="<?php echo esc_attr( self::OPTION_KEY ); ?>" type="password" value="<?php echo esc_attr( (string) get_option( self::OPTION_KEY, '' ) ); ?>" autocomplete="new-password"></td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<p class="description"><?php echo esc_html__( 'Real-time flights are available on the free plan; airline routes require a paid plan.', 'agentic-shop' ); ?></p>
			<p>
				<a class="button button-secondary" href="https://aviationstack.com/dashboard" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'View API usage', 'agentic-shop' ); ?></a>
				<a class="button button-secondary" href="https://docs.apilayer.com/aviationstack/docs/api-documentation" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'API documentation', 'agentic-shop' ); ?></a>
			</p>
		</div>
		<?php
	}

	public static function render_shortcode(): string {
		$mode   = '';
		$query  = '';
		$result = null;

		if ( isset( $_POST['agentic_airport_action'], $_POST['agentic_airport_nonce'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( (string) $_POST['agentic_airport_nonce'] ) );
			if ( wp_verify_nonce( $nonce, 'agentic_airport_lookup' ) ) {
				$mode  = sanitize_key( wp_unslash( (string) $_POST['agentic_airport_action'] ) );
				$query = sanitize_text_field( wp_unslash( (string) ( $_POST['agentic_airport_query'] ?? '' ) ) );

				$allowed_actions = array( 'flight', 'route' );
				if ( ! in_array( $mode, $allowed_actions, true ) ) {
					$result = new WP_Error( 'agentic_airport_invalid_action', __( 'Invalid search type.', 'agentic-shop' ) );
				} elseif ( ! preg_match( '/^[A-Za-z0-9]{2,8}$/', $query ) ) {
					$result = new WP_Error( 'agentic_airport_invalid_query', __( 'Enter a valid flight or airline IATA code.', 'agentic-shop' ) );
				} else {
					$client_ip = sanitize_text_field( wp_unslash( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) ) );
					$cache_key = 'agentic_aviationstack_' . $mode . '_' . strtolower( $query );
					$rate_key  = 'agentic_airport_rate_' . hash( 'sha256', $client_ip );
					$cached    = get_transient( $cache_key );
					if ( false !== $cached ) {
						$result = $cached;
					} elseif ( false !== get_transient( $rate_key ) ) {
						$result = new WP_Error( 'agentic_airport_rate_limited', __( 'Please wait before trying another search.', 'agentic-shop' ) );
					} else {
						// ponytail: per-IP transient is a soft limit; move this to the edge under sustained traffic.
						set_transient( $rate_key, 1, 6 );
						$api    = new Agentic_Airport_API( (string) get_option( self::OPTION_KEY, '' ) );
						$result = 'route' === $mode ? $api->get_routes( $query ) : $api->get_flight( $query );
						set_transient( $cache_key, $result, is_wp_error( $result ) ? MINUTE_IN_SECONDS : 5 * MINUTE_IN_SECONDS );
					}
				}
			}
		}

		ob_start();
		?>
		<div class="agentic-airport-support">
			<h2><?php echo esc_html__( 'Flight status and airline routes', 'agentic-shop' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'agentic_airport_lookup', 'agentic_airport_nonce' ); ?>
				<label for="agentic-airport-action"><?php echo esc_html__( 'Search type', 'agentic-shop' ); ?></label>
				<select id="agentic-airport-action" name="agentic_airport_action">
					<option value="flight" <?php selected( $mode, 'flight' ); ?>><?php echo esc_html__( 'Flight status', 'agentic-shop' ); ?></option>
					<option value="route" <?php selected( $mode, 'route' ); ?>><?php echo esc_html__( 'Airline routes', 'agentic-shop' ); ?></option>
				</select>
				<label for="agentic-airport-query"><?php echo esc_html__( 'Flight or airline IATA code', 'agentic-shop' ); ?></label>
				<input id="agentic-airport-query" name="agentic_airport_query" type="text" value="<?php echo esc_attr( $query ); ?>" required maxlength="8" pattern="[A-Za-z0-9]{2,8}">
				<button type="submit"><?php echo esc_html__( 'Check', 'agentic-shop' ); ?></button>
			</form>
			<?php self::render_results( $result, $mode ); ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param array<int, mixed>|WP_Error|null $result API result.
	 */
	private static function render_results( $result, string $mode ): void {
		if ( null === $result ) {
			return;
		}
		if ( is_wp_error( $result ) ) {
			echo '<p role="alert">' . esc_html( $result->get_error_message() ) . '</p>';
			return;
		}
		if ( array() === $result ) {
			echo '<p>' . esc_html__( 'No matching information was found.', 'agentic-shop' ) . '</p>';
			return;
		}

		echo '<div aria-live="polite"><h3>' . esc_html( 'route' === $mode ? __( 'Airline routes', 'agentic-shop' ) : __( 'Flight status', 'agentic-shop' ) ) . '</h3>';
		foreach ( array_slice( $result, 0, 25 ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			if ( 'route' === $mode ) {
				$departure = isset( $item['departure']['iata'] ) ? (string) $item['departure']['iata'] : '';
				$arrival   = isset( $item['arrival']['iata'] ) ? (string) $item['arrival']['iata'] : '';
				echo '<p><strong>' . esc_html( $departure ) . '</strong> &rarr; <strong>' . esc_html( $arrival ) . '</strong></p>';
			} else {
				$flight = isset( $item['flight']['iata'] ) ? (string) $item['flight']['iata'] : '';
				$status = isset( $item['flight_status'] ) ? (string) $item['flight_status'] : __( 'Unknown', 'agentic-shop' );
				echo '<p><strong>' . esc_html( $flight ) . ':</strong> ' . esc_html( $status ) . '</p>';
			}
		}
		echo '</div>';
	}
}
