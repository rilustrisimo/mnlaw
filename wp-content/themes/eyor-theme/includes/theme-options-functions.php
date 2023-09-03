<?php
/**
 * Functions related to Theme Options section.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0
 */
defined( 'ABSPATH' ) || die();

/**
 * Walker function for categories list making function.
 *
 * @param  array  &$set     link to current items set.
 * @param  assoc  $children mapper that contains set of items for each parent.
 * @param  string $cur_id   current element id.
 * @param  assoc  $el       element that should be added to set.
 * @param  string $pad
 * @return void
 */
function __qed_vp_cat_list_walker( &$set, $children, $cur_id, $el, $pad ) {
	if ( $pad ) {
		$el['label'] = $pad . $el['label'];
	}

	$set[] = $el;

	if ( isset( $children[ $cur_id ] ) ) {
		foreach ( $children[ $cur_id ] as $child_id => $child_el ) {
			__qed_vp_cat_list_walker( $set, $children, $child_id, $child_el, $pad . '&nbsp;&nbsp;&nbsp;' );
		}
	}
}

if ( ! function_exists( 'qed_vp_header_section_masks_list' ) ) {
	/**
	 * Returns set of masks available for header section image.
	 *
	 * @return array
	 */
	function qed_vp_header_section_masks_list() {
		$list = apply_filters( 'qed_get_header_section_masks', array(
			'' => esc_html__( 'None', 'swishdesign' ),
			'default' => esc_html__( 'Default', 'swishdesign' ),
		) );

		$result = array();

		if ( $list ) {
			foreach ( $list as $value => $label ) {
				$result[] = array(
					'label' => $label,
					'value' => $value,
				);
			}
		}

		return $result;
	}
}

/**
 * Returns the link for focus button.
 *
 * @return array
 */
function qed_vp_focus_button_link( $meta ) {

	$link = null;

	switch ( $meta['link_type'] ) {
		case 'page':
			$link = get_permalink( $meta['page_id'] );
			break;
		case 'post':
			$link = get_permalink( $meta['post_id'] );
			break;
		case 'external':
			$link = $meta['external_link'];
			break;
	}

	return $link;
}

/**
 * Returns prepared accordion section if the focus button type is content.
 *
 * @return array
 */
function qed_focus_button_accordion_section( $meta ) {
	$accordions = $meta['accordion_group'];
	$accordion_style = $meta['accordion_style'];
	$accordion_items = '';

	foreach ( $accordions as $accordion ) {
		$accordion_items .= sprintf('[accordion_item title="%s" style="%s"]%s[/accordion_item]',
			$accordion['accordion_item_title'],
			$accordion_style,
			do_shortcode($accordion['accordion_item_content'])
		);
	}

	return sprintf('[accordion style="%s"]%s[/accordion]',
			$accordion_style,
			$accordion_items
	);
}
