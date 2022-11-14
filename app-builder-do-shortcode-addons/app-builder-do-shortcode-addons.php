<?php
/**
 * Plugin Name: App Builder - Do Shortcode Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-do-shortcode-addons
 * Domain Path: /languages/
 * Description: App Builder do shortcode via URL
 * Author: Appcheap
 * Version: 1.0.1
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_app_builder_do_shortcode_addons_domain() {
	load_plugin_textdomain( 'app-builder-do-shortcode-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_app_builder_do_shortcode_addons_domain' );

/**
 * Render shortcode
 *
 * Ex: https://domain.com/wp-json/app-builder/v1/do-shortcode?shorcode=[code]
 *
 * @since 1.0.0
 */
function app_builder_do_short_code_api( $request ) {
	$sort_code = $request->get_param( 'shortcode' );
	header( 'Content-Type: text/html' );
	echo '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>';
	echo do_shortcode( $sort_code );
	echo '</body></html>';
	exit();
}

function app_builder_do_short_code_rest_init() {

	$namespace = 'app-builder/v1';
	$route     = 'do-shortcode';

	register_rest_route( $namespace, $route, array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'app_builder_do_short_code_api',
		'permission_callback' => '__return_true',
	) );
}

add_action( 'rest_api_init', 'app_builder_do_short_code_rest_init' );

add_filter( 'app_builder_prepare_product_object', 'app_builder_do_short_code_addons_prepare_product_object', 999, 3 );
function app_builder_do_short_code_addons_prepare_product_object( $data, $post, $request ) {

	if ( ! isset( $data['id'] ) ) {
		return $data;
	}

	if ( ! class_exists( '\WC_Smart_Coupons' ) ) {
		return $data;
	}

	$shortcode = $data['acf']['coupoun_code'] ?? '';

	if ( $shortcode == '' ) {
		return $data;
	}

	$afc_fields = $data['afc_fields'] ?? [];
	$html       = do_shortcode( $shortcode );

	$afc_fields['coupoun_code_html'] = [
		"key"    => uniqid(),
		"label"  => "coupoun_code_html",
		"name"   => "coupoun_code_html",
		"prefix" => "acf",
		"type"   => "html",
		"value"  => $html,
		"_name"  => "coupoun_code_html",
		"_valid" => 1
	];

	$data['afc_fields'] = $afc_fields;

	if ( isset( $data['acf'] ) ) {
		$data['acf']['coupoun_code_html'] = $html;
	} else {
		$data['acf'] = [ 'coupoun_code_html' => $html ];
	}

	return $data;
}
