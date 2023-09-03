<?php
/**
 * Shortcode [title] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string  $text
 * @var string  $subtitle
 * @var string  $size
 * @var string  $position
 * @var boolean $decoration
 * @var boolean $underline
 * @var string  $style
 * @var string  $css_class
 * @var string  $view
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

$size_class = ( 'big' == $size ) ? ' qed-title--big' : '';
$position_class = ( 'center' == $position ) ? ' qed-title--center' : '';
$underline_class = $underline ? ' qed-title--underline' : '';
$style_class = ( 'light' == $style ) ? ' qed-title--light qed-title--underline-light' : '';

$decoration_class = '';
if ( $decoration ) {
	switch ( $position ) {
		case 'center':
			$decoration_class = ' qed-title--decoration-bottom-center';
			break;
		case 'left':
			$decoration_class = ' qed-title--decoration-bottom-left';
			break;
	}
}

$title_class = $size_class . $position_class . $underline_class . $style_class . $decoration_class;
if ( ! empty( $css_class ) ) {
	$title_class .= ' ' . $css_class;
};
?>
<div class="qed-title<?php echo esc_attr( $title_class ); ?>">
	<?php if ( $subtitle ) { ?>
		<div class="qed-title__subtitle"><?php echo esc_html( $subtitle ); ?></div>
	<?php } ?>
	<h3 class="qed-title__primary"><?php echo esc_html( $text ); ?></h3>
</div>