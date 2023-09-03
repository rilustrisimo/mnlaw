<?php
/**
 * Notices view.
 *
 * @var array $messages
 * @var string $type
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

if ( ! $messages ) {
	return;
}

// type success default.
$types = array(
	'error' => 'notices-box--error',
	'notice' => 'notices-box--notice',
);

$type = isset( $type ) ? $type : '';
$type_class = isset( $types[ $type ] ) ? ' ' . $types[ $type ] : '';

$icon_class = ' fa-check-circle';
switch ( $type ) {
	case 'error':
		$icon_class = ' fa-exclamation-triangle';
		break;
	case 'notice':
		$icon_class = ' fa-info-circle';
		break;
}
?>

<div class="notices-box block-after-indent<?php echo esc_attr( $type_class ); ?>">
	<ul class="notices-box__list">
		<?php foreach ( $messages as $message ) { ?>
			<li class="notices-box__item"><i class="notices-box__item__icon fa<?php echo esc_attr( $icon_class ); ?>"></i><?php echo wp_kses_post( $message ); ?></li>
		<?php } ?>
	</ul>
</div>