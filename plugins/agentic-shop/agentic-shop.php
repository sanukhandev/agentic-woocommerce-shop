<?php
/**
 * Plugin Name: Agentic Shop
 * Description: Custom functionality for the Agentic WooCommerce demonstration project.
 * Version: 1.0.0
 * Author: Techcarrot
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AGENTIC_SHOP_VERSION', '1.0.0' );
define( 'AGENTIC_SHOP_PATH', plugin_dir_path( __FILE__ ) );
define( 'AGENTIC_SHOP_URL', plugin_dir_url( __FILE__ ) );

require_once AGENTIC_SHOP_PATH . 'includes/class-agentic-airport-api.php';
require_once AGENTIC_SHOP_PATH . 'includes/class-agentic-airport-support.php';

/**
 * Initialize the plugin.
 */
function agentic_shop_init(): void {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    Agentic_Airport_Support::init();
}

add_action( 'plugins_loaded', 'agentic_shop_init' );
