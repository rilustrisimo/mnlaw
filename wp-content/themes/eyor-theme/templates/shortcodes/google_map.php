<?php
/**
 * Shortcode [google_map] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string $address
 * @var string $coordinates
 * @var string $zoom
 * @var string $height
 * @var string $width_mode
 * @var string $css_class
 * @var string $view
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

$instance_id = qed_di( 'shortcodes_helper' )->generate_id();
$element_id = 'googleMapCanvas' . $instance_id;

$config_json = wp_json_encode( array(
	'coordinates' => explode( ',', $coordinates ),
	'zoom' => (int) $zoom,
	// 'MapTypeId' => 'satellite',
	'address' => $address,
	'height' => $height,
	'element_id' => $element_id,
	'full_width' => 'full-width' == $width_mode,
	'is_reset_map_fix_for_bootstrap_tabs_accrodion' => true,
) );

$google_map_api_url = 'https://maps.google.com/maps/api/js?sensor=true';
$google_map_api_key = qed_get_option( 'google_map_api_key' );
if ( $google_map_api_key ) {
	$google_map_api_url .= '&key=' . urlencode( $google_map_api_key );
}
wp_enqueue_script( 'googleMapApi', apply_filters( 'qed_google_map_api_url', $google_map_api_url ), array(), null, true );

SD_Js_Client_Script::add_script( 'initGoogleMap' . $instance_id, 'Theme.initGoogleMap(' . $config_json . ');', SD_Js_Client_Script::POS_FOOTER );

printf( '<div id="%s" class="google-map%s"></div>',
	esc_attr( $element_id ),
	$css_class ? esc_attr( ' ' . $css_class ) : ''
);
