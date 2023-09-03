<?php
/**
 * Application component class.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign/Components
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0 SwishDesign/Components
 */
defined( 'ABSPATH' ) || die();

/**
 * Class App
 */
class SD_App extends SD_Component {
	/**
	 * Analog for the get_template_part.
	 * Allows render view with possibility to passing some params for rendering.
	 *
	 * @param  string $template_name view name.
	 * @param  string $template_postfix optional postfix.
	 * @param  array  $data assoc array with variables that should be passed to view.
	 * @param  bool   $return if result should be returned instead of outputting.
	 *
	 * @return string
	 */
	public function render_template_part( $template_name, $template_postfix = '', array $data = array(), $return = false ) {
		static $__rf_cache;
		if ( null === $__rf_cache ) {
			$__rf_cache = array();
		}
		$__cache_key = $template_name . $template_postfix;
		if ( isset( $__rf_cache[ $__cache_key ] ) ) {
			$__view_filepath = $__rf_cache[ $__cache_key ];
		} else {
			$__template_variations = array();
			if ( $template_postfix ) {
				$__template_variations[] = $template_name . '-' . $template_postfix . '.php';
			}
			$__template_variations[]   = $template_name . '.php';
			$__rf_cache[ $__cache_key ] = $__view_filepath = locate_template( $__template_variations );
		}

		if ( ! $__view_filepath ) {
			return '';
		}

		if ( $data ) {
			extract( $data );
		}

		$__rf_data   = $data;
		$__rf_return = $return;

		unset( $template_name );
		unset( $template_postfix );
		unset( $data );
		unset( $return );

		if ( $__rf_data ) {
			extract( $__rf_data );
		}

		if ( $__rf_return ) {
			ob_start();
			include $__view_filepath;

			return ob_get_clean();
		} else {
			include $__view_filepath;
		}
	}
}
