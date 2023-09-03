<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Functions extends LoginLockdown
{
  static function countFails($username = "")
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    $ip = LoginLockdown_Utility::getUserIP();

    $numFails = $wpdb->get_var(
      $wpdb->prepare(
        "SELECT COUNT(login_attempt_ID) FROM " . $wpdb->lockdown_login_fails . " WHERE login_attempt_date + INTERVAL %d MINUTE > %s AND login_attempt_IP = %s",
        array($options['retries_within'], current_time('mysql'), $ip)
      )
    );

    return $numFails;
  }

  static function incrementFails($username = "", $reason = "")
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    $ip = LoginLockdown_Utility::getUserIP();

    $username = sanitize_user($username);
    $user = get_user_by('login', $username);

    if ($user || 1 == $options['lockout_invalid_usernames']) {
      if ($user === false) {
        $user_id = -1;
      } else {
        $user_id = $user->ID;
      }

      $wpdb->insert(
        $wpdb->lockdown_login_fails,
        array(
          'user_id' => $user_id,
          'login_attempt_date' => current_time('mysql'),
          'login_attempt_IP' => $ip,
          'failed_user' => $username,
          'reason' => $reason
        )
      );
    }
  }

  static function lockDown($username = "", $reason = "")
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    $ip = LoginLockdown_Utility::getUserIP();

    $username = sanitize_user($username);
    $user = get_user_by('login', $username);
    if ($user || 1 == $options['lockout_invalid_usernames']) {
      if ($user === false) {
        $user_id = -1;
      } else {
        $user_id = $user->ID;
      }

      $wpdb->insert(
        $wpdb->lockdown_lockdowns,
        array(
          'user_id' => $user_id,
          'lockdown_date' => current_time('mysql'),
          'release_date' => date('Y-m-d H:i:s', strtotime(current_time('mysql')) + $options['lockout_length'] * 60),
          'lockdown_IP' => $ip,
          'reason' => $reason
        )
      );
    }
  }

  static function isLockedDown()
  {
    global $wpdb;
    $ip = LoginLockdown_Utility::getUserIP();

    $stillLocked = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM " . $wpdb->lockdown_lockdowns . " WHERE release_date > %s AND lockdown_IP = %s AND unlocked = 0", array(current_time('mysql'), $ip)));

    return $stillLocked;
  }

  static function is_rest_request()
  {
    if (defined('REST_REQUEST') && REST_REQUEST || isset($_GET['rest_route']) && strpos(sanitize_text_field(wp_unslash($_GET['rest_route'])), '/', 0) === 0) {
      return true;
    }

    global $wp_rewrite;
    if (null === $wp_rewrite) {
      $wp_rewrite = new WP_Rewrite();
    }

    $rest_url = wp_parse_url(trailingslashit(rest_url()));
    if (!is_array($rest_url) || !array_key_exists('path', $rest_url)) {
      return false;
    }

    $current_url = wp_parse_url(add_query_arg(array()));
    if (!is_array($current_url) || !array_key_exists('path', $current_url)) {
      return false;
    }

    $is_rest     = strpos($current_url['path'], $rest_url['path'], 0) === 0;

    return $is_rest;
  }

  static function wp_authenticate_username_password($user, $username, $password)
  {
    if (is_a($user, 'WP_User')) {
      return $user;
    }

    $options = LoginLockdown_Setup::get_options();

    $whitelisted = false;
    $user_ip = LoginLockdown_Utility::getUserIP();
    if (in_array($user_ip, $options['whitelist'])) {
      $whitelisted = true;
    }

    if (!$whitelisted && self::isLockedDown()) {
      self::lockdown_screen($options['block_message']);
      return new WP_Error('lockdown_fail_count', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
    }

    if (!$username) {
      return $user;
    }

    if (self::is_rest_request()) {
      return $user;
    }

    $captcha = self::handle_captcha();
    if (is_wp_error($captcha)) {
      if ($options['max_login_retries'] <= self::countFails($username) && self::countFails($username) > 0) {
        self::lockDown($username, 'Too many captcha fails');
      }
      return $captcha;
    }

    $userdata = get_user_by('login', $username);

    if (!$whitelisted && $options['max_login_retries'] <= self::countFails($username)) {
      if ($options['max_login_retries'] <= self::countFails($username) && self::countFails($username) > 0) {
        self::lockDown($username, 'Too many fails');
      }

      return new WP_Error('lockdown_fail_count', __("<strong>ERROR</strong>: We're sorry, but this IP has been blocked due to too many recent failed login attempts.<br /><br />Please try again later.", 'login-lockdown'));
    }

    if (empty($username) || empty($password)) {
      $error = new WP_Error();

      if (empty($username))
        $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.', 'login-lockdown'));

      if (empty($password))
        $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.', 'login-lockdown'));

      return $error;
    }

    if ($userdata === false) {
      return new WP_Error('invalid_username', sprintf(__('<strong>ERROR</strong>: Invalid username. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'login-lockdown'), site_url('wp-login.php?action=lostpassword', 'login')));
    }

    $userdata = apply_filters('wp_authenticate_user', $userdata, $password);
    if (is_wp_error($userdata)) {
      return $userdata;
    }

    if (!wp_check_password($password, $userdata->user_pass, $userdata->ID)) {
      return new WP_Error('incorrect_password', sprintf(__('<strong>ERROR</strong>: Incorrect password. <a href="%s" title="Password Lost and Found">Lost your password</a>?', 'login-lockdown'), site_url('wp-login.php?action=lostpassword', 'login')));
    }

    $user =  new WP_User($userdata->ID);
    return $user;
  }

  static function handle_captcha()
  {
    $options = LoginLockdown_Setup::get_options();

    if ($options['captcha'] == 'builtin') {
      if (isset($_POST['loginlockdown_captcha']) && sanitize_text_field($_POST['loginlockdown_captcha']) === $_COOKIE['loginlockdown_captcha']) {
        return true;
      } else {
        return new WP_Error('lockdown_builtin_captcha_failed', __("<strong>ERROR</strong>: captcha verification failed.<br /><br />Please try again.", 'login-lockdown'));
      }
    }

    return true;
  }

  static function loginFailed($username, $error)
  {
    self::incrementFails($username, $error->get_error_code());
  }

  static function login_error_message($error)
  {
    $options = LoginLockdown_Setup::get_options();

    if ($options['mask_login_errors'] == 1) {
      $error = 'Login Failed';
    }
    return $error;
  }

  static function login_form_fields()
  {
    $options = LoginLockdown_Setup::get_options();
    $showcreditlink = $options['show_credit_link'];

    if ($options['captcha'] == 'builtin') {
      echo '<p><label for="loginlockdown_captcha">Are you human? Please solve: ';
      echo '<img class="loginlockdown-captcha-img" style="vertical-align: text-top;" src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/libs/captcha.php?loginlockdown-generate-image=true&noise=1&rnd=' . esc_attr(rand(0, 10000)) . '" alt="Captcha" />';
      echo '<input class="input" type="text" size="3" name="loginlockdown_captcha" id="loginlockdown_captcha" />';
      echo '</label></p><br />';
    }

    if ($showcreditlink != "no" && $showcreditlink != 0) {
      echo "<div id='loginlockdown-protected-by' style='display: block; clear: both; padding-top: 20px; text-align: center;'>";
      esc_html_e('Login form protected by', 'login-lockdown');
      echo ' <a target="_blank" href="' . esc_url('https://wploginlockdown.com/') . '">Login Lockdown</a></div>';
      echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelector("#loginform").append(document.querySelector("#loginlockdown-protected-by"));
            });
            </script>';
    }
  }

  static function lockdown_screen($block_message = false)
  {
    $main_color = '#29b99a';
    $secondary_color = '#3fccb0';

    echo '<style>
            @import url(\'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400;1,500;1,700&display=swap\');

            #loginlockdown_lockdown_screen_wrapper{
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                width:100%;
                height:100%;
                position:fixed;
                top:0;
                left:0;
                z-index: 999999;
                font-size: 14px;
                color: #333;
                line-height: 1.4;
                background-image: linear-gradient(45deg, ' . esc_attr($main_color) . ' 25%, ' . esc_attr($secondary_color) . ' 25%, ' . esc_attr($secondary_color) . ' 50%, ' . esc_attr($main_color) . ' 50%, ' . esc_attr($main_color) . ' 75%, ' . esc_attr($secondary_color) . ' 75%, ' . esc_attr($secondary_color) . ' 100%);
                background-size: 28.28px 28.28px;
            }

            #loginlockdown_lockdown_screen_wrapper form{
                max-width: 300px;
                top:50%;
                left:50%;
                margin-top:-200px;
                margin-left:-200px;
                border: none;
                background: #ffffffde;
                box-shadow: 0 1px 3px rgb(0 0 0 / 4%);
                position: fixed;
                text-align:center;
                background: #fffffff2;
                padding: 20px;
                -webkit-box-shadow: 5px 5px 0px 1px rgba(0,0,0,0.22);
                box-shadow: 5px 5px 0px 1px rgba(0,0,0,0.22);
            }

            #loginlockdown_lockdown_screen_wrapper p{
                padding: 10px;
                line-height:1.5;
            }

            #loginlockdown_lockdown_screen_wrapper p.error{
                background: #f11c1c;
                color: #FFF;
                font-weight: 500;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="text"]{
                padding: 4px 10px;
                border-radius: 2px;
                border: 1px solid #c3c4c7;
                font-size: 16px;
                line-height: 1.33333333;
                margin: 0 6px 16px 0;
                min-height: 40px;
                max-height: none;
                width: 100%;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="submit"]{
                padding: 10px 10px;
                border-radius: 2px;
                border: none;
                font-size: 16px;
                background: ' . esc_attr($main_color) . ';
                color: #FFF;
                cursor: pointer;
                width: 100%;
            }

            #loginlockdown_lockdown_screen_wrapper form input[type="submit"]:hover{
                background: ' . esc_attr($secondary_color) . ';
            }
        </style>

        <script>
        document.title = "' . esc_html(get_bloginfo('name')) . '";
        </script>';
    echo '<div id="loginlockdown_lockdown_screen_wrapper">';

    echo '<form method="POST">';

    if (isset($_POST['loginlockdown_recovery_submit']) && wp_verify_nonce($_POST['loginlockdown_recovery_nonce'], 'loginlockdown_recovery')) {
      $email = sanitize_text_field($_POST['loginlockdown_recovery_email']);
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $display_message = '<p class="error">Invalid email address.</p>';
      } else {
        $user = get_user_by('email', $email);
        if (user_can($user, 'administrator')) {
          $unblock_key = md5(time() . rand(10000, 9999));
          $unblock_attempts = get_transient('loginlockdown_unlock_count_' . $user->ID);
          if (!$unblock_attempts) {
            $unblock_attempts = 0;
          }

          $unblock_attempts++;
          set_transient('loginlockdown_unlock_count_' . $user->ID, $unblock_attempts, HOUR_IN_SECONDS);

          if ($unblock_attempts <= 3) {
            set_transient('loginlockdown_unlock_' . $unblock_key, $unblock_key, HOUR_IN_SECONDS);

            $unblock_url = add_query_arg(array('loginlockdown_unblock' => $unblock_key), wp_login_url());

            $subject  = 'Login Lockdown unblock instructions for ' . site_url();
            $message  = '<p>The IP ' . LoginLockdown_Utility::getUserIP() . ' has been locked down and someone submitted an unblock request using your email address <strong>' . $email . '</strong></p>';
            $message .= '<p>If this was you, and you have locked yourself out please click <a target="_blank" href="' . $unblock_url . '">this link</a> which is valid for 1 hour.</p>';
            $message .= '<p>Please note that for security reasons, this will only unblock the IP of the person opening the link, not the IP of the person who submitted the unblock request. To unblock someone else please do so on the <a href="' . admin_url('options-general.php?page=loginlockdown#loginlockdown_activity') . '">Login Lockdown Activity Page</p>';

            add_filter('wp_mail_content_type', function () {
              return "text/html";
            });

            wp_mail($user->user_email, $subject, $message);
          }
        } else {
          // If no admin using the submitted email exists, ignore silently
        }

        if (isset($unblock_attempts) && $unblock_attempts > 3) {
          $display_message = '<p class="error">You have already attempted to unblock yourself recently, please wait 1 hour before trying again.</p>';
        } else {
          $display_message = '<p>If an administrator having the email address <strong>' . $email . '</strong> exists, an email has been sent with instructions to regain access.</p>';
        }
      }
    }

    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/loginlockdown-logo.png" alt="Login Lockdown" height="60" title="Login Lockdown">';

    echo '<br />';
    echo '<br />';
    if ($block_message !== false) {
      echo '<p class="error">' . esc_html($block_message) . '</p>';
    } else {
      echo '<p class="error">We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.</p>';
    }
    if (!empty($display_message)) {
      LoginLockdown_Utility::wp_kses_wf($display_message);
    }
    echo '<p>If you are a user with administrative privilege please enter your email below to receive instructions on how to unblock yourself.</p>';
    echo '<input type="text" name="loginlockdown_recovery_email" value="" placeholder="" />';
    echo '<input type="submit" name="loginlockdown_recovery_submit" value="Send unblock email" placeholder="" />';
    wp_nonce_field('loginlockdown_recovery', 'loginlockdown_recovery_nonce');


    echo '</form>';
    echo '</div>';

    exit();
  }

  static function handle_unblock()
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();
    if (isset($_GET['loginlockdown_unblock']) && $options['global_unblock_key'] === sanitize_text_field($_GET['loginlockdown_unblock'])) {
      $user_ip = LoginLockdown_Utility::getUserIP();
      if (!in_array($user_ip, $options['whitelist'])) {
        $options['whitelist'][] = LoginLockdown_Utility::getUserIP();
      }
      update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
    }

    if (isset($_GET['loginlockdown_unblock']) && strlen($_GET['loginlockdown_unblock']) == 32) {
      $unblock_key = sanitize_key($_GET['loginlockdown_unblock']);
      $unblock_transient = get_transient('loginlockdown_unlock_' . $unblock_key);
      if ($unblock_transient == $unblock_key) {
        $user_ip = LoginLockdown_Utility::getUserIP();
        $wpdb->delete(
          $wpdb->lockdown_lockdowns,
          array(
            'lockdown_IP' => $user_ip
          )
        );

        if (!in_array($user_ip, $options['whitelist'])) {
          $options['whitelist'][] = LoginLockdown_Utility::getUserIP();
        }

        update_option(LOGINLOCKDOWN_OPTIONS_KEY, $options);
      }
    }
  }

  static function handle_global_block()
  {
    $options = LoginLockdown_Setup::get_options();

    //If user is on local or cloud whitelist, don't check anything else
    $user_ip = LoginLockdown_Utility::getUserIP();
    if (in_array($user_ip, $options['whitelist'])) {
      return false;
    }

    //Check website lock
    if ($options['global_block'] == '1' && self::isLockedDown()) {
      self::lockdown_screen($options['block_message']);
    }
  }

  public static function clean_ip_string($ip)
  {
    $ip = trim($ip);
    return $ip;
  }

  public static function pretty_fail_errors($error_code)
  {
    switch ($error_code) {
      case 'lockdown_location_blocked':
        return 'Blocked Location';
        break;
      case 'lockdown_fail_count':
        return 'User exceeded maximum number of fails';
        break;
      case 'lockdown_bot':
        return 'Bot';
        break;
      case 'empty_username':
        return 'Empty Username';
        break;
      case 'empty_password':
        return 'Empty Password';
        break;
      case 'incorrect_password':
        return 'Incorrect Password';
        break;
      case 'invalid_username':
        return 'Invalid Username';
        break;
      case 'lockdown_builtin_captcha_failed':
        return 'Built-in captcha failed verification';
        break;
      default:
        return 'Unknown';
        break;
    }
  }

  static function generate_export_file()
  {
    $filename = str_replace(array('http://', 'https://'), '', home_url());
    $filename = str_replace(array('/', '\\', '.'), '-', $filename);
    $filename .= '-' . date('Y-m-d') . '-loginlockdown.txt';

    $options = LoginLockdown_Setup::get_options();
    $options_json = json_encode($options);

    header('Content-type: text/txt');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($options_json));

    @ob_end_clean();
    flush();

    LoginLockdown_Utility::wp_kses_wf($options_json);

    exit;
  } // generate_export_file


  // auto download / install / activate WP 301 Redirects plugin
  static function install_wp301()
  {
    check_ajax_referer('install_wp301');

    if (false === current_user_can('administrator')) {
      wp_die('Sorry, you have to be an admin to run this action.');
    }

    $plugin_slug = 'eps-301-redirects/eps-301-redirects.php';
    $plugin_zip = 'https://downloads.wordpress.org/plugin/eps-301-redirects.latest-stable.zip';

    @include_once ABSPATH . 'wp-admin/includes/plugin.php';
    @include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    @include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
    @include_once ABSPATH . 'wp-admin/includes/file.php';
    @include_once ABSPATH . 'wp-admin/includes/misc.php';
    echo '<style>
		body{
			font-family: sans-serif;
			font-size: 14px;
			line-height: 1.5;
			color: #444;
		}
		</style>';

    echo '<div style="margin: 20px; color:#444;">';
    echo 'If things are not done in a minute <a target="_parent" href="' . admin_url('plugin-install.php?s=301%20redirects%20webfactory&tab=search&type=term') . '">install the plugin manually via Plugins page</a><br><br>';
    echo 'Starting ...<br><br>';

    wp_cache_flush();
    $upgrader = new Plugin_Upgrader();
    echo 'Check if WP 301 Redirects is already installed ... <br />';
    if (self::is_plugin_installed($plugin_slug)) {
      echo 'WP 301 Redirects is already installed! <br /><br />Making sure it\'s the latest version.<br />';
      $upgrader->upgrade($plugin_slug);
      $installed = true;
    } else {
      echo 'Installing WP 301 Redirects.<br />';
      $installed = $upgrader->install($plugin_zip);
    }
    wp_cache_flush();

    if (!is_wp_error($installed) && $installed) {
      echo 'Activating WP 301 Redirects.<br />';
      $activate = activate_plugin($plugin_slug);

      if (is_null($activate)) {
        echo 'WP 301 Redirects Activated.<br />';

        echo '<script>setTimeout(function() { top.location = "' . admin_url('options-general.php?page=eps_redirects') . '"; }, 1000);</script>';
        echo '<br>If you are not redirected in a few seconds - <a href="' . admin_url('options-general.php?page=eps_redirects') . '" target="_parent">click here</a>.';
      }
    } else {
      echo 'Could not install WP 301 Redirects. You\'ll have to <a target="_parent" href="' . admin_url('plugin-install.php?s=301%20redirects%20webfactory&tab=search&type=term') . '">download and install manually</a>.';
    }

    echo '</div>';
  } // install_wp301


  /**
   * Check if given plugin is installed
   *
   * @param [string] $slug Plugin slug
   * @return boolean
   */
  static function is_plugin_installed($slug)
  {
    if (!function_exists('get_plugins')) {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();

    if (!empty($all_plugins[$slug])) {
      return true;
    } else {
      return false;
    }
  } // is_plugin_installed
} // class
