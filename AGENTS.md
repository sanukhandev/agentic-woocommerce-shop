# Agentic WooCommerce Shop — Agent Instructions

## 1. Purpose

This repository is a demonstration project for **AI-agent-assisted software development using Codex Cloud**.

The application stack is:

* WordPress
* WooCommerce
* PHP
* JavaScript where required
* Docker
* `wp-env`

The primary goal is to demonstrate how an AI coding agent can:

* Read a development task
* Understand the existing implementation
* Make a controlled code change
* Validate the change
* Report exactly what was modified
* Avoid unintended changes to the application

The agent must behave as a **conservative software engineer**.

Prefer the smallest correct change over broad refactoring.

---

# 2. Repository Architecture

Custom application functionality belongs primarily inside:

```text
plugins/agentic-shop/
```

Expected structure may include:

```text
plugins/
└── agentic-shop/
    ├── agentic-shop.php
    ├── includes/
    ├── admin/
    ├── public/
    ├── assets/
    │   ├── css/
    │   └── js/
    ├── templates/
    ├── tests/
    └── README.md
```

Before implementing a task, inspect the existing plugin structure and follow the established architecture.

Do not create new architectural layers unless required by the task.

---

# 3. Allowed Modification Scope

The agent may modify:

```text
plugins/agentic-shop/**
```

The agent may also modify project-level development configuration when explicitly required, for example:

```text
.wp-env.json
package.json
composer.json
phpcs.xml
phpunit.xml
```

Only modify these files when necessary for the requested task.

---

# 4. Prohibited Modification Scope

Do not modify:

```text
WordPress core
WooCommerce core
wp-admin/**
wp-includes/**
third-party plugins
vendor/**
node_modules/**
generated build files
lock files unless dependency changes are explicitly required
```

Do not patch third-party source code to solve an issue.

If functionality requires changing third-party behavior, use:

* WordPress hooks
* WooCommerce hooks
* filters
* actions
* extension APIs
* custom plugin code

If a requested task appears to require modifying WordPress or WooCommerce core, stop and find an extension-point-based solution instead.

---

# 5. Task Scope Control

Implement only what is explicitly required by the task.

Do not introduce unrelated:

* Refactoring
* Formatting
* Dependency upgrades
* Library upgrades
* Architectural restructuring
* Folder restructuring
* Naming changes
* Code cleanup
* Performance optimizations unrelated to the task
* UI redesign
* Feature enhancements

Do not change working code simply because another implementation would be cleaner.

Avoid "while I am here" changes.

If an unrelated issue is discovered, report it separately instead of fixing it.

---

# 6. Existing Code First

Before writing new code:

1. Inspect the existing implementation.
2. Search for similar functionality.
3. Reuse existing helpers, services, classes, utilities, hooks, and patterns.
4. Follow the current plugin architecture.
5. Avoid duplicate implementations.

Do not introduce a new helper or abstraction when an equivalent implementation already exists.

---

# 7. WordPress Development Rules

Follow WordPress coding standards.

Prefer native WordPress APIs over custom implementations.

Use appropriate WordPress APIs for:

* Database access
* HTTP calls
* Settings
* Options
* Transients
* Users
* Roles
* Capabilities
* Nonces
* Sanitization
* Escaping
* Filesystem operations
* Cron jobs

Prefer WordPress extension points:

```php
add_action()
add_filter()
do_action()
apply_filters()
```

Do not modify WordPress internals.

---

# 8. WooCommerce Development Rules

Use WooCommerce-supported extension mechanisms.

Prefer:

* WooCommerce actions
* WooCommerce filters
* WooCommerce CRUD objects
* WooCommerce APIs
* WooCommerce template overrides where appropriate
* WooCommerce extension points

Examples:

```php
wc_get_product()
wc_get_order()
wc_get_orders()
WC_Product
WC_Order
```

Avoid querying WooCommerce internal database tables directly unless there is a strong technical reason.

Do not assume WooCommerce orders are always stored using legacy `wp_posts` tables.

Code should remain compatible with WooCommerce HPOS where practical.

---

# 9. Hooks

Use the narrowest appropriate hook.

Avoid hooks that execute unnecessarily on every WordPress request.

For example, avoid putting expensive logic inside:

```php
init
wp_loaded
plugins_loaded
```

unless required.

Prefer context-specific WooCommerce or WordPress hooks.

Do not register duplicate hooks.

When registering callbacks inside classes, ensure callbacks can be cleanly identified and maintained.

---

# 10. Database Rules

Prefer WordPress and WooCommerce APIs before writing raw SQL.

When raw SQL is required:

* Use `$wpdb`
* Use prepared statements
* Never concatenate user input directly into SQL
* Select only required columns
* Avoid unbounded queries

Example:

