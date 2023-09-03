<?php
/**
 * Sidebar template file.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @version   1.0.0
 */

$show_sidebar = is_active_sidebar( 'sidebar' );

if ( ! $show_sidebar ) {
	return;
}
?>
<aside class="col-md-3 sidebar" role="complementary">
	<?php dynamic_sidebar( 'sidebar' ); ?>
</aside>
