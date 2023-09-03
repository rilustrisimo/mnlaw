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

$element_id = 'googleMapCanvas' . QEDDTP()->shortcodes_helper()->generate_id();

global $__swishdesign_shortcode_googme_map_config;
$__swishdesign_shortcode_googme_map_config[] = array(
	'coordinates' => explode( ',', $coordinates ),
	'zoom' => (int) $zoom,
	'address' => $address,
	'height' => $height,
	'element_id' => $element_id,
	'full_width' => 'full-width' == $width_mode,
	'is_reset_map_fix_for_bootstrap_tabs_accrodion' => true,
);

printf( '<div id="%s" class="google-map%s"></div>',
	esc_attr( $element_id ),
	$css_class ? esc_attr( ' ' . $css_class ) : ''
);

if ( ! function_exists( '_gmap_sh_action_wp_print_footer_scripts_google_map' ) ) {
	function _gmap_sh_action_wp_print_footer_scripts_google_map(){
		global $__swishdesign_shortcode_googme_map_config;
		if ( ! $__swishdesign_shortcode_googme_map_config ) {
			return;
		}
		$list_configs_json = wp_json_encode($__swishdesign_shortcode_googme_map_config);
		echo <<<MAPSCRIPT
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript">
	;var _tmpFun = function(cfg){
		if ( 'undefined' == typeof(cfg) ) {
			return;
		}

		var mapElement = document.getElementById(cfg.element_id);

		if ( ! mapElement ){
			return;
		}

		var jMap = jQuery(mapElement);
		jMap.height(cfg.height);

		if (cfg.full_width) {
			var on_resize_hander = function(){
				jMap.width(jQuery(window).outerWidth())
					.offset({left:0});
				if (map) {
					//google.maps.event.trigger(map, 'resize');
					if (mapLang) {
						map.setCenter(mapLang);
					}
				}
			};
			on_resize_hander();
			jQuery(window).on('resize', on_resize_hander);
		}

		var mapLang = new google.maps.LatLng(parseFloat(cfg.coordinates[0]), parseFloat(cfg.coordinates[1])),
			map = new google.maps.Map(mapElement,{
				scaleControl: true,
				center: mapLang,
				zoom: cfg.zoom,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				scrollwheel: false
			}),
			marker = new google.maps.Marker({
				map: map,
				position: map.getCenter()
			});

		if (cfg.address) {
			var infowindow = new google.maps.InfoWindow();
			infowindow.setContent(cfg.address);
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map, marker);
			});
		}

		// fix display map in bootstrap tabs and accordion
		if ( cfg.is_reset_map_fix_for_bootstrap_tabs_accrodion ) {
			jQuery(document).on('shown.bs.collapse shown.bs.tab', '.panel-collapse, a[data-toggle=\"tab\"]', function () {
				google.maps.event.trigger(map, 'resize');
				map.setCenter(mapLang);
			});
		}
	};var __mapsList = {$list_configs_json};for (var i in __mapsList){ _tmpFun(__mapsList[i]);}delete(_tmpFun);
</script>
MAPSCRIPT;
	}
	add_action( 'wp_print_footer_scripts', '_gmap_sh_action_wp_print_footer_scripts_google_map' );
}
