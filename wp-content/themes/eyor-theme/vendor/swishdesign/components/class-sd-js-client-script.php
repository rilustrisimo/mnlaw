<?php
/**
 * Class for inline JS injections.
 *
 * @example
 * <pre>
 * // to execute code on document redy event
 * SD_Js_Client_Script::add_script('initExampleClass','Example.init({x:100,y:200});');
 *
 * // to execute code in footer (without any wrappers)
 * SD_Js_Client_Script::add_script('initExampleClass','Example.init({x:100,y:200});', SD_Js_Client_Script::POS_FOOTER);
 * </pre>
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
 * Class SD_Js_Client_Script
 */
class SD_Js_Client_Script {

	/**
	 * On ready scripts.
	 *
	 * @var array
	 */
	private static $on_ready = array();

	/**
	 * Footer scripts.
	 *
	 * @var array
	 */
	private static $footer_scripts = array();

	/**
	 * Script files on the footer
	 *
	 * @var array
	 */
	private static $footer_script_files = array();

	const POS_ON_READY = 1;

	const POS_FOOTER = 2;

	const HANDLER_FOOTER = 1;

	/**
	 * List of handlers
	 *
	 * @var array
	 */
	private static $added_handlers = array();

	/**
	 * Initialize handlers
	 *
	 * @param int $type handler type.
	 * @return bool
	 */
	public static function init_handler( $type ) {
		if ( empty( self::$added_handlers[ $type ] ) ) {
			self::$added_handlers[ $type ] = true;
		} else {
			return false;
		}

		switch ( $type ) {
			case self::HANDLER_FOOTER:
				// wp_footer.
				$action_name = is_admin() ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';
				add_action( $action_name, array( 'SD_Js_Client_Script', 'print_footer_scripts' ) );
				break;
		}
	}

	/**
	 * Add scripts on ready or footer.
	 *
	 * @param string $id for the script.
	 * @param string $text the script to add.
	 * @param int    $position the position of the script.
	 * @throws Exception If the value is unsupported.
	 */
	public static function add_script( $id, $text, $position = 1 ) {
		self::init_handler( self::HANDLER_FOOTER );
		switch ( $position ) {
			case self::POS_FOOTER:
				self::$footer_scripts[ $id ] = $text;
				break;
			case self::POS_ON_READY:
				self::$on_ready[ $id ] = $text;
				break;
			default:
				throw new Exception( "Unsupported value for position parameter ($position)." );
				break;
		}
	}

	/**
	 * Add a script file.
	 *
	 * @param string $id for the script.
	 * @param string $url the file url.
	 * @param int    $position the position of the script.
	 * @throws Exception If the value is unsupported.
	 */
	public static function add_script_scriptfile( $id, $url, $position = 2 ) {
		self::init_handler( self::HANDLER_FOOTER );
		switch ( $position ) {
			case self::POS_FOOTER:
				self::$footer_script_files[ $id ] = $url;
				break;
			default:
				throw new Exception( "Unsupported value for position parameter ($position)." );
				break;
		}
	}

	/**
	 * Getter for the on ready script text.
	 *
	 * @param bool|false $without_reset flag to determine if we need to reset.
	 * @return string
	 */
	public static function get_onready_script_text( $without_reset = false ) {
		$result = '';
		if ( self::$on_ready ) {
			$result = 'jQuery(document).ready(function($){' .
			          join( "\n", self::$on_ready ) .
			          "\n" . '})';
			if ( ! $without_reset ) {
				self::$on_ready = array();
			}
		}
		return $result;
	}

	/**
	 * Getter for the footer scripts text.
	 *
	 * @param bool|false $without_reset flag to determine if we need to reset.
	 * @return string
	 */
	public static function get_footer_scripts_text( $without_reset = false ) {
		$result = '';
		if ( self::$footer_script_files ) {
			foreach ( self::$footer_script_files as $url ) {
				$result .= '<script type="text/javascript" src="' . $url . '"></script>' . "\n";
			}
			if ( ! $without_reset ) {
				self::$footer_script_files = array();
			}
		}
		if ( self::$footer_scripts ) {
			foreach ( self::$footer_scripts as $js_code ) {
				$result .= '<script type="text/javascript">' . $js_code . '</script>' . "\n";
			}
			if ( ! $without_reset ) {
				self::$footer_scripts = array();
			}
		}
		if ( $on_ready_script = self::get_onready_script_text( $without_reset ) ) {
			$result .= '<script type="text/javascript">' . $on_ready_script . '</script>' . "\n";
		}
		return $result;
	}

	/**
	 * Print the footer scripts.
	 */
	public static function print_footer_scripts() {
		echo self::get_footer_scripts_text();
	}

}
