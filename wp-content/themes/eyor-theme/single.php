<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Eyorsogood_Design
 */

get_header(); ?>

	<div class="page-single-narrow">
		<main class="page-single__content" role="main">

		<?php
		while ( have_posts() ) : the_post();

			get_template_part( 'templates/content', get_post_type() );

//			the_post_navigation();

			// If comments are open or we have at least one comment, load up the comment template.
//			if ( comments_open() || get_comments_number() ) :
//				comments_template();
//			endif;

		endwhile; // End of the loop.
		?>

		</main><!-- .page-single__content -->
	</div><!-- .page-single-narrow -->

<?php
get_sidebar();
get_footer();
