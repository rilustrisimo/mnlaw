<?php
/**
 * Category details page template.
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

if ( is_active_sidebar( 'sidebar' ) ) : ?>
	<div class="row">
		<main class="col-md-9" role="main">
			<?php if ( category_description() ) : ?>
				<div class="post-category__description padding-all margin-bottom"><?php echo category_description(); ?></div>
			<?php endif; ?>
			<?php get_template_part( 'loop' ); ?>
		</main>
		<?php get_sidebar(); ?>
	</div>
<?php else : ?>
	<?php if ( category_description() ) : ?>
		<div class="post-category__description padding-all margin-bottom"><?php echo category_description(); ?></div>
	<?php endif;
	get_template_part( 'loop' );
endif;

get_footer();
