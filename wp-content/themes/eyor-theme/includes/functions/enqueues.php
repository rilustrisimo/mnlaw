<?php
/**
 * Theme enqueues file.
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
// Assets registration
// -----------------------------------------------------------------#
if ( ! function_exists( 'qed_init_theme_assets' ) ) {
	/**
	 * Defines theme assets.
	 *
	 * @return void
	 */
	function qed_init_theme_assets() {
		$min_ext = SCRIPT_DEBUG ? '' : '.min';

		$is_rtl = is_rtl();
		if ( THEME_IS_DEV_MODE ) {
			if ( $is_rtl ) {
				wp_enqueue_style( 'bootstrap-custom-rtl', PARENT_URL . '/assets/csslib/bootstrap-custom-rtl.css' );
			} else {
				wp_enqueue_style( 'bootstrap', PARENT_URL . '/assets/csslib/bootstrap.css' );
			}

			wp_enqueue_style( 'datepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css', array(), '' );
			wp_enqueue_style( 'fontawesome',  PARENT_URL . '/assets/fontawesome/css/all.css', array(), '6.1.1');
			wp_enqueue_style( 'bootstrap-select', PARENT_URL . '/assets/csslib/bootstrap-select/bootstrap-select.min.css', array(), '1.12.2' );
			wp_enqueue_style( 'bxslider', PARENT_URL . '/assets/csslib/bxslider/jquery.bxslider.min.css', array(), '4.2.12' );
			wp_register_style( 'magnific-popup', PARENT_URL . '/assets/csslib/magnific-popup.css', array(), '1.1.0' );

			wp_register_style( 'swipebox', PARENT_URL . '/assets/csslib/swipebox.css' );
			wp_register_style( 'swiper', PARENT_URL . '/assets/csslib/swiper.min.css' );
			
			wp_enqueue_script( 'datepicker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'dialog', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'fontawesome', PARENT_URL . '/assets/fontawesome/js/all.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'bootstrap', PARENT_URL . '/assets/jslib/bootstrap.min.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'bootstrap-select', PARENT_URL . '/assets/jslib/bootstrap-select/bootstrap-select.min.js', array( 'jquery', 'bootstrap' ), '1.12.2', true );
			wp_enqueue_script( 'bxslider', PARENT_URL . '/assets/jslib/bxslider/jquery.bxslider.min.js', array( 'jquery' ), '4.2.12', true );
			wp_enqueue_script( 'slick', PARENT_URL . '/assets/jslib/slick/slick.min.js', array( 'jquery' ), '1.8.0', true );
			wp_enqueue_script( 'slicknav', PARENT_URL . '/assets/jslib/jquery.slicknav.js',array( 'jquery' ), '',true );
			wp_enqueue_script( 'tabcollapse', PARENT_URL . '/assets/jslib/bootstrap-tabcollapse.js', array( 'jquery' ), '', true );
			wp_enqueue_script( 'waypoints', PARENT_URL . '/assets/jslib/jquery.waypoints.min.js', array( 'jquery' ), '4.0.1', true );
			wp_enqueue_script( 'counterup', PARENT_URL . '/assets/jslib/jquery.counterup.min.js', array( 'jquery', 'waypoints' ), '1.0', true );
			wp_enqueue_script( 'vivus', PARENT_URL . '/assets/jslib/vivus.min.js', array(), '0.4.2', true );
			wp_register_script( 'fitvid', PARENT_URL . '/assets/jslib/bxslider/vendor/jquery.fitvids.js', array( 'jquery' ), '1.0', true );
			wp_register_script( 'magnific-popup', PARENT_URL . '/assets/jslib/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );
			wp_register_script( 'theme', PARENT_URL . '/assets/js/Theme.js', array( 'jquery' ), rand(), true );
			wp_localize_script( 'theme', 'AjaxHelper', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'headerSectionNonce' => wp_create_nonce( 'swish-header-section-nonce' ),
			) );
			wp_enqueue_script( 'theme' );

			if ( qed_get_option( 'show_header_search' ) ) {
				wp_enqueue_style( 'magnific-popup' );
				wp_enqueue_script( 'magnific-popup' );
			}

			wp_register_script( 'swipebox', PARENT_URL . '/assets/jslib/jquery.swipebox.js', array( 'jquery' ), '1.3.0.2', true );
			wp_register_script( 'swiper', PARENT_URL . '/assets/jslib/swiper/swiper.jquery.min.js', array(), '3.4.2', true );

			wp_register_script( 'parallax', PARENT_URL . '/assets/jslib/jquery.parallax-1.1.3.js', array( 'jquery' ), '1.1.3', true );

			wp_register_script( 'sharrre', PARENT_URL . '/assets/jslib/jquery.sharrre.js', array( 'jquery' ), '',true );
			
		} else {
			wp_enqueue_style( 'theme-addons', PARENT_URL . '/assets/csslib/theme-addons' . ( $is_rtl ? '-rtl' : '' ) . $min_ext . '.css', array(), '2.2.7' );
			wp_enqueue_script( 'theme', PARENT_URL . '/assets/js/theme-full' . $min_ext . '.js', array( 'jquery' ), QED_VERSION, true );
		} // End if().

		$style_collection = apply_filters('get_theme_styles', array(
			'style-css' => get_stylesheet_uri(),
		));

		if ( $style_collection ) {
			foreach ( $style_collection as $_item_key => $resource_info ) {
				$_style_text = null;
				$_style_url = null;
				if ( ! is_array( $resource_info ) ) {
					$_style_url = $resource_info;
				} else {
					if ( isset( $resource_info['text'] ) ) {
						$_style_text = $resource_info['text'];
					} elseif ( isset( $resource_info['url'] ) ) {
						$_style_url = $resource_info['url'];
					}
				}
				if ( $_style_url ) {
					wp_enqueue_style( $_item_key, $_style_url );
				} elseif ( $_style_text ) {
					qed_di( 'register' )->push_var( 'header_inline_css_text', array(
						'id' => $_item_key,
						'text' => $_style_text,
					) );
				}
			}
		}

		wp_register_script( 'jPages', PARENT_URL . '/assets/jslib/jPages.js', array( 'jquery' ), '', true );

		// wp_register_style( 'jquery-ui-datepicker-custom', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css', array(), '1.8.2' );
		wp_register_style( 'jquery-ui-datepicker-custom', PARENT_URL . '/assets/csslib/jquery-ui-custom/jquery-ui.min.css', array(), '1.11.4' );
	}

	add_action( 'wp_enqueue_scripts', 'qed_init_theme_assets' );
}
