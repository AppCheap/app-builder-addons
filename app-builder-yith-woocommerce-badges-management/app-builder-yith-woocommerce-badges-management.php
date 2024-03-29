<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://appcheap.io
 * @since             1.0.0
 * @package           App_Builder_Yith_Woocommerce_Badges_Management
 *
 * @wordpress-plugin
 * Plugin Name:       App Builder - YITH WooCommerce Badge Management Premium
 * Plugin URI:        https://appcheap.io
 * Description:       Support YITH WooCommerce Badge Management Premium on Cirilla App
 * Version:           1.0.0
 * Author:            Ngoc Dang
 * Author URI:        https://appcheap.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       app-builder-yith-woocommerce-badges-management
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'APP_BUILDER_YITH_WOOCOMMERCE_BADGES_MANAGEMENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-app-builder-yith-woocommerce-badges-management-activator.php
 */
function activate_app_builder_yith_woocommerce_badges_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-app-builder-yith-woocommerce-badges-management-activator.php';
	App_Builder_Yith_Woocommerce_Badges_Management_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-app-builder-yith-woocommerce-badges-management-deactivator.php
 */
function deactivate_app_builder_yith_woocommerce_badges_management() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-app-builder-yith-woocommerce-badges-management-deactivator.php';
	App_Builder_Yith_Woocommerce_Badges_Management_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_app_builder_yith_woocommerce_badges_management' );
register_deactivation_hook( __FILE__, 'deactivate_app_builder_yith_woocommerce_badges_management' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-app-builder-yith-woocommerce-badges-management.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_app_builder_yith_woocommerce_badges_management() {

	$plugin = new App_Builder_Yith_Woocommerce_Badges_Management();
	$plugin->run();

}
run_app_builder_yith_woocommerce_badges_management();
