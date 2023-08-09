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
 *
 * @package App Builder - B2BKing Addons
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads the plugin text domain for translation.
 *
 * @return void
 */
function app_builder_b2bking_add_addons_textdomain() {
	load_plugin_textdomain( 'app-builder-b2bking-add-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

/**
 * Prepares user options for App Builder based on B2BKing customer group.
 *
 * @param array   $options The user options.
 * @param WP_User $user The WordPress user object.
 * @return array The updated user options.
 */
function app_builder_b2bking_prepare_user_options( $options, $user ) {
	$user_id = $user->ID;

	$b2bking_customer_group_id = get_user_meta( $user_id, 'b2bking_customergroup', true );

	if ( $user_id && $b2bking_customer_group_id ) {
		$options['b2bkingCustomerGroupId'] = $b2bking_customer_group_id;
	}

	return $options;
}

/**
 * Filter the product quantity multiple of value.
 *
 * @param int|float  $value The product quantity multiple of value.
 * @param WC_Product $product The product object.
 * @param array      $cart_item The cart item data.
 * @return int|float The updated product quantity multiple of value.
 */
function app_builder_woocommerce_store_api_product_quantity_multiple_of( $value, $product, $cart_item = array() ) {
	if ( ! class_exists( 'B2bking_Dynamic_Rules' ) ) {
		return $value;
	}

	$data = B2bking_Dynamic_Rules::b2bking_dynamic_rule_required_multiple_quantity( array(), $product );

	if ( isset($data['step']) && $data['step'] ) {
		return $data['step'];
	}

	return $value;
}

add_action( 'plugins_loaded', 'app_builder_b2bking_add_addons_textdomain' );
add_filter( 'app_builder_prepare_user_options', 'app_builder_b2bking_prepare_user_options', 10, 2 );
add_filter( 'woocommerce_store_api_product_quantity_multiple_of', 'app_builder_woocommerce_store_api_product_quantity_multiple_of', 10, 3 );
