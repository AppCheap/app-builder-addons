<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://appcheap.io
 * @since      1.0.0
 *
 * @package    App_Builder_Yith_Woocommerce_Badges_Management
 * @subpackage App_Builder_Yith_Woocommerce_Badges_Management/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    App_Builder_Yith_Woocommerce_Badges_Management
 * @subpackage App_Builder_Yith_Woocommerce_Badges_Management/includes
 * @author     Ngoc Dang <ngocdt@rnlab.io>
 */
class App_Builder_Yith_Woocommerce_Badges_Management_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'app-builder-yith-woocommerce-badges-management',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
