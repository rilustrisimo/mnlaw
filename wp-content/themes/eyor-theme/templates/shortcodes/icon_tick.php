<?php
/**
 * Shortcode [icon_tick] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var boolean $state
 * @var string  $css_class
 * @var string  $view
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

if ( $state ) {
	echo '<i class="fa fa-check icon-tick icon-tick--on' . ( empty( $css_class ) ? '' : ' ' . esc_attr( $css_class ) ) . '"></i>';
} else {
	echo '<i class="fa fa-times icon-tick icon-tick--off' . ( empty( $css_class ) ? '' : ' ' . esc_attr( $css_class ) ) . '"></i>';
}
