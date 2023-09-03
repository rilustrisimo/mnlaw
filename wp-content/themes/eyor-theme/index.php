<?php
/**
 * Index template.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

get_header();
if ( qed_show_blog_sidebar() ) : ?>
	<div class="row">
		<main class="col-md-9" role="main"><?php get_template_part( 'loop' ); ?></main>
		<?php get_sidebar(); ?>
	</div>
<?php
else :
	get_template_part( 'loop' );
endif;
get_footer();
