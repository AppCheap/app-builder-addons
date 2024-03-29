<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 *
 * @package    App_Builder_Yith_Woocommerce_Badges_Management
 * @subpackage App_Builder_Yith_Woocommerce_Badges_Management/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    App_Builder_Yith_Woocommerce_Badges_Management
 * @subpackage App_Builder_Yith_Woocommerce_Badges_Management/public
 * @author     Ngoc Dang <ngocdt@rnlab.io>
 */
class App_Builder_Yith_Woocommerce_Badges_Management_Public {

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
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in App_Builder_Yith_Woocommerce_Badges_Management_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The App_Builder_Yith_Woocommerce_Badges_Management_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/app-builder-yith-woocommerce-badges-management-public.css', array(), $this->version, 'all' );
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
		 * defined in App_Builder_Yith_Woocommerce_Badges_Management_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The App_Builder_Yith_Woocommerce_Badges_Management_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/app-builder-yith-woocommerce-badges-management-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add badge to product.
	 *
	 * @param  array           $response The product response.
	 * @param  WC_Product      $p The product.
	 * @param  WP_REST_Request $request The request.
	 *
	 * @return array
	 */
	public function woocommerce_rest_prepare_product_object( $response, $p, $request ) {
		$data      = $response->get_data();
		$meta_data = $data['meta_data'];

		$product_id = $p->get_id();

		// Get wc product.
		$product = wc_get_product( $product_id );

		// Add badge to product.
		$badges_to_show = yith_wcbm_get_product_badges( $product );
		$badges_to_show = apply_filters( 'yith_wcbm_badges_to_show_on_product', $badges_to_show, $product );

		$badges = array();

		foreach ( $badges_to_show as $badge_id ) {
			$badge_id = yith_wcbm_wpml_translate_badge_id( $badge_id );
			$badge    = yith_wcbm_get_badge_object( $badge_id );

			if ( $badge && $badge->is_enabled() ) {
				$data = $badge->get_data();
				// If badge is image, get image url.
				if ( $badge->get_type() === 'image' ) {
					$data['image_url'] = $badge->get_image_url();
				}
				$badges[] = $data;
			}
		}

		$meta_data[] = array(
			'id'    => 'mobile_yith_wcbm_badges',
			'key'   => 'mobile_yith_wcbm_badges',
			'value' => $badges,
		);

		$response->data['meta_data'] = $meta_data;

		return $response;
	}

	/**
	 * Add badge to product variable.
	 *
	 * @param  array      $meta_data The meta data.
	 * @param  WC_Product $product The product.
	 *
	 * @return array
	 */
	public function app_builder_rest_prepare_product_variable_object_meta( $meta_data, $product ) {
		// Add badge to product.
		$badges_to_show = yith_wcbm_get_product_badges( $product );
		$badges_to_show = apply_filters( 'yith_wcbm_badges_to_show_on_product', $badges_to_show, $product );

		$badges = array();

		foreach ( $badges_to_show as $badge_id ) {
			$badge_id = yith_wcbm_wpml_translate_badge_id( $badge_id );
			$badge    = yith_wcbm_get_badge_object( $badge_id );

			if ( $badge && $badge->is_enabled() ) {
				$data = $badge->get_data();
				// If badge is image, get image url.
				if ( $badge->get_type() === 'image' ) {
					$data['image_url'] = $badge->get_image_url();
				}
				$badges[] = $data;
			}
		}

		$meta_data[] = array(
			'id'    => 'mobile_yith_wcbm_badges',
			'key'   => 'mobile_yith_wcbm_badges',
			'value' => $badges,
		);

		return $meta_data;
	}

	// public function yith_wcbm_badge_metabox_fields( $meta_box ) {
	// 	$meta_box['scale_on_mobile_app'] = array(
	// 		'name'            => 'yith_wcbm_badge[_scale_on_mobile_app]',
	// 		'label'           => __( 'Scale on mobile app', 'yith-woocommerce-badges-management' ),
	// 		'desc'            => __( 'Set the badge scale on mobile view.', 'yith-woocommerce-badges-management' ),
	// 		'type'            => 'number',
	// 		'extra_row_class' => 'yith-wcbm-visible-if-text yith-wcbm-visible-if-image yith-wcbm-visible-if-css yith-wcbm-visible-if-advanced',
	// 		'min'             => 0,
	// 		'std'             => 1,
	// 		'step'            => 0.01,
	// 	);
	// 	return $meta_box;
	// }

}
