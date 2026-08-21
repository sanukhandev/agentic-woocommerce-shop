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

/**
 * Initialize the plugin.
 */
function agentic_shop_init() {

    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

}

add_action( 'plugins_loaded', 'agentic_shop_init' );
