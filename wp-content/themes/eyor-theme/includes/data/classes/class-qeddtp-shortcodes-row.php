<?php
/**
 * Helper for processing [row] and [column] shorcodes.
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

class QEDDTP_Shortcodes_Row {

	/**
	 * List of aliases available for definition of the column width.
	 *
	 * @var assoc
	 */
	public static $col_width_aliases = array(
		'one' => 1,
		'two' => 2,
		'half' => 2,
		'three' => 3,
		'four' => 4,
		'five' => 5,
		'seven' => 7,
		'eight' => 8,
		'nine' => 9,
		'ten' => 10,
		'eleven' => 11,
		'twelve' => 12,
	);

	/**
	 * Determines max columns number.
	 *
	 * @var integer
	 */
	protected static $default_grid_size = 12;

	/**
	 * Registers row and column renderer on specified shortcode names.
	 *
	 * @param  string $row_shortcode_name    shortcode name that should be used for 'row'.
	 * @param  string $column_shortcode_name shortcode name that should be used for 'column'.
	 * @return boolean
	 */
	public static function register( $row_shortcode_name, $column_shortcode_name ) {
		$class = __CLASS__;

		$result = false;

		if ( $row_shortcode_name ) {
			add_shortcode( $row_shortcode_name, array( $class, 'do_shortcode_row') );
			$result = true;
		}

		if ( $column_shortcode_name ) {
			add_shortcode( $column_shortcode_name, array( $class, 'do_shortcode_column') );
			$result = true;
		}

		return $result;
	}

	/**
	 * Renders row shortcode.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	public static function do_shortcode_row( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'css_class' => '',
		), $atts );

		$rowClass = 'row' . ( $atts['css_class'] ? ' ' . $atts['css_class'] : '' );

		return '<div class="' . esc_attr( $rowClass ) . '">' . do_shortcode( $content ) . '</div>';
	}

	/**
	 * Renders column shortcode.
	 *
	 * @param  array  $atts     shortcode attributes.
	 * @param  string $content  shortcode content text.
	 * @return string
	 */
	public static function do_shortcode_column( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'width' => '',
			'css_class' => '',
			'add_mobile_spacer' => '',
		), $atts );

		$size_class = self::get_size_class_by_width_attribute( $atts['width'] );

		if ( $atts['css_class'] ) {
			$size_class .= ' ' . $atts['css_class'];
		}

		$spacer_html = QEDDTP()->shortcodes_helper()->attribute_is_true( $atts['add_mobile_spacer'] )
			? '<div class="margin-top margin-bottom visible-sm visible-xs"></div>'
			: '';

		return '<div class="' . esc_attr( $size_class ) . '">' . $spacer_html . do_shortcode( $content ) . '</div>';
	}

	public static function convert_width_to_class( $width ) {
		return self::get_size_class_by_width_attribute( $width );
	}

	/**
	 * Converts column width attribute into related css class.
	 *
	 * @param  string  $width
	 * @param  string  $prefix    css column class prefix.
	 * @param  integer $default   default width value.
	 * @param  string  $delimiter
	 * @return string
	 */
	protected static function get_size_class_by_width_attribute( $width, $prefix = 'col-md-', $default = 1, $delimiter = '/' ) {
		$size_index = 0;
		if ( $width ) {
			$parts = explode( $delimiter, $width );

			$default_grid_size = self::$default_grid_size;
			$size = self::convert_string_to_number( $parts[0], $default_grid_size );

			$base = ! empty( $parts[1] ) ? self::convert_string_to_number( $parts[1], $default_grid_size ) : $default_grid_size;
			if ( $size > 0 && $base > 0 ) {
				$multy_index = $base < $default_grid_size ? $default_grid_size / $base : 1;
				$size_index = $multy_index * $size;
			}
		}

		return $prefix . ($size_index > 0 ? $size_index : $default);
	}

	/**
	 * Converts width attribute value into number.
	 *
	 * @param  string $text_number   number/string alias (see $col_width_aliases option).
	 * @param  int    $default_value value that should be used if $text_number is empty.
	 * @return mixed
	 */
	protected static function convert_string_to_number( $text_number, $default_value = null ) {
		if ( ! $text_number ) {
			return $default_value;
		}

		if ( is_numeric( $text_number ) ) {
			return $text_number;
		}

		return isset( self::$col_width_aliases[$text_number] ) ? self::$col_width_aliases[$text_number] : $default_value;
	}
}