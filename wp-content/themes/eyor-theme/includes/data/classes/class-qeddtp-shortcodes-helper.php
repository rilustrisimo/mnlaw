<?php
/**
 * Shortcodes helper service.
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

class QEDDTP_Shortcodes_Helper {

	/**
	 * @return QEDDTP_Shortcodes_Nl_Escaper
	 */
	private $nl_escaper;

	protected $configs_set = array();

	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'action_register_shortcodes' ) );

		if ( is_admin() ) {
			add_filter( 'qed_shortcodes_register_preload_list', array( $this, 'filter_preload_shortcodes_for_register') );
		}
	}

	private $register_done = false;

	/**
	 * Hook for 'after_setup_theme' event.
	 *
	 * @return void
	 */
	public function action_register_shortcodes() {
		if ( $this->register_done ) {
			return;
		}

		$this->register_done = true;

		QEDDTP()->require_file( '/includes/shortcodes-static.php' );
		QEDDTP()->require_file( '/includes/shortcodes-dynamic.php' );
	}

	/**
	 * @return QEDDTP_Shortcodes_Nl_Escaper
	 */
	public function nl_escaper() {
		if ( ! $this->nl_escaper ) {
			//QEDDTP()->require_file( '/classes/class-qeddtp-shortcodes-nl-escaper.php' );

			$this->nl_escaper = new QEDDTP_Shortcodes_Nl_Escaper();
		}

		return $this->nl_escaper;
	}

	/**
	 * Renders view with specifies set of parameters.
	 *
	 * @param  string  $view    view name.
	 * @param  string  $postfix optional, view postfix.
	 * @param  array   $data            assoc array with variables that should be passed to view.
	 * @return string
	 */
	public function render_view( $view, $postfix = '', array $data = array() ) {
		static $__rf_cache;
		if ( null === $__rf_cache ) {
			$__rf_cache = array();
		}
		$__cache_key = $view . $postfix;
		if ( isset( $__rf_cache[ $__cache_key ] ) ) {
			$__view_file_path = $__rf_cache[ $__cache_key ];
		} else {
			$__template_variations = array();
			if ( $postfix ) {
				$__template_variations[] = $view . '-' . $postfix . '.php';
			}
			$__template_variations[] = $view . '.php';

			$__view_file_path = locate_template( $__template_variations );

			if ( ! $__view_file_path ) {
				foreach ( $__template_variations as $__template_variant ) {
					$__cur_variant_path = QEDDTP_PATH . '/' . $__template_variant;
					if ( file_exists( $__cur_variant_path ) ) {
						$__view_file_path = $__cur_variant_path;
					}
				}
			}

			$__rf_cache[ $__cache_key ] = $__view_file_path;
		}

		if ( ! $__view_file_path ) {
			return '';
		}

		$__rf_data = $data;

		unset( $view );
		unset( $postfix );
		unset( $data );

		if ( $__rf_data ) {
			extract( $__rf_data );
		}

		ob_start();
		include $__view_file_path;
		return ob_get_clean();
	}

	/**
	 * Checks if values of the boolean attribute is true.
	 *
	 * @param  string $value
	 * @return boolean
	 */
	public function attribute_is_true( $value ) {
		if ( ! $value || in_array( $value, array( 'no','false', 'off' ) ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Get shortcode identifier.
	 *
	 * @return integer
	 */
	public function generate_id(){
		static $id = 0;
		$id++;

		return $id;
	}

	/**
	 * Makes different checks required for correct plugin working.
	 *
	 * @param  string $check_name check uniq. code.
	 * @return boolean
	 */
	protected function check( $check_name ) {
		$result = false;

		switch ( $check_name ) {
			case 'is_wc_loaded':
				$result = function_exists( 'WC' );
				break;
		}

		return $result;
	}

	public function add_shortcode( $shortcode_name, $callback, $config ) {
		add_shortcode( $shortcode_name, $callback );
		$this->configs_set[ $shortcode_name ] = $config;
	}

	public function is_shortcode_registered( $name ) {
		return isset( $this->configs_set[ $name ] );
	}

	public function get_shortcodes_config() {
		return $this->configs_set;
	}

	public function get_order_values( $mode ) {
		$modes = array(
			'category_order' => array(
				'ASC',
				'DESC',
			),
			'category_orderby' => array(
				'name',
				'id',
				'slug',
				'count',
				'term_group',
				'category__in',
			),
			'article_order' => array(
				'DESC',
				'ASC',
			),
			'article_orderby' => array(
				'date',
				'title',
				'name',
				'modified',
				'rand',
				'comment_count',
				'post__in',
			),
			'testimonial_orderby' => array(
					'date',
					'name',
					'modified',
					'rand',
			),
		);

		return isset( $modes[ $mode ] ) ? $modes[ $mode ] : array();
	}

	public function filter_preload_shortcodes_for_register( $set ) {
		$list = $this->get_shortcodes_config();

		if ( $list ) {
			foreach ( $list as $shortcode_name => $sc_config ) {
				$params = !empty( $sc_config['params'] ) ? $sc_config['params'] : array();
				foreach ( $params as $_p_name => &$_p_config ) {
					if ( empty( $_p_config['type'] ) ) {
						$_p_config['type'] = 'text';
					}

					switch( $_p_config['type'] ) {
						case 'dropdown':
							$_p_config['values'] = !empty( $_p_config['value'] ) ? $_p_config['value'] : array();
							if ( $_p_config['values'] ) {
								$_p_config['value'] = $_p_config['values'][0];
							}
							break;
					}

				}
				if(isset($sc_config['display_on_menu'])) {
					if ( $sc_config['display_on_menu'] ) {
						$set[ $shortcode_name ] = array(
								'name' => $this->filter_shortcode_name_for_register( $sc_config['name'], $shortcode_name ),
								'params' => $params,
						);
					}
				}
			}

			$sc_categories = $this->get_shortcode_categories();
			if ( $sc_categories ) {
				$sorted = array();
				$right_order = array_keys( $sc_categories );
				foreach( $right_order as $current_shortcode ) {
					if ( !isset( $set[ $current_shortcode ] ) ) {
						continue;
					}

					$sorted[ $current_shortcode ] = $set[ $current_shortcode ];
					unset( $set[ $current_shortcode ] );
				}
				if ( $set ) {
					$sorted = array_merge( $sorted, $set );
				}
				return $sorted;
			}

		}

		return $set;
	}

	public function filter_shortcode_name_for_register( $name, $shortcode ) {
		$sc_category = $this->get_shortcode_categories();
		if ( !empty( $sc_category[ $shortcode ] ) ) {
			return $sc_category[ $shortcode ] . '.' . $name;
		}
		return $name;
	}

	public function get_shortcode_categories( $as_tree = false ) {
		static $tree, $sc_category = array();
		if ( null === $tree ) {
			$tree = array();

//			$tree[ esc_html__( 'Typography', 'swishdesign' ) ] = array(
//				'title',
//				'icon_tick',
//				'qed_btn',
//				'qed_icon',
//			);

			// This shortcodes and category added in functions.php @see qed_init_tiny_mce_integration
//			$tree[ esc_html__( 'Tables', 'swishdesign' ) ] = array(
//				'table',
//			);

			$tree[ esc_html__( 'General', 'swishdesign' ) ] = array(
				'row',
				'title',
				'accordion',
				'latest_posts',
				'testimonials',
				'tabs',
			);

			$tree[ esc_html__( 'External Services', 'swishdesign' ) ] = array(
				'google_map',
				'google_map_embed',
			);

			foreach ( $tree as $cat => $list ) {
				foreach ( $list as $_cur_shortcode ) {
					$sc_category[ $_cur_shortcode ] = $cat;
				}
			}
		}

		if ( $as_tree ) {
			return $tree;
		}
		return $sc_category;
	}

	/**
	 * Returns sets of icons. Name of the set should be defined via key.
	 * Each set is assoc where key is icon class, value is icon label.
	 *
	 * @example
	 * <pre>
	 * array(
	 *     'Collection 1' => array(
	 *         'icon icon-1' => 'Icon #1',
	 *         'icon icon-1' => 'Icon #2',
	 *     ),
	 *     'Set #2' => array(
	 *         'iset icon-1' => 'ISet icon #1',
	 *         'iset icon-2' => 'ISet icon #2',
	 *     ),
	 * )
	 * </pre>
	 *
	 * @return assoc
	 */
	public function get_at_icon_shortcode_icons() {
		return apply_filters( 'qed_get_sd_icon_shortcode_icons', array() );
	}
}