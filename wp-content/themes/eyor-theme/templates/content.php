<?php
/**
 * Content template part.
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
$thumbnail = $thumb_id ? qed_get_the_post_thumbnail( $post_id, 'thumb_single' ) : null;

$post_class = 'blog__item margin-bottom-large';
if ( ! $thumbnail ) {
	$post_class .= ' blog__item--without-image';
}

$is_single = is_single();
$permalink = get_permalink();
?>
<article id="<?php echo get_post_type() . '-' . $post_id; ?>" <?php post_class( $post_class ); ?> itemscope itemtype="http://schema.org/BlogPosting">
	<div class="blog__item__box">
		<?php if ( is_sticky() ) : ?>
			<div class="blog__item__sticky">
				<div class="blog__item__sticky__bg"><i class="fa fa-bookmark"></i></div>
			</div>
		<?php endif; ?>
		<div class="blog__item__info">
			<?php if ( $is_single ) : ?>
				<h1 class="blog__item__title" itemprop="headline"><?php the_title(); ?></h1>
			<?php else : ?>
				<h2 class="blog__item__title" itemprop="headline"><a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a></h2>
			<?php endif; ?>

			<?php
			if ( $permalink ) {
				printf( '<meta itemprop="url" content="%s">', esc_url( $permalink ) );
			} // End if().
			?>

			<?php
			if ( $thumbnail ) {
				$thumb_src = wp_get_attachment_image_src( $thumb_id, 'full' );
				if ( $thumb_src ) {
					printf( '<span itemprop="image" itemscope itemtype="http://schema.org/ImageObject"><meta itemprop="url" content="%s"><meta itemprop="width" content="%s"><meta itemprop="height" content="%s"></span>',
						esc_url( $thumb_src[0] ),
						esc_url( $thumb_src[1] ),
						esc_url( $thumb_src[2] )
					);
				}

				printf( '<div class="blog__item__thumbnail">%s</div>',
					$is_single ? $thumbnail : sprintf( '<a href="%s">%s</a>', esc_url( $permalink ), $thumbnail )
				);
			} // End if().
			?>
		</div>
		<?php if ( $is_single ) : ?>
			<div class="blog-single__content padding-top">
				<div itemprop="articleBody">
					<?php the_content(); ?>
					<span class="social-share__title">Share this article:</span>
					<?php echo do_shortcode('[social_warfare]'); ?>
				</div>
<!--				<div class="margin-top">--><?php //qed_render_post_pagination(); ?><!--</div>-->
				<?php
//				if ( qed_get_option( 'post_tags', 'option' ) ) {
//					get_template_part( 'templates/parts/post-tags' );
//				} // End if().
				?>
				<?php
//				if ( qed_get_option( 'social_sharing_blog_single', 'option' ) && $is_single ) {
//					get_template_part( 'templates/parts/share-badges' );
//				} // End if().
				?>
			</div>
		<?php else : ?>
			<meta itemprop="description" content="<?php echo esc_attr( qed_get_short_description( null, 300 ) ); ?>">

			<div class="blog__item__content <?php echo get_the_content() ? ' padding-all' : ' padding-top'; ?>">
				<?php qed_the_content(); ?>
			</div>

			<?php
//			if ( qed_get_option( 'social_sharing_blog', 'option' ) ) {
//				get_template_part( 'templates/parts/share-buttons' );
//			} // End if().
			?>
		<?php endif; ?>
	</div>

	<?php if ( $is_single ) : ?>
		<?php
//		printf('<div class="more-posts margin-bottom-large"><div class="more-posts__title margin-bottom-large"><span>More Posts</span></div>%s</div>',
//			do_shortcode( '[more_posts number="3" read_more_text="Read more" words_limit="10" ignore_sticky_posts="on" order="DESC" orderby="rand"]' )
//		);
		?>

		<?php
//		if ( qed_get_option( 'about_author', 'option' ) ) {
//			get_template_part( 'templates/parts/about-author' );
//		} // End if().
		?>
		<?php
//		if ( qed_get_option( 'single_post_navigation', 'option' ) ) {
//			get_template_part( 'templates/parts/post-navigation' );
//		} // End if().
		?>

		<?php //comments_template(); ?>
	<?php endif; ?>
</article>
<div class="form-subscribe">
	<h3 class="form-subscribe__title">Subscribe to Get the Latest News</h3>
	<div class="form-subscribe__form-wrap">
		<?php echo do_shortcode('[mc4wp_form id="314"]'); ?>
	</div>
</div>
