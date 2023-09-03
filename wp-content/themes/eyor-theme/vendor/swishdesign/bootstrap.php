<?php
/**
 * Theme bootstrap file that defines component loaders.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

if ( ! function_exists( 'swishdesign_autoloader' ) ) {

	/**
	 *  Vendor components loading function.
	 *
	 * @param string $class class name that should be loaded.
	 */
	function swishdesign_autoloader( $class ) {

		if ( 0 === strpos( $class, 'SD' ) ) {
			$class_filename = 'class-' . str_replace( '_', '-', strtolower( $class ) );
			require dirname( __FILE__ ) . '/components/' . $class_filename . '.php';
		}

	}
	spl_autoload_register( 'swishdesign_autoloader' );
}
