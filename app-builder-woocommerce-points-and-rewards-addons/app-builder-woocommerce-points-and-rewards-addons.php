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

function app_builder_points_rewards_my_points( $request ) {

	if ( ! class_exists( '\WC_Points_Rewards_Manager' ) ) {
		return new WP_Error(
			'exist_discount',
			__( "Plugin not installed.", "app_builder" )
		);
	}

	if ( get_current_user_id() == 0 ) {
		return new WP_Error(
			'user_logged',
			__( "User logout.", "app_builder" )
		);
	}

	$page     = $request->get_param( 'page' );
	$per_page = $request->get_param( 'per_page' );

	global $wc_points_rewards;

	$points_balance = \WC_Points_Rewards_Manager::get_users_points( get_current_user_id() );
	$points_label   = $wc_points_rewards->get_points_label( $points_balance );

	$count        = empty( $per_page ) ? 5 : absint( $per_page );
	$current_page = empty( $page ) ? 1 : absint( $page );

	// get a set of points events, ordered newest to oldest
	$args = array(
		'calc_found_rows' => true,
		'orderby'         => array(
			'field' => 'date',
			'order' => 'DESC',
		),
		'per_page'        => $count,
		'paged'           => $current_page,
		'user'            => get_current_user_id(),
	);

	$events     = \WC_Points_Rewards_Points_Log::get_points_log_entries( $args );
	$total_rows = \WC_Points_Rewards_Points_Log::$found_rows;

	return new WP_REST_Response(
		[
			'points_balance' => $points_balance,
			'points_label'   => $points_label,
			'events'         => $events,
			'total_rows'     => $total_rows,
			'current_page'   => $current_page,
			'count'          => $count,
		]
	);
}

function app_builder_woocommerce_points_and_rewards_rest_init() {

	$namespace = 'app-builder/v1';
	$route     = 'points-and-rewards';

	register_rest_route( $namespace, $route, [
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'app_builder_points_rewards_my_points',
			'permission_callback' => '__return_true',
		],
	] );
}

add_action( 'rest_api_init', 'app_builder_woocommerce_points_and_rewards_rest_init' );