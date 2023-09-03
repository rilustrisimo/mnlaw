<?php
/**
 * Page header template part for the logo rendering.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

if ( 'image' !== qed_get_option( 'logo_type', 'option' ) ) {
	echo strtr(
		'<div class="logo"><a id="logoLink" href="{home_url}">{name}</a></div>',
		array(
			'{home_url}' => esc_url( home_url( '/' ) ),
			'{name}' => esc_html( get_bloginfo( 'name' ) ),
		)
	);
} else {
	echo strtr(
		'<div class="logo logo--image"><a id="logoLink" href="{home_url}">' .
		'<img id="normalImageLogo" src="{logo_url}" alt="{blog_name_atr}" title="{blog_description_atr}">' .
		'<img id="retinaImageLogo" src="{retina_logo_url}" alt="{blog_name_atr}" title="{blog_description_atr}">' .
		'</a></div>',
		array(
			'{home_url}' => esc_url( home_url( '/' ) ),
			'{blog_name_atr}' => esc_attr( get_bloginfo( 'name' ) ),
			'{blog_description_atr}' => esc_attr( get_bloginfo( 'description' ) ),
			'{logo_url}' => esc_url( qed_get_option( 'logo_image', 'option' ) ),
			'{retina_logo_url}' => esc_url( qed_get_option( 'logo_image_retina', 'option' ) ),
		)
	);
}
