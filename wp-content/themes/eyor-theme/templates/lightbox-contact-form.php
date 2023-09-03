<?php
/**
 * Contact Form Lightbox
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

$contact_address = qed_get_option( 'contact_address', 'option' );
$contact_phone = qed_get_option( 'contact_phone', 'option' );
$contact_email = qed_get_option( 'contact_email', 'option' );
?>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="contact-info__wrap">
				<?php if ( null !== $contact_address ) : ?>
					<div class="contact-info__address">
						<span class="contact-info__title">A:</span><span class="contact-info__value"><?php echo esc_html( $contact_address ); ?></span>
						<div class="clearfix"></div>
					</div>
				<?php endif; ?>

				<?php if ( null !== $contact_phone ) : ?>
					<div class="contact-info__phone">
						<span class="contact-info__title">P:</span><span class="contact-info__value"><a href="tel:<?php echo esc_html( qed_get_formatted_mobile_number( $contact_phone ) ); ?>">+61 <?php echo esc_html( $contact_phone ); ?></a></span>
						<div class="clearfix"></div>
					</div>
				<?php endif; ?>

				<?php if ( null !== $contact_email ) : ?>
					<div class="contact-info__email">
						<span class="contact-info__title">E:</span><span class="contact-info__value"><a href="mailto:<?php echo esc_html( $contact_email ); ?>?subject=Enquiry for TXM Chartered Accountants"><?php echo esc_html( $contact_email ); ?></a></span>
						<div class="clearfix"></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="lightbox-contact-form-wrap">
				<?php echo do_shortcode('[contact-form-7 id="333" title="Contact Form Lightbox"]'); ?>
			</div>
		</div>
	</div>
</div>
