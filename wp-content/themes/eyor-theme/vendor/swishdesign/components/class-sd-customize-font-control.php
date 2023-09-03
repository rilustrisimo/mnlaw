<?php
/**
 * Font selection control for customization theme panel.
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
 * Class SD_Customize_Font_Control
 */
class SD_Customize_Font_Control extends WP_Customize_Control {

	/**
	 * Flag for field type.
	 *
	 * @var string
	 */
	public $type = 'swishdesign_font';

	/**
	 * Font filter.
	 *
	 * @var string
	 */
	public $font_set_filter = 'swishdesign_customize_font_set';

	/**
	 * Font set.
	 *
	 * @var array|mixed|void
	 */
	public $font_set = array(
		/*'font1' => array(),
		'font2' => array(
			'style' => array( 'normal', 'italic', ),
			'weight' => array( '400', '700' )
		),
		'font3' => array(
			'weight' => array('300','400')
		),*/
	);

	/**
	 * Flag that determines if js should be cached.
	 *
	 * @var bool
	 */
	public $prevent_js_cache = false;

	/**
	 * SD_Customize_Font_Control constructor.
	 *
	 * @param WP_Customize_Manager $manager the manager.
	 * @param string               $id the id.
	 * @param array                $args the arguments.
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		if ( $this->font_set_filter ) {
			$this->font_set = apply_filters( $this->font_set_filter, $this->font_set, $this->id );
		}
	}

	/**
	 * Enqueue scripts and styles method.
	 */
	public function enqueue() {
		// $script_id = "swishdesign-themecustomize-font-control{$this->id}";
		$script_id = 'swishdesign-themecustomize-font-control';

		wp_enqueue_script(
			$script_id,
			PARENT_URL . '/assets/sd/js/sd-customize-font-control.js',
			array( 'jquery', 'customize-controls' ),
			$this->prevent_js_cache ? time() : '',
			true
		);

		wp_localize_script($script_id, '_SdCustomizeFontControl' . $this->id, array(
			'font_set' => $this->font_set,
		));
	}

	/**
	 * Render control.
	 */
	public function render_content() {
		$cur_family = $this->get_sub_value( 'family' );
		$font_list = $this->get_font_family_list();
		$style_list = $this->get_style_list( $cur_family );
		$weight_list = $this->get_weight_list( $cur_family );
		?>
		<?php if ( ! empty( $this->label ) ) : ?>
			<span class="customize-control-title"><?php print esc_html( $this->label ); ?></span>
		<?php endif;
		if ( ! empty( $this->description ) ) : ?>
			<span class="description customize-control-description"><?php print $this->description; ?></span>
		<?php endif; ?>

		<select <?php print $this->get_sub_link( 'family' ); ?>>
			<?php $this->render_options( $font_list, $cur_family ); ?>
		</select>

		<div style="margin:8px 0;">
			<span style="width:60px;display:inline-block">Style:</span><select <?php print $this->get_sub_link( 'style' ); ?>>
				<?php $this->render_options( $style_list, $this->get_sub_value( 'style', 'normal' ) ); ?>
			</select>
		</div>

		<div>
			<span style="width:60px;display:inline-block">Weight:</span><select <?php print $this->get_sub_link( 'weight' ); ?>>
				<?php $this->render_options( $weight_list, $this->get_sub_value( 'weight', '400' ) ); ?>
			</select>
		</div>
		<?php
	}

	/**
	 * Get font family list.
	 *
	 * @return array $result result array.
	 */
	protected function get_font_family_list() {
		$result = array();

		$list = $this->font_set ? array_keys( $this->font_set ) : array();

		foreach ( $list as $name ) {
			$result[ $name ] = $name;
		}

		return $result;
	}

	/**
	 * Get font family style list
	 *
	 * @param string $family font family.
	 * @return array $result result array.
	 */
	protected function get_style_list( $family ) {
		$list = array( 'normal' );

		$font_config = isset( $this->font_set[ $family ] ) ? $this->font_set[ $family ] : array();

		if ( ! empty( $font_config['style'] ) ) {
			$list = $font_config['style'];
		}

		$result = array();
		foreach ( $list as $weight ) {
			$result[ $weight ] = $weight;
		}

		return $result;
	}

	/**
	 * Get font family weight list
	 *
	 * @param string $family font family.
	 * @return array $result result array.
	 */
	protected function get_weight_list( $family ) {
		$list = array( 'normal' );

		$font_config = isset( $this->font_set[ $family ] ) ? $this->font_set[ $family ] : array();

		if ( ! empty( $font_config['weight'] ) ) {
			$list = $font_config['weight'];
		}

		$result = array();
		foreach ( $list as $weight ) {
			$result[ $weight ] = $weight;
		}

		return $result;
	}

	/**
	 * Get the data subkey.
	 *
	 * @param  string $key the key.
	 * @param  string $setting the setting.
	 * @return string returned data subkey attribute.
	 */
	protected function get_sub_link( $key, $setting = 'default' ) {
		// $link = $this->get_link($setting);
		// $result = substr($link, 0, -1) . "[$key]\" data-subkey=\"{$key}\"";
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
			echo '<option value="' . esc_attr( $value ) . '"' . selected( $curvalue, $value, false ) . '>' . $label . '</option>';
		}
	}
}
