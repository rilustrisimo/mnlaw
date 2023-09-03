<?php
/**
 * Shortcode [google_map_embed] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string  $src
 * @var string  $height
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

if ( ! $src ) {
	return '';
}

$attributes = array(
	'style="width:100%"',
);

if ( $height ) {
	$attributes[] = 'height="' . esc_attr( $height ) . '"';
}
if ( $css_class ) {
	$attributes[] = 'class="' . esc_attr( $css_class ) . '"';
}

printf(
	'<embed src="%s" %s>',
	esc_url( $src ),
	join( ' ', $attributes )
);
