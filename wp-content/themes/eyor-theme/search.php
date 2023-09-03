<?php
/**
 * Search template part file.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @version   1.0.0
 */

get_header();

if ( is_active_sidebar( 'sidebar' ) ) : ?>
	<div class="row">
		<main class="col-md-9" role="main"><?php get_template_part( 'loop', 'search' ); ?></main>
		<?php get_sidebar(); ?>
	</div>
<?php else :
	get_template_part( 'loop', 'search' );
endif;

get_footer();