```php
$wpdb->prepare(
    "SELECT id FROM {$table} WHERE user_id = %d",
    $user_id
);
```

Avoid:

```php
SELECT *
```

when unnecessary.

Avoid queries inside loops.

Example to avoid:

```php
foreach ( $products as $product ) {
    $data = $wpdb->get_results(...);
}
```

Prefer bulk retrieval.

---

# 11. WooCommerce Query Rules

Avoid loading large product or order collections without limits.

Avoid:

```php
'posts_per_page' => -1
```

unless the dataset is guaranteed to remain small.

Prefer:

* Pagination
* IDs-only queries
* WooCommerce CRUD queries
* Batch processing

For example:

```php
wc_get_orders(
    array(
        'limit'  => 20,
        'page'   => 1,
        'return' => 'ids',
    )
);
```

when complete order objects are unnecessary.

---

# 12. Performance

Every implementation should avoid creating unnecessary request-time work.

Review modified code for:

* Queries inside loops
* N+1 queries
* Repeated API calls
* Repeated `get_option()` calls where expensive processing follows
* Large object loading
* Unbounded queries
* Expensive calculations on every request
* Repeated product/order retrieval
* Excessive hook execution
* Loading frontend assets globally
* Large autoloaded options

Do not introduce caching unless there is a meaningful reason.

When caching is appropriate, prefer:

* WordPress object cache
* Transients
* WooCommerce caching mechanisms

Ensure cached values can be invalidated correctly.

---

# 13. Frontend Asset Rules

Do not load plugin CSS or JavaScript globally unless required.

Prefer conditional enqueueing.

Example:

```php
if ( is_product() ) {
    wp_enqueue_script(...);
}
```

Use:

```php
wp_enqueue_script()
wp_enqueue_style()
```

Do not manually inject `<script>` or `<link>` tags when normal enqueue APIs can be used.

---

# 14. Security

Security requirements are mandatory.

All external or user-controlled input must be treated as untrusted.

---

## 14.1 Sanitization

Sanitize input according to its expected type.

Examples:

```php
sanitize_text_field()
sanitize_email()
sanitize_key()
absint()
intval()
esc_url_raw()
```

Do not use a generic sanitization strategy for every input type.

---

# 14.2 Output Escaping

Escape output at the point where it is rendered.

Use:

```php
esc_html()
esc_attr()
esc_url()
wp_kses_post()
```

depending on context.

Do not escape values unnecessarily before storing them.

Sanitize on input.

Escape on output.

---

# 14.3 Nonces

State-changing operations must use nonces where applicable.

Examples include:

* Admin actions
* AJAX requests
* Custom forms
* Destructive operations

Use:

```php
wp_nonce_field()
wp_verify_nonce()
check_admin_referer()
check_ajax_referer()
```

as appropriate.

---

# 14.4 Capabilities

Administrative operations must verify user authorization.

Use:

```php
current_user_can()
```

Never rely only on:

```php
is_admin()
```

`is_admin()` does not indicate that the current user has administrative privileges.

---

# 14.5 AJAX

For custom AJAX handlers:

1. Verify nonce.
2. Verify permissions where required.
3. Sanitize request parameters.
4. Validate business rules.
5. Escape output where appropriate.
6. Return structured responses.

Prefer:

```php
wp_send_json_success()
wp_send_json_error()
```

---

# 14.6 REST API

Custom REST endpoints must define an appropriate:

```php
permission_callback
```

Do not use:

```php
'permission_callback' => '__return_true'
```

for privileged operations.

Validate and sanitize REST arguments.

---

# 14.7 Secrets

Never commit:

* Passwords
* API keys
* Access tokens
* Refresh tokens
* Database credentials
* OAuth secrets
* Private certificates
* Production credentials

Use environment variables or configuration mechanisms appropriate for the development environment.

If secrets are discovered in tracked code, report them.

Do not automatically rotate or replace credentials unless explicitly requested.

---

# 15. Admin Functionality

Administrative pages must:

* Check appropriate capabilities
* Use nonces for state changes
* Sanitize submitted values
* Escape rendered values

Avoid granting functionality based only on a user's role name.

Prefer capabilities.

---

# 16. Error Handling

Do not silently ignore failures.

Use appropriate WordPress patterns such as:

```php
WP_Error
is_wp_error()
```

Avoid exposing:

* Stack traces
* Credentials
* Internal file paths
* Database details

to frontend users.

Errors intended for developers may be logged when appropriate.

---

# 17. Logging

Do not add excessive production logging.

Never log:

* Passwords
* Authentication tokens
* Payment details
* Sensitive personal data

Temporary debugging statements must not remain in the final implementation.

Remove:

```php
var_dump()
print_r()
console.log()
die()
exit()
```

unless intentionally required.

---

