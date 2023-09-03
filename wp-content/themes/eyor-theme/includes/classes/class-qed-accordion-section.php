<?php
/**
 * Component for handling page accordion section meta settings.
 * Requires vaffpress framework.
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

/**
 * Class QED_Accordion_Section
 */
class QED_Accordion_Section extends SD_Component {

	/**
	 * The focus button section meta key.
	 *
	 * @var string
	 */
	public $page_meta_key = 'accordion_section_meta';

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
	 * @param int $post_id
	 * @param int $max_depth
	 *
	 * @return array
	 */
	public function get_section_meta_by_post_id( $post_id, $max_depth = 5 ) {
		$section_meta = array();

//		$meta_object = $post_id > 0 && $this->page_meta_key ? vp_metabox( $this->page_meta_key, null, $post_id ) : null;
//		if ( $meta_object && $meta_object->meta ) {
//			$section_meta = $meta_object->meta;
//		}

		$section_meta['accordion_is_enabled'] = qed_get_option( 'accordion_is_enabled', $post_id );
		$section_meta['accordion_style'] = qed_get_option( 'accordion_style', $post_id );
		$section_meta['accordion_group'] = qed_get_option( 'accordion_group', $post_id );

		return $section_meta;
	}
}
