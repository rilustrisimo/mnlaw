<?php
/**
 * Page footer template part for the site details rendering.
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

$contacts_html = '';
$contact_email = qed_get_option( 'contact_email' );
$contact_phone = qed_get_option( 'contact_phone' );
//$footer_info_delimeter = ' footer__info__item--delimiter';
$footer_info_delimeter = '';

if ( $contact_email ) {
	$contacts_html .= sprintf( '<div class="footer__info__item footer__info__item--email%s"><a href="mailto:%s"><i class="fa fa-envelope-o"></i></a></div>',
		$contact_email ? '' : $footer_info_delimeter,
		esc_html( $contact_email ) );
}

if ( $contact_phone ) {
	$contacts_html .= sprintf( '<div class="footer__info__item footer__info__item--phone%s"><a href="tel:+61%s"><i class="fa fa-phone"></i></a></div>',
		$contact_phone ? '' : $footer_info_delimeter,
		esc_html( str_replace( '–', '', preg_replace( '/\s+/', '', $contact_phone ) ) ) );
}

ob_start();
get_template_part( 'templates/footer/social-icons' );
$social_icons_html = ob_get_clean();


?>
<div class="footer__info">
	<div class="footer__info__items-right">
		<?php echo $contacts_html; ?>
		<?php echo $social_icons_html; ?>
	</div>
</div>