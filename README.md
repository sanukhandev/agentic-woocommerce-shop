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
│       └── agentic-shop.php
└── themes/
```