# 18. Payment and Checkout Code

Changes affecting checkout, orders, or payments require additional caution.

Do not:

* Trust client-provided prices
* Trust client-provided product totals
* Store payment card information
* Bypass WooCommerce validation
* Create duplicate payment operations

Prefer WooCommerce APIs for:

* Cart calculations
* Order creation
* Payment state
* Order status transitions

Changes to payment-related code should remain minimal and explicitly scoped.

---

# 19. Data Validation

Sanitization and validation are separate requirements.

Sanitize input first.

Then validate business rules.

Example:

A product ID may be converted using:

```php
$product_id = absint( $_POST['product_id'] );
```

but the implementation must still verify that:

```php
wc_get_product( $product_id )
```

returns a valid product.

---

# 20. Internationalization

User-facing strings should use WordPress translation functions when appropriate.

Examples:

```php
__( 'Text', 'agentic-shop' )
_e( 'Text', 'agentic-shop' )
esc_html__( 'Text', 'agentic-shop' )
```

Use the plugin text domain:

```text
agentic-shop
```

Avoid changing existing strings solely to add internationalization unless relevant to the task.

---

# 21. PHP Compatibility

Follow the PHP version defined by the project.

Do not introduce newer language features unless the configured environment supports them.

Before using newer syntax, inspect project configuration.

---

# 22. JavaScript

For custom JavaScript:

* Avoid unnecessary dependencies
* Avoid polluting the global namespace
* Validate AJAX/REST responses
* Handle errors
* Avoid duplicate event registration
* Avoid unnecessary DOM queries inside loops

Do not introduce a frontend framework unless explicitly required.

---

# 23. Dependency Management

Do not upgrade dependencies unless required by the task.

Do not run broad upgrades such as:

```bash
npm update
composer update
```

unless explicitly requested.

Prefer targeted dependency installation.

Do not modify lock files unless dependencies actually change.

---

# 24. Testing Requirements

Before completing any implementation, perform the applicable validation steps.

At minimum:

1. Check modified PHP files for syntax errors.
2. Run available automated tests.
3. Verify plugin activation.
4. Check for obvious WordPress runtime errors.
5. Test the affected functionality where practical.

---

# 25. PHP Syntax Validation

Run syntax checks against modified PHP files.

Example:

```bash
php -l plugins/agentic-shop/path/to/file.php
```

Do not claim syntax validation passed unless the command was actually executed successfully.

---

# 26. Plugin Activation

Where the environment permits, verify that the plugin activates successfully.

Example:

```bash
wp plugin activate agentic-shop
```

If already active, confirm its status.

Example:

```bash
wp plugin status agentic-shop
```

---

# 27. wp-env

Use the project's existing `wp-env` setup.

Typical commands may include:

```bash
npm run start
```

or:

```bash
npx wp-env start
```

Do not replace the existing local development environment unless required.

---

# 28. Automated Tests

If tests exist, run tests relevant to the modified functionality.

Do not automatically run unrelated expensive test suites if a targeted test is available.

If a full suite is practical, it may be run after targeted tests.

Report:

```text
PASS
FAIL
NOT RUN
```

Do not describe a test as passing when it was not executed.

---

# 29. Runtime Validation

When possible, verify:

* WordPress loads
* Plugin activates
* Relevant page loads
* Modified functionality executes
* No obvious PHP fatal errors occur

Check logs where appropriate.

---

# 30. Failed Validation

If a test or command cannot be run, clearly report why.

Examples:

```text
NOT RUN — Docker daemon unavailable.
```

```text
NOT RUN — No automated test suite exists for this feature.
```

```text
FAILED — Plugin activation produced a PHP fatal error.
```

Never hide failed validation.

---

# 31. Git Rules

Never push directly to:

```text
main
master
```

Development must occur on a feature branch.

Branch naming convention:

```text
feature/<ticket-id>-<short-description>
```

Example:

```text
feature/SHOP-1-featured-product-badge
```

Other examples:

```text
feature/SHOP-12-product-stock-message
feature/SHOP-18-order-admin-column
feature/SHOP-24-checkout-validation
```

Use lowercase kebab-case for the description.

---

# 32. Existing Working Tree

Before making changes, inspect:

```bash
git status
```

Do not overwrite unrelated modifications already present in the working tree.

Do not discard user changes.

If unrelated modified files exist, leave them untouched.

---

# 33. Commits

Keep changes logically focused.

Do not combine unrelated modifications into the same implementation.

Suggested commit format:

```text
SHOP-1 Add featured product badge
```

Do not amend, rewrite, squash, or force-push existing history unless explicitly requested.

---

# 34. Pull Requests

Never automatically merge a pull request.

A completed implementation must provide a concise pull-request-ready summary.

Use the following structure.

## Requirement

