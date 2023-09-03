<?php
/*
  Plugin Name: Login Lockdown
  Plugin URI: https://wploginlockdown.com/
  Description: Protect the login form by banning IPs after multiple failed login attempts.
  Version: 2.06
  Author: WebFactory Ltd
  Author URI: https://www.webfactoryltd.com/
  License: GNU General Public License v3.0
  Text Domain: login-lockdown
  Requires at least: 4.0
  Tested up to: 6.2
  Requires PHP: 5.2

  Copyright 2022 - 2023  WebFactory Ltd  (email: support@webfactoryltd.com)
  Copyright 2007 - 2022  M. VanDeMar

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// include only file
if (!defined('ABSPATH')) {
  wp_die(__('Do not open this file directly.', 'login-lockdown'));
}

define('LOGINLOCKDOWN_PLUGIN_FILE', __FILE__);
define('LOGINLOCKDOWN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LOGINLOCKDOWN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LOGINLOCKDOWN_OPTIONS_KEY', 'loginlockdown_options');
define('LOGINLOCKDOWN_META_KEY', 'loginlockdown_meta');
define('LOGINLOCKDOWN_POINTERS_KEY', 'loginlockdown_pointers');
define('LOGINLOCKDOWN_NOTICES_KEY', 'loginlockdown_notices');

require_once LOGINLOCKDOWN_PLUGIN_DIR . 'libs/admin.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'libs/setup.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'libs/utility.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'libs/functions.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'libs/stats.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'libs/ajax.php';


require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_login_form.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_activity.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_geoip.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_2fa.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_captcha.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_cloud_protection.php';
require_once LOGINLOCKDOWN_PLUGIN_DIR . 'interface/tab_temp_access.php';

require_once dirname(__FILE__) . '/wf-flyout/wf-flyout.php';


// main plugin class
class LoginLockdown
{
  static $version = 0;

  static $type;

  /**
   * Setup Hooks

   * @return null
   */
  static function init()
  {
    // check if minimal required WP version is present
    if (false === LoginLockdown_Setup::check_wp_version(4.6) || false === LoginLockdown_Setup::check_php_version('5.6.20')) {
      return false;
    }

    LoginLockdown_Setup::maybe_upgrade();
    LoginLockdown_Functions::handle_unblock();
    LoginLockdown_Functions::handle_global_block();
    $options = LoginLockdown_Setup::get_options();

    if (is_admin()) {
      new wf_flyout(__FILE__);

      // add Login Lockdown menu to admin tools menu group
      add_action('admin_menu', array('LoginLockdown_Admin', 'admin_menu'));

      // additional links in plugin description and footer
      add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('LoginLockdown_Admin', 'plugin_action_links'));
      add_filter('plugin_row_meta', array('LoginLockdown_Admin', 'plugin_meta_links'), 10, 2);
      add_filter('admin_footer_text', array('LoginLockdown_Admin', 'admin_footer_text'));

      add_action('wp_dashboard_setup', array('LoginLockdown_Admin', 'add_widget'));

      // settings registration
      add_action('admin_init', array('LoginLockdown_Setup', 'register_settings'));

      // enqueue admin scripts
      add_action('admin_enqueue_scripts', array('LoginLockdown_Admin', 'admin_enqueue_scripts'));

      // admin actions
      add_action('admin_action_loginlockdown_export_settings', array('LoginLockdown_Functions', 'generate_export_file'));
      add_action('admin_action_loginlockdown_install_wp301', array('LoginLockdown_Functions', 'install_wp301'));

      // AJAX endpoints
      add_action('wp_ajax_loginlockdown_run_tool', array('LoginLockdown_AJAX', 'ajax_run_tool'));
    } else {
      add_action('login_form', array('LoginLockdown_Functions', 'login_form_fields'));
      add_action('woocommerce_login_form', array('LoginLockdown_Functions', 'login_form_fields'));

      add_action('wp_login_failed', array('LoginLockdown_Functions', 'loginFailed'), 10, 2);
      add_filter('login_errors', array('LoginLockdown_Functions', 'login_error_message'));

      remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
      add_filter('authenticate', array('LoginLockdown_Functions', 'wp_authenticate_username_password'), 20, 3);
    } // if not admin
  } // init

  /**
   * Get plugin version

   * @return int plugin version
   *
   */
  static function get_plugin_version()
  {
    $plugin_data = get_file_data(__FILE__, array('version' => 'Version'), 'plugin');
    self::$version = $plugin_data['version'];

    return $plugin_data['version'];
  } // get_plugin_version

  /**
   * Set plugin version and text domain

   * @return null
   */
  static function plugins_loaded()
  {
    self::get_plugin_version();
    load_plugin_textdomain('login-lockdown');
  } // plugins_loaded

  static function run()
  {
    self::plugins_loaded();
    LoginLockdown_Setup::load_actions();
  }
} // class LoginLockdown


/**
 * Setup Hooks
 */
register_activation_hook(__FILE__, array('LoginLockdown_Setup', 'activate'));
register_deactivation_hook(__FILE__, array('LoginLockdown_Setup', 'deactivate'));
register_uninstall_hook(__FILE__, array('LoginLockdown_Setup', 'uninstall'));
add_action('plugins_loaded', array('loginlockdown', 'run'), -9999);
add_action('init', array('loginlockdown', 'init'), -1);
