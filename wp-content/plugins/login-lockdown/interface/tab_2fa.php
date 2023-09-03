<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_2FA extends LoginLockdown
{
  static function display()
  {
    echo '<div class="tab-content">';

    echo '<div class="notice-box-info">
        The 2FA Features allows you to add an extra level of security to your website, requiring users logging in for the first time from a device to confirm their login by clicking a link that is emailed to them. Even if someone steals the username &amp; password they still won\'t be able to login without access to the account email. <a href="#" class="open-pro-dialog" data-pro-feature="2fa">Get PRO now</a> to use the 2FA feature.
        </div>';

    echo '<table class="form-table"><tbody>';

    echo '<tr valign="top">
        <th scope="row"><label for="2fa_email">Email Based<br>Two Factor Authentication</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="2fa_email" class="open-upsell pro-label">PRO</a></th>
        <td>';
    echo '<div class="open-upsell open-upsell-block" data-feature="2fa_email">';
    LoginLockdown_Utility::create_toggle_switch('2fa_email', array('saved_value' => 0, 'option_key' => ''));
    echo '</div>';
    echo '<br /><span>After the correct username &amp; password are entered the user will receive an email with a one-time link to confirm the login.<br>In case somebody steals the username &amp; password they still won\'t be able to login without access to the account email.</span>';
    echo '</td></tr>';

    echo '<tr><td></td><td>';
    echo '<p class="submit"><a class="button button-primary button-large open-upsell" data-feature="2fa-save">Save Changes <i class="loginlockdown-icon loginlockdown-checkmark"></i></a></p>';
    echo '</td></tr>';

    echo '</tbody></table>';

    echo '</div>';
  } // display
} // class LoginLockdown_Tab_2FA
