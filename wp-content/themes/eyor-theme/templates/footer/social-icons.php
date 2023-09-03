<?php
/**
 * Social icons rendering template part.
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

$social_icons = array(
	'facebook' => 'facebook',
	'linkedin' => 'linkedin',
	'twitter' => 'twitter',
	'googleplus' => 'google-plus',
	'pinterest' => 'pinterest',
	'instagram' => 'instagram',
	'tumblr' => 'tumblr',
);
//$footer_info_delimeter = ' footer__info__item--delimiter';
$footer_info_delimeter = '';

$links_set = array();
foreach ( $social_icons as $key => $icon_class ) {
	$url = qed_get_option( 'social_link_' . $key, 'option' );
	if ( $url ) {
		$links_set[] = array(
			'icon_class' => 'fa fa-' . $icon_class,
			'url' => $url
		);
	}
}

//for( $i = 1; $i <= 5; $i++ ) {
//	$url = qed_get_option( "social_link_{$i}_is_active" ) ? qed_get_option( "social_link_{$i}_url" ) : null;
//	if ( ! $url ) {
//		continue;
//	}
//	$icon_class = qed_get_option( "social_link_{$i}_icon" );
//	if ( $icon_class ) {
//		$links_set[] = array(
//			'icon_class' => 'fa ' . $icon_class,
//			'url' => $url
//		);
//	}
//}

$social_icons_html = '';
foreach ( $links_set as $link_info ) {
	$social_icons_html .= '<a href="' . esc_url( $link_info['url'] ) . '"><i class="' . esc_attr( $link_info['icon_class'] ) . '"></i></a>';
}
if ( $social_icons_html ) {
	printf( '<div class="footer__info__item%s footer__info__item--social-icons">%s</div>',
		$footer_info_delimeter,
		$social_icons_html
	);
}