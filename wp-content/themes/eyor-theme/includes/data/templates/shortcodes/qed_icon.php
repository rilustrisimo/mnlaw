<?php
/**
 * Shortcode [qed_icon] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string  $icon
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

if ( ! $icon ) {
	return;
}

printf(
	'<i class="%s"></i>',
	esc_attr(
		$icon . ( $css_class ? ' ' . $css_class : '' )
	)
);
