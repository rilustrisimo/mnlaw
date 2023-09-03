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

if ( $css_class ) {
	$css_class = ' ' . $css_class;
}

if ( $state ) {
	echo '<i class="fa fa-check qed-icon-tick qed-icon-tick--on' . ( $css_class ? ' ' . esc_attr( $css_class ) : '' ) . '"></i>';
} else {
	echo '<i class="fa fa-times qed-icon-tick qed-icon-tick--off' . ( $css_class ? ' ' . esc_attr( $css_class ) : '' ) . '"></i>';
}
