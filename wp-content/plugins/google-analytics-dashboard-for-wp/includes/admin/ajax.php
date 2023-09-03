<?php
/**
 * Handles all admin ajax interactions for the ExactMetrics plugin.
 *
 * @since 6.0.0
 *
 * @package ExactMetrics
 * @subpackage Ajax
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores a user setting for the logged-in WordPress User
 *
 * @access public
 * @since 6.0.0
 */
function exactmetrics_ajax_set_user_setting() {

	// Run a security check first.
	check_ajax_referer( 'exactmetrics-set-user-setting', 'nonce' );

	// Prepare variables.
	$name  = stripslashes( $_POST['name'] );
	$value = stripslashes( $_POST['value'] );

	// Set user setting.
	set_user_setting( $name, $value );

	// Send back the response.
	wp_send_json_success();
	wp_die();

}

add_action( 'wp_ajax_exactmetrics_install_addon', 'exactmetrics_ajax_install_addon' );

/**
 * Installs a ExactMetrics addon.
 *
 * @access public
 * @since 6.0.0
 */
function exactmetrics_ajax_install_addon() {

	// Run a security check first.
	check_ajax_referer( 'exactmetrics-install', 'nonce' );

	if ( ! exactmetrics_can_install_plugins() ) {
		wp_send_json( array(
			'error' => esc_html__( 'You are not allowed to install plugins', 'google-analytics-dashboard-for-wp' ),
		) );
	}

	// Install the addon.
	if ( isset( $_POST['plugin'] ) ) {
		$download_url = $_POST['plugin'];
		global $hook_suffix;

		// Set the current screen to avoid undefined notices.
		set_current_screen();

		// Prepare variables.
		$method = '';
		$url    = add_query_arg(
			array(
				'page' => 'exactmetrics-settings'
			),
			admin_url( 'admin.php' )
		);
		$url    = esc_url( $url );

		// Start output bufferring to catch the filesystem form if credentials are needed.
		ob_start();
		if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, null ) ) ) {
			$form = ob_get_clean();
			echo json_encode( array( 'form' => $form ) );
			wp_die();
		}

		// If we are not authenticated, make it happen now.
		if ( ! WP_Filesystem( $creds ) ) {
			ob_start();
			request_filesystem_credentials( $url, $method, true, false, null );
			$form = ob_get_clean();
			echo json_encode( array( 'form' => $form ) );
			wp_die();
		}

		// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
		exactmetrics_require_upgrader( false );

		// Create the plugin upgrader with our custom skin.
		$installer = new Plugin_Upgrader( $skin = new ExactMetrics_Skin() );
		$installer->install( $download_url );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();
		if ( $installer->plugin_info() ) {
			$plugin_basename = $installer->plugin_info();
			echo json_encode( array( 'plugin' => $plugin_basename ) );
			wp_die();
		}
	}

	// Send back a response.
	echo json_encode( true );
	wp_die();

}

add_action( 'wp_ajax_exactmetrics_activate_addon', 'exactmetrics_ajax_activate_addon' );
/**
 * Activates a ExactMetrics addon.
 *
 * @access public
 * @since 6.0.0
 */
function exactmetrics_ajax_activate_addon() {

	// Run a security check first.
	check_ajax_referer( 'exactmetrics-activate', 'nonce' );

	if ( ! current_user_can( 'activate_plugins' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'You are not allowed to activate plugins', 'google-analytics-dashboard-for-wp' ),
		) );
	}

	// Activate the addon.
	if ( isset( $_POST['plugin'] ) ) {
		if ( isset( $_POST['isnetwork'] ) && $_POST['isnetwork'] ) {
			$activate = activate_plugin( $_POST['plugin'], null, true );
		} else {
			$activate = activate_plugin( $_POST['plugin'] );
		}
		/* Restrict thirt-party redirections on activation */
		delete_transient( '_userfeedback_activation_redirect' );
		if ( is_wp_error( $activate ) ) {
			echo json_encode( array( 'error' => $activate->get_error_message() ) );
			wp_die();
		}

		do_action( 'exactmetrics_after_ajax_activate_addon', sanitize_text_field( $_POST['plugin'] ) );
	}

	echo json_encode( true );
	wp_die();

}

add_action( 'wp_ajax_exactmetrics_deactivate_addon', 'exactmetrics_ajax_deactivate_addon' );
/**
 * Deactivates a ExactMetrics addon.
 *
 * @access public
 * @since 6.0.0
 */
function exactmetrics_ajax_deactivate_addon() {

	// Run a security check first.
	check_ajax_referer( 'exactmetrics-deactivate', 'nonce' );

	if ( ! current_user_can( 'deactivate_plugins' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'You are not allowed to deactivate plugins', 'google-analytics-dashboard-for-wp' ),
		) );
	}

	// Deactivate the addon.
	if ( isset( $_POST['plugin'] ) ) {
		if ( isset( $_POST['isnetwork'] ) && $_POST['isnetwork'] ) {
			$deactivate = deactivate_plugins( $_POST['plugin'], false, true );
		} else {
			$deactivate = deactivate_plugins( $_POST['plugin'] );
		}
	}

	echo json_encode( true );
	wp_die();
}

