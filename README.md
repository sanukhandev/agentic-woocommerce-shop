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

The Agentic Shop plugin includes a server-side Aviation Edge integration for
flight status and airline route lookups. To use it:

1. Activate **Agentic Shop** and WooCommerce.
2. In WordPress admin, open **Settings → Airport Support** and save an
   Aviation Edge API key.
3. Add the `[agentic_airport_support]` shortcode to a page.
4. On that page, choose **Flight status** and enter a flight IATA code, or
   choose **Airline routes** and enter an airline IATA code.

The API key is stored as a WordPress option and is used only for server-side
requests to Aviation Edge; it is not included in front-end markup.
