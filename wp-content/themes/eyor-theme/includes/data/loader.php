<?php
/**
 * Theme bootstrap file that defines data types loaders.
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

if ( ! function_exists( 'swishdesign_dtp_autoloader' ) ) {

	/**
	 *  Vendor components loading function.
	 *
	 * @param string $class class name that should be loaded.
	 */
	function swishdesign_dtp_autoloader( $class ) {

		if ( 0 === strpos( $class, 'QEDDTP' ) ) {
			$class_filename = 'class-' . str_replace( '_', '-', strtolower( $class ) );
			require dirname( __FILE__ ) . '/classes/' . $class_filename . '.php';
		}

	}
	spl_autoload_register( 'swishdesign_dtp_autoloader' );
}


/**
 * @return QEDDTP
 */
function QEDDTP() {
	return QEDDTP::instance();
}

define( 'QEDDTP_PATH', dirname( __FILE__ ) );
QEDDTP(); // To init data types.