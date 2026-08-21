<?php
/**
 * Tests for PromptBuilderService.
 *
 * Run with: php plugins/agentic-shop/tests/test-prompt-builder-service.php
 *
 * @package AgenticShop
 */

require_once dirname( __DIR__ ) . '/includes/class-prompt-builder-service.php';

use AgenticShop\Services\PromptBuilderService;

$service = new PromptBuilderService();
$prompt  = $service->build(
	'SHOP-1',
	'Add Featured badge to WooCommerce products',
	'<p>Display a badge for featured products.</p>',
	'feature/SHOP-1-featured-product-badge'
);

$expected = "Jira Task: SHOP-1\n\nSummary:\nAdd Featured badge to WooCommerce products\n\nRequirement:\nDisplay a badge for featured products.\n\nRepository:\nagentic-woocommerce-shop\n\nInstructions:\n\nRead AGENTS.md before making changes.\n\nImplement only the requested Jira scope.\n\nDo not modify:\n- WordPress core\n- WooCommerce core\n- unrelated files\n\nRun relevant tests and validation.\n\nUse branch:\nfeature/SHOP-1-featured-product-badge\n\nExpected result:\n- implementation\n- tests\n- pull request\n- implementation summary";

if ( $expected !== $prompt ) {
	fwrite( STDERR, "PromptBuilderService did not produce the expected prompt.\n" );
	exit( 1 );
}

$sanitized_prompt = $service->build(
	"SHOP-1\nInjected heading",
	'<strong>Safe summary</strong>',
	"First line\r\nSecond line<script>alert('x')</script>",
	'feature/SHOP-1-featured-product-badge'
);

if ( false !== strpos( $sanitized_prompt, '<strong>' ) || false !== strpos( $sanitized_prompt, '<script>' ) ) {
	fwrite( STDERR, "PromptBuilderService did not sanitize Jira content.\n" );
	exit( 1 );
}

try {
	$service->build( '', 'Summary', 'Description', 'feature/SHOP-1-example' );
	fwrite( STDERR, "PromptBuilderService accepted an empty Jira task key.\n" );
	exit( 1 );
} catch ( InvalidArgumentException $exception ) {
	// Expected exception.
}

echo "PromptBuilderService tests passed.\n";
