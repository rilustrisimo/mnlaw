<?php
/**
 * Theme helpers file.
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

// -----------------------------------------------------------------#
// Rendering: filters & helpers
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_render_header_share_meta' ) ) {
	/**
	 * Renders social network related meta tags.
	 *
	 * @return void
	 */
	function qed_render_header_share_meta() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}

		$is_sharing_active = apply_filters( 'qed_render_header_social_meta', qed_get_option( 'social_sharing_blog' ) );
		if ( ! $is_sharing_active ) {
			return;
		}

		$title = esc_attr( get_the_title() );
		$description = esc_attr( qed_get_short_description( null, 300 ) );

		$thumb_id = get_post_thumbnail_id();
		$image = $thumb_id ? esc_url( wp_get_attachment_url( $thumb_id ) ) : '';

		$tags = array(
			'og' => array(
				'title' => $title,
				'description' => $description,
				'image' => $image,
			)
		);
		if ( qed_get_option( 'social_sharing_twitter' ) ) {
			$tags['twitter'] = array(
				'title' => $title,
				'description' => $description,
				'image' => $image,
				'card' => $image ? 'summary_large_image' : 'summary',
			);
		}

		$tags = apply_filters( 'qed_header_social_meta_tags', $tags );
		if ( $tags ) {
			if ( !empty( $tags['og'] ) ) {
				foreach ( $tags['og'] as $mata_name => $meta_value ) {
					if ( $meta_value ) {
						printf( PHP_EOL . '<meta property="og:%s" content="%s">', $mata_name, $meta_value );
					}
				}
			}

			if ( ! empty( $tags['twitter'] ) ) {
				foreach ( $tags['twitter'] as $mata_name => $meta_value ) {
					if ( $meta_value ) {
						printf( PHP_EOL . '<meta name="twitter:%s" content="%s">', $mata_name, $meta_value );
					}
				}
			}
		}
	}
	// Disabled for performance reason. Can be added to child theme functions.php
	// add_action( 'wp_head', 'qed_render_header_share_meta' );
} // End if().

if ( ! function_exists( 'qed_render_header_resources' ) ) {
	/**
	 * Renders theme header resources.
	 *
	 * @return void
	 */
	function qed_render_header_resources() {
		$inline_pieces = qed_di( 'register' )->get_var( 'header_inline_css_text' );
		if ( $inline_pieces ) {
			foreach ( $inline_pieces as $inline_piece_info ) {
				if ( empty( $inline_piece_info['text'] ) ) {
					continue;
				}
				printf( "<style type=\"text/css\">%s</style>\n", $inline_piece_info['text'] );
			}
			qed_di( 'register' )->set_var( 'header_inline_css_text', array() );
		}

		$custom_css = qed_get_option( 'custom_css_text' );
		if ( $custom_css ) {
			printf( "<style type=\"text/css\">\n%s\n</style>\n", $custom_css );
		}
	}
	add_action( 'wp_head', 'qed_render_header_resources' );
} // End if().

if ( ! function_exists( 'qed_filter_theme_styles' ) ) {
	/**
	 * Filter for theme style files list.
	 *
	 * @param  array $default_set list of default files that should be used.
	 * @return array
	 */
	function qed_filter_theme_styles(array $default_set) {
		$is_customize_request = isset( $_POST['wp_customize'] ) && 'on' == $_POST['wp_customize'];

		$is_rtl = is_rtl();

		$cache_id = $is_customize_request || THEME_IS_DEV_MODE ? '' : ( 'qed_generated_styles_list' . ( $is_rtl ? '_rtl' : '' ) );

		$cached_value = $cache_id ? get_transient( $cache_id ) : false;

		if ( false === $cached_value || empty( $cached_value['version'] ) || QED_VERSION !== $cached_value['version'] ) {
			$app = qed_di( 'app' );
			$style_options = $app->get_style_options( $is_customize_request );
			// Special variable used to point url locations.
			if ( ! isset( $style_options['assets_url'] ) ) {
				$style_options['assets_url'] = '"' . PARENT_URL . '/assets/"';
			}

			if ( $is_rtl ) {
				$style_options['bi-app-left'] = 'right';
				$style_options['bi-app-right'] = 'left';
				$style_options['bi-app-direction'] = 'rtl';
				$style_options['bi-app-invert-direction'] = 'ltr';
			} else {
				$style_options['bi-app-left'] = 'left';
				$style_options['bi-app-right'] = 'right';
				$style_options['bi-app-direction'] = 'ltr';
				$style_options['bi-app-invert-direction'] = 'rtl';
			}

			$compiled = $app->generate_custom_css(
				qed_di( 'register' )->get_var( 'main_less_file' ),
				$style_options,
				$is_customize_request ? 'preview-main' :  ( 'main-custom' . ( $is_rtl ? '-rtl' : '' ) )
			);

			$cached_value = array(
				'version' => QED_VERSION,
				'value' => array_merge( $default_set, $compiled ),
			);
			if ( $cache_id ) {
				set_transient( $cache_id, $cached_value );
			}
		}

		return isset( $cached_value['value'] ) ? $cached_value['value'] : $default_set;
	}
	add_filter( 'get_theme_styles', 'qed_filter_theme_styles', 1, 1 );
} // End if().

