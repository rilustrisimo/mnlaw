<?php
/**
 * Page header section template.
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

if ( is_404() ) {
	return;
}

$is_home = ( 'home' === qed_check_if_home() || 'default' === qed_check_if_home() ) ? true : false;
$section_meta_service = qed_di( 'header_section' );
$section_meta = $section_meta_service ? $section_meta_service->get_section_meta() : array();

// $mode == 'hide' means "default" mode.
$mode = isset( $section_meta['section_mode'] ) ? $section_meta['section_mode'] : 'hide';
if ( 'banner' === $mode && empty( $section_meta['banner_image'] ) ) {
	$mode = 'hide';
}

if ( 'hide' === $mode && is_front_page() ) {
	// To hide default title for home page.
	return;
}

switch ( $mode ) {
	case 'banner':
		print '<div id="dee-slider">';
		qed_render_template_part( 'templates/header/banner', '', $section_meta );
		print '<div class="clearfix"></div></div>';
		if ( ! $is_home ):
		qed_render_template_part( 'templates/header/title-block', '', $section_meta );
		endif;
		break;

	case 'slider':
		print '<div id="dee-slider">';
		qed_render_template_part( 'templates/header/slider', '', $section_meta );
		print '<div class="clearfix"></div></div>';
		if ( ! $is_home ):
		qed_render_template_part( 'templates/header/title-block', '', $section_meta );
		endif;
		break;

	case 'hide':
	default:
		if ( ! is_page_template('template-contact-page.php') ):
		qed_render_template_part( 'templates/header/title-block', '', $section_meta );
		endif;
		break;
}
