<?php
/**
 * Plugin Name: App Builder - B2BKing Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-b2bking-add-addons
 * Domain Path: /languages/
 * Description: App Builder B2BKing Addons
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_b2bking_add_addons_textdomain() {
	load_plugin_textdomain( 'app-builder-b2bking-add-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function app_builder_b2bking_prepare_user_options( $options, $user ) {
	$user_id = $user->ID;

	$b2bking_customer_group_id = get_user_meta( $user_id, 'b2bking_customergroup', true );

	if ( $user_id && $b2bking_customer_group_id ) {
		$options['b2bkingCustomerGroupId'] = $b2bking_customer_group_id;
	}

	return $options;
}

add_action( 'plugins_loaded', 'app_builder_b2bking_add_addons_textdomain' );
add_filter( 'app_builder_prepare_user_options', 'app_builder_b2bking_prepare_user_options', 10, 2 );