<?php
/**
 * Plugin Name: App Builder - Custom Register Data
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-custom-register
 * Domain Path: /languages/
 * Description: App Builder Custom Register Data
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_custom_register_textdomain() {
	load_plugin_textdomain( 'app-builder-custom-register', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_custom_register_textdomain' );

add_filter( 'app_builder_register_user_data', 'app_builder_custom_register_user_data', 10, 2 );

function app_builder_custom_register_user_data( $user_data, $request ) {
	return $user_data;
}

add_action( 'app_builder_after_insert_user', 'app_builder_custom_after_insert_user', 10, 2 );

function app_builder_custom_after_insert_user( $user_id, $request ) {
	$ur_form_id = $request->get_param( 'ur_form_id' );
	$data       = $request->get_params();

	if ( empty( $ur_form_id ) ) {
		return null;
	}

	add_user_meta( $user_id, 'ur_form_id', $ur_form_id, true );

	foreach ( $data as $key => $value ) {
		if ( str_starts_with( $key, 'user_registration_' ) ) {
			add_user_meta( $user_id, $key, $value, true );
		}
	}
}