<?php

/**
 * Plugin Name: App Builder - Cancel Order
 * Plugin URI: https://appcheap.io/docs
 * Text Domain: app-builder-wc-cancel-order
 * Domain Path: /languages/
 * Description: Support Cancel Order
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined('ABSPATH') || exit;

function app_builder_wc_cancel_order_domain()
{
    load_plugin_textdomain('app-builder-wc-cancel-order', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

add_action('plugins_loaded', 'app_builder_wc_cancel_order_domain');

/**
 * API post cancel order
 *
 * @param $request
 *
 * @return WP_Error|WP_REST_Response
 */


function app_builder_get_cancel_order($request)
{
    $settings = get_option('wc_cancel_settings');
    return new WP_REST_Response([
        $settings
    ]);
}

function app_builder_post_cancel_order()
{
    $settings = get_option('wc_cancel_settings');
    $wc_cancel_order = WC_Cancel_Order::instance();
    $customer_id     = get_current_user_id();
    if ($customer_id == 0) {
        return new WP_Error(
            'no_current_login ',
            __('User not login.', "app-builder"),
            array(
                'status' => 403,
            )
        );
    }
    $clean_str = $wc_cancel_order->clean_str($_REQUEST['order_id']);
    $order_id = isset($_REQUEST['order_id']) ? $clean_str : 0;

    if (isset($order_id)) {
        $order = wc_get_order($order_id);
        if (is_a($order, 'WC_Order')) {
            $order_id_by_key = 0;

            $status_key = $wc_cancel_order->get_status_key($order->get_status());

            $order_key = isset($_REQUEST['order_key']) ? $_REQUEST['order_key'] : '';

            $check_order_customer = $wc_cancel_order->check_order_customer($order);

            $user_has_role = $wc_cancel_order->user_has_role();

            $clean_str = $wc_cancel_order->clean_str($_REQUEST['additional_details']);

            if ($order_key != '') {
                $details = new WC_Cancel_Order_Details($order_key, $settings);
                $order_id_by_key = $details->get_order_id($order_key);
            }
            if (($user_has_role && $check_order_customer) || ($user_has_role && $order_id_by_key == $order_id)) {
                if (isset($_REQUEST['additional_details'])) {
                    update_post_meta($order_id, '_wc_cancel_additional_txt', $clean_str);
                }
                if (is_array($settings['req-status']) && in_array($status_key, $settings['req-status'])) {
                    $wc_cancel_order->add_req($order_id);
                    $order->update_status('cancel-request', __('Order Status updated by Wc Cancel Order.', 'wc-cancel-order'));
                    do_action('wc_cancel_request', $order_id);
                    return new WP_REST_Response([
                        'success' => true,
                    ]);
                }
            }
        }
    }
}


function app_builder_wc_cancel_order_init()
{

    $namespace = 'app-builder/v1';
    $route     = 'cancel-order';

    register_rest_route($namespace, $route, array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'app_builder_get_cancel_order',
        'permission_callback' => '__return_true',
    ));

    register_rest_route($namespace, $route . '/post', array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'app_builder_post_cancel_order',
        'permission_callback' => '__return_true',
    ));
}

add_action('rest_api_init', 'app_builder_wc_cancel_order_init');
