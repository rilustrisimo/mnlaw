<?php
/**
 * Class for integration shortcodes into TinyMCE editor.
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
 * Class SD_Shortcodes_TinyMCE_Integrator
 */
class SD_Shortcodes_TinyMCE_Integrator extends SD_Component {

	/**
	 * Instance of SD_Shortcodes_Register.
	 *
	 * @var SD_Shortcodes_Register
	 */
	public $register_service;

	/**
	 * The root directory url.
	 *
	 * @var string
	 */
	public $base_url;

	/**
	 * The assets directory url relative to the base url.
	 *
	 * @var string
	 */
	public $assets_url;

	/**
	 * Initialize TinyMCE integrator.
	 *
	 * @return bool
	 */
	public function init() {
		if ( parent::init() ) {
			add_action( 'admin_init', array( $this, 'action_admin_init' ) );
			return true;
		}
		return false;
	}

	/**
	 * Initialize method.
	 */
	public function action_admin_init() {

		if ( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) && get_user_option( 'rich_editing' ) == 'true' ) {
			//Only use wp_ajax if user is logged in.
			//add_action( 'wp_ajax_check_url_action', array( $this, 'ajax_action_check_url' ) );

			add_action( 'wp_ajax_swishdesign_render_shortcode', array( $this, 'ajax_render_shortcode' ) );

			$this->base_url = $plugin_url = get_template_directory_uri() . '/includes/shortcodes/tinymce/';
			$this->assets_url = $this->base_url . 'assets/';

			// TinyMCE plugin stuff.
			add_filter( 'mce_buttons', array( $this, 'filter_mce_buttons' ) );
			add_filter( 'mce_external_plugins', array( $this, 'filter_mce_external_plugins' ) );

			// TinyMCE shortcode plugin CSS.
			wp_enqueue_style( 'tinymce-shortcodes', $this->assets_url . 'admin.css' );
			add_action( 'admin_print_scripts', array( $this, 'print_config' ) );
		}
	}

	/**
	 * Render the configuration options.
	 */
	public function print_config() {
		$config = array(
			'menu' => $this->register_service->get_menu_config(),
			'attributes' => $this->register_service->get_dialogs_config(),
		);

		echo '<script>var QedShortcodesConfig=' . wp_json_encode( $config ) . ';</script>';
	}

	/**
	 * Filter mce buttons.
	 *
	 * @param $buttons
	 * @return mixed
	 */
	public function filter_mce_buttons( $buttons ) {
		array_push( $buttons, 'shortcodes_button', 'shortcodes_render_mode_switcher' );
		return $buttons;
	}

	// Actually add tinyMCE plugin attachment.
	public function filter_mce_external_plugins( $plugins ) {
		$plugins['QedShortcodesPlugin'] = $this->assets_url . 'QedShortcodesPlugin.js';
		$plugins['QedShortcodesRender'] = $this->assets_url . 'QedShortcodesRender.js';
		return $plugins;
	}

	// Ajax actions - renders shortcode for the visual editor.
	public function ajax_render_shortcode() {
		if ( isset( $_GET['shortcode'] ) ) {
			$shortcode = urldecode( $_GET['shortcode'] );
			echo '<html><head>';
			wp_head();
			echo '</head><body><div class="wrapper">';
			echo do_shortcode( $shortcode );
			echo '</div></body>';
			#wp_footer();
			echo '</html>';
		}
		exit();
	}

	public function wpse_9080_admin_init()
	{
		global $pagenow;
		if ( 'edit.php' == $pagenow)
		{
			if ( !isset($_GET['post_type']) )
			{
				echo 'I am the Posts listings page';
			}
			elseif ( isset($_GET['post_type']) && 'page' == $_GET['post_type'] )
			{
				// Will occur only in this screen: /wp-admin/edit.php?post_type=page
				echo 'I am the Pages listings page';
			}
		}
		if ('post.php' == $pagenow && isset($_GET['post']) )
		{
			// Will occur only in this kind of screen: /wp-admin/post.php?post=285&action=edit
			// and it can be a Post, a Page or a CPT
			$post_type = get_post_type($_GET['post']);
			print_r($post_type);
			if ( 'post' == $post_type )
			{
				$firephp->log('I am editing a post');
			}
			elseif ( 'page' == $post_type)
			{
				$firephp->log('I am editing a page');
			}
			elseif ( 'movie' == $post_type)
			{
				$firephp->log('I am editing a custom post type');
			}
		}

		if ('post-new.php' == $pagenow )
		{
			// Will occur only in this kind of screen: /wp-admin/post-new.php
			// or: /wp-admin/post-new.php?post_type=page
			if ( !isset($_GET['post_type']) )
			{
				echo 'I am creating a new post';
			}
			elseif ( isset($_GET['post_type']) && 'page' == $_GET['post_type'] )
			{
				echo 'I am creating a new page';
			}
			elseif ( isset($_GET['post_type']) && 'movie' == $_GET['post_type'] )
			{
				echo 'I am creating a new custom post type';
			}
		}
	}
	/*public function ajax_action_check_url()
	{
		$hadError = true;

		$url = isset( $_REQUEST['url'] ) ? $_REQUEST['url'] : '';
		if ( strlen( $url ) > 0  && function_exists( 'get_headers' ) ) {
			$file_headers = @get_headers( $url );
			$exists       = $file_headers && $file_headers[0] != 'HTTP/1.1 404 Not Found';
			$hadError     = false;
		}

		echo '{ "exists": '. ($exists ? '1' : '0') . ($hadError ? ', "error" : 1 ' : '') . ' }';
		die();
	}*/

}
