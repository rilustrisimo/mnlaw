<?php
/**
 * Template for pages with empty content.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @version   1.0.0
 */

?>
<div class="page-404">
	<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
		<div class="page-404__box padding-all">
			<?php
			printf( esc_html__( 'Ready to publish your first post?', 'swishdesign' ) );
			printf( ' <a href="%1$s">' . esc_html__( 'Get started here', 'swishdesign' ) . '</a>.', admin_url( 'post-new.php' ) );
			?>
		</div>
	<?php elseif ( is_search() ) : ?>
		<div class="page-404__box padding-top padding-bottom">
			<div class="page-404__notice padding-left padding-right"><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with different keywords.', 'swishdesign' ); ?></div>
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-form page-404__form page-404__form--style2 padding-left padding-right">
				<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Type in your request...', 'swishdesign' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
				<div class="page-404__form--style2__button-wrap">
					<button type="submit" class="search-submit page-404__form--style2__button qedbtn"><i class="qedbtn__icon fa fa-search"></i><?php esc_attr_e( 'Search', 'swishdesign' ); ?></button>
				</div>
			</form>
		</div>
	<?php else : ?>
		<div class="page-404__container stick-to-top stick-to-bottom">
			<div class="page-404__content">
				<div class="page-404__image"></div>
				<div class="page-404__map"></div>
				<p class="page-404__description"><?php esc_html_e( 'Oops! The page you are looking for is not found!', 'swishdesign' ); ?></p>
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-form page-404__form page-404__form--style1">
					<input type="search" class="search-field" placeholder="<?php esc_attr_e( 'Type in your request...', 'swishdesign' ); ?>" value="<?php echo get_search_query(); ?>" name="s">
					<i class="fa fa-search"></i>
					<input type="submit" class="search-submit" value="<?php esc_attr_e( 'Search', 'swishdesign' ); ?>">
				</form>
			</div>
		</div>
	<?php endif; ?>
</div>