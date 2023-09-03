<?php
/**
 * Template Name: Page with sidebar
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
		<div class="page-single row">
			<main class="page-single__content col-md-9" role="main">
				<?php //print qed_get_the_post_thumbnail( null, 'thumb_single' ); ?>
				<div class="section-white-box padding-all"><?php the_content(); ?></div>
				<?php get_template_part( 'templates/parts/accordion', 'section' ); ?>
			</main>
			<?php get_sidebar(); ?>
		</div>
	<?php } ?>
<?php else :
	get_template_part( 'templates/content', 'none' );
endif;

get_footer();
