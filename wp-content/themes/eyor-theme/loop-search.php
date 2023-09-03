<?php
/**
 * Partial template used for looping through search results.
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

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$post_type = get_post_type();
		switch ( $post_type ) {
			case 'post':
				get_template_part( 'templates/content', get_post_format() );
				break;

			case 'page':
				// If using woocommerce add case 'product':.
				get_template_part( 'templates/parts/search-result-block' );
				break;

			default:
				// Alrernative is: get_template_part( 'content', $post_type ); .
				echo strtr( '<div><h2><a href="{url}">{title}</a></h2></div>', array(
					'{url}' => get_permalink(),
					'{title}' => get_the_title(),
				));
				break;
		}
	}
	if ( ! is_single() ) {
		qed_render_pagination();
	}
} else {
	get_template_part( 'templates/content', 'none' );
}
