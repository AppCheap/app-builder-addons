<?php
/**
 * Plugin Name: App Builder - Smart Coupon Addons
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-smart-coupon-addons
 * Domain Path: /languages/
 * Description: App Builder get smart coupon via REST API
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

function app_builder_app_builder_smart_coupon_addons_domain() {
	load_plugin_textdomain( 'app-builder-do-shortcode-addons', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_app_builder_smart_coupon_addons_domain' );

function app_builder_smart_coupon_list( $request ) {
	if ( ! class_exists( 'WC_Smart_Coupons' ) ) {
		return [];
	}

	$wc_sc_display_coupons = WC_SC_Display_Coupons::get_instance();

	$max_coupon_to_show = get_option( 'wc_sc_setting_max_coupon_to_show', 5 );
	$max_coupon_to_show = apply_filters( 'wc_sc_max_coupon_to_show', $max_coupon_to_show, array( 'source' => $wc_sc_display_coupons ) );
	$max_coupon_to_show = absint( $max_coupon_to_show );

	$coupons = array();
	if ( $max_coupon_to_show > 0 ) {
		$coupons = $wc_sc_display_coupons->sc_get_available_coupons_list( array() );
	}

	if ( empty( $coupons ) ) {
		return [];
	}

	$show_coupon_description = get_option( 'smart_coupons_show_coupon_description', 'no' );

	$coupons_applied = ( is_object( WC()->cart ) && is_callable( array(
			WC()->cart,
			'get_applied_coupons'
		) ) ) ? WC()->cart->get_applied_coupons() : array();

	$data = [];

	foreach ( $coupons as $code ) {

		if ( in_array( strtolower( $code->post_title ), array_map( 'strtolower', $coupons_applied ), true ) ) {
			continue;
		}

		$coupon_id = wc_get_coupon_id_by_code( $code->post_title );

		if ( empty( $coupon_id ) ) {
			continue;
		}

		if ( $max_coupon_to_show <= 0 ) {
			break;
		}

		$coupon  = new WC_Coupon( $code->post_title );
		$invalid = false;

		// Force show coupon like minimum order total
		if ( 'woocommerce_before_my_account' !== current_filter() && ! $coupon->is_valid() ) {

			// Filter to allow third party developers to show coupons which are invalid due to cart requirements like minimum order total or products.
			$wc_sc_force_show_coupon = apply_filters( 'wc_sc_force_show_invalid_coupon', false, array( 'coupon' => $coupon ) );
			if ( false === $wc_sc_force_show_coupon ) {
				$invalid = true;
			}
		}

		if ( $wc_sc_display_coupons->is_wc_gte_30() ) {
			if ( ! is_object( $coupon ) || ! is_callable( array( $coupon, 'get_id' ) ) ) {
				continue;
			}
			$coupon_id = $coupon->get_id();
			if ( empty( $coupon_id ) ) {
				continue;
			}
			$is_free_shipping = ( $coupon->get_free_shipping() ) ? 'yes' : 'no';
			$discount_type    = $coupon->get_discount_type();
			$expiry_date      = $coupon->get_date_expires();
			$coupon_code      = $coupon->get_code();
		} else {
			$coupon_id        = ( ! empty( $coupon->id ) ) ? $coupon->id : 0;
			$is_free_shipping = ( ! empty( $coupon->free_shipping ) ) ? $coupon->free_shipping : '';
			$discount_type    = ( ! empty( $coupon->discount_type ) ) ? $coupon->discount_type : '';
			$expiry_date      = ( ! empty( $coupon->expiry_date ) ) ? $coupon->expiry_date : '';
			$coupon_code      = ( ! empty( $coupon->code ) ) ? $coupon->code : '';
		}

		$coupon_amount = $wc_sc_display_coupons->get_amount( $coupon, true );

		$is_show_zero_amount_coupon = true;

		if ( ( empty( $coupon_amount ) ) && ( ( ! empty( $discount_type ) && ! in_array( $discount_type, array(
						'free_gift',
						'smart_coupon'
					), true ) ) || ( 'yes' !== $is_free_shipping ) ) ) {
			if ( 'yes' !== $is_free_shipping ) {
				$is_show_zero_amount_coupon = false;
			}
		}

		$is_show_zero_amount_coupon = apply_filters( 'show_zero_amount_coupon', $is_show_zero_amount_coupon, array( 'coupon' => $coupon ) );

		if ( false === $is_show_zero_amount_coupon ) {
			continue;
		}

		if ( $wc_sc_display_coupons->is_wc_gte_30() && $expiry_date instanceof WC_DateTime ) {
			$expiry_date = ( is_callable( array(
				$expiry_date,
				'getTimestamp'
			) ) ) ? $expiry_date->getTimestamp() : null;
		} elseif ( ! is_int( $expiry_date ) ) {
			$expiry_date = strtotime( $expiry_date );
		}

		if ( ! empty( $expiry_date ) && is_int( $expiry_date ) ) {
			$expiry_time = (int) get_post_meta( $coupon_id, 'wc_sc_expiry_time', true );
			if ( ! empty( $expiry_time ) ) {
				$expiry_date += $expiry_time; // Adding expiry time to expiry date.
			}
		}

		if ( empty( $discount_type ) || ( ! empty( $expiry_date ) && time() > $expiry_date ) ) {
			continue;
		}

		$coupon_post = get_post( $coupon_id );

		$coupon_data = $wc_sc_display_coupons->get_coupon_meta_data( $coupon );

		$coupon_type = ( ! empty( $coupon_data['coupon_type'] ) ) ? $coupon_data['coupon_type'] : '';

		if ( 'yes' === $is_free_shipping ) {
			if ( ! empty( $coupon_type ) ) {
				$coupon_type .= __( ' & ', 'woocommerce-smart-coupons' );
			}
			$coupon_type .= __( 'Free Shipping', 'woocommerce-smart-coupons' );
		}

		$coupon_description = '';
		if ( ! empty( $coupon_post->post_excerpt ) && 'yes' === $show_coupon_description ) {
			$coupon_description = $coupon_post->post_excerpt;
		}

		$is_percent = $wc_sc_display_coupons->is_percent_coupon( array( 'coupon_object' => $coupon ) );

		$args = array(
			'coupon_object'      => $coupon,
			'coupon_amount'      => $coupon_amount,
			'amount_symbol'      => html_entity_decode( ( true === $is_percent ) ? '%' : get_woocommerce_currency_symbol() ),
			'discount_type'      => wp_strip_all_tags( $coupon_type ),
			'coupon_description' => ( ! empty( $coupon_description ) ) ? $coupon_description : wp_strip_all_tags( $wc_sc_display_coupons->generate_coupon_description( array( 'coupon_object' => $coupon ) ) ),
			'coupon_code'        => $coupon_code,
			'coupon_expiry'      => ( ! empty( $expiry_date ) ) ? $wc_sc_display_coupons->get_expiration_format( $expiry_date ) : __( 'Never expires', 'woocommerce-smart-coupons' ),
			'thumbnail_src'      => $wc_sc_display_coupons->get_coupon_design_thumbnail_src(
				array(
					'coupon_object' => $coupon,
				)
			),
			'classes'            => 'apply_coupons_credits',
			'is_percent'         => $is_percent,
			'is_invalid'         => $invalid,
		);

		$data[] = $args;

		$max_coupon_to_show --;

	}

	return $data;
}

function app_builder_smart_coupon_rest_init() {

	$namespace = 'app-builder/v1';
	$route     = 'smart-coupon-list';

	register_rest_route( $namespace, $route, array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'app_builder_smart_coupon_list',
		'permission_callback' => '__return_true',
	) );
}

add_action( 'rest_api_init', 'app_builder_smart_coupon_rest_init' );
