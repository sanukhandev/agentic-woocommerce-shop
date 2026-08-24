# Agentic WooCommerce Shop

An agentic WooCommerce development workspace powered by `@wordpress/env`.

## Getting Started

### Prerequisites
- Node.js & npm
- Docker (required by `@wordpress/env`)

### Installation & Local Setup

1. Install project dependencies:
   ```bash
   npm install
   ```

2. Launch the WordPress environment:
   ```bash
   npx wp-env start
   ```

3. Stop the WordPress environment:
   ```bash
   npx wp-env stop
   ```

## Directory Structure

```
agentic-woocommerce-shop/
├── .wp-env.json
├── AGENTS.md
├── README.md
├── package.json
├── plugins/
│   └── agentic-shop/
│       ├── agentic-shop.php
│       ├── includes/
│       └── tests/
└── themes/
```

## Airport Support

The Agentic Shop plugin includes a server-side Aviationstack integration for
flight status and airline route lookups. To use it:

1. Activate **Agentic Shop** and WooCommerce.
2. In WordPress admin, open **Settings → Airport Support** and save an
   Aviationstack API access key.
3. Add the `[agentic_airport_support]` shortcode to a page.
4. On that page, choose **Flight status** and enter a flight IATA code, or
   choose **Airline routes** and enter an airline IATA code.

Real-time flights are available on Aviationstack's free plan; airline routes
require a paid plan.

The API key is stored as a WordPress option and is used only for server-side
requests to Aviationstack; it is not included in front-end markup. Authoritative
quota usage is linked from the plugin settings page.

On WordPress pages, the form submits through `admin-ajax.php` and updates the
results without reloading. A normal POST fallback remains available when
JavaScript is disabled.

## Headless WordPress Usage

The current airport interface is a server-rendered shortcode. It works on a
WordPress page, but it does not expose a REST endpoint for a separate React,
Next.js, or other headless frontend.

For a fully headless implementation:

1. Keep the Aviationstack access key in WordPress under **Settings → Airport
   Support**. Never send it to the browser or store it in frontend environment
   variables.
2. Add a custom WordPress REST endpoint that accepts only `flight` or `route`
   plus a validated IATA query and calls the existing `Agentic_Airport_API`
   methods.
3. Return only the fields required by the frontend; do not proxy the complete
   provider response.
4. Reuse the plugin's transient caching and rate limiting so frontend requests
   do not consume unnecessary provider quota.
5. Allow only the deployed frontend origin through CORS. For private endpoints,
   authenticate server-to-server with a WordPress Application Password or the
   site's existing authentication provider.

WordPress cookie nonces are suitable only when the frontend shares the
WordPress login session and origin. They are not authentication for a public
cross-origin API. Public headless lookups require edge rate limiting and must
not expose administrative capabilities.

Fetching a page through the core `/wp-json/wp/v2/pages` endpoint may return the
rendered shortcode HTML, but its POST form and nonce still target WordPress.
Treat that as display-only content, not a headless API contract.

Headless lookup support is therefore a documented future extension, not a
currently available endpoint. The existing WordPress shortcode remains the
supported interface until that REST route is implemented and tested.
