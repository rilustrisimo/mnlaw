<?php
/**
 * Template Name: Contact Page
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
$contact_mail = qed_get_option( 'contact_mail', 'option' );
$contact_phone = qed_get_option( 'contact_phone', 'option' );
$contact_email = qed_get_option( 'contact_email', 'option' );
$contact_skype = qed_get_option( 'contact_skype', 'option' );

get_header();
qed_di( 'register' )->set_var( 'is_banner', true );
?>
<div class="header-section header-section--with-banner parallax-section parallax-section--header">
	<?php
	wp_enqueue_script( 'parallax' );
	SD_Js_Client_Script::add_script( 'initParallax', 'Theme.initParallax();' );
	$banner_image_repeat = ' background-repeat:no-repeat;';

	printf( '<div class="parallax-image" style="background-image:url(%s);%s"></div>',
		esc_url( PARENT_URL . '/assets/images/contact-page-slide.jpg' ),
		$banner_image_repeat
	);
	?>
	<div class="container">
		<div class="header-section__content">
			<a class="button button--default-reverse" href="https://www.google.com/maps/dir/Current+Location/-31.908964,115.809056" target="_blank">Get Directions</a>
		</div>
	</div>
</div>
<?php qed_render_template_part( 'templates/header/title-block', '', array('title' => get_the_title()) ); ?>
<div class="container layout-container margin-top-large margin-bottom-large">
<?php
if ( have_posts() ) : ?>
	<?php while ( have_posts() ) { the_post(); ?>
		<div class="page-single page-contact">
			<main class="page-single__content contact-info" role="main">
				<div class="row">
					<div class="col-md-12">
						<div class="contact-info__wrap">
							<?php if ( null !== $contact_address ) : ?>
								<div class="contact-info__address">
									<span class="contact-info__title">A:</span><span class="contact-info__value"><?php echo esc_html( $contact_address ); ?></span>
									<div class="clearfix"></div>
								</div>
							<?php endif; ?>

							<?php if ( null !== $contact_mail ) : ?>
								<div class="contact-info__mail">
									<span class="contact-info__title">M:</span><span class="contact-info__value"><?php echo esc_html( $contact_mail ); ?></span>
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

							<?php if ( null !== $contact_skype ) : ?>
								<div class="contact-info__skype">
									<span class="contact-info__title">S:</span><span class="contact-info__value"><?php echo esc_html( $contact_skype ); ?></span>
									<div class="clearfix"></div>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<?php echo do_shortcode('[contact-form-7 id="262" title="Contact Form"]'); ?>
					</div>
				</div>
			</main>
		</div>
	<?php } ?>
<?php else :
	get_template_part( 'templates/content', 'none' );
endif;

get_footer();
