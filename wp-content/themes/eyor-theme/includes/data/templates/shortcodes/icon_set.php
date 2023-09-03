<?php
/**
 * Shortcode [icons_set] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var array   $items
 * @var integer $row_size
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

if ( ! $items ) {
	return;
}

if ( $row_size < 2 ) {
	$row_size = 2;
} elseif ( $row_size > 4 ) {
	$row_size = 4;
}
$cell_size = 12 / $row_size;
$row_item_counter = 0;

?>
<div class="qed-icons-set<?php if ( ! empty( $css_class ) ) { echo ' ' . esc_attr( $css_class ); }; ?>">
	<div class="row qed-icons-set__row">
		<?php foreach ( $items as $item ) { ?>
			<?php
			if ( 0 != $row_item_counter ) {
				if ( $row_item_counter % $row_size == 0 ) {
					echo '</div><div class="row qed-icons-set__row">';
					$row_item_counter = 0;
				}
			}
			$row_item_counter++;
			$title_url = ! empty( $item['title_url'] ) ? $item['title_url'] : '';
			?>
			<div class="col-sm-<?php echo esc_attr( $cell_size ); ?>">
				<div class="qed-icons-set__item">
					<?php if( $item['icon'] ) {
						if ( $title_url ) {
							printf( '<div class="qed-icons-set__item__icon-wrap"><a href="%s"><i class="qed-icons-set__item__icon %s"></i></a></div>',
								esc_url( $title_url ),
								esc_attr( $item['icon'] )
							);
						} else {
							printf( '<div class="qed-icons-set__item__icon-wrap"><i class="qed-icons-set__item__icon %s"></i></div>',
								esc_attr( $item['icon'] )
							);
						}
					} ?>
					<div class="qed-icons-set__item__content">
						<?php if ( $item['title'] ) {
							if ( $title_url ) {
								printf( '<h3 class="qed-icons-set__item__title"><a href="%s">%s</a></h3>',
									esc_url( $title_url ),
									esc_html( $item['title'] )
								);
							} else {
								printf( '<h3 class="qed-icons-set__item__title">%s</h3>',
									esc_html( $item['title'] )
								);
							}
						} ?>
						<div class="qed-icons-set__item__description"><?php echo do_shortcode( $item['content'] ); ?></div>
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
