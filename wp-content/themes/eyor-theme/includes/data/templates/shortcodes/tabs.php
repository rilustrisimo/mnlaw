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

?>
<div class="<?php echo esc_attr( $css_class ); ?>">
	<ul class="nav nav-tabs">
		<?php foreach ( $items as $item_id => $title_info ) {
			printf(
				'<li%s><a href="#%s" data-toggle="tab">%s</a></li>',
				$title_info['is_active'] ? ' class="active"' : '',
				esc_attr( $item_id ),
				esc_attr( $title_info['title'] )
			);
		} ?>
	</ul>
	<div class="tab-content">
		<?php foreach ( $items as $item_id => $item_info ) {
			$css_class = !empty( $item_info['is_active'] ) ? ' active' : '';
			if ( !empty( $item_info['css_class'] ) ) {
				$css_class .= ' ' . $item_info['css_class'];
			}
			printf(
				'<div class="tab-pane%s" id="%s">%s</div>',
				$css_class ? esc_attr( $css_class ) : '',
				esc_attr( $item_id ),
				do_shortcode( $item_info['content'] )
			);
		} ?>
	</div>
</div>