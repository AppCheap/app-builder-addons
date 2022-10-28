<?php
/**
 * Plugin Name: App Builder - Custom Template
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-custom-template
 * Domain Path: /languages/
 * Description: App Builder Custom Template
 * Author: Appcheap
 * Version: 1.0.1
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_custom_template_textdomain() {
	load_plugin_textdomain( 'app-builder-custom-template', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_custom_template_textdomain' );

add_filter( 'single_template', 'app_builder_custom_template_path', 999 );

function app_builder_custom_template_path( $template ) {
	global $post;

	if ( 'service' === $post->post_type && locate_template( array( 'empty.php' ) ) !== $template && $_GET['template'] == 'empty') {
		/*
		 * This is a 'movie' post
		 * AND a 'single movie template' is not found on
		 * theme or child theme directories, so load it
		 * from our plugin directory.
		 */
		return plugin_dir_path( __FILE__ ) . 'empty.php';
	}

	return $template;

}