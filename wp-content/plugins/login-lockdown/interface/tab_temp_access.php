<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Temporary_Access extends LoginLockdown
{
  static function display()
  {
    echo '<div class="tab-content">';

    echo '<div class="notice-box-info">
        Temporary Access links are a convenient way to give temporary access to other people. You can set the lifetime of the link and the maximum number of times it can be used to prevent abuse. <a href="#" class="open-pro-dialog" data-pro-feature="temp-access">Get PRO now</a> to use the Temporary Links feature.
        </div>';

    echo '<img style="width: 100%;" src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/temporary-access.png" alt="Login Lockdown" title="Login Lockdown Temporary Access Links" />';
    echo '</div>';
  } // display
} // class LoginLockdown_Tab_2FA
