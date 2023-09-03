<?php
/**
 * Page header view for the default mode (mode without any specific settings).
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

global $post;
if ( 'home' === qed_check_if_home() || 'default' === qed_check_if_home() || is_single() ) {
	return;
}

/**
 * Page header view for the default mode (mode without any specific settings).
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
?>

<div id="header-title-block" class="container block-after-indent margin-top-large margin-bottom-large">
	<div class="row">
		<div class="col-md-12">
			<div class="header-section padding-left padding-right">
				<div class="header-section__content<?php echo ( $breadcrumbs_html ? ' header-section__content--breadcrumbs' : ' header-section__content--title' ); ?>">
					<?php
					printf( '<div class="%s"><h1 class="header-section__title">%s</h1></div>',
						$breadcrumbs_html ? 'header-section__title-wrap--breadcrumbs' : 'header-section__title-wrap',
						esc_html( $title )
					);

					if ( $breadcrumbs_html ) {
						printf( '<div class="breadcrumbs-wrap">%s</div>', $breadcrumbs_html );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
