<?php
/**
 * Template Name: Homepage
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
				<?php //print qed_get_the_post_thumbnail( null, 'thumb_single' ); ?>
				<div class="section-white-box padding-all"><?php the_content(); ?></div>
				<?php get_template_part( 'templates/parts/accordion', 'section' ); ?>
			</main>
		</div>
	<?php } ?>
<?php else :
	get_template_part( 'templates/content', 'none' );
endif;

get_footer();
