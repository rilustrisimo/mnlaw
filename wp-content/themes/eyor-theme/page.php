<?php
/**
 * Page template file.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package Eyorsogood_Design
 * @version   1.0.0
 */

get_header();
if ( have_posts() ) : ?>
	<?php while ( have_posts() ) { the_post(); ?>
		<div class="page-single">
			<main class="page-single__content" role="main">
				<?php //qed_render_template_part( 'templates/header/title-block', '', array('title' => get_the_title()) ); ?>
				<?php get_template_part( 'templates/parts/focusbutton', 'section' ); ?>
				<?php the_content(); ?>
				<?php get_template_part( 'templates/parts/accordion', 'section' ); ?>
			</main>
		</div>
	<?php } ?>
<?php else :
	get_template_part( 'templates/content', 'none' );
endif;

get_footer();
