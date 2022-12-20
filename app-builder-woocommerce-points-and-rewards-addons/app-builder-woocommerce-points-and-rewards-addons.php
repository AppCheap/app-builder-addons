<?php
/**
 * Plugin Name: App Builder - WooCommerce Points and Rewards Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain:app-builder-woocommerce-points-and-rewards-addons
 * Domain Path: /languages/
 * Description: Show product points
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_woocommerce_points_and_rewards_addons_text_domain() {
	load_plugin_textdomain( 'app-builder-woocommerce-points-and-rewards-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function app_builder_woocommerce_points_and_rewards_addons_prepare_product_object( $data, $post, $request ) {

	if ( ! isset( $data['id'] ) ) {
		return $data;
	}

	if ( ! class_exists( '\YITH_WC_Points_Rewards_Frontend' ) ) {
		return $data;
	}

	$afc_fields = $data['afc_fields'] ?? [];
	$message    = do_shortcode( '[yith_points_product_message product_id="' . $data['id'] . '"]' );

	$afc_fields['yith_points_product_message'] = [
		"key"    => uniqid(),
		"label"  => "points",
		"name"   => "points",
		"prefix" => "acf",
		"type"   => "html",
		"value"  => $message,
		"_name"  => "points",
		"_valid" => 1
	];

	$data['afc_fields'] = $afc_fields;

	if ( isset( $data['acf'] ) ) {
		$data['acf']['points'] = $message;
	} else {
		$data['acf'] = [ 'points' => $message ];
	}

	return $data;
}

add_action( 'plugins_loaded', 'app_builder_woocommerce_points_and_rewards_addons_text_domain' );
add_filter( 'app_builder_prepare_product_object', 'app_builder_woocommerce_points_and_rewards_addons_prepare_product_object', 999, 3 );

/**
 * Generate the message that will be displayed showing how many points the customer will receive for completing their purchase.
 *
 */
function app_builder_generate_earn_points_message( $request ) {
	$message = '';
	if ( class_exists( '\WC_Points_Rewards_Cart_Checkout' ) ) {
		$wc_point = new \WC_Points_Rewards_Cart_Checkout();
		$message  = $wc_point->generate_earn_points_message();
	}

	return new WP_REST_Response( [
		'message' => $message,
	] );
}

function app_builder_woocommerce_points_and_rewards_rest_init() {

	$namespace = 'app-builder/v1';
	$route     = 'points-and-rewards';

	register_rest_route( $namespace, $route, array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'app_builder_generate_earn_points_message',
		'permission_callback' => '__return_true',
	) );

//	register_rest_route( $namespace, $route . '/make-primary', array(
//		'methods'             => WP_REST_Server::CREATABLE,
//		'callback'            => 'app_builder_make_primary_address_books',
//		'permission_callback' => '__return_true',
//	) );
//
//	register_rest_route( $namespace, $route . '/delete', array(
//		'methods'             => WP_REST_Server::CREATABLE,
//		'callback'            => 'app_builder_delete_address_books',
//		'permission_callback' => '__return_true',
//	) );
}

add_action( 'rest_api_init', 'app_builder_woocommerce_points_and_rewards_rest_init' );