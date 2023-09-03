<?php
/**
 * Footer template part.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

$is_back_to_top = qed_get_option( 'back-to-top', 'option' );
do_action('eyor_after_main_content');
if ( qed_get_option( 'is_testimonial_enabled', 'option' ) && qed_is_display_testimonials() ) :?>
<div class="qed-testimonials__wrap">
	<div class="qed-testimonials__inner-wrap">
		<?php
		$testimonial_title = ( qed_get_option( 'testimonial_section_title', 'option' ) ) ? ' title="' . qed_get_option( 'testimonial_section_title', 'option' ) . '"': '';
		$testimonial_words_limit = ( qed_get_option( 'is_testimonial_excerpt', 'option' ) ) ? ' words_limit="' . qed_get_option( 'testimonial_excerpt_length', 'option' ) . '"':'';
		$testimonial_order = ( qed_get_option( 'testimonial_order', 'option' ) ) ? ' order="' . qed_get_option( 'testimonial_order', 'option' ) . '"':'';
		$testimonial_orderby = ( qed_get_option( 'testimonial_orderby', 'option' ) ) ? ' orderby="' . qed_get_option( 'testimonial_orderby', 'option' ) . '"':'';
		$testimonial_shortcode = sprintf('[testimonials%s%s%s]',
			$testimonial_words_limit,
			$testimonial_order,
			$testimonial_orderby
		);
		echo do_shortcode( $testimonial_shortcode );
		?>
	</div>
</div>
<?php endif; ?>
<footer class="footer">
	<?php get_template_part( 'templates/footer/widget-areas' ); ?>
	<div class="footer__bottom">
		<?php
		if ( $is_back_to_top ) {
			echo '<div class="footer__arrow-top"><a href="#"><i class="fa fa-chevron-up"></i></a></div>';
		}
		?>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<?php //get_template_part( 'templates/footer/info' ); ?>
					<div class="footer__copyright"><?php echo qed_esc_text( qed_get_option( 'footer_text_note', 'option', '© 2020 - Eyorsogood | Development and Design by Rouie Ilustrisimo' ), 'option_input', true ); ?></div>
				</div>
			</div>
		</div>
	</div>
</footer>
<div style="display:none" class="fancybox-hidden">
	<div id="contact-lightbox" style="display: block;max-width: 1080px;width: 100%;">
		<?php get_template_part( 'templates/lightbox', 'contact-form' );?>
	</div>
</div>
<?php
get_template_part( 'templates/footer/footer', 'clean' );
