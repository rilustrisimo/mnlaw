<?php
/**
 * Helper that contains functions related to shortcodes.
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
 * Class QED_Shortcodes_Helper_Service
 */
class QED_Shortcodes_Helper_Service extends SD_Component {

	/**
	 * Get shortcode identifier.
	 *
	 * @return integer
	 */
	public function generate_id() {
		static $id = 0;
		$id++;

		return $id;
	}

	/**
	 * Checks if values of the boolean attribute is true.
	 *
	 * @param  string $value value to check.
	 * @return boolean
	 */
	public function attribute_is_true( $value ) {
		if ( ! $value || in_array( $value, array( 'no', 'false', 'off' ), true ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Makes different checks required for correct plugin working.
	 *
	 * @param  string $check_name check uniq. code.
	 * @return boolean
	 */
	protected function check( $check_name ) {
		$result = false;

		switch ( $check_name ) {
			case 'is_wc_loaded':
				$result = function_exists( 'WC' );
				break;
		}

		return $result;
	}
}
