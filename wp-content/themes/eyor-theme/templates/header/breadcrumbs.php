<?php
/**
 * Breadcrumbs template.
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

if ( ! qed_get_option( 'breadcrumbs_is_show' ) ) {
	return;
}
$breadcrumbs_html = qed_di( 'breadcrumbs' )->get_html();
if ( $breadcrumbs_html ) {
	printf( '<div class="breadcrumbs">%s</div>', $breadcrumbs_html );
}