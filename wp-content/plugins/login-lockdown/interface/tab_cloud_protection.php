<?php
/**
 * Login Lockdown Pro
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Cloud_Protection extends LoginLockdown
{
    static function display()
    {
        echo '<div class="tab-content">';

        echo '<div class="notice-box-info">
        Cloud Protection allows you to manage IP Whitelists and Blacklists in your Login Lockdown Dashboard and apply them to all your websites. <a href="#" class="open-pro-dialog" data-pro-feature="cloud-protection">Get PRO now</a> to use the Cloud Protection feature.
        </div>';

        echo '<table class="form-table"><tbody>';

        echo '<tr valign="top">
        <th scope="row"><label for="cloud_use_account_lists">Use Account<br>IP Whitelist &amp; Blacklist</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="cloud_use_account_lists" class="open-upsell pro-label">PRO</a></th>
        <td>';
        echo '<div class="open-upsell open-upsell-block" data-feature="cloud_use_account_lists">';
        LoginLockdown_Utility::create_toggle_switch('cloud_use_account_lists', array('saved_value' => 0, 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[cloud_use_account_lists]'));
        echo '</div>';
        echo '<br /><span>These lists are private and available only to your sites.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="cloud_use_blacklist">Use Global Cloud IP Blacklist</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="cloud_use_blacklist" class="open-upsell pro-label">PRO</a></th>
        <td>';

        echo '<div class="open-upsell open-upsell-block" data-feature="cloud_use_blacklist">';
        LoginLockdown_Utility::create_toggle_switch('cloud_use_blacklist', array('saved_value' => 0, 'option_key' => LOGINLOCKDOWN_OPTIONS_KEY . '[cloud_use_blacklist]'));
        echo '</div>';
        echo '<br /><span>A list of bad IPs maintained daily by WebFactory, and based on realtime malicios activity observed on thousands of websites. IPs found on this list are trully bad and should not have access to your site.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="cloud_global_block_global">Cloud Block Type</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="cloud_block" class="open-upsell pro-label">PRO</a></th>
        <td>';
        echo '<div class="open-upsell open-upsell-block" data-feature="cloud_block">';
        echo '<label class="loginlockdown-radio-option">';
        echo '<span class="radio-container"><input type="radio" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[cloud_global_block]" id="cloud_global_block_global" value="1" checked><span class="radio"></span></span> Completely block website access';
        echo '</label>';

        echo '<label class="loginlockdown-radio-option">';
        echo '<span class="radio-container"><input type="radio" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[cloud_global_block]" id="cloud_global_block_login" value="0"><span class="radio"></span></span> Only block access to the login page';
        echo '</label>';
        echo '</div>';
        echo '<span>Completely block website access for IPs on cloud blacklist, or just blocking access to the login page.</span>';
        echo '</td></tr>';

        echo '<tr valign="top">
        <th scope="row"><label for="block_message_cloud">Block Message</label><a title="This feature is available in the PRO version. Click for details." href="#" data-feature="block_message_cloud" class="open-upsell pro-label">PRO</a></th>
        <td>';
        echo '<div class="open-upsell open-upsell-block" data-feature="block_message_cloud">';
        echo '<input type="text" disabled class="regular-text" id="block_message_cloud" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[block_message_cloud]" value="" placeholder="We\'re sorry, but access from your IP is not allowed." />';
        echo '</div>';
        echo '<br /><span>Message displayed to visitors blocked based on cloud lists. Default: <i>We\'re sorry, but access from your IP is not allowed.</i></span>';
        echo '</td></tr>';

        echo '<tr><td></td><td>';
        echo '<p class="submit"><a class="button button-primary button-large open-upsell" data-feature="cloud-protection-save">Save Changes <i class="loginlockdown-icon loginlockdown-checkmark"></i></a></p>';
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '</div>';
    } // display
} // class LoginLockdown_Tab_Cloud_Protection
