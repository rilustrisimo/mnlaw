<?php
/**
 * Shortcode [latest_posts] view.
 * For more detailed list see list of shortcode attributes.
 *
 * @var string  $title
 * @var boolean $title_underline
 * @var string  $number
 * @var string  $translate
 * @var srting  $read_more_text
 * @var string  $words_limit
 * @var boolean $ignore_sticky_posts
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
$render_limit = isset( $number ) && $number > 0 ? $number : 0;
$item_index = 0;
$column_size = ( $render_limit >= 4 )? 4:$render_limit;
$item_wrapper_class = 'col-md-' . ( 12 / $column_size ) . ' col-xs-6 qedgrid__item-wrap';

if ( $column_size > 3 ) {
	if ( $css_class ) {
		$css_class .= ' ';
	}
	$css_class .= 'qedgrid--small';
}

?>
<div class="qedgrid<?php if ( ! empty( $css_class ) ) { echo ' ' . esc_attr( $css_class ); }; ?>">
	<div class="row qedgrid__row">
		<?php foreach ( $items as $post ) : ?>
			<?php
			if ( $item_index > 0 && 0 === $item_index % $column_size ) {
				echo '<div class="clearfix hidden-sm hidden-xs"></div>';
			}
			if ( $item_index > 0 && 0 === $item_index % 2 ) {
				echo '<div class="clearfix visible-sm visible-xs"></div>';
			}
			$image = get_the_post_thumbnail( $post->ID, 'thumb_single' );
			$classItem = ($image) ? ' qed-last-posts__item--with-images' : '';
			$post_link = get_permalink( $post->ID );
			$date_modified = date( 'Y-m-d', strtotime($post->post_modified) );
			?>
			<div class="<?php echo esc_attr( $item_wrapper_class ); ?>">
				<div class="qedgrid__item margin-bottom-large">
					<?php
					printf( '<div class="qedgrid__item__top">%s</div>',
						sprintf( '<a href="%s" class="qedgrid__item__top__image">%s</a>',
							esc_url( $post_link ),
							$image
						)
					);
					?>
					<div class="qedgrid__item__content">
						<h3 class="qedgrid__item__title"><a href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $post->post_title ); ?></a></h3>
						<div class="qedgrid__item__description">
							<?php echo qed_do_excerpt( $post->post_content, 10 ); ?>
							<div class="qed-excerpt-more-link-wrap padding-top"><a class="qedbtn qedbtn--small qedbtn--transparent" href="<?php echo esc_url( $post_link ); ?>"><?php echo esc_html( $read_more_text ); ?></a></div>
						</div>
					</div>
					<div class="qedgrid__item__meta clearfix">
						<?php
						$settings = array(
							'show_author' => true,
							'show_date' => true,
						);

						$author_id = $settings['show_author'] ?  get_post_field( 'post_author', $post->ID ) : null;
						?>
						<div class="article-info">
							<?php if ( $author_id ) { ?>
								<div class="article-info__item"><a href="<?php echo esc_url( get_author_posts_url( $author_id ) ) ?>"><span itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name"><?php echo get_the_author(); ?></span></span></a></div>
							<?php } ?>
							<?php if ( $settings['show_date'] ) { ?>
								<div class="article-info__item article-info__item-delimeter"><i class="fa fa-circle"></i></div>
								<div class="article-info__item">
									<a href="<?php echo $post_link; ?>"><time datetime="<?php echo get_the_time('Y-m-d', $post->ID); ?>" itemprop="datePublished"><?php echo get_the_date('F d, Y', $post->ID); ?></time></a>
									<meta itemprop="dateModified" content="<?php echo $date_modified; ?>">
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		<?php $item_index++;
		endforeach; ?>
	</div>
</div>