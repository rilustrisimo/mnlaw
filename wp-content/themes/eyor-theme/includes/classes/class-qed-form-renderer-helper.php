<?php
/**
 * Helper for form rendering/fields rendering, js related init fuctions running.
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
 * Class QED_Form_Renderer_Helper
 */
class QED_Form_Renderer_Helper extends SD_Component {

	/**
	 * Configuration object for form settings.
	 *
	 * @var array
	 */
	public $field_config = array();

	/**
	 * Assoc of field values.
	 *
	 * @var array
	 */
	public $field_vals = array();

	/**
	 * Assoc of field errors.
	 *
	 * @var array
	 */
	public $field_errors = array();

	/**
	 * Date format that should be used on the booking form.
	 *
	 * @see get_date_format
	 * @var string
	 */
	public $date_format = 'd/m/Y H:i';

	/**
	 * Characters set that possible to use as date format delimiter symbols.
	 *
	 * @var array
	 */
	protected $date_format_delimiters = array( ' ', '/', '-' );

	/**
	 * Template.
	 *
	 * @var string
	 */
	public $row_template = '<div class="form-block__item form-block__field-width-icon form-block__field--{field_key}">{label}{input_html}{icon_html}</div>';

	/**
	 * Template.
	 *
	 * @var string
	 */
	public $checkbox_row_template = '<div class="form-block__item form-block__field-width-icon form-block__field--checkbox form-block__field--{field_key}">{label}{input_html}{icon_html}</div>';

	/**
	 * Renders fields from configuration via $field_config option.
	 *
	 * @return string
	 */
	public function render() {
		return $this->render_fields_set( array_keys( $this->field_config ) );
	}

	/**
	 * Renders specified set of fields from $fields_config option.
	 *
	 * @var array     $field_keys list of field key.
	 * @return string
	 */
	public function render_fields_set( array $field_keys ) {
		$fields = array();
		foreach ( $field_keys as $field_key ) {
			$field_config = isset( $this->field_config[ $field_key ] ) ? $this->field_config[ $field_key ] : array();
			$fields[ $field_key ] = $this->render_field_row( $field_key, $field_config );
		}

		return join( PHP_EOL, $this->order_fields_set( $fields ) );
	}

	/**
	 * Renders specefied field row.
	 *
	 * @param  string $field_key the field key.
	 * @param  array  $field_config the field configuration.
	 * @return string
	 */
	public function render_field_row( $field_key, array $field_config ) {
		if ( 'checkbox' === $field_config['type'] ) {
			return strtr( $this->checkbox_row_template, array(
					'{field_key}' => esc_attr( $field_key ),
					'{label}' => isset( $field_config['label'] ) ? esc_html( $field_config['label'] ) : '',
					'{input_html}' => $this->render_input( $field_key, $field_config ),
					'{icon_html}' => ! empty( $field_config['icon_class'] ) ? sprintf( '<i class="%s"></i>', $field_config['icon_class'] ) : '',
			) );
		} else {
			return strtr( $this->row_template, array(
					'{field_key}' => esc_attr( $field_key ),
					'{label}' => isset( $field_config['label'] ) ? esc_html( $field_config['label'] ) : '',
					'{input_html}' => $this->render_input( $field_key, $field_config ),
					'{icon_html}' => ! empty( $field_config['icon_class'] ) ? sprintf( '<i class="%s"></i>', $field_config['icon_class'] ) : '',
			) );
		}
	}

	/**
	 * Renders field input element.
	 *
	 * @param  string $field_key the field key.
	 * @param  array  $field_config the field configuration.
	 * @return string
	 */
	public function render_input( $field_key, array $field_config ) {
		$type = isset( $field_config['type'] ) ? $field_config['type'] : 'text';

		$value = '';
		if ( ! isset( $this->field_vals[ $field_key ] ) ) {
			if ( isset( $field_config['default'] ) ) {
				$value = $field_config['default'];
			}
		} else {
			$value = $this->field_vals[ $field_key ];
		}

		$name = isset( $field_config['name'] ) ? $field_config['name'] : $field_key;
		$errors = isset( $this->field_errors[ $field_key ] ) ? $this->field_errors[ $field_key ] : array();

		$attributes = ! empty( $field_config['attributes'] ) ? $field_config['attributes'] : array();

		$attributes['name'] = $name;

		$transparent_attributes = array( 'id','class' );
		foreach ( $transparent_attributes as $att_name ) {
			if ( ! empty( $field_config[ $att_name ] ) ) {
				$attributes[ $att_name ] = $field_config[ $att_name ];
			}
		}

		if ( $errors ) {
			$attributes['title'] = join( '<br>', $errors );
		}

		$result = '';
		switch ( $type ) {
			case 'select':
				$options_html = '';
				if ( ! empty( $field_config['options'] ) ) {
					$options_html = $this->render_options( $field_config['options'], $value );
				}
				$result = sprintf( '<select %s>%s</select>', $this->render_field_attributes( $attributes, $field_key, $field_config ), $options_html );
				break;

			case 'textarea':
				if ( ! empty( $field_config['placeholder'] ) ) {
					$attributes['placeholder'] = $field_config['placeholder'];
				}

				$textarea_attributes = $attributes;
				unset( $textarea_attributes['type'] );

				$result = sprintf( '<textarea %s>%s</textarea>', $this->render_field_attributes( $textarea_attributes, $field_key, $field_config ), $value );
				break;

			case 'date':
				$attributes['type'] = 'text';
				$attributes['value'] = $value;
				if ( ! empty( $field_config['placeholder'] ) ) {
					$attributes['placeholder'] = $field_config['placeholder'];
				}

				$textarea_attributes = $attributes;
				unset( $textarea_attributes['type'] );
				$result = sprintf( '<div class="date-wrapper"><input %s><div class="clearfix"></div></div>', $this->render_field_attributes( $attributes, $field_key, $field_config ) );
				break;

			case 'text':
			case 'hidden':
			case 'number':
			default:
				$attributes['value'] = $value;
				if ( in_array( $type, array( 'hidden', 'number', 'text', 'checkbox' ), true ) ) {
					$attributes['type'] = $type;
				} else {
					$attributes['type'] = 'text';
				}

				if ( ! empty( $field_config['placeholder'] ) ) {
					$attributes['placeholder'] = $field_config['placeholder'];
				}

				$result = sprintf( '<input %s>', $this->render_field_attributes( $attributes, $field_key, $field_config ) );

				/*if ( 'date' == $name ) {
					$options_html = '';
					if ( ! empty( $field_config['options'] ) ) {
						$options_html = $this->render_options( $field_config['options'], $value );
					}
					$result = sprintf( '<select %s>%s</select>', $this->render_field_attributes( $attributes, $field_key, $field_config ), $options_html );
				}*/

				break;
		} // End switch().

		return $result;
	}

