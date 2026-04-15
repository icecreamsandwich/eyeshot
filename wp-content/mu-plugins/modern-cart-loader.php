<?php
/**
 * Plugin Name: Eyeshot Modern Cart Loader
 * Description: Loads the Modern Cart plugin from the standard plugins directory.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$modern_cart_plugin = WP_PLUGIN_DIR . '/modern-cart/modern-cart.php';

if ( file_exists( $modern_cart_plugin ) && ! defined( 'MODERNCART_FILE' ) ) {
	require_once $modern_cart_plugin;
}
