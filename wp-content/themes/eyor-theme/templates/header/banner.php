<?php
/**
 * Banner mode template part.
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

/**
 * Page header view for the banner mode.
 *
 * @var string $title
 * @var string $section_mode
 * @var string $slider_alias
 * @var string $banner_subtitle
 * @var string $banner_image
 * @var string $is_banner_image_parallax
 * @var string $banner_image_repeat
 * @var string $banner_mask
 */
$breadcrumbs_html = qed_render_template_part( 'templates/header/breadcrumbs', '', array(), true );

$is_use_parallax = isset( $is_banner_image_parallax ) && $is_banner_image_parallax;
$is_image = $banner_image && ! $is_use_parallax;
$is_banner_paralax = $is_use_parallax && $banner_image;

$mask_html = ! empty( $banner_mask )
	? sprintf( '<div class="header-section-mask %s"></div>', esc_attr( 'header-section-mask--' . $banner_mask ) )
	: '';

qed_di( 'register' )->set_var( 'is_banner', true );

$wrapper_additional_class = $is_banner_paralax ? ' parallax-section parallax-section--header' : '';
if ( $banner_mask ) {
	$wrapper_additional_class .= esc_attr( ' header-section--with-mask-' . $banner_mask );
}
?>

<div class="header-section header-section--with-banner<?php print $wrapper_additional_class; ?>">
	<?php
	if ( $is_banner_paralax ) {
		wp_enqueue_script( 'parallax' );
		SD_Js_Client_Script::add_script( 'initParallax', 'Theme.initParallax();' );

		printf( '%s<div class="parallax-image" style="background-image:url(%s);%s"></div>',
			$mask_html,
			esc_url( $banner_image ),
			$banner_image_repeat ? ' background-repeat:' . esc_attr( $banner_image_repeat ) . ';' : ''
		);
	} // End if().
	?>
	<div class="container">
		<?php print $breadcrumbs_html; ?>
		<div class="header-section__content">
			<?php
			printf( '<h1 class="header-section__title">%s</h1>', esc_html( $title ) );

			if ( $banner_subtitle ) {
				printf( '<p class="header-section__description">%s</p>', esc_html( $banner_subtitle ) );
			}
			?>
		</div>
	</div>
	<?php
	if ( $is_image ) {
		printf( '<div class="header-section__simple-image%s">%s<img src="%s" alt="%s"></div>',
			$breadcrumbs_html ? ' header-section__simple-image--with-breadcrumbs' : '',
			$mask_html,
			esc_url( $banner_image ),
			esc_attr( $title )
		);
	} // End if().
	?>
</div>