/**
 * Called whenever a notice is dismissed in ExactMetrics or its Addons.
 *
 * Updates a key's value in the options table to mark the notice as dismissed,
 * preventing it from displaying again
 *
 * @access public
 * @since 6.0.0
 */
function exactmetrics_ajax_dismiss_notice() {

	// Run a security check first.
	check_ajax_referer( 'exactmetrics-dismiss-notice', 'nonce' );

	// Deactivate the notice
	if ( isset( $_POST['notice'] ) ) {
		// Init the notice class and mark notice as deactivated
		ExactMetrics()->notices->dismiss( $_POST['notice'] );

		// Return true
		echo json_encode( true );
		wp_die();
	}

	// If here, an error occurred
	echo json_encode( false );
	wp_die();

}

add_action( 'wp_ajax_exactmetrics_ajax_dismiss_notice', 'exactmetrics_ajax_dismiss_notice' );

/**
 * Dismiss SEMRush CTA
 *
 * @access public
 * @since 7.12.3
 */
function exactmetrics_ajax_dismiss_semrush_cta() {
	check_ajax_referer( 'mi-admin-nonce', 'nonce' );

	if ( ! current_user_can( 'exactmetrics_save_settings' ) ) {
		return;
	}

	// Deactivate the notice
	if ( update_option( 'exactmetrics_dismiss_semrush_cta', 'yes' ) ) {
		// Return true
		wp_send_json( array(
			'dismissed' => 'yes',
		) );
		wp_die();
	}

	// If here, an error occurred
	wp_send_json( array(
		'dismissed' => 'no',
	) );
	wp_die();
}

add_action( 'wp_ajax_exactmetrics_vue_dismiss_semrush_cta', 'exactmetrics_ajax_dismiss_semrush_cta' );

/**
 * Get the sem rush cta dismiss status value
 */
function exactmetrics_get_sem_rush_cta_status() {
	check_ajax_referer( 'mi-admin-nonce', 'nonce' );

	$dismissed_cta = get_option( 'exactmetrics_dismiss_semrush_cta', 'no' );

	wp_send_json( array(
		'dismissed' => $dismissed_cta,
	) );
}

add_action( 'wp_ajax_exactmetrics_get_sem_rush_cta_status', 'exactmetrics_get_sem_rush_cta_status' );

function exactmetrics_handle_get_plugin_info() {

    $auth = ExactMetrics()->auth;

    //  Authenticate with public key
    $key = sanitize_text_field($_REQUEST['key']);

    $site_key = is_network_admin() ? $auth->get_network_key() : $auth->get_key();

    if ( !hash_equals( $site_key, $key ) ) {
        wp_send_json_error([
            'error'     => __( 'Invalid site key.', 'google-analytics-dashboard-for-wp' )
        ], 401);
    }

    $v4 = is_network_admin() ? $auth->get_network_v4_id() :  $auth->get_v4_id();
    $has_secret = is_network_admin() ?
        !empty( $auth->get_network_measurement_protocol_secret() ) :
        !empty( $auth->get_measurement_protocol_secret() );

    wp_send_json([
        'v4'                => $v4,
        'has_mp_secret'     => $has_secret,
        'plugin_version'    => ExactMetrics()->version
    ]);
}

add_action( 'wp_ajax_nopriv_exactmetrics_get_plugin_info', 'exactmetrics_handle_get_plugin_info' );

/**
 * User journey report show demo report for not licensed.
 *
 * @return void
 * @since 8.16
 */
function exactmetrics_user_journey_demo_report_ajax() {
	$report = array();

	$sources   = array( 'google', 'newsletter', 'billboard' );
	$mediums   = array( 'cpc', 'banner', 'email' );
	$campaigns = array( 'campaign-name', 'slogan', 'promo-code' );

	for ( $i = 1; $i <= 13; $i ++ ) {
		$rand_key = array_rand( $sources );

		$report[] = array(
			'transaction_id' => wp_rand( 12, 30 ),
			'steps'          => wp_rand( 3, 8 ),
			'order_total'    => wp_rand( 10, 50 ),
			'utm_source'     => $sources[ $rand_key ],
			'utm_medium'     => $mediums[ $rand_key ],
			'utm_campaign'   => $campaigns[ $rand_key ],
			'purchase_date'  => '--',
		);
	}

	wp_send_json( array( 'items' => $report, 'demo'  => true ) );
}

$license_type = ExactMetrics()->license->get_license_type();

// If it is not a pro licensed.
if ( ! ( $license_type === 'master' || $license_type === 'pro' ) ) {
	add_action( 'wp_ajax_exactmetrics_user_journey_report', 'exactmetrics_user_journey_demo_report_ajax' );
	add_action( 'wp_ajax_exactmetrics_user_journey_report_filter_params', '__return_false' );
}
