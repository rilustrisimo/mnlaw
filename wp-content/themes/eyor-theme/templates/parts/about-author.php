<?php
/**
 * Author about template part.
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

$author_id = get_the_author_meta( 'ID' );
if ( $author_id < 1 ) {
	return;
}
?>
<div class="about-author margin-top" itemscope itemtype="http://schema.org/Person">
	<div class="section-title title title--small title--center title--decoration-bottom-center title--underline">
		<h3 class="title__primary"><?php esc_html_e( 'About Author', 'swishdesign' ); ?></h3>
	</div>
	<div class="about-author__content padding-all">
		<meta itemprop="image" content="<?php echo esc_url( get_avatar_url( $author_id ) ); ?>" />
		<?php echo get_avatar( $author_id, 110, '', 'client-photo-1' ); ?>
		<div class="about-author__name" itemprop="name"><?php echo esc_html( get_the_author_meta( 'display_name' ) ); ?></div>
		<div class="about-author__description" itemprop="description"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></div>
	</div>
</div>
