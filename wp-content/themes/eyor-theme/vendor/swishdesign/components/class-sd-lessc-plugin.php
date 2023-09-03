<?php
/**
 * Class adapter to allow use some protected methods from lessc class.
 * Requires lessphp/lessc.inc.php package.
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

/**
 * Class SD_Lessc_Plugin
 */
class SD_Lessc_Plugin extends lessc {

	public function inject( lessc $instance ) {
		$instance->registerFunction(
			'hsvsaturation', array( $this,'function_hsvsaturation' )
		);
	}

	public function convert_color_toHSL($color) {
		return $this->toHSL( $this->coerceColor( $color ) );
	}

	public static function get_instance() {
		static $instance;
		if ( ! $instance ) {
			$instance = new self();
		}
		return $instance;
	}

	public static function function_hsvsaturation($color) {
		$hsv = self::get_instance()->convert_color_toHSL( $color );
		return round( $hsv[2] * 100 ) . '%';
	}

}
