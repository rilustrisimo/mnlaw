<?php
/**
 * Theme actions file.
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

add_action('eyor_before_main_content', 'qed_before_main_content', 10);
function qed_before_main_content() {
	print apply_filters( 'eyor_before_main_content_filter', '<div class="container layout-container margin-top-large margin-bottom-large">' );
}

add_action('eyor_after_main_content', 'qed_after_main_content', 10);
function qed_after_main_content() {
	print apply_filters( 'eyor_after_main_content_filter', '</div><!-- .container -->' );
}