Describe what the ticket requested.

## Implementation

Describe the implemented solution.

## Files Changed

List relevant files only.

Example:

```text
plugins/agentic-shop/includes/class-featured-badge.php
plugins/agentic-shop/assets/css/featured-badge.css
```

## Validation

List actual validation performed.

Example:

```text
PASS — PHP syntax validation
PASS — Plugin activation
PASS — Product page manual test
PASS — Existing PHPUnit tests
```

## Assumptions

List assumptions required to implement the task.

If none:

```text
None.
```

## Risks

List known limitations or potential impacts.

If none:

```text
No known material risks.
```

---

# 35. Code Review Mode

When the task is to review existing code rather than implement functionality, do not automatically modify files unless explicitly requested.

Review only custom code under:

```text
plugins/agentic-shop/
```

Do not review:

* WordPress core
* WooCommerce core
* Third-party dependencies
* Generated files

Focus on meaningful findings related to:

* Security
* Performance
* Database access
* WooCommerce compatibility
* Scalability
* Error handling
* Maintainability where it affects correctness
* WordPress best practices

Ignore cosmetic issues.

---

# 36. Review Priority

Classify findings as:

```text
HIGH
MEDIUM
LOW
```

### HIGH

Examples:

* Security vulnerability
* Data loss risk
* Broken checkout
* Duplicate payment possibility
* Authentication/authorization failure
* SQL injection
* Serious production performance problem

### MEDIUM

Examples:

* Inefficient queries
* Missing caching for expensive operations
* N+1 queries
* Incorrect WooCommerce API usage
* Missing validation
* Scalability concern

### LOW

Examples:

* Small optimization
* Minor maintainability concern
* Non-critical standards issue

Do not report trivial style issues unless specifically asked.

---

# 37. Minimal Review Output

For code reviews, conserve tokens.

Use:

```text
[HIGH] Issue title
File: path
Location: function/class
Issue: Short explanation
Fix: Exact modification
```

Do not produce long explanations.

Do not repeat unchanged code.

When showing a fix, prefer a minimal diff.

Example:

```diff
- $product_id = $_POST['product_id'];
+ $product_id = absint( $_POST['product_id'] );
```

---

# 38. Performance Review Checklist

When custom code is modified or reviewed, check specifically for:

* N+1 database queries
* Queries inside loops
* `posts_per_page => -1`
* Large `meta_query`
* Repeated `wc_get_product()`
* Repeated `wc_get_order()`
* Expensive work on `init`
* Expensive work on every frontend request
* Global asset loading
* Unbounded REST responses
* Unbounded AJAX results
* Missing pagination
* Large autoloaded options
* Duplicate external API calls
* Missing cache opportunities
* Cache invalidation problems

Only report issues that materially matter.

---

# 39. WooCommerce Compatibility Checklist

When touching WooCommerce functionality, check:

* HPOS compatibility
* Correct CRUD APIs
* Checkout lifecycle
* Order status behavior
* Product type compatibility
* Stock handling
* Taxes where relevant
* Cart/session behavior
* Hook execution context

Do not assume WooCommerce internal implementation details unless unavoidable.

---

# 40. Agent Decision Rules

When several solutions are possible, use this priority order:

1. Existing project pattern
2. WordPress-supported API
3. WooCommerce-supported API
4. Existing project utility
5. Small custom implementation
6. New abstraction only when genuinely necessary

Prefer:

```text
small change
```

over:

```text
large redesign
```

---

# 41. Ambiguous Requirements

Do not invent major business requirements.

If a minor implementation detail is unspecified, follow:

1. Existing code behavior
2. WordPress convention
3. WooCommerce convention
4. Safest minimal implementation

Document the assumption.

Do not expand ticket scope.

---

# 42. Definition of Done

A task is complete only when:

* Requested functionality is implemented
* Scope is respected
* No WordPress/WooCommerce core files were modified
* Security checks are present
* Modified PHP files pass syntax validation
* Relevant tests are run where available
* Plugin can activate successfully where testable
* No obvious runtime error is introduced
* Changes are summarized
* Assumptions are documented
* Risks are documented

---

# 43. Final Agent Response

Keep final responses concise.

Use:

## Requirement

One or two sentences.

## Implementation

Short list of meaningful changes.

## Files Changed

Relevant files only.

## Validation

Actual executed checks only.

## Assumptions

Only real assumptions.

## Risks

Only material risks.

Do not include large code dumps unless specifically requested.

Do not report planned work as completed work.

Do not claim tests passed unless they were actually executed.

---

# 44. Core Principle

The governing principle for this repository is:

> Make the smallest safe change that satisfies the requirement while respecting WordPress and WooCommerce extension boundaries.

When uncertain between a broad refactor and a targeted fix, choose the targeted fix.
