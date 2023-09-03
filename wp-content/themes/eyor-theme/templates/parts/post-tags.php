<?php
/**
 * Post tags rendering template part.
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

if ( ! has_tag() ) {
	return '';
}
?>
<div class="post-tags margin-top">
	<span><i class="fa fa-tags"></i><?php esc_html_e( 'Tags', 'swishdesign' ); ?>:</span>
	<?php the_tags( '', ' ', '' ); ?>
</div>
