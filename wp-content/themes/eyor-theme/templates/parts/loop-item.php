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

$post_id = get_the_ID();
$thumb_id = get_post_thumbnail_id();
//$featured = $thumb_id ? qed_get_the_post_thumbnail( $post_id, 'featured_single' ) : null;
$thumbnail = $thumb_id ? qed_get_the_post_thumbnail( $post_id, 'blog_grid_thumb' ) : null;
$post_class = 'qedgrid__item margin-bottom';

$is_single = is_single();
$permalink = get_permalink();
?>
<article id="<?php echo get_post_type() . '-' . $post_id; ?>" <?php post_class( $post_class ); ?> itemscope
         itemtype="http://schema.org/BlogPosting" xmlns="http://www.w3.org/1999/html">
	<a href="<?php echo esc_url( $permalink ); ?>">
		<meta itemprop="dateModified" content="<?php the_modified_time( 'Y-m-d' ); ?>">
		<?php
		if ( $thumbnail ) {
			$thumb_src = wp_get_attachment_image_src( $thumb_id, 'full' );
			$bg_image = 'style="background-image: url(' . esc_url( $thumb_src[0] ) . ')"';
			if ( $thumb_src ) {
				printf( '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject"><meta itemprop="url" content="%s"><meta itemprop="width" content="%s"><meta itemprop="height" content="%s"></span>',
						esc_url( $thumb_src[0] ),
						$thumb_src[1],
						$thumb_src[2]
				);
			}

			printf( '<div class="qedgrid__item__image" %s><div class="qedgrid__item__content"><h3 class="qedgrid__item__title">%s</h3><div class="qedgrid__item__meta"><time datetime="%s" itemprop="datePublished">%s</time></div></div></div>',
				$bg_image,
				qed_limit_title_length(get_the_title(), 64),
				get_the_time( 'Y-m-d' ),
				get_the_date()
				//$is_single ? $featured : sprintf( '<a class="qedgrid__item__top__image" href="%s">%s</a>', esc_url( $permalink ), $thumbnail )
			);
		} else {
			$bg_image = 'style="background-image: url(' . qed_placeholder_img_src() . ')"';
			printf( '<div class="qedgrid__item__image" %s></div>',
				$bg_image
			);
		}// End if().
		?>
	</a>
</article>
