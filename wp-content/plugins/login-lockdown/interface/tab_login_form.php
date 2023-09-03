<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Login_Form extends LoginLockdown
{
  static function display()
  {
    $tabs[] = array('id' => 'tab_login_basic', 'class' => 'tab-content', 'label' => __('Basic', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_basic'));
    $tabs[] = array('id' => 'tab_login_advanced', 'class' => 'tab-content', 'label' => __('Advanced', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_advanced'));
    $tabs[] = array('id' => 'tab_login_tools', 'class' => 'tab-content', 'label' => __('Tools', 'login-lockdown'), 'callback' => array(__CLASS__, 'tab_tools'));

    echo '<div id="tabs_log" class="ui-tabs loginlockdown-tabs-2nd-level">';
    echo '<ul>';
    foreach ($tabs as $tab) {
      echo '<li><a href="#' . esc_attr($tab['id']) . '">' . esc_attr($tab['label']) . '</a></li>';
    }
    echo '</ul>';

    foreach ($tabs as $tab) {
      if (is_callable($tab['callback'])) {
        echo '<div style="display: none;" id="' . esc_attr($tab['id']) . '" class="' . esc_attr($tab['class']) . '">';
        call_user_func($tab['callback']);
        echo '</div>';
      }
    } // foreach

    echo '</div>'; // second level of tabs


  } // display

  static function tab_basic()
  {
    $options = LoginLockdown_Setup::get_options();

    echo '<table class="form-table"><tbody>';

    echo '<tr valign="top">
        <th scope="row"><label for="max_login_retries">Max Login Retries</label></th>
        <td><input type="number" class="regular-text" id="max_login_retries" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[max_login_retries]" value="' . esc_attr($options['max_login_retries']) . '" />';
    echo '<br><span>Number of failed login attempts within the "Retry Time Period Restriction" (defined below) needed to trigger a Lockdown.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="retries_within">Retry Time Period Restriction</label></th>
        <td><input type="number" class="regular-text" id="retries_within" name="' .  esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[retries_within]" value="' . esc_attr($options['retries_within']) . '" /> minutes';
    echo '<br><span>The time in which failed login attempts are allowed before a lockdown occurs.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="lockout_length">Lockout Length</label></th>
        <td><input type="number" class="regular-text" id="lockout_length" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[lockout_length]" value="' . esc_attr($options['lockout_length']) . '" /> minutes';
    echo '<br><span>The time a particular IP will be locked out once a lockdown has been triggered.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="lockout_invalid_usernames">Log Failed Attempts With Non-existant Usernames</label></th>
        <td>';
    LoginLockdown_Utility::create_toggle_switch('lockout_invalid_usernames', array('saved_value' => $options['lockout_invalid_usernames'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[lockout_invalid_usernames]'));
    echo '<br /><span>Log failed log in attempts with non-existant usernames the same way failed attempts with bad passwords are logged.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="mask_login_errors">Mask Login Errors</label></th>
        <td>';
    LoginLockdown_Utility::create_toggle_switch('mask_login_errors', array('saved_value' => $options['mask_login_errors'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[mask_login_errors]'));
    echo '<br /><span>Hide log in error details (such as invalid username, invalid password, invalid captcha value) to minimize data available to attackers.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="global_block">Block Type</label></th>
        <td>';
    echo '<label class="loginlockdown-radio-option">';
    echo '<span class="radio-container"><input type="radio" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[global_block]" id="global_block_global" value="1" ' . ($options['global_block'] == 1 ? 'checked' : '') . '><span class="radio"></span></span> Completely block website access';
    echo '</label>';

    echo '<label class="loginlockdown-radio-option">';
    echo '<span class="radio-container"><input type="radio" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[global_block]" id="global_block_login" value="0" ' . ($options['global_block'] != 1 ? 'checked' : '') . '><span class="radio"></span></span> Only block access to the login page';
    echo '</label>';
    echo '<span>Completely block website access for blocked IPs, or just blocking access to the login page.</span>';
    echo '</td></tr>';


    echo '<tr valign="top">
        <th scope="row"><label for="block_message">Block Message</label></th>
        <td><input type="text" class="regular-text" id="block_message" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[block_message]" value="' . esc_html($options['block_message']) . '" />';
    echo '<br /><span>Message displayed to visitors blocked due to too many failed login attempts. Default: <i>We\'re sorry, but your IP has been blocked due to too many recent failed login attempts.</i></span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="whitelist">Whitelisted IPs</label></th>
        <td><textarea class="regular-text" id="whitelist" rows="6" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[whitelist]">' . (is_array($options['whitelist']) ? esc_html(implode(PHP_EOL, $options['whitelist'])) : esc_html($options['whitelist'])) . '</textarea>';
    echo '<br /><span>List of IP addresses that will never be blocked. Enter one IP per line.<br>Your current IP is: <code>' . esc_html($_SERVER['REMOTE_ADDR']) . '</code></span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="show_credit_link">Show Credit Link</label></th>
        <td>';
    LoginLockdown_Utility::create_toggle_switch('show_credit_link', array('saved_value' => $options['show_credit_link'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[show_credit_link]'));
    echo '<br /><span>Show a small "form protected by" link below the login form to help others learn about the free Login Lockdown plugin &amp; protect their sites.</span>';
    echo '</td></tr>';

    echo '<tr><td></td><td>';
    LoginLockdown_admin::footer_save_button();
    echo '</td></tr>';

    echo '</tbody></table>';
  }

  static function tab_advanced()
  {
    $options = LoginLockdown_Setup::get_options();

    echo '<table class="form-table"><tbody>';

    echo '<tr valign="top">
        <th scope="row"><label for="passwords_check">Password Check</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="passwords_check" class="open-upsell pro-label">PRO</a></th>
        <td><button class="button button-primary button-large open-upsell" data-feature="passwords_check" style="margin-bottom:6px;">Test user passwords <i class="loginlockdown-icon loginlockdown-lock"></i></button>';
    echo '<br><span>Check if any user has a weak password that is vulnerable to common brute-force dictionary attacks.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="anonymous_logging">Anonymous Activity Logging</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="anonymous_logging" class="open-upsell pro-label">PRO</a></th>
        <td>';
    echo '<div class="open-upsell open-upsell-block">';
    LoginLockdown_Utility::create_toggle_switch('anonymous_logging', array('saved_value' => 0, 'option_key' => ''));
    echo '</div>';
    echo '<br /><span>Logging anonymously means IP addresses of your visitors are stored as hashed values.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="block_bots">Block Bots</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="block_bots" class="open-upsell pro-label">PRO</a></th>
        <td>';
    echo '<div class="open-upsell open-upsell-block">';
    LoginLockdown_Utility::create_toggle_switch('block_bots', array('saved_value' => 0, 'option_key' => ''));
    echo '</div>';
    echo '<br /><span>Block bots from accessing the login page and attempting to log in.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="instant_block_nonusers">Block Login Attempts With Non-existing Usernames</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="instant_block_nonusers" class="open-upsell pro-label">PRO</a></th>
        <td>';
    echo '<div class="open-upsell open-upsell-block">';
    LoginLockdown_Utility::create_toggle_switch('instant_block_nonusers', array('saved_value' => 0, 'option_key' => ''));
    echo '</div>';
    echo '<br /><span>Immediately block IP if there is a failed login attempt with a non-existing username</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="honeypot">Add Honeypot for Bots</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="honeypot" class="open-upsell pro-label">PRO</a></th>
        <td>';
    echo '<div class="open-upsell open-upsell-block">';
    LoginLockdown_Utility::create_toggle_switch('honeypot', array('saved_value' => 0, 'option_key' => ''));
    echo '</div>';
    echo '<br /><span>Add a special, hidden "honeypot" field to the login form to catch and prevent bots from attempting to log in.<br>This does not affect the way humans log in, nor does it add an extra step.</span>';
    echo '</td></tr>';

    echo '<table class="form-table"><tbody>';

    $cookie_lifetime = array();
    $cookie_lifetime[] = array('val' => '14', 'label' => '14 days (default)', 'class' => 'pro-option');
    $cookie_lifetime[] = array('val' => '30', 'label' => '30 days');
    $cookie_lifetime[] = array('val' => '90', 'label' => '3 months');
    $cookie_lifetime[] = array('val' => '180', 'label' => '6 months');
    $cookie_lifetime[] = array('val' => '365', 'label' => '1 year');

    echo '<tr valign="top">
        <th scope="row"><label for="cookie_lifetime">Cookie Lifetime</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="cookie_lifetime" class="open-upsell pro-label">PRO</a></th>
        <td>';
    echo '<div class="open-upsell open-upsell-block">';
    echo '<select id="cookie_lifetime" data-feature="cookie_lifetime" class="open-upsell">';
    LoginLockdown_Utility::create_select_options($cookie_lifetime, '14');
    echo '</select>';
    echo '</div>';
    echo '<br /><span>Cookie lifetime if "Remember Me" option is checked on login form.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="uninstall_delete">Wipe Data on Plugin Delete</label></th>
        <td>';
    LoginLockdown_Utility::create_toggle_switch('uninstall_delete', array('saved_value' => $options['uninstall_delete'], 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[uninstall_delete]'));
    echo '<br /><span>If enabled, Login Lockdown options, rules and all log tables will be deleted when the plugin is deleted.</span>';
    echo '</td></tr>';

    echo '<tr><td></td><td>';
    LoginLockdown_admin::footer_save_button();
    echo '</td></tr>';

    echo '</tbody></table>';
  }

  static function tab_tools()
  {
    $options = LoginLockdown_Setup::get_options();

    echo '<table class="form-table"><tbody>';

    echo '<tr valign="top">
        <th scope="row"><label for="password_check">Email Test</label></th>
        <td><button id="lockdown_send_email" class="button button-primary button-large" style="margin-bottom:6px;">Send test email</button>';
    echo '<br><span>Send an email to test that you can receive emails from your website.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th scope="row"><label for="lockdown_recovery_url">Recovery URL</label></th>
        <td><button id="lockdown_recovery_url_show" class="button button-primary button-large" style="margin-bottom:6px;">View Recovery URL</button>';
    echo '<br><span>In case you lock yourself out and need to whitelist your IP address, please save the recovery URL somewhere safe.<br>Do NOT share the recovery URL.</span>';
    echo '</td></tr>';

    echo '<tr valign="top">
        <th><label>Import Settings</label></th>
        <td>
        <input accept="txt" type="file" name="loginlockdown_import_file" value="">
        <button name="loginlockdown_import_file" id="submit" class="button button-primary button-large" value="">Upload</button>
        </td>
        </tr>';

    echo '<tr valign="top">
        <th><label>Export Settings</label></th>
        <td>
        <a class="button button-primary button-large" style="padding-top: 3px;" href="' . esc_url(add_query_arg(array('action' => 'loginlockdown_export_settings'), admin_url('admin.php'))) . '">Download Export File</a>
        </td>
        </tr>';

    echo '</tbody></table>';
  }
} // class LoginLockdown_Tab_Login_Form
