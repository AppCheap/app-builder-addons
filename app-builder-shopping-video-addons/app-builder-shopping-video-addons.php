<?php
/**
 * Plugin Name: App Builder - Shopping Video Addons
 * Plugin URI: https://appcheap.io/docs/app-builder-shopping-video-addons
 * Text Domain: app-builder-shopping-video-addons
 * Domain Path: /languages/
 * Description: Show shopping video like Tiktok and Youtube short
 * Author: Appcheap
 * Version: 1.0.0
 * Author URI: https://appcheap.io
 */

defined( 'ABSPATH' ) || exit;

const APP_BUILDER_SHOPPING_VIDEO_ADDONS             = 'app_builder_shopping_video_addons';
const APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN = 'app-builder-shopping-video-addons';

global $app_builder_db_version;
$app_builder_db_version                             = '1.0';

/**
 * Load language for plugin
 */
function app_builder_shopping_video_addons_text_domain() {
	load_plugin_textdomain( APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'app_builder_shopping_video_addons_text_domain' );

/**
 * Add tab setting video
 */
add_filter( 'woocommerce_product_data_tabs', 'app_builder_shopping_video_custom_product_data_tab', 99, 1 );

function app_builder_shopping_video_custom_product_data_tab( $product_data_tabs ) {
	$product_data_tabs[ APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ] = array(
		'label'  => __( 'Shopping Video', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
		'target' => APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN,
	);

	return $product_data_tabs;
}

/**
 * Add fields config in tab video shopping
 */
add_action( 'woocommerce_product_data_panels', 'app_builder_shopping_video_custom_product_data_fields' );
function app_builder_shopping_video_custom_product_data_fields() {
	global $woocommerce, $post;
	$name = '_' . APP_BUILDER_SHOPPING_VIDEO_ADDONS;
	?>
    <!-- id below must match target registered in above app_builder_shopping_video_custom_product_data_tab function -->
    <div id="<?php echo APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ?>" class="panel woocommerce_options_panel">
		<?php
		woocommerce_wp_text_input(
			array(
				'id'          => $name . '_video_url',
				'label'       => __( 'Video URL', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
				'description' => __( 'My Custom Field Description', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
				'default'     => '',
				'desc_tip'    => true,
				'value'       => get_post_meta( get_the_ID(), $name . '_video_url', true )
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => $name . '_video_name',
				'label'       => __( 'Video Name', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
				'description' => __( 'My Custom Field Description', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
				'default'     => '',
				'desc_tip'    => true,
				'value'       => get_post_meta( get_the_ID(), $name . '_video_name', true )
			)
		);
		woocommerce_wp_textarea_input(
			array(
				'id'          => $name . '_video_description',
				'label'       => __( 'Video Description', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
				'description' => __( 'My Custom Field Description', APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ),
				'default'     => '',
				'desc_tip'    => true,
				'value'       => get_post_meta( get_the_ID(), $name . '_video_description', true )
			)
		);
		?>
    </div>
	<?php
}

/**
 * Save data for fields config video
 */
add_action( 'woocommerce_process_product_meta', 'app_builder_shopping_video_woocommerce_process_product_meta_fields_save' );

function app_builder_shopping_video_woocommerce_process_product_meta_fields_save( $post_id ) {
	$input_name = '_' . APP_BUILDER_SHOPPING_VIDEO_ADDONS;

	$url         = $_POST[ $input_name . '_video_url' ] ?? '';
	$name        = $_POST[ $input_name . '_video_name' ] ?? '';
	$description = $_POST[ $input_name . '_video_description' ] ?? '';

	update_post_meta( $post_id, $input_name . '_video_url', $url );
	update_post_meta( $post_id, $input_name . '_video_name', $name );
	update_post_meta( $post_id, $input_name . '_video_description', $description );
}

/**
 * API like video
 *
 * @param $request
 *
 * @return WP_Error|WP_REST_Response
 */
function app_builder_shopping_video_like_video( $request ) {
	global $wpdb;
	$table_name = $wpdb->prefix . APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_likes';

	if ( empty( $request->get_param( 'post_id' ) ) ) {
		return new WP_Error( 'error', __( "Post ID not provider.", APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ) );
	}

	// Type is one of [positive, negative]
	$type             = $request->get_param( 'type' ) ?? 'positive';
	$post_id          = (int) $request->get_param( 'post_id' );
	$user_id          = get_current_user_id();
	$guest_like_video = apply_filters( APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_guess_like_video', true, $request );
	$likes_meta       = get_post_meta( $post_id, APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_likes', true );
	$likes            = empty( $likes_meta ) ? 0 : (int) $likes_meta;
	$like_type        = 'like';

	// Do not allow user like video when logged out
	if ( $user_id == 0 && ! $guest_like_video ) {
		return new WP_Error( 'error', __( "User not logged.", APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN ) );
	}

	if ( $user_id == 0 && $guest_like_video ) {
		if ( $type == 'positive' ) {
			$likes ++;
		} else {
			$likes --;
			$like_type = 'unlike';
		}
	} else {
		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND user_id = %d", $post_id, $user_id
			)
		);

		if ( $total > 0 ) {
			// The user unlike
			$likes --;
			$like_type = 'unlike';
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM " . $table_name . " WHERE user_id = %d AND post_id = %d",
					$user_id,
					$post_id
				)
			);
		} else {
			$likes ++;
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO " . $table_name . " (`user_id`, `post_id`) VALUES (%d, %s)",
					$user_id,
					$post_id,
				)
			);
		}
	}

	update_post_meta( $post_id, APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_likes', $likes );

	$result = [
		'likes' => $likes,
		'type'  => $like_type,
	];

	return new WP_REST_Response( $result );
}

function app_builder_shopping_video_rest_init() {
	$namespace = APP_BUILDER_SHOPPING_VIDEO_ADDONS_TEXT_DOMAIN . '/v1';
	$route     = 'likes';

	register_rest_route( $namespace, $route, array(
		'methods'  => WP_REST_Server::CREATABLE,
		'callback' => 'app_builder_shopping_video_like_video',
		'permission_callback' => '__return_true',
	) );
}

add_action( 'rest_api_init', 'app_builder_shopping_video_rest_init' );

/**
 * Add table store user like when the plugin active
 */
function app_builder_shopping_video_install() {
	global $wpdb;
	global $app_builder_db_version;

	$table_name = $wpdb->prefix . APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_likes';

	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		post_id mediumint(9) NOT NULL,
		user_id mediumint(9) NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	add_option( APP_BUILDER_SHOPPING_VIDEO_ADDONS, $app_builder_db_version );
}

register_activation_hook( __FILE__, 'app_builder_shopping_video_install' );

/**
 *
 * Add likes and liked for each product
 *
 * @param $data
 * @param $post
 * @param $request
 *
 * @return mixed
 */
function app_builder_shopping_video_addons_prepare_product_object( $data, $post, $request ) {
	global $wpdb;

	if ( ! isset( $data['id'] ) ) {
		return $data;
	}

	$meta_data  = $data['meta_data'] ?? [];
	$user_id    = $request->get_param( 'user_id' ) ? (int) $request->get_param( 'user_id' ) : 0;
	$table_name = $wpdb->prefix . APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_likes';
	$post_id    = (int) $data['id'];

	$likes = get_post_meta( $post_id, APP_BUILDER_SHOPPING_VIDEO_ADDONS . '_likes', true );

	$meta_data[] = [
		'key'   => 'app_builder_shopping_video_likes',
		'value' => empty( $likes ) ? 0 : (int) $likes,
	];

	if ( $user_id > 0 ) {

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND user_id = %d", [ $post_id, $user_id ]
			)
		);

		$meta_data[] = [
			'key'   => 'app_builder_shopping_video_liked',
			'value' => $total > 0 ? 'true' : 'false',
		];

	}

	$data['meta_data'] = $meta_data;

	return $data;
}

add_filter( 'app_builder_prepare_product_object', 'app_builder_shopping_video_addons_prepare_product_object', 999, 3 );

/**
 * Support filter product by rand
 *
 * @param $query_params
 *
 * @return array
 */
function add_rand_orderby_rest_product_collection_params( $query_params ) {
	$query_params['orderby']['enum'][] = 'rand';

	return $query_params;
}

add_filter( 'rest_product_collection_params', 'add_rand_orderby_rest_product_collection_params' );