<?php
/**
 * Plugin Name: App Builder - YITH WooCommerce Points and Rewards Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain:app-builder-yith-woocommerce-points-and-rewards-addons
 * Domain Path: /languages/
 * Description: Show product points
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_yith_woocommerce_points_and_rewards_addons_text_domain() {
	load_plugin_textdomain( 'app-builder-yith-woocommerce-points-and-rewards-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function app_builder_yith_woocommerce_points_and_rewards_addons_prepare_product_object( $data, $post, $request ) {

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

	return $data;
}

add_action( 'plugins_loaded', 'app_builder_yith_woocommerce_points_and_rewards_addons_text_domain' );
add_filter( 'app_builder_prepare_product_object', 'app_builder_yith_woocommerce_points_and_rewards_addons_prepare_product_object', 999, 3 );