<?php
/**
 * Text transform selection control for customization theme panel.
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
 * Class SD_Customize_Text_Transform_Control
 */
class SD_Customize_Text_Transform_Control extends WP_Customize_Control {

	/**
	 * Flag for field type.
	 *
	 * @var string
	 */
	public $type = 'swishdesign_text_transform';

	/**
	 * Flag that determines if it is a sub field.
	 *
	 * @var bool
	 */
	public $as_subfield = false;

	/**
	 * The list of units for the font size.
	 *
	 * @var assoc
	 */
	public $unit_list = array(
		'none' => 'None',
		'capitalize' => 'Capitalize',
		'uppercase' => 'Uppercase',
		'lowercase' => 'Lowercase',
		'full-width' => 'Full Width',
		'inherit' => 'Inherit',
		'initial' => 'Initial',
	);

	/**
	 * Flag that determines if js should be cached.
	 *
	 * @var bool
	 */
	public $prevent_js_cache = false;

	/**
	 * Enqueue scripts and styles method.
	 */
	public function enqueue() {
		wp_enqueue_script(
			'swishdesign-themecustomize-text-transform-control',
			PARENT_URL . '/assets/sd/js/sd-customize-text-transform-control.js',
			array( 'jquery', 'customize-controls' ),
			$this->prevent_js_cache ? time() : '',
			true
		);
	}

	/**
	 * Render control.
	 */
	public function render_content() {
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<?php if ( ! $this->description && $this->as_subfield ) { ?>
				<span><?php print esc_html( $this->label ); ?>:</span>
			<?php } else { ?>
				<span class="customize-control-title"><?php print esc_html( $this->label ); ?></span>
			<?php } ?>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php print $this->description; ?></span>
		<?php endif; ?>
		<select <?php print $this->get_sub_link( 'transform' ); ?>>
			<?php $this->render_options( $this->unit_list, $this->get_sub_value( 'transform', 'none' ) ); ?>
		</select>
		<?php
	}

	/**
	 * Get the data subkey.
	 *
	 * @param  string $key the key.
	 * @param  string $setting the setting.
	 * @return string returned data subkey attribute.
	 */
	protected function get_sub_link( $key, $setting = 'default' ) {
		return "data-subkey=\"{$key}\"";
	}

	/**
	 * Get the sub value.
	 *
	 * @param string $key the key.
	 * @param string $default default value.
	 * @return string returned value.
	 */
	protected function get_sub_value( $key, $default = '' ) {
		$value = $this->value();
		return $value && isset( $value[ $key ] ) ? $value[ $key ] : $default;
	}

	/**
	 * Method to render options.
	 *
	 * @param array $options the options.
	 * @param string $curvalue the current value selected.
	 */
	protected function render_options( array $options, $curvalue ) {
		foreach ( $options as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $curvalue, $value, false ) . '>' . $label . '</option>'; }
	}

}
