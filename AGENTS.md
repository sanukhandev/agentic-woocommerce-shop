# Agentic WooCommerce Shop

## Purpose

This repository is a demonstration project for AI-agent-assisted
software development using Codex Cloud.

The application is based on:

- WordPress
- WooCommerce
- PHP
- Docker
- wp-env

---

## Architecture

Custom application functionality belongs inside:

plugins/agentic-shop/

Do not modify:

- WordPress core
- WooCommerce core
- third-party plugins
- generated dependency files unless required

---

## WooCommerce Development Rules

Prefer WooCommerce and WordPress:

- actions
- filters
- APIs
- extension points

Do not modify WooCommerce internal source code.

Use WordPress coding standards.

---

## Security

All user input must be sanitized.

All output must be escaped appropriately.

Administrative operations must verify:

- capabilities
- nonces where applicable

Never commit:

- passwords
- API keys
- secrets
- tokens

---

## Scope Control

Implement only the functionality described in the task.

Avoid unrelated:

- refactoring
- formatting
- dependency upgrades
- architectural changes

---

## Testing

Before completing a task:

1. Check modified PHP files for syntax errors.
2. Run available tests.
3. Verify the plugin activates successfully.
4. Check for obvious WordPress runtime errors.

If something cannot be tested, explain why.

---

## Git

Never push directly to:

main

Use a feature branch:

feature/<ticket-id>-<short-description>

Example:

feature/SHOP-1-featured-product-badge

---

## Pull Request Requirements

Each implementation must report:

### Requirement

What was requested.

### Implementation

What was changed.

### Files Changed

List relevant files.

### Validation

Tests and checks performed.

### Assumptions

Any assumptions made.

### Risks

Known limitations or potential issues.

Never automatically merge a pull request.