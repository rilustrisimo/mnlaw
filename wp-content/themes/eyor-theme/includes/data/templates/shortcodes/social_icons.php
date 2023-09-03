<?php
/**
 * Shortcode [social_icons] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string $title
 * @var string $facebook_url
 * @var string $twitter_url
 * @var string $googleplus_url
 * @var string $youtube_url
 * @var string $pinterest_url
 * @var string $linkedin_url
 * @var string $instagram_url
 * @var string $dribbble_url
 * @var string $tumblr_url
 * @var string $vk_url
 * @var string $tripadvisor_url
 * @var string $css_class
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
<div class="qed-social-icons<?php if ( ! empty( $css_class ) ) { echo ' ' . esc_attr( $css_class ); }; ?>">
	<?php if ( $title ) { ?>
		<div class="qed-social-icons__title"><?php echo esc_html( $title ); ?></div>
	<?php } ?>
	<div class="qed-social-icons__icons">
		<?php if ( $facebook_url ) { ?>
			<a href="<?php echo esc_url( $facebook_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--facebook"><i class="fa fa-facebook"></i></a>
		<?php } ?>
		<?php if ( $twitter_url ) { ?>
			<a href="<?php echo esc_url( $twitter_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--twitter"><i class="fa fa-twitter"></i></a>
		<?php } ?>
		<?php if ( $googleplus_url ) { ?>
			<a href="<?php echo esc_url( $googleplus_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--google"><i class="fa fa-google-plus"></i></a>
		<?php } ?>
		<?php if ( $youtube_url ) { ?>
			<a href="<?php echo esc_url( $youtube_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--youtube"><i class="fa fa-youtube"></i></a>
		<?php } ?>
		<?php if ( $pinterest_url ) { ?>
			<a href="<?php echo esc_url( $pinterest_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--pinterest"><i class="fa fa-pinterest"></i></a>
		<?php } ?>
		<?php if ( $linkedin_url ) { ?>
			<a href="<?php echo esc_url( $linkedin_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--linkedin"><i class="fa fa-linkedin"></i></a>
		<?php } ?>
		<?php if ( $instagram_url ) { ?>
			<a href="<?php echo esc_url( $instagram_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--instagram"><i class="fa fa-instagram"></i></a>
		<?php } ?>
		<?php if ( $dribbble_url ) { ?>
			<a href="<?php echo esc_url( $dribbble_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--dribbble"><i class="fa fa-dribbble"></i></a>
		<?php } ?>
		<?php if ( $tumblr_url ) { ?>
			<a href="<?php echo esc_url( $tumblr_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--tumblr"><i class="fa fa-tumblr"></i></a>
		<?php } ?>
		<?php if ( $vk_url ) { ?>
			<a href="<?php echo esc_url( $vk_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--vk"><i class="fa fa-vk"></i></a>
		<?php } ?>
		<?php if ( $tripadvisor_url ) { ?>
			<a href="<?php echo esc_url( $tripadvisor_url ); ?>" class="qed-social-icons__icon qed-social-icons__icon--tripadvisor"><i class="fa fa-tripadvisor"></i></a>
		<?php } ?>
	</div>
</div>
