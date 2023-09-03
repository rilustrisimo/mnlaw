<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Setup extends LoginLockdown
{
    /**
     * Actions to run on load, but init would be too early as not all classes are initialized
     *
     * @return null
     */
    static function load_actions()
    {
        self::register_custom_tables();
    } // admin_actions


    /**
     * Check if user has the minimal WP version required by Login Lockdown
     *
     *
     * @return bool
     *
     */
    static function check_wp_version($min_version)
    {
        if (!version_compare(get_bloginfo('version'), $min_version,  '>=')) {
            add_action('admin_notices', array(__CLASS__, 'notice_min_wp_version'));
            return false;
        } else {
            return true;
        }
    } // check_wp_version

    /**
     * Check if user has the minimal PHP version required by Login Lockdown
     *
     *
     * @return bool
     *
     */
    static function check_php_version($min_version)
    {
        if (!version_compare(phpversion(), $min_version,  '>=')) {
            add_action('admin_notices', array(__CLASS__, 'notice_min_php_version'));
            return false;
        } else {
            return true;
        }
    } // check_wp_version

    /**
     * Display error message if WP version is too low

     * @return null
     *
     */
    static function notice_min_wp_version()
    {
        LoginLockdown_Utility::wp_kses_wf('<div class="error"><p>' . sprintf(__('Login Lockdown plugin <b>requires WordPress version 4.6</b> or higher to function properly. You are using WordPress version %s. Please <a href="%s">update it</a>.', 'login-lockdown'), get_bloginfo('version'), admin_url('update-core.php')) . '</p></div>');
    } // notice_min_wp_version_error

    /**
     * Display error message if PHP version is too low

     * @return null
     *
     */
    static function notice_min_php_version()
    {
        LoginLockdown_Utility::wp_kses_wf('<div class="error"><p>' . sprintf(__('Login Lockdown plugin <b>requires PHP version 5.6.20</b> or higher to function properly. You are using PHP version %s. Please <a href="%s" target="_blank">update it</a>.', 'login-lockdown'), phpversion(), 'https://wordpress.org/support/update-php/') . '</p></div>');
    } // notice_min_wp_version_error


    /**
     * activate doesn't get fired on upgrades so we have to compensate

     * @return null
     *
     */
    public static function maybe_upgrade()
    {
        $meta = self::get_meta();
        if (empty($meta['database_ver']) || $meta['database_ver'] < self::$version) {
            self::create_custom_tables();
        }


        // Copy options from free
        $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY);
        if (false === $options) {
            $free_options = get_option("loginlockdownAdminOptions");
            if (false !== $free_options && isset($free_options['max_login_retries'])) {
                if ($free_options['mask_login_errors'] == 'yes') {
                    $free_options['mask_login_errors'] = 1;
                } else {
                    $free_options['mask_login_errors'] = 0;
                }

                if ($free_options['lockout_invalid_usernames'] == 'yes') {
                    $free_options['lockout_invalid_usernames'] = 1;
                } else {
                    $free_options['lockout_invalid_usernames'] = 0;
                }

                if ($free_options['show_credit_link'] == 'yes') {
                    $free_options['show_credit_link'] = 1;
                } else {
                    $free_options['show_credit_link'] = 0;
                }

                update_option(LOGINLOCKDOWN_OPTIONS_KEY, $free_options);
                delete_option("loginlockdownAdminOptions");
            }
        }
    } // maybe_upgrade


    /**
     * Get plugin options
     * @return array options
     *
     */
    static function get_options()
    {
        $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY, array());

        if (!is_array($options)) {
            $options = array();
        }
        $options = array_merge(self::default_options(), $options);

        return $options;
    } // get_options

    /**
     * Register all settings
     * @return false
     *
     */
    static function register_settings()
    {
        register_setting(LOGINLOCKDOWN_OPTIONS_KEY, LOGINLOCKDOWN_OPTIONS_KEY, array(__CLASS__, 'sanitize_settings'));
    } // register_settings


    /**
     * Set default options

     * @return null
     *
     */
    static function default_options()
    {
        $defaults = array(
            'max_login_retries'            => 3,
            'retries_within'               => 5,
            'lockout_length'               => 60,
            'lockout_invalid_usernames'    => 1,
            'mask_login_errors'            => 0,
            'show_credit_link'             => 0,
            'captcha'                      => 'disabled',
            'global_block'                 => 0,
            'uninstall_delete'             => 0,
            'block_message'                => 'We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.',
            'global_unblock_key'           => 'll' . md5(time() . rand(10000, 9999)),
            'whitelist'                    => array()
        );

        return $defaults;
    } // default_options


    /**
     * Sanitize settings on save

     * @return array updated options
     *
     */
    static function sanitize_settings($options)
    {
        $old_options = self::get_options();

        if(isset($options['captcha_verified']) && $options['captcha_verified'] != 1 && $options['captcha'] != 'disabled'){
            $options['captcha'] = $old_options['captcha'];
        }

        if (isset($_POST['submit'])) {
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'lockout_invalid_usernames':
                    case 'mask_login_errors':
                    case 'show_credit_link':
                        $options[$key] = trim($value);
                        break;
                    case 'max_login_retries':
                    case 'retries_within':
                    case 'lockout_length':
                        $options[$key] = (int) $value;
                        break;
                } // switch
            } // foreach
        }

        if (!isset($options['lockout_invalid_usernames'])) {
            $options['lockout_invalid_usernames'] = 0;
        }

        if (!isset($options['mask_login_errors'])) {
            $options['mask_login_errors'] = 0;
        }

        if (!isset($options['block_undetermined_countries'])) {
            $options['block_undetermined_countries'] = 0;
        }

        if (!isset($options['global_block'])) {
            $options['global_block'] = 0;
        }

        if (!isset($options['uninstall_delete'])) {
            $options['uninstall_delete'] = 0;
        }

        if (!is_array($options['whitelist'])) {
            $options['whitelist'] = explode(PHP_EOL, $options['whitelist']);
        }

        if (isset($_POST['loginlockdown_import_file'])) {
            $mimes = array(
                'text/plain',
                'text/anytext',
                'application/txt'
            );

            if (!in_array($_FILES['loginlockdown_import_file']['type'], $mimes)) {
                LoginLockdown_Utility::display_notice(
                    sprintf(
                        "WARNING: Not a valid CSV file - the Mime Type '%s' is wrong! No settings have been imported.",
                        $_FILES['loginlockdown_import_file']['type']
                    ),
                    "error"
                );
            } else if (($handle = fopen($_FILES['loginlockdown_import_file']['tmp_name'], "r")) !== false) {
                $options_json = json_decode(fread($handle, 8192), ARRAY_A);

                if (is_array($options_json) && array_key_exists('max_login_retries', $options_json) && array_key_exists('retries_within', $options_json) && array_key_exists('lockout_length', $options_json)) {
                    $options = $options_json;
                    LoginLockdown_Utility::display_notice("Settings have been imported.", "success");
                } else {
                    LoginLockdown_Utility::display_notice("Invalid import file! No settings have been imported.", "error");
                }
            } else {
                LoginLockdown_Utility::display_notice("Invalid import file! No settings have been imported.", "error");
            }
        }

        LoginLockdown_Utility::clear_3rdparty_cache();
        $options['last_options_edit'] = current_time('mysql', true);

        return array_merge($old_options, $options);
    } // sanitize_settings

    /**
     * Get plugin metadata

     * @return array meta
     *
     */
    static function get_meta()
    {
        $meta = get_option(LOGINLOCKDOWN_META_KEY, array());

        if (!is_array($meta) || empty($meta)) {
            $meta['first_version'] = self::get_plugin_version();
            $meta['first_install'] = current_time('timestamp');
            update_option(LOGINLOCKDOWN_META_KEY, $meta);
        }

        return $meta;
    } // get_meta

    static function update_meta($key, $value)
    {
        $meta = get_option(LOGINLOCKDOWN_META_KEY, array());
        $meta[$key] = $value;
        update_option(LOGINLOCKDOWN_META_KEY, $meta);
    } // update_meta

    /**
     * Register custom tables

     * @return null
     *
     */
    static function register_custom_tables()
    {
        global $wpdb;

        $wpdb->lockdown_login_fails = $wpdb->prefix . 'login_fails';
        $wpdb->lockdown_lockdowns = $wpdb->prefix . 'lockdowns';
    } // register_custom_tables

    /**
     * Create custom tables

     * @return null
     *
     */
    static function create_custom_tables()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        self::register_custom_tables();

        $lockdown_login_fails = "CREATE TABLE " . $wpdb->lockdown_login_fails . " (
			`login_attempt_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`login_attempt_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`login_attempt_IP` varchar(100) NOT NULL default '',
            `failed_user` varchar(200) NOT NULL default '',
            `reason` varchar(200) NULL,
			PRIMARY KEY  (`login_attempt_ID`)
			);";
        dbDelta($lockdown_login_fails);

        $lockdown_lockdowns = "CREATE TABLE " . $wpdb->lockdown_lockdowns . " (
			`lockdown_ID` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`lockdown_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`release_date` datetime NOT NULL default '0000-00-00 00:00:00',
			`lockdown_IP` varchar(100) NOT NULL default '',
            `reason` varchar(200) NULL,
            `unlocked` smallint(20) NOT NULL default '0',
			PRIMARY KEY  (`lockdown_ID`)
			);";
        dbDelta($lockdown_lockdowns);

        self::update_meta('database_ver', self::$version);
    } // create_custom_tables

    /**
     * Actions on plugin activation

     * @return null
     *
     */
    static function activate()
    {
        self::create_custom_tables();
        LoginLockdown_Admin::reset_pointers();
    } // activate


    /**
     * Actions on plugin deactivaiton

     * @return null
     *
     */
    static function deactivate()
    {
    } // deactivate

    /**
     * Actions on plugin uninstall

     * @return null
     */
    static function uninstall()
    {
        global $wpdb;

        $options = get_option(LOGINLOCKDOWN_OPTIONS_KEY, array());

        if ($options['uninstall_delete'] == '1') {
            delete_option(LOGINLOCKDOWN_OPTIONS_KEY);
            delete_option(LOGINLOCKDOWN_META_KEY);
            delete_option(LOGINLOCKDOWN_POINTERS_KEY);
            delete_option(LOGINLOCKDOWN_NOTICES_KEY);

            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "login_fails");
            $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "lockdowns");
        }
    } // uninstall
} // class
