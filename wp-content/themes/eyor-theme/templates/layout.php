<?php
/**
 * Layout template part that determines how content should be rendered based on in has any content for the sidebar.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign/QED
 * @version   1.0.0
 */

if ( ! $sidebar || ! trim( $sidebar ) ) {
	print $content;
} else {
	printf(
		'<div class="row">' .
		'<main class="col-md-9" role="main">%s</main>' .
		'%s' .
		'</div>',
		$content,
		$sidebar
	);
}