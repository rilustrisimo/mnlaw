<?php
/**
 * Page header view for the slider mode.
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
 * Page header view for the slider mode.
 *
 * @var string $title
 * @var string $section_mode
 * @var string $slider_mask
 * @var string $slider_timeout
 * @var string $slider_fadespeed
 * @var array|object slider_group
 * @var key slide_title
 * @var key slide_subtitle
 * @var key slide_image
 */

$is_home = ( 'home' === qed_check_if_home() || 'default' === qed_check_if_home() ) ? true : false;
$script_id = ( $is_home ) ? 'homeSliderInit' : 'innerSliderInit';
$slide_main_class = 'slick-slide';
$slide_class = ( $is_home ) ? $slide_main_class . ' slick-slide-home' : $slide_main_class;
$slider_id = 'desktop' . get_the_ID();
$mask_html = ! empty( $banner_default_mask )
		? sprintf( '<div class="header-section-mask %s"></div>', esc_attr( 'header-section-mask--' . $banner_default_mask ) )
		: '';

$js_config = array(
	'sliderSelector' => '#' . $slider_id,
	'slideTransitionType' => $slide_transition_type,
	'nextSelector' => '.' . $slide_main_class
);
$slick_options = array();
$slick_options['speed'] = intval( $slider_fadespeed );
$slick_options['dots'] = true;

if ( $is_home ) {
	$slick_options['arrows'] = false;
} else {
	$slick_options['arrows'] = false;
}

switch ($slide_transition_type) {
	case 'autoplay':
		$slick_options['autoplay'] = true;
		$slick_options['autoplaySpeed'] = ( $slider_timeout > 0 ) ? intval( $slider_timeout ) : 0;
		break;
	case 'static':
	case 'click':
		$slick_options['autoplay'] = false;
		$slick_options['dots'] = false;
		break;
}

if ( $slick_options ) {
	$js_config['sliderOptions'] = $slick_options;
}

if ( 'random' === $section_order ) {
	shuffle( $slider_group );
}

SD_Js_Client_Script::add_script( $script_id . $slider_id, 'Theme.makeSlider(' . wp_json_encode( $js_config ) . ');' );
?>
<div id="<?php echo esc_attr( $slider_id ); ?>" class="slider<?php echo ( $is_home ) ? ' slider-home' : ''; ?>">
	<!-- Slides -->
	<?php
	foreach ( $slider_group as $slide ) {

		// TODO: Add slides param.
		$title = $slide['slide_title'];
		$subtitle = $slide['slide_subtitle'];
		$image = "background-image: url('" . $slide['slide_image'] . "')";
		$button = '';
		$button_enabled = $slide['slide_button_enabled'];

		if ( $button_enabled ) {
			$button_text = $slide['slide_button_text'];
			$button_type = $slide['slide_button_type'];

			switch ( $button_type ) {
				case 'page':
					$button_link = get_permalink( $slide['slide_page_link'] );
					$button_new_tab = ($slide['slide_new_tab']) ? ' target="_blank"' : '';
					$button = sprintf('<a class="button button--default" href="%s"%s>%s</a>',
						$button_link,
						$button_new_tab,
						$button_text
					);
					break;
				case 'post':
					$button_link = get_permalink( $slide['slide_post_link'] );
					$button_new_tab = ($slide['slide_new_tab']) ? ' target="_blank"' : '';
					$button = sprintf('<a class="button button--default" href="%s"%s>%s</a>',
						$button_link,
						$button_new_tab,
						$button_text
					);
					break;
				case 'attachment':
					$button_link = $slide['slide_attachment_link'];
					$button_new_tab = ($slide['slide_new_tab']) ? ' target="_blank"' : '';
					$button = sprintf('<a class="button button--default" href="%s"%s>%s</a>',
						$button_link,
						$button_new_tab,
						$button_text
					);
					break;
				case 'external':
					$button_link = $slide['slide_external_url'];
					$button_new_tab = ($slide['slide_new_tab']) ? ' target="_blank"' : '';
					$button = sprintf('<a class="button button--default" href="%s"%s>%s</a>',
						$button_link,
						$button_new_tab,
						$button_text
					);
					break;
			}
		}

		$text_section = sprintf('<div class="slick-slide__title-wrap"><div class="slick-slide__title-inner-wrap"><div class="slick-slide__title">%s</div><div class="slick-slide__subtitle">%s</div><div class="slick-slide__button">%s</div></div></div>',
				$title,
				$subtitle,
				$button
		);

		if( $is_home ) {
			printf('<div class="%s" style="%s">%s%s</div>',
					$slide_class,
					$image,
					$mask_html,
					$text_section
			);
		} else {
			printf('<div class="%s" style="%s">%s</div>',
					$slide_class,
					$image,
					$mask_html
			);
		}

	}
	?>
</div>