<?php 
/**
 * Theme filters file.
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

// -----------------------------------------------------------------#
// Filters
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_default_fonts' ) ) {
	function qed_default_fonts( $fonts ) {
	    /*
	     * Add fonts that you need to be loaded by default.
	     */
	    // $fonts['main_nav'] = array(
		// 	'family' => 'Raleway',
		// 	'weight' => 'regular',
		// 	'style' => 'normal',
		// );
	    return $fonts;
	}
	add_filter( 'eyor_default_fonts', 'qed_default_fonts' );
}
