<?php
/**
 * Plugin Name: App Builder - Support Yith Product Brand
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-support-yith-product-brand
 * Domain Path: /languages/
 * Description: App Builder Support Yith Product Brand plugin
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_support_yith_product_brand_textdomain() {
	load_plugin_textdomain( 'app-builder-support-yith-product-brand', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_support_yith_product_brand_textdomain' );

add_action( 'rest_api_init', 'app_builder_support_brand_product_rest_api_register_routes' );

function app_builder_support_brand_product_rest_api_register_routes() {
	if ( ! is_a( WC()->api, 'WC_API' ) ) {
		return;
	}

	$dir = plugin_dir_path( __FILE__ );

	require_once( $dir . '/class-awc-brands-rest-api-v2-controller.php' );

	$controllers = array(
		'AWC_Brands_REST_API_V2_Controller',
	);

	foreach ( $controllers as $controller ) {
		WC()->api->$controller = new $controller();
		WC()->api->$controller->register_routes();
	}
}

add_filter( 'woocommerce_rest_prepare_product_object', 'app_builder_support_brand_product_rest_api_prepare_brands_to_product', 10, 2 );

function app_builder_support_brand_product_rest_api_prepare_brands_to_product( $response, $post ) {
	$post_id = is_callable( array( $post, 'get_id' ) ) ? $post->get_id() : ( ! empty( $post->ID ) ? $post->ID : null );

	if ( empty( $response->data['brands'] ) ) {
		$terms = array();

		foreach ( wp_get_post_terms( $post_id, 'yith_product_brand' ) as $term ) {
			$terms[] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			);
		}

		$response->data['brands'] = $terms;
	}

	return $response;
}

add_filter( 'woocommerce_rest_product_object_query', 'app_builder_support_brand_product_rest_api_filter_products_by_brand', 10, 2 );

function app_builder_support_brand_product_rest_api_filter_products_by_brand( $args, $request ) {

	if ( ! empty( $request['brand'] ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'yith_product_brand',
			'field'    => 'term_id',
			'terms'    => $request['brand'],
		);
	}

	return $args;
}