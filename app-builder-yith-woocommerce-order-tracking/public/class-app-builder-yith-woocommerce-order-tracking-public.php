<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 *
 * @package    App_Builder_Yith_Woocommerce_Order_Tracking
 * @subpackage App_Builder_Yith_Woocommerce_Order_Tracking/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    App_Builder_Yith_Woocommerce_Order_Tracking
 * @subpackage App_Builder_Yith_Woocommerce_Order_Tracking/public
 * @author     Ngoc Dang <ngocdt@rnlab.io>
 */
class App_Builder_Yith_Woocommerce_Order_Tracking_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register rest routes.
	 */
	public function register_rest_routes() {
		register_rest_route(
			'app-builder/v1',
			'/order-tracking',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_order_tracking' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Get order tracking.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_order_tracking( $request ) {
		$order_id = absint( $request->get_param( 'order_id' ) );
		$email    = sanitize_email( $request->get_param( 'email' ) );

		// Check YITH_WooCommerce_Order_Tracking_Premium class exists.
		if ( ! class_exists( 'YITH_WooCommerce_Order_Tracking_Premium' ) ) {
			return new WP_REST_Response( array( 'error' => 'YITH_WooCommerce_Order_Tracking_Premium class not found' ), 404 );
		}

		$order = wc_get_order( $order_id );
		// Check order exists.
		if ( ! is_object( $order ) ) {
			return new WP_REST_Response( array( 'error' => 'Order not found' ), 404 );
		}

		$class = new YITH_WooCommerce_Order_Tracking_Premium();

		if ( apply_filters( 'yith_ywot_is_order_shipped', true, $order_id ) && ! $class->is_order_shipped( $order_id ) ) {
			return new WP_REST_Response( array( 'error' => 'Order not shipped' ), 404 );
		}

		if ( strtolower( $order->get_billing_email() ) === strtolower( $email ) ) {

			$data   = YITH_Tracking_Data::get( $order );
			$output = 'frontend';

			$message = ( 'email' === $output ) ? $class->get_picked_up_message( $order, '', 1 ) : $class->get_picked_up_message( $order, '' );
			$url     = $class->get_track_url( $order );

			return new WP_REST_Response(
				array(
					'message' => $message,
					'url'     => $url,
					'data'    => array(
						'tracking_code'           => $data->get_tracking_code(),
						'tracking_postcode'       => $data->get_tracking_postcode(),
						'carrier_id'              => $data->get_carrier_id(),
						'pickup_date'             => $data->get_pickup_date(),
						'estimated_delivery_date' => $data->get_estimated_delivery_date(),
					),
				),
				200
			);

		} else {
			return new WP_REST_Response( array( 'error' => 'The email does not match Order ID' ), 404 );
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in App_Builder_Yith_Woocommerce_Order_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The App_Builder_Yith_Woocommerce_Order_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/app-builder-yith-woocommerce-order-tracking-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in App_Builder_Yith_Woocommerce_Order_Tracking_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The App_Builder_Yith_Woocommerce_Order_Tracking_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/app-builder-yith-woocommerce-order-tracking-public.js', array( 'jquery' ), $this->version, false );
	}

}
