<?php
/**
 * Shortcode [testimonials] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string  $title
 * @var string  $number
 * @var string  $words_limit
 * @var string  $css_class
 * @var string  $view
 * @var array   $items
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

if ( ! $items ) {
	return '';
}

$script_id = 'testimonialInit';
$testimonial_id = 'testimonialSwiper' . $instance;
$total_items = count($items);
$item_count = 0;

$js_config = array(
	'sliderSelector' => '#' . $testimonial_id,
);
$slick_options = array();
$slick_options['autoplay'] = false;
$slick_options['speed'] = 1500;
$slick_options['dots'] = true;
$slick_options['arrows'] = false;

switch( $transition ) {
	case 'autoplay':
		$slick_options['autoplay'] = true;
		$slick_options['autoplaySpeed'] = 3000;
		break;
	case 'click':
		$js_config['navPrevSelector'] = '.qed-testimonials__item';
		break;
	case 'static':
	default:
		$slick_options['autoplay'] = false;
		break;
}

if ( $slick_options ) {
	$js_config['sliderOptions'] = $slick_options;
}

SD_Js_Client_Script::add_script( $script_id . $testimonial_id, 'Theme.makeSlider(' . wp_json_encode( $js_config ) . ');' );
?>
<?php if ( $title ) { ?>
	<h3 class="qed-testimonials__title"><?php echo $title; ?></h3>
<?php } ?>
<div id="<?php echo esc_attr( $testimonial_id ); ?>" class="qed-testimonials">

	<?php if ($total_items > 0):?>
	<div class="qed-testimonials__slide"><div class="qed-testimonials__slide-wrap">
	<?php endif; ?>
	<?php foreach ( $items as $post ) : $item_count++; ?>
		<?php
		$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID),'thumbnail');
		$classItem = ($image) ? ' qed-testimonials__item--with-images' : '';
		$post_link = get_permalink( $post->ID );
		$style_attr = 'background-image: url("' . $image[0] . '")';
		?>
		<div class="qed-testimonials__item<?php echo esc_attr( $classItem ); ?>">
			<?php
			printf( '<div class="qed-testimonials__item__image-wrap" style="%s"></div>',
				esc_attr($style_attr)
			);
			?>
			<div class="qed-testimonials__item__content">
				<div class="qed-testimonials__item__description">"<?php echo qed_do_excerpt( $post->post_content, $words_limit ); ?>"</div>
				<div class="qed-testimonials__item__name">- <?php echo esc_html( $post->post_title ); ?></div>
			</div>
		</div>
		<?php
		switch (true) {
			case ($item_count % 2 == 0) && ($item_count != $total_items):
				print '</div></div><div class="qed-testimonials__slide"><div class="qed-testimonials__slide-wrap">';
				break;
			case $item_count == $total_items:
				print '</div></div>';
				break;
		}
		?>
	<?php endforeach; ?>
</div>