<?php
/**
 * Checks for theme udates and renders notification about them to the root admin.
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
 * Class SD_Theme_Updater
 */
class SD_Theme_Updater extends SD_Component {

	/**
	 * Required option. Defines url to the json file with information about latest version.
	 *
	 * @var string
	 */
	public $updates_file_url = '';

	/**
	 * Theme name.
	 *
	 * @var string
	 */
	public $theme_name = '';

	/**
	 * Theme slug.
	 *
	 * @var string
	 */
	public $theme_id = '';

	/**
	 * Prefix required to enable xml file caching.
	 *
	 * @var string
	 */
	public $cache_prefix = '';

	/**
	 * Time in seconds during that class will cache file with information about latest version
	 * Default 3 hours.
	 *
	 * @var integer
	 */
	public $cache_time = 10800;

	/**
	 * If set to true class will throws exception with any errors those happens during the init process.
	 * NOTE: should be false for production!
	 *
	 * @var boolean
	 */
	public $is_development_mode = false;

	/**
	 * Cache for new version checker.
	 * @see has_new_version method
	 * @var boolean
	 */
	private $new_version_check_cache;

	/**
	 * Checks requirements and setup all required filters.
	 * @return void
	 */
	public function init() {
		if ( ! parent::init() ) {
			return false;
		}

		if ( ! is_super_admin() ) {
			return true;
		}

		$file_url = $this->get_updates_file_url();
		if ( empty( $file_url ) ) {
			return $this->error( 'Updates file url is empty.' );
		}

		if ( ! function_exists( 'json_decode' ) ) {
			return $this->error( 'Function "json_decode" does not exists.' );
		}

		add_action( 'admin_menu', array( $this, 'update_admin_menu' ) );

		if ( is_admin_bar_showing() ) {
			add_action( 'admin_bar_menu', array( $this, 'update_admin_bar_menu' ), 1000 );
		}

		return true;
	}

	/**
	 * Renders information about available updates.
	 *
	 * @return void
	 */
	public function action_update_notifier() {

		$update_details = $this->get_latest_information_details();
		$theme_data = wp_get_theme( $this->theme_id );

		$updates_flat_log = array();
		$current_version = $theme_data->version;
		if ( $update_details->changelog ) {
			foreach ( $update_details->changelog as $c_version => $c_list ) {
				if ( ! $c_list ) {
					continue;
				}
				if ( version_compare( $c_version, $current_version ) == 1 ) {
					$updates_flat_log = array_merge( $updates_flat_log, $c_list );
				}
			}
		}

		print $this->render_view('theme-updater-update-notification.php',array(
			'theme_name' => $this->theme_name,
			'new_version' => $update_details->latest,
			'current_version' => $current_version,
			'updates_flat_log' => $updates_flat_log,
			'update_information' => $update_details,
		));
	}

	/**
	 * Adds menu option to the admin's menu if new version of the theme is available.
	 *
	 * @return void
	 */
	public function update_admin_menu() {

		if ( $this->has_new_version() ) {
			$theme_name = $this->theme_name ? $this->theme_name . ' ' : '';
			add_theme_page(
				$theme_name . esc_html__( 'Theme Updates','qed' ),
				$theme_name . '<span class="update-plugins count-1"><span class="update-count">' . esc_html__( 'Update','qed' ) . '</span></span>',
				'administrator',
				'theme-update-notifier',
				array( $this, 'action_update_notifier' )
			);
		}
	}

	/**
	 * Adds menu option to the admin's menu bar if any new apdates available.
	 *
	 * @return void
	 */
	public function update_admin_bar_menu() {

		if ( $this->has_new_version() ) {
			global $wp_admin_bar;
			$theme_name = $this->theme_name ? $this->theme_name . ' ' : '';
			$wp_admin_bar->add_menu(array(
				'id' => 'update_notifier',
				'title' => '<span>' . esc_html( $theme_name ) . '<span id="ab-updates">' . esc_html__( 'New Version', 'qed' ) . '</span></span>',
				'href' => get_admin_url() . 'themes.php?page=theme-update-notifier',
			));
		}
	}

	/**
	 * Updates internal settings.
	 *
	 * @param array|assoc $options see class properies to get list of available options.
	 */
	public function set_options( array $options ) {
		foreach ( $options as $name => $value ) {
			$this->$name = $value;
		}
	}

