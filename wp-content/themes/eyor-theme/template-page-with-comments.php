<?php
/**
 * Template Name: Page with comments
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

get_header();

if ( have_posts() ) : ?>
	<?php while ( have_posts() ) { the_post(); ?>
		<div class="page-single">
			<main class="page-single__content" role="main">
				<?php
//				$thumb = qed_get_the_post_thumbnail( null, 'thumb_single' );
//				if ( $thumb ) {
//					printf( '<div class="margin-bottom">%s</div>', $thumb );
//				}
				the_content();
				get_template_part( 'templates/parts/accordion', 'section' );
				if ( comments_open() ) {
					comments_template();
				}
				?>
			</main>
		</div>
	<?php } ?>
<?php else :
	get_template_part( 'templates/content', 'none' );
endif;

get_footer();
