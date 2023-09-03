<?php
/**
 * Shortcode [contact_info] view without links (just text details).
 * For more detailed list see list of shortcode attributes.
 *
 * @var string $address
 * @var string $phone
 * @var string $email
 * @var string $view
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
?>
<div class="contact-info">
	<?php if ( $address ) { ?>
		<div class="contact-info__item">
			<div class="contact-info__item__icon"><i class="fa fa-map-marker"></i></div>
			<div class="contact-info__item__text"><?php echo esc_html( $address ); ?></div>
		</div>
	<?php } ?>
	<?php if ( $phone ) { ?>
		<div class="contact-info__item">
			<div class="contact-info__item__icon"><i class="fa fa-phone"></i></div>
			<div class="contact-info__item__text"><?php echo esc_html( $phone ); ?></div>
		</div>
	<?php } ?>
	<?php if ( $email ) { ?>
		<div class="contact-info__item">
			<div class="contact-info__item__icon"><i class="fa fa-envelope"></i></div>
			<div class="contact-info__item__text"><?php echo esc_html( $email ); ?></div>
		</div>
	<?php } ?>
</div>