	/**
	 * Renders form field value and title attributes.
	 * Value for the 'value' attribute taken from 'field_vals' set,
	 * the value for the 'title' attribute - from field errors.
	 *
	 * @param  array  $attributes list of attributes.
	 * @param  string $field_key the field key.
	 * @param  assoc  $field_config the field configuration.
	 * @return string
	 */
	public function render_field_attributes( array $attributes, $field_key, $field_config ) {
		if ( ! $attributes ) {
			return '';
		}

		$parts = array();
		foreach ( $attributes as $key => $value ) {
			$parts[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}
		return join( ' ', $parts );
	}

	/**
	 * Allows change fields order during fields set rendering.
	 *
	 * @param  array $fields list of fields.
	 * @return array
	 */
	public function order_fields_set( array $fields ) {
		return apply_filters( 'qed_form_renderer_order_fields_set', $fields, $this );
	}

	/**
	 * Renders html for of options elements for select input.
	 *
	 * @param  assoc  $list the list.
	 * @param  string $selected the selected option.
	 * @return string
	 */
	protected function render_options( $list, $selected ) {
		if ( ! $list ) {
			return '';
		}
		$parts = array();
		foreach ( $list as $val => $text ) {
			if ( $val == 'title' ) {
				$parts[] = sprintf( '<option data-content="%s" value="%s"%s>%s</option>',
					$text,
					esc_attr( $val ),
					$selected && $selected == $val ? ' selected="selected"' : '',
					esc_html( $text )
				);
			} else {
				$parts[] = sprintf( '<option value="%s"%s>%s</option>',
				esc_attr( $val ),
				$selected && $selected == $val ? ' selected="selected"' : '',
				esc_html( $text )
				);
			}
			
		}
		return join( PHP_EOL, $parts );
	}

	/**
	 * Init form validations.
	 *
	 * @param string $items_selector the item selector.
	 */
	public function init_js_errors( $items_selector ) {
		if ( ! $items_selector ) {
			return;
		}

		SD_Js_Client_Script::add_script('initValidationForms', <<<SCRIPT
			Theme.FormValidationHelper
				.initTooltip('{$items_selector}')
				.addClass('form-validation-item')
				.tooltip('show');
SCRIPT
		);
	}

	/**
	 * Returns date format for the booking form.
	 *
	 * @param  string $for target element for that date should be returned.
	 * @return string
	 */
	public function get_date_format( $for = 'php' ) {
		$date_format = $this->date_format;
		switch ( $for ) {
			case 'datepicker':
				$replacement = array(
						'm' => 'mm',
						'd' => 'dd',
						'Y' => 'yy',
						'H:i' => '',
						'h:i' => '',
						'g:i' => '',
						'A' => '',
						'a' => '',
				);
				return trim( str_replace( array_keys( $replacement ), $replacement, $date_format ) );
				break;

			case 'datepicker-time':
				$replacement = array(
						'm' => '',
						'd' => '',
						'Y' => '',
						'h:i' => 'hh:ii',
						'H:i' => 'hh:ii',
						'g:i' => 'h:ii',
				);

				return trim(
						str_replace(
								array_keys( $replacement ),
								$replacement,
								$date_format
						),
						$this->date_format_delimiters ? join( '', $this->date_format_delimiters ) : ' /-'
				);

			case 'php-no-time':
				$replacement = array(
						'H:i' => '',
						'h:i' => '',
						'g:i' => '',
						'A' => '',
						'a' => '',
				);
				return trim( str_replace( array_keys( $replacement ), $replacement, $date_format ) );
				break;

			default:
				return $date_format;
				break;
		}
	}
}
