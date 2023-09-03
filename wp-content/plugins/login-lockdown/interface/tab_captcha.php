<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Tab_Captcha extends LoginLockdown
{
  static function display()
  {
    $options = LoginLockdown_Setup::get_options();

    echo '<div class="tab-content">';

    echo '<table class="form-table"><tbody>';

    $captcha = array();
    $captcha[] = array('val' => 'disabled', 'label' => 'Disabled');
    $captcha[] = array('val' => 'builtin', 'label' => 'Built-in Captcha');
    $captcha[] = array('val' => 'recaptchav2', 'label' => 'reCAPTCHA v2 - PRO option', 'class' => 'pro-option');
    $captcha[] = array('val' => 'recaptchav3', 'label' => 'reCAPTCHA v3 - PRO option', 'class' => 'pro-option');
    $captcha[] = array('val' => 'hcaptcha', 'label' => 'hCaptcha - PRO option', 'class' => 'pro-option');

    echo '<tr valign="top">
        <th scope="row"><label for="captcha">Captcha</label></th>
        <td><select id="captcha" name="' . esc_attr(LOGINLOCKDOWN_OPTIONS_KEY) . '[captcha]">';
    LoginLockdown_Utility::create_select_options($captcha, $options['captcha']);
    echo '</select>';
    echo '<br /><span>Captcha or "are you human" verification ensures bots can\'t attack your login page and provides additional protection with minimal impact to users.</span>';
    echo '</td></tr>';

    echo '<tr class="captcha_verify_wrapper" style="display:none;" valign="top">
        <th scope="row"></th>
        <td><button id="verify-captcha" class="button button-primary button-large button-yellow">Verify Captcha <i class="loginlockdown-icon loginlockdown-make-group"></i></button>';
    echo '<input type="hidden" class="regular-text" id="captcha_verified" name="' . LOGINLOCKDOWN_OPTIONS_KEY . '[captcha_verified]" value="0">';
    echo '<br /><span style="display: inline-block; padding-top: 6px;">Click the Verify Captcha button to verify that the captcha works properly. Otherwise captcha settings will not be saved.</span>';
    echo '</td></tr>';

    echo '<tr><td></td><td>';
    LoginLockdown_admin::footer_save_button();
    echo '</td></tr>';

    echo '<tr><td colspan="2">';
    echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'disabled' ? 'captcha-selected' : '') . '" data-captcha="disabled">';
    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/captcha_disabled.png" />';
    echo '<div class="captcha-box-desc">';
    echo '<h3>Captcha Disabled</h3>';
    echo '<ul>';
    echo '<li>No Additional Security</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'builtin' ? 'captcha-selected' : '') . '" data-captcha="builtin">';
    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/captcha_builtin.png" />';
    echo '<div class="captcha-box-desc">';
    echo '<h3>Built-in Captcha</h3>';
    echo '<ul>';
    echo '<li>Medium Security</li>';
    echo '<li>No API keys</li>';
    echo '<li>GDPR Compatible</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'recaptchav2' ? 'captcha-selected' : '') . '" data-captcha="recaptchav2">';
    echo '<a title="This feature is available in the PRO version. Click for details." href="#" data-feature="recaptchav2" class="open-upsell pro-label">PRO</a>';
    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/captcha_recaptcha_v2.png" />';
    echo '<div class="captcha-box-desc">';
    echo '<h3>reCaptcha v2</h3>';
    echo '<ul>';
    echo '<li>High Security</li>';
    echo '<li>Requires <a href="https://www.google.com/recaptcha/about/" target="_blank">API Keys</a></li>';
    echo '<li>Not GDPR Compatible</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'recaptchav3' ? 'captcha-selected' : '') . '" data-captcha="recaptchav3">';
    echo '<a title="This feature is available in the PRO version. Click for details." href="#" data-feature="recaptchav3" class="open-upsell pro-label">PRO</a>';
    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/captcha_recaptcha_v3.png" />';
    echo '<div class="captcha-box-desc">';
    echo '<h3>reCaptcha v3</h3>';
    echo '<ul>';
    echo '<li>High Security</li>';
    echo '<li>Requires <a href="https://www.google.com/recaptcha/about/" target="_blank">API Keys</a></li>';
    echo '<li>Not GDPR Compatible</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';

    echo '<div class="captcha-box-wrapper ' . ($options['captcha'] == 'hcaptcha' ? 'captcha-selected' : '') . '" data-captcha="hcaptcha">';
    echo '<a title="This feature is available in the PRO version. Click for details." href="#" data-feature="hcaptcha" class="open-upsell pro-label">PRO</a>';
    echo '<img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/captcha_hcaptcha.png" />';
    echo '<div class="captcha-box-desc">';
    echo '<h3>hCaptcha</h3>';
    echo '<ul>';
    echo '<li>High Security</li>';
    echo '<li>Requires <a href="https://www.hcaptcha.com/signup-interstitial" target="_blank">API Keys</a></li>';
    echo '<li>GDPR Compatible</li>';
    echo '<li>Best Choice</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
    echo '</td></tr>';

    echo '</tbody></table>';

    echo '</div>';
  } // display
} // class LoginLockdown_Tab_2FA
