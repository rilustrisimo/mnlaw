<?php
/**
 * Blog grid item template part.
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

$item_index = 0;
$is_subscribe_form = qed_get_option( 'is_display_subscribe', 'option' );
$subscribe_form_title = 'subscribe to updates';
$column_size = qed_get_option( 'blog_grid_column', 'option' );
$posts_per_page = get_option( 'posts_per_page', 'option' );
$rows_per_page = ceil( $posts_per_page / $column_size );
$add_after = ( $rows_per_page > 2 ) ? 'between' : 'end';
$post_count_after_row = $column_size * 2;
$item_wrapper_class = 'col-md-' . ( 12 / $column_size ) . ' col-xs-6 qedgrid__item-wrap';

if ( $column_size > 3 ) {
	if ( $css_class ) {
		$css_class .= ' ';
	}
	$css_class .= 'qedgrid--small';
}
?>
<div class="qedgrid<?php if ( $css_class ) echo ' ' . esc_attr( $css_class ); ?>">
	<div class="row qedgrid__row">
		<?php while ( have_posts() ): the_post();
			if ( $item_index > 0 && 0 === $item_index % $column_size ) {
				echo '<div class="clearfix hidden-sm hidden-xs"></div>';
			}
			if ( $item_index > 0 && 0 === $item_index % 2 ) {
				echo '<div class="clearfix visible-sm visible-xs"></div>';
			}
			if ( $item_index > 0 && 0 === $item_index % $post_count_after_row && 'between' === $add_after && $is_subscribe_form ) {
				printf('<div class="col-md-12 margin-bottom"><div class="form-subscribe"><div class="form-subscribe__title">%s</div><div class="form-subscribe__form-wrap">%s</div></div><div class="clearfix"></div></div>',
					$subscribe_form_title,
					do_shortcode( qed_get_option( 'subscribe_form_shortcode' ) )
				);
			}
			?>
			<div class="<?php echo esc_attr( $item_wrapper_class ); ?>">
				<?php get_template_part( 'templates/parts/loop', 'item' ); ?>
			</div>
			<?php $item_index++;
		endwhile; ?>
	</div>
</div>
<?php
qed_render_pagination();
?>
<div class="form-subscribe">
	<h3 class="form-subscribe__title">Subscribe to Get the Latest News</h3>
	<div class="form-subscribe__form-wrap">
		<?php echo do_shortcode('[mc4wp_form id="314"]'); ?>
	</div>
</div>

