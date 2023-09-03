<?php
/**
 * Display Accordion
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

$section_meta_service = qed_di( 'accordion_section' );
$section_meta = $section_meta_service ? $section_meta_service->get_section_meta() : array();

if ( ! $section_meta['accordion_is_enabled'] ) {
	return;
}

$accordion_style = ( $section_meta['accordion_style'] ) ? $section_meta['accordion_style'] : 'theme-default';
$accordions = $section_meta['accordion_group'];
$accordion_shortcode = '[accordion style="' . $accordion_style . '"]';

foreach ( $accordions as $accordion ) {

	$accordion_shortcode .= '[accordion_item title="' . $accordion['accordion_item_title'] . '"]';
	$accordion_shortcode .= $accordion['accordion_item_content'];
	$accordion_shortcode .= '[/accordion_item]';
}

$accordion_shortcode .= '[/accordion]';

echo do_shortcode($accordion_shortcode);