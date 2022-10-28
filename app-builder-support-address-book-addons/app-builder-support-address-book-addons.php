<?php
/**
 * Plugin Name: App Builder - Address Book Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-support-address-book-addons
 * Domain Path: /languages/
 * Description: Support Custom Address Book Addons
 * Author: Appcheap
 * Version: 1.0.1
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_support_address_book_addons_text_domain() {
	load_plugin_textdomain( 'app-builder-support-address-book-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_support_address_book_addons_text_domain' );