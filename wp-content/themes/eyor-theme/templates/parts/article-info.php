<?php
/**
 * Post attributes rendering template part.
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

$settings = array(
	'show_author' => true,
	'show_date' => true,
	'show_categories' => false,
	'show_comments' => false,
);

$has_comments = $settings['show_comments'] && comments_open() && get_comments_number() > 0;

$author_id = $settings['show_author'] ? get_the_author_meta( 'ID' ) : null;

$category_links = array();
if ( $settings['show_categories'] ) {
	$cat_ids = wp_get_post_categories( get_post()->ID );
	if ( $cat_ids ) {
		$categories = get_categories( array(
			'include' => $cat_ids,
		) );
		foreach ( $categories as $category ) {
			$category_links[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>';
		}
	}
}
?>
<div class="article-info padding-left padding-right">
	<?php if ( $author_id ) { ?>
		<div class="article-info__item"><a href="<?php echo esc_url( get_author_posts_url( $author_id ) ) ?>"><span itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name"><?php echo get_the_author(); ?></span></span></a></div>
	<?php } ?>
	<?php if ( $settings['show_date'] ) { ?>
		<div class="article-info__item article-info__item-delimeter"><i class="fa fa-circle"></i></div>
		<div class="article-info__item">
			<a href="<?php the_permalink(); ?>"><time datetime="<?php the_time( 'Y-m-d' ) ?>" itemprop="datePublished"><?php echo get_the_date(); ?></time></a>
			<meta itemprop="dateModified" content="<?php the_modified_time( 'Y-m-d' ); ?>">
		</div>
	<?php } ?>
	<?php if ( $category_links ) { ?>
		<div class="article-info__item article-info__item-delimeter"><i class="fa fa-circle"></i></div>
		<div class="article-info__item"><span itemprop="articleSection"><?php echo join( ', ', $category_links ); ?></span></div>
	<?php } ?>
	<?php if ( $has_comments ) { ?>
		<div class="article-info__item article-info__item-delimeter"><i class="fa fa-circle"></i></div>
		<div class="article-info__item"><?php comments_popup_link( esc_html__( 'No Comments', 'swishdesign' ), esc_html__( '1 Comment', 'swishdesign' ), esc_html__( '% Comments', 'swishdesign' ) ); ?></div>
	<?php } ?>
</div>