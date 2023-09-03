<?php
/**
 * Footer widgets area template.
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

$columns_count = qed_get_footer_columns();

$has_active_area = false;
$cur_index = $columns_count;
while ( $cur_index >= 1 ) {
	if ( is_active_sidebar( 'footer' . $cur_index ) ) {
		$has_active_area = true;
		break;
	}
	$cur_index--;
}

if ( ! $has_active_area ) {
	return '';
}

$col_class = $columns_count <= 4 ? 'col-md-' . (12 / $columns_count) : 'col-md-3';
?>
<div class="footer__widgets-area-wrap">
	<div class="container-fluid">
		<div class="row margin-top margin-bottom footer__widgets-areas">
			<?php
			for ( $i = 1; $i <= $columns_count; $i++ ) {
				echo '<div class="' . esc_attr( $col_class . ' footer__widgets-area footer__widgets-area--' . $i ) . '">';
				dynamic_sidebar( 'footer' . $i );
				echo '</div>';
			}
			?>
		</div>
	</div>
</div>