if ( ! function_exists( 'qed_flush_style_cache' ) ) {
	/**
	 * Resets generated styles cache.
	 *
	 * @return void
	 */
	function qed_flush_style_cache() {
		delete_transient( 'qed_generated_styles_list' );
		delete_transient( 'qed_generated_styles_list_rtl' );
	}
	add_action( 'customize_save_after', 'qed_flush_style_cache' );
	add_action( 'after_switch_theme', 'qed_flush_style_cache' );
} // End if().

if ( ! function_exists( 'qed_get_fonts_icons' ) ) {
	/**
	 * Filter function for 'qed_get_sd_icon_shortcode_icons' action.
	 * Filter sets of icons. Name of the set should be defined via key.
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
	 * @param  mixed $icons the icons.
	 * @return assoc
	 */
	function qed_filter_theme_fonts_icon_sets( $icons ) {
		$di = qed_di();
		if ( isset( $di['icons_manager'] ) ) {
			$qed_icons_list = qed_di( 'icons_manager' )->get_list();
			if ( $qed_icons_list ) {
				$set = array();
				foreach ( $qed_icons_list as $icon ) {
					$set[ $icon['value'] ] = $icon['label'];
				}
				$icons['Swish Design'] = $set;
			}
		}

		$icons_manager = new SD_Font_Icons_Manager( array(
			'font_file_url' => PARENT_URL . '/assets/csslib/font-awesome.min.css',
			'pattern' => '/\.(fa-(?:\w+(?:-)?)+):before\s*{\s*content/',
			'cache_key' => 'qed-font-awesome-icons-list',
		) );
		$font_awesome_icons_list = $icons_manager->get_list();
		if ( $font_awesome_icons_list ) {
			$set = array();
			foreach ( $font_awesome_icons_list as $icon ) {
				$icon_class = 'fa ' . $icon['value'];
				$set[ $icon_class ] = $icon['label'];
			}
			$icons['Font Awesome'] = $set;
		}

		return $icons;
	}
	add_filter( 'qed_get_sd_icon_shortcode_icons', 'qed_filter_theme_fonts_icon_sets' );
} // End if().

if ( ! function_exists( 'qed_check' ) ) {
	/**
	 * Theme function for checks.
	 *
	 * @param string     $check_name value to check.
	 * @param bool|false $ignore_cache flag to determine if will ignore cache.
	 *
	 * @return mixed
	 */
	function qed_check( $check_name, $ignore_cache = false ) {
		static $cache = array();

		if ( ! isset( $cache[ $check_name ] ) || $ignore_cache ) {
			$result = false;
			switch ( $check_name ) {
				case 'media_category_taxonomy_exists':
					$result = taxonomy_exists( 'media_category' );
					break;

				case 'is_wpml_in_use':
					$result = defined( 'ICL_SITEPRESS_VERSION' ); // function_exists( 'icl_object_id' );.
					break;

				case 'is_wordpress_seo_in_use':
					$result = defined( 'WPSEO_VERSION' );
					break;
			}

			$cache[ $check_name ] = $result;
		}

		return $cache[ $check_name ];
	}
}

add_filter( 'gettext', 'qed_change_flamingo_menu' );
add_filter( 'ngettext', 'qed_change_flamingo_menu' );

function qed_change_flamingo_menu( $translated ) { 
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( is_plugin_active( 'flamingo/flamingo.php' ) ) {
		$translated = str_replace( 'Flamingo', 'Contact Submissions', $translated );
    	$translated = str_replace( 'flamingo', 'contact submissions', $translated );
	} 
    
    return $translated;
}

