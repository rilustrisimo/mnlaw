<?php
/**
 * Helper for processing [tabs] and [tab_item] shortcodes.
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

class QEDDTP_Shortcodes_Tabs {

	public static $tabs_view = 'templates/shortcodes/tabs';

	public static $id_prefix = 'attab';

	protected static $items = array();

	protected static $item_index = 0;

	protected static $id_index = 0;

	public static function register( $tabs_shortcode_name, $tab_item_shortcode_name ) {
		$class = get_class();
		$result = false;

		if ( $tabs_shortcode_name ) {
			add_shortcode( $tabs_shortcode_name, array( $class, 'tabs_do_shortcode' ) );
			$result = true;
		}

		if ( $tab_item_shortcode_name ) {
			add_shortcode( $tab_item_shortcode_name, array( $class, 'tab_item_do_shortcode' ) );
			$result = true;
		}

		return $result;
	}

	public static function tabs_do_shortcode( $atts, $content = null ) {
		if ( ! $content ) {
			return '';
		}

		$atts = shortcode_atts( array(
			'style' => '',
			'css_class' => '',
			'view' => ''
		), $atts );

		$tab_comp_id = self::get_id( true );
		$tab_content_html = do_shortcode( $content );

		$atts['content'] = $content;
		$atts['items'] = self::get_items();

		return QEDDTP()->shortcodes_helper()->render_view( self::$tabs_view, $atts['view'], $atts );
	}

	public static function tab_item_do_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'is_active' => ''
		), $atts );

		$item_id = self::get_next_item_id();
		$is_active = QEDDTP()->shortcodes_helper()->attribute_is_true( $atts['is_active'] );

		self::register_item( $item_id, $atts['title'], $content, $is_active );

		return '';
	}

	public static function get_id( $generate_new = false ) {
		if ( $generate_new ) {
			self::$id_index++;
			self::$item_index = 0;
		}
		return self::$id_prefix . self::$id_index;
	}

	public static function get_next_item_id() {
		self::$item_index++;
		return self::get_id() . '_' . self::$item_index;
	}

	public static function get_items() {
		$id = self::get_id();
		if ( isset( self::$items[$id] ) ) {
			return self::$items[$id];
		}
		return array();
	}

	public static function register_item( $item_id, $title, $content, $is_active = false ) {
		$id = self::get_id();
		if ( ! isset( self::$items[$id] ) ) {
			self::$items[$id] = array();
		}
		self::$items[$id][$item_id] = array(
			'title' => $title,
			'content' => $content,
			'is_active' => $is_active
		);
	}
}