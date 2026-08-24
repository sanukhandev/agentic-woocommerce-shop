<?php
/**
 * Builds implementation prompts from Jira task details.
 *
 * @package AgenticShop
 */

namespace AgenticShop\Services;

if ( ! defined( 'ABSPATH' ) && ! \in_array( \php_sapi_name(), array( 'cli', 'phpdbg' ), true ) ) {
	exit;
}

use InvalidArgumentException;

/**
 * Builds the prompt supplied to the implementation agent.
 */
final class PromptBuilderService {

	/**
	 * Build an implementation prompt for a Jira task.
	 *
	 * @param string $task_key   Jira task key.
	 * @param string $summary    Jira task summary.
	 * @param string $description Jira task description.
	 * @param string $branch     Git branch to use.
	 * @return string
	 *
	 * @throws InvalidArgumentException When a required value is empty.
	 */
	public function build( string $task_key, string $summary, string $description, string $branch ): string {
		$task_key   = $this->sanitize_single_line( $task_key );
		$summary    = $this->sanitize_single_line( $summary );
		$description = $this->sanitize_multiline( $description );
		$branch     = $this->sanitize_single_line( $branch );

		$required_values = array(
			'task key'   => $task_key,
			'summary'    => $summary,
			'description' => $description,
			'branch'     => $branch,
		);

		foreach ( $required_values as $name => $value ) {
			if ( '' === $value ) {
				throw new InvalidArgumentException( sprintf( 'The Jira %s is required.', $name ) );
			}
		}

		return sprintf(
			"Jira Task: %s\n\nSummary:\n%s\n\nRequirement:\n%s\n\nRepository:\nagentic-woocommerce-shop\n\nInstructions:\n\nRead AGENTS.md before making changes.\n\nImplement only the requested Jira scope.\n\nDo not modify:\n- WordPress core\n- WooCommerce core\n- unrelated files\n\nRun relevant tests and validation.\n\nUse branch:\n%s\n\nExpected result:\n- implementation\n- tests\n- pull request\n- implementation summary",
			$task_key,
			$summary,
			$description,
			$branch
		);
	}

	/**
	 * Sanitize a value that must occupy one line in the prompt.
	 *
	 * @param string $value Value to sanitize.
	 * @return string
	 */
	private function sanitize_single_line( string $value ): string {
		if ( function_exists( 'sanitize_text_field' ) ) {
			return sanitize_text_field( $value );
		}

		return trim( preg_replace( '/[\r\n\t ]+/', ' ', strip_tags( $value ) ) ?? '' );
	}

	/**
	 * Sanitize a value while preserving meaningful line breaks.
	 *
	 * @param string $value Value to sanitize.
	 * @return string
	 */
	private function sanitize_multiline( string $value ): string {
		if ( function_exists( 'sanitize_textarea_field' ) ) {
			return sanitize_textarea_field( $value );
		}

		$value = str_replace( array( "\r\n", "\r" ), "\n", strip_tags( $value ) );

		return trim( preg_replace( '/[\t ]+/', ' ', $value ) ?? '' );
	}
}
