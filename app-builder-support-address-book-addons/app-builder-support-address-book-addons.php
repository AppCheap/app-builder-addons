<?php
/**
 * Plugin Name: App Builder - Address Book Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-support-address-book-addons
 * Domain Path: /languages/
 * Description: Support Custom Address Book Addons
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_support_address_book_addons_text_domain() {
	load_plugin_textdomain( 'app-builder-support-address-book-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_support_address_book_addons_text_domain' );


/**
 * API get list address books
 *
 * @param $request
 *
 * @return WP_Error|WP_REST_Response
 */
function app_builder_get_address_books( $request ) {

	$wc_address_book = WC_Address_Book::get_instance();

	$woo_address_book_customer_id           = get_current_user_id();
	$woo_address_book_billing_address_book  = $wc_address_book->get_address_book( $woo_address_book_customer_id, 'billing' );
	$woo_address_book_shipping_address_book = $wc_address_book->get_address_book( $woo_address_book_customer_id, 'shipping' );

	$billing_enable  = $wc_address_book->get_wcab_option( 'billing_enable' ) === true;
	$shipping_enable = $wc_address_book->get_wcab_option( 'shipping_enable' ) === true;


	$billing = [];

	if ( $billing_enable ) {
		$woo_address_book_billing_address = get_user_meta( $woo_address_book_customer_id, 'billing_address_1', true );

		if ( ! empty( $woo_address_book_billing_address ) ) {
			foreach ( $woo_address_book_billing_address_book as $woo_address_book_name => $woo_address_book_fields ) {
				// Prevent default billing from displaying here.
				if ( 'billing' === $woo_address_book_name ) {
					continue;
				}

				$woo_address_book_address = apply_filters(
					'woocommerce_my_account_my_address_formatted_address',
					array(
						'first_name' => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_first_name', true ),
						'last_name'  => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_last_name', true ),
						'company'    => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_company', true ),
						'address_1'  => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_address_1', true ),
						'address_2'  => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_address_2', true ),
						'city'       => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_city', true ),
						'state'      => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_state', true ),
						'postcode'   => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_postcode', true ),
						'country'    => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_country', true ),
					),
					$woo_address_book_customer_id,
					$woo_address_book_name
				);

				$woo_address_book_formatted_address = WC()->countries->get_formatted_address( $woo_address_book_address );
				if ( $woo_address_book_formatted_address ) {
					$billing[] = [
						'book_name'    => $woo_address_book_name,
						'book_address' => wp_kses( $woo_address_book_formatted_address, array( 'br' => array() ) ),
					];
				}
			}
		}

	}

	$shipping = [];

	if ( $shipping_enable ) {

		$woo_address_book_shipping_address = get_user_meta( $woo_address_book_customer_id, 'shipping_address_1', true );

		// Only display if primary addresses are set and not on an edit page.
		if ( ! empty( $woo_address_book_shipping_address ) ) {
			foreach ( $woo_address_book_shipping_address_book as $woo_address_book_name => $woo_address_book_fields ) {

				// Prevent default shipping from displaying here.
				if ( 'shipping' === $woo_address_book_name ) {
					continue;
				}

				$woo_address_book_address           = apply_filters(
					'woocommerce_my_account_my_address_formatted_address',
					array(
						'first_name' => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_first_name', true ),
						'last_name'  => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_last_name', true ),
						'company'    => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_company', true ),
						'address_1'  => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_address_1', true ),
						'address_2'  => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_address_2', true ),
						'city'       => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_city', true ),
						'state'      => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_state', true ),
						'postcode'   => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_postcode', true ),
						'country'    => get_user_meta( $woo_address_book_customer_id, $woo_address_book_name . '_country', true ),
					),
					$woo_address_book_customer_id,
					$woo_address_book_name
				);
				$woo_address_book_formatted_address = WC()->countries->get_formatted_address( $woo_address_book_address );
				if ( $woo_address_book_formatted_address ) {
					$shipping[] = [
						'book_name'    => $woo_address_book_name,
						'book_address' => wp_kses( $woo_address_book_formatted_address, array( 'br' => array() ) ),
					];
				}
			}
		}
	}

	$result = [
		'billing_enable'  => $billing_enable,
		'shipping_enable' => $shipping_enable,
		'billing'         => $billing,
		'shipping'        => $shipping,
	];

	return new WP_REST_Response( $result );
}

function app_builder_address_books_rest_init() {

	$namespace = 'app-builder/v1';
	$route     = 'address-books';

	register_rest_route( $namespace, $route, array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'app_builder_get_address_books',
		'permission_callback' => '__return_true',
	) );
}

add_action( 'rest_api_init', 'app_builder_address_books_rest_init' );