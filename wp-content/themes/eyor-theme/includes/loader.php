<?php
/**
 * Theme bootstrap file that defines classes loaders.
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

$loader_path = dirname( __FILE__ );
if ( ! defined( '_THEME_VENDOR_PATH_' ) ) {
	define( '_THEME_VENDOR_PATH_', $loader_path . '/../vendor/' );
}

if ( ! defined( '_THEME_VENDOR_PATH_URI_' ) ) {
	define( '_THEME_VENDOR_PATH_URI_', PARENT_URL . '/vendor/' );
}

set_include_path(
	get_include_path() .
	PATH_SEPARATOR . $loader_path
);

if ( ! function_exists( 'qed_autoloader' ) ) {
	/**
	 * Vendor components loading function.
	 *
	 * @param  string $class class name that should be loaded.
	 * @return void
	 */
	function qed_autoloader( $class ) {
		static $map, $includes_path;
		if ( ! $map ) {
			$map = array(
				'lessc' => _THEME_VENDOR_PATH_ . 'lessphp/lessc.inc.php',
				'JuiceContainer' => _THEME_VENDOR_PATH_ . 'juice/JuiceContainer.php',
				'wp_bootstrap_navwalker' => _THEME_VENDOR_PATH_ . 'twittem/wp_bootstrap_navwalker.php',
				'tmhOAuth' => _THEME_VENDOR_PATH_ . 'tmhOAuth/tmhOAuth.php',
				'Mobile_Detect' => _THEME_VENDOR_PATH_ . 'mobiledetect/Mobile_Detect.php',
			);
			$includes_path = dirname( __FILE__ );
		}

		if ( isset( $map[ $class ] ) ) {
			$file_name = $map[ $class ];
			if ( $file_name ) {
				require $file_name;
			}
		} elseif ( 0 === strpos( $class, 'QED' ) ) {
			$class_filename = 'class-' . str_replace( '_', '-', strtolower( $class ) );
			$theme_class_file = "{$includes_path}/classes/{$class_filename}.php";
			if ( file_exists( $theme_class_file ) ) {
				require $theme_class_file;
			}
		}
	}

	spl_autoload_register( 'qed_autoloader' );
}

require _THEME_VENDOR_PATH_ . 'swishdesign/bootstrap.php';

// 1. Customize ACF path
add_filter('acf/settings/path', 'eyor_acf_settings_path');
function eyor_acf_settings_path( $path ) {
	$path = _THEME_VENDOR_PATH_ . '/acf-pro/';

	return $path;
}

// 2. customize ACF dir
add_filter('acf/settings/dir', 'eyor_acf_settings_dir');
function eyor_acf_settings_dir( $dir ) {
	$dir = _THEME_VENDOR_PATH_URI_ . '/acf-pro/';

	return $dir;
}

// 3. Hide ACF field group menu item if not in dev mode.
if ( ! THEME_IS_DEV_MODE ) {
	add_filter('acf/settings/show_admin', '__return_false');
}

// 4. Include ACF
include_once( _THEME_VENDOR_PATH_ . '/acf-pro/acf.php' );

// 5. Activate local JSON saving feature
add_filter('acf/settings/save_json', 'eyor_acf_json_save_point');
function eyor_acf_json_save_point( $path ) {
	$path = PARENT_DIR . '/includes/acf-json';

	return $path;
}

// 6. Activate local JSON loading feature
add_filter('acf/settings/load_json', 'eyor_acf_json_load_point');
function eyor_acf_json_load_point( $paths ) {
	unset($paths[0]);

	$paths[] = PARENT_DIR . '/includes/acf-json';

	return $paths;

}