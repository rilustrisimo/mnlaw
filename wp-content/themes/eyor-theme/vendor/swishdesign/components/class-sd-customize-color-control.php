<?php
/**
 * Color selection control for customization theme panel.
 *
 * @author    eyorsogood.com, Rouie Ilustrisimo
 * @package   SwishDesign
 * @version   1.0.0
 */

/**
 * No direct access to this file.
 *
 * @since 1.0.0 SwishDesign/Components
 */
defined( 'ABSPATH' ) || die();

/**
 * Class Customize_Color_Control
 */
class SD_Customize_Color_Control extends WP_Customize_Control {

	/**
	 * Flag for field type.
	 *
	 * @var string
	 */
	public $type = 'alphacolor';

	/**
	 * Flag that determines if a palette is used.
	 *
	 * @var bool
	 */
	public $palette = true; // public $palette = '#3FADD7,#555555,#666666, #F5f5f5,#333333,#404040,#2B4267';.

	/**
	 * The default color.
	 *
	 * @var string
	 */
	public $default = '#ffffff';

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
		// parent::enqueue(); // of the color control
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );

		$script_id = 'swishdesign-themecustomize-color-control';

		wp_enqueue_style(
			$script_id,
			PARENT_URL . '/assets/sd/css/sd-customize-color-control.css'
		);

		wp_enqueue_script(
			$script_id,
			PARENT_URL . '/assets/sd/js/sd-customize-color-control.js',
			array( 'jquery', 'customize-controls' ),
			$this->prevent_js_cache ? time() : '',
			true
		);
	}

	/**
	 * Render.
	 */
	protected function render() {
		$id = 'customize-control-' . str_replace( '[', '-', str_replace( ']', '', $this->id ) );
		$class = 'customize-control customize-control-' . $this->type;
		ob_start();
		$this->render_content();
		$content = ob_get_clean();
		echo strtr('<li id="{id}" class="{class}">{content}</li>', array(
			'{id}' => esc_attr( $id ),
			'{class}' => esc_attr( $class ),
			'{content}' => $content,
		));
	}

	/**
	 * Render control.
	 */
	public function render_content() {
		ob_start();
		$this->link();
		$link_attrib = ob_get_clean();

		echo strtr('<label><span class="customize-control-title">{label}</span>' .
		           '<input type="text" data-palette="{pallete}" data-default-color="{default_color}" value="{value}" class="tdcolor-color-control" {link_attrib} />' .
		           '</label>',
			array(
				'{label}' => esc_html( $this->label ),
				'{pallete}' => $this->palette,
				'{default_color}' => $this->setting->default ? $this->setting->default : $this->default,
				'{value}' => intval( $this->value() ),
				'{link_attrib}' => $link_attrib
			)
		);
	}

}
