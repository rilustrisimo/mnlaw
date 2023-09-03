<?php
/**
 * Shortcode [tabs] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var array  $items
 * @var string $style
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

if ( ! $items ) {
	return '';
}

if ( $css_class ) {
	$css_class = ' ' . $css_class;
}
if ( 'with-border' === $style ) {
	$css_class .= ' swish-tabs--with-border';
}
?>
<div class="swish-tabs<?php if ( $css_class ) { echo esc_attr( $css_class ); }; ?>">
	<ul class="nav nav-tabs">
		<?php
		foreach ( $items as $item_id => $title_info ) {
			printf(
				'<li%s><a href="#%s" data-toggle="tab">%s</a></li>',
				$title_info['is_active'] ? ' class="active"' : '',
				esc_attr( $item_id ),
				esc_html( $title_info['title'] )
			);
		} // End foreach().
		?>
	</ul>
	<div class="tab-content">
		<?php
		foreach ( $items as $item_id => $item_info ) {
			printf(
				'<div class="tab-pane%s" id="%s"><div class="swish-tabs__content padding-all">%s</div></div>',
				$item_info['is_active'] ? ' active' : '',
				esc_attr( $item_id ),
				do_shortcode( $item_info['content'] )
			);
		} // End foreach().
		?>
	</div>
</div>