	/**
	 * Error reporting method.
	 *
	 * @param  string $message
	 * @throws Exception If $is_development_mode set to true.
	 * @return boolean
	 */
	protected function error($message) {
		if ( $this->is_development_mode ) {
			if ( ! $message ) {
				$message = strtr('Unknown {className} error.', array(
					'{className}' => get_class( $this ),
				));
			}
			throw new Exception( $message );
		}
		return false;
	}

	/**
	 * Name of the cache option, if $cache_prefix empty - returns empty string, this means that cache is disabled.
	 *
	 * @return string
	 */
	protected function get_cache_key_id() {
		return $this->cache_prefix ? $this->cache_prefix . '-updater-cache' : '';
	}

	/**
	 * Fallback for case when host with updates information is unavailable.
	 * This state will be saved into cache until the next time checking event.
	 *
	 * @return assoc
	 */
	protected function get_default_information_details() {
		$r = new stdClass();
		$r->latest = '1.0';
		$r->changelog = array();

		return $r;
	}

	/**
	 * Retirns latest retrived information about updates.
	 *
	 * @throws Exception If $is_development_mode enabled.
	 * @param  boolean $cache_allowed set to false to prevent cache usage.
	 * @return assoc
	 */
	protected function get_latest_information_details( $cache_allowed = true ) {
		$cache_id = $this->cache_time > 0 ? $this->get_cache_key_id() : null;

		$result = false;
		if ( $cache_id && $cache_allowed ) {
			$result = get_transient( $cache_id );
		}

		if ( $result === false ) {
			$file_url = $this->get_updates_file_url();
			$response = wp_remote_get($file_url, array(
				'redirection' => 5,
				'timeout' => 30,
			));
			if ( is_wp_error( $response ) ) {
				if ( $this->is_development_mode ) {
					return $this->error( $return->get_error_message() );
				}
			} else {
				$response_code = ! empty( $response['response']['code'] ) ? $response['response']['code'] : false;
				if ( $response_code < 200 || $response_code > 302 || empty( $response['body'] ) ) {
					// http error, so will set.
					if ( $this->is_development_mode ) {
						return $this->error(strtr('URL {file_url} can not be loaded.', array(
							'{file_url}' => $file_url,
						)));
					}
				} else {
					try {
						$result = json_decode( $response['body'] );
					} catch ( Exception $e ) {
						$result = null;
						if ( $this->is_development_mode ) {
							return $this->error( $e->getMessage() );
						}
					}
				}
			}

			if ( ! $result ) {
				if ( $this->is_development_mode ) {
					return $this->error(strtr('Can not decode (json_decode) {file_url}.', array(
						'{file_url}' => $file_url,
					)));
				} else {
					$result = $this->get_default_information_details();
				}
			}
			if ( $cache_id ) {
				set_transient( $cache_id, $result, $this->cache_time );
			}
		}
		return $result;
	}

	/**
	 * Returns url to the file with information about available updates.
	 *
	 * @return string
	 */
	protected function get_updates_file_url() {
		return $this->updates_file_url;
	}

	/**
	 * Checks if new version is available.
	 *
	 * @param  boolean $allow_cache
	 * @return boolean
	 */
	public function has_new_version($allow_cache = true) {
		if ( $allow_cache && null !== $this->new_version_check_cache ) {
			return $this->new_version_check_cache;
		}
		$result = false;

		$latest_details = $this->get_latest_information_details( $allow_cache );
		if ( $latest_details ) {
			$theme_data = wp_get_theme( $this->theme_id );
			$result = version_compare( $latest_details->latest, $theme_data->version ) == 1;
		}

		$this->new_version_check_cache = $result;

		return $result;
	}

	/**
	 * Fetch content from specefied template file.
	 * Uses get_template_path to convert local file name to the full file path.
	 *
	 * @param  string $file_name
	 * @param  array  $data     data that should be passed into template
	 *
	 * @throws Exception If $is_development_mode enabled
	 *
	 * @return string
	 */
	protected function render_view( $file_name, array $data ) {
		$template_path = $this->get_template_path( $file_name );

		if ( ! file_exists( $template_path ) ) {
			return $this->error( strtr( 'File "{filePath}" does not exists.', array(
				'{filePath}' => $template_path,
			) ) );
		}

		ob_start();
		extract( $data );
		include $template_path;
		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Converts local template name to the full file path.
	 *
	 * @param  string $file_name
	 * @return string
	 */
	protected function get_template_path( $file_name ) {
		return dirname( __FILE__ ) . '/../views/' . $file_name;
	}
}
