<?php
/**
 * Component for handling page focus button section meta settings.
 * Requires vaffpress framework.
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
 * Class QED_Focus_Button_Section
 */
class QED_Focus_Button_Section extends SD_Component {

	/**
	 * The focus button section meta key.
	 *
	 * @var string
	 */
	public $page_meta_key = 'focus_button_section_meta';

	/**
	 * Method to get the section meta.
	 *
	 * @return array
	 */
	public function get_section_meta() {
		$section_meta = array();

		$is_single = is_singular();

		$section_post_id = $is_single ? get_the_ID() : 0;

		if ( $section_post_id ) {
			$section_meta = $this->get_section_meta_by_post_id( $section_post_id );
		}

		return $section_meta;
	}

	/**
	 * Method to get section meta.
	 *
	 * @param     $post_id
	 * @param int $max_depth
	 *
	 * @return array
	 */
	public function get_section_meta_by_post_id( $post_id ) {
		$section_meta = array();

//		$meta_object = $post_id > 0 && $this->page_meta_key ? vp_metabox( $this->page_meta_key, null, $post_id ) : null;
//		if ( $meta_object && $meta_object->meta ) {
//			$section_meta = $meta_object->meta;
//		}

		$section_meta['focus_buttons_is_enabled'] = qed_get_option( 'focus_buttons_is_enabled', $post_id );
		$section_meta['focus_button_group'] = qed_get_option( 'focus_button_group', $post_id );

		return $section_meta;
	}
}
