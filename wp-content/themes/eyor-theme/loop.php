<?php
/**
 * Partial template used for looping through query results.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

$blog_display_mode = qed_get_option( 'blog_display_mode', 'option', 'list' );

if ( have_posts() ) {

	if ( 'grid' === $blog_display_mode && ! is_single() ) {
		get_template_part( 'templates/parts/loop', 'grid' );
	} else {
		while ( have_posts() ) {
			the_post();
			$post_type = get_post_type();
			switch ( $post_type ) {
				case 'post':
					get_template_part( 'templates/content', get_post_format() );
					break;
				// If using woocommerce uncomment following lines.
				// case 'product':
				//  wc_get_template_part( 'content-product', get_post_format() );
				//  break;

				default:
					get_template_part( 'templates/content', $post_type );
					break;
			}
		}
	}
} else {
	get_template_part( 'templates/content', 'none' );
}
