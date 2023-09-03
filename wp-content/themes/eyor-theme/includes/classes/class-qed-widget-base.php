<?php
/**
 * Base class for widget components.
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
 * Class QED_Widget_Base
 */
class QED_Widget_Base extends WP_Widget {

	/**
	 * Configuration object for widget settings.
	 *
	 * @var array
	 */
	protected $fields_config = array();

	/**
	 * Outputs widget settings form.
	 *
	 * @param  assoc $instance settings instance.
	 * @return void
	 */
	public function form( $instance ) {
		$rows = array();
		foreach ( $this->fields_config as $fkey => $fconfig ) {
			$rows[] = $this->render_row( $fkey, $instance );
		}

		print join( '', $rows );
	}

	/**
	 * Outputs form row.
	 *
	 * @param string $field_code
	 * @param $instance
	 * @return string
	 */
	protected function render_row( $field_code, $instance ) {
		$controll_html = null;
		$field_config = $this->get_field_config( $field_code );

		if ( $field_config ) {
			$type = isset( $field_config['type'] ) ? $field_config['type'] : 'text';
			$value = isset( $instance[ $field_code ] ) ? $instance[ $field_code ] : null;

			$invert_label = false;
			switch ( $type ) {
				case 'select':
					$controll_html = $this->render_select_input( $field_code, $value, isset( $field_config['options'] ) ? $field_config['options'] : array() );
					break;

				case 'checkbox':
					$invert_label = true;
					$controll_html = $this->render_checkbox_input( $field_code, $value );
					break;

				case 'textarea':
					$controll_html = $this->render_textarea( $field_code, $value );
					break;

				default:
					$controll_html = $this->render_text_input( $field_code, $value );
					break;
			}
		}
		return $controll_html ? $this->render_input_row( $field_code, $this->get_field_label( $field_code ), $controll_html, '', $invert_label ) : '';
	}

	/**
	 * Outputs form input row.
	 *
	 * @param            $field_code
	 * @param            $label
	 * @param            $input_html
	 * @param string     $css_class
	 * @param bool|false $invert_label
	 *
	 * @return string
	 */
	protected function render_input_row( $field_code, $label, $input_html, $css_class = '', $invert_label = false ) {
		$class_attribute_text = $css_class ? sprintf( ' class="%s"', esc_attr( $css_class ) ) : '';

		if ( $invert_label ) {
			return sprintf( '<p%s>%s&nbsp;<label for="%s">%s</label></p>',
				$class_attribute_text,
				$input_html,
				esc_attr( $this->get_field_id( $field_code ) ),
				esc_html( $label )
			);
		} else {
			return sprintf( '<p%s><label for="%s">%s</label>&nbsp;%s</p>',
				$class_attribute_text,
				esc_attr( $this->get_field_id( $field_code ) ),
				esc_html( $label ),
				$input_html
			);
		}
	}

	/**
	 * Merge instances.
	 *
	 * @param $instance
	 *
	 * @return mixed
	 */
	protected function merge_instance( $instance ) {
		$defaults = $this->get_field_defaults();
		foreach ( $defaults as $fkey => $default_value ) {
			if ( ! array_key_exists( $fkey, $instance ) ) {
				$instance[ $fkey ] = $default_value;
			}
		}
		return $instance;
	}

	/**
	 * Returns set of default values for widget settings defined in 'fields_config' property.
	 *
	 * @return assoc
	 */
	protected function get_field_defaults() {
		$result = array();
		foreach ( $this->fields_config as $fkey => $fconfig ) {
			$result[ $fkey ] = isset( $fconfig['default'] ) ? $fconfig['default'] : null;
		}
		return $result;
	}

	/**
	 * Returns configuration object for specific field.
	 *
	 * @return assoc
	 */
	protected function get_field_config( $field_code ) {
		return isset( $this->fields_config[ $field_code ] ) ? $this->fields_config[ $field_code ] : array();
	}

	/**
	 * Returns label text for specific field.
	 *
	 * @return string
	 */
	protected function get_field_label( $field_code ) {
		$field_config = $this->get_field_config( $field_code );
		return isset( $field_config['label'] ) ? $field_config['label'] : $field_code;
	}

	/**
	 * Renders text input element.
	 *
	 * @param  string $field_code
	 * @param  mixed  $value
	 * @param  string $css_class
	 * @return string
	 */
	protected function render_text_input( $field_code, $value, $css_class = 'widefat' ) {
		return sprintf( '<input class="%s" id="%s" name="%s" type="text" value="%s">',
			$css_class ? esc_attr( $css_class ) : '',
			esc_attr( $this->get_field_id( $field_code ) ),
			esc_attr( $this->get_field_name( $field_code ) ),
			esc_attr( $value )
		);
	}

	/**
	 * Renders textarea input element.
	 *
	 * @param  string $field_code
	 * @param  mixed  $value
	 * @param  string $css_class
	 * @return string
	 */
	protected function render_textarea( $field_code, $value, $css_class = 'widefat' ) {
		return sprintf( '<textarea class="%s" id="%s" name="%s">%s</textarea>',
			$css_class ? esc_attr( $css_class ) : '',
			esc_attr( $this->get_field_id( $field_code ) ),
			esc_attr( $this->get_field_name( $field_code ) ),
			esc_attr( $value )
		);
	}

	/**
	 * Renders select element.
	 *
	 * @param  string $field_code
	 * @param  string $value
	 * @param  assoc  $options_list
	 * @return string
	 */
	protected function render_select_input( $field_code, $value, $options_list, $css_class = 'widefat' ) {
		return sprintf( '<select class="%s" id="%s" name="%s">%s</select>',
			$css_class ? esc_attr( $css_class ) : '',
			esc_attr( $this->get_field_id( $field_code ) ),
			esc_attr( $this->get_field_name( $field_code ) ),
			$this->render_options_html( $options_list, $value )
		);
	}

	/**
	 * Renders html of options for select element.
	 *
	 * @param  assoc  $options
	 * @param  string $selected_value
	 * @return string
	 */
	protected function render_options_html( array $options, $selected_value = '' ) {
		$result = '';
		foreach ( $options as $val => $title ) {
			$checked = ( $val == $selected_value ) ? ' selected="selected"' : '';
			$result .= '<option value="' . esc_attr( $val ) . '" ' . $checked . '>' . esc_html( $title ) . '</option>';
		}
		return $result;
	}

	/**
	 * Renders checkbox input element.
	 *
	 * @param  string $field_code
	 * @param  mixed  $value
	 * @param  string $css_class
	 * @return string
	 */
	protected function render_checkbox_input( $field_code, $value, $css_class = '' ) {
		return sprintf( '<input class="%s" id="%s" name="%s" type="checkbox" value="on"%s>',
			$css_class ? esc_attr( $css_class ) : '',
			esc_attr( $this->get_field_id( $field_code ) ),
			esc_attr( $this->get_field_name( $field_code ) ),
			$value ? ' checked="checked"' : ''
		);
	}

	/**
	 * Outputs the html at the start of a widget.
	 *
	 * @param  array $args
	 * @return void
	 */
	public function widget_start( $args, $instance ) {
		echo $args['before_widget'];

		if ( $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
	}

	/**
	 * Outpus the html at the end of a widget.
	 *
	 * @param  array $args
	 * @return void
	 */
	public function widget_end( $args ) {
		echo $args['after_widget'];
	}
}
