<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_Admin extends LoginLockdown
{

  /**
   * Enqueue Admin Scripts

   * @return null
   */
  static function admin_enqueue_scripts($hook)
  {
    if ('settings_page_loginlockdown' == $hook) {
      wp_enqueue_style('loginlockdown-admin', LOGINLOCKDOWN_PLUGIN_URL . 'css/loginlockdown.css', array(), self::$version);
      wp_enqueue_style('loginlockdown-dataTables', LOGINLOCKDOWN_PLUGIN_URL . 'css/jquery.dataTables.min.css', array(), self::$version);
      wp_enqueue_style('loginlockdown-sweetalert', LOGINLOCKDOWN_PLUGIN_URL . 'css/sweetalert2.min.css', array(), self::$version);
      wp_enqueue_style('loginlockdown-tooltipster', LOGINLOCKDOWN_PLUGIN_URL . 'css/tooltipster.bundle.min.css', array(), self::$version);
      wp_enqueue_style('wp-jquery-ui-dialog');

      wp_enqueue_script('jquery-ui-tabs');
      wp_enqueue_script('jquery-ui-core');
      wp_enqueue_script('jquery-ui-position');
      wp_enqueue_script("jquery-effects-core");
      wp_enqueue_script("jquery-effects-blind");
      wp_enqueue_script('jquery-ui-accordion');
      wp_enqueue_script('jquery-ui-dialog');

      wp_enqueue_script('loginlockdown-tooltipster', LOGINLOCKDOWN_PLUGIN_URL . 'js/tooltipster.bundle.min.js', array('jquery'), self::$version, true);
      wp_enqueue_script('loginlockdown-dataTables', LOGINLOCKDOWN_PLUGIN_URL . 'js/jquery.dataTables.min.js', array(), self::$version, true);
      wp_enqueue_script('loginlockdown-chart', LOGINLOCKDOWN_PLUGIN_URL . 'js/chart.min.js', array(), self::$version, true);
      wp_enqueue_script('loginlockdown-moment', LOGINLOCKDOWN_PLUGIN_URL . 'js/moment.min.js', array(), self::$version, true);
      wp_enqueue_script('loginlockdown-sweetalert', LOGINLOCKDOWN_PLUGIN_URL . 'js/sweetalert2.min.js', array(), self::$version, true);

      $js_localize = array(
        'undocumented_error' => __('An undocumented error has occurred. Please refresh the page and try again.', 'login-lockdown'),
        'documented_error' => __('An error has occurred.', 'login-lockdown'),
        'plugin_name' => __('Login Lockdown', 'login-lockdown'),
        'plugin_url' => LOGINLOCKDOWN_PLUGIN_URL,
        'icon_url' => LOGINLOCKDOWN_PLUGIN_URL . 'images/loginlockdown-loader.gif',
        'captcha_url' => LOGINLOCKDOWN_PLUGIN_URL . 'libs/captcha.php?loginlockdown-generate-image=true&noise=1&rnd=' . esc_attr(rand(0, 10000)),
        'settings_url' => admin_url('options-general.php?page=loginlockdown'),
        'version' => self::$version,
        'site' => get_home_url(),
        'nonce_lc_check' => wp_create_nonce('loginlockdown_save_lc'),
        'cancel_button' => __('Cancel', 'login-lockdown'),
        'ok_button' => __('OK', 'login-lockdown'),
        'run_tool_nonce' => wp_create_nonce('loginlockdown_run_tool'),
        'stats_unavailable' => 'Stats will be available once enough data is collected.',
        'stats_locks' => LoginLockdown_Stats::get_stats('locks'),
        'stats_fails' => LoginLockdown_Stats::get_stats('fails'),
        'wp301_install_url' => add_query_arg(array('action' => 'loginlockdown_install_wp301', '_wpnonce' => wp_create_nonce('install_wp301'), 'rnd' => rand()), admin_url('admin.php')),
      );

      $js_localize['chart_colors'] = array('#29b99a', '#ff5429', '#ff7d5c', '#ffac97');

      wp_enqueue_script('loginlockdown-admin', LOGINLOCKDOWN_PLUGIN_URL . 'js/loginlockdown.js', array('jquery'), self::$version, true);
      wp_localize_script('loginlockdown-admin', 'loginlockdown_vars', $js_localize);
    }

    $pointers = get_option(LOGINLOCKDOWN_POINTERS_KEY);
    if ($pointers && 'settings_page_loginlockdown' != $hook) {
      $pointers['run_tool_nonce'] = wp_create_nonce('loginlockdown_run_tool');
      wp_enqueue_script('wp-pointer');
      wp_enqueue_style('wp-pointer');
      wp_localize_script('wp-pointer', 'loginlockdown_pointers', $pointers);

      wp_enqueue_script('loginlockdown-admin-global', LOGINLOCKDOWN_PLUGIN_URL . 'js/loginlockdown-global.js', array('jquery'), self::$version, true);
    }
  } // admin_enqueue_scripts

  /**
   * Admin menu entry

   * @return null
   */
  static function admin_menu()
  {

    add_options_page(
      __('Login Lockdown', 'login-lockdown'),
      'Login Lockdown',
      'manage_options',
      'loginlockdown',
      array(__CLASS__, 'main_page')
    );
  } // admin_menu

  // add widget to dashboard
  static function add_widget()
  {
    if (current_user_can('manage_options')) {
      add_meta_box('loginlockdown_dashboard_widget', 'Login Lockdown', array(__CLASS__, 'widget_content'), 'dashboard', 'side', 'high');
    }
  } // add_widget


  // render widget
  static function widget_content()
  {
    $stats = array();
    $stats['locks'] =   LoginLockdown_Stats::get_stats('locks', 360);
    $stats['locks24'] = LoginLockdown_Stats::get_stats('locks', 1);
    $stats['fails'] =   LoginLockdown_Stats::get_stats('fails', 360);
    $stats['fails24'] = LoginLockdown_Stats::get_stats('fails', 1);
    echo '<style>
        .loginlockdown-widget-boxes {
            overflow:auto;
            width:100%;
            margin: 0;
        }
        #loginlockdown_dashboard_widget p {
          padding: 0 12px 12px 12px;
        }
        #loginlockdown_dashboard_widget .inside {
          margin: 0;
          padding: 0;
        }
        .loginlockdown-widget-box {
            width:50%;
            float:left;
            width: calc(50%);
            padding:8px 8px 11px 8px;
            margin: 0;
            background-color: #29b99a22;
            text-align: center;
            box-sizing: border-box;
        }
        .loginlockdown-widget-box .loginlockdown-widget-counter {
            font-size:36px;
            font-weight:bold;
            width:100%;
            text-align: center;
            display:block;
            color:#29b99a;
        }
        </style>';
    echo '<ul class="loginlockdown-widget-boxes">';
    echo '<li class="loginlockdown-widget-box" style="border-right:1px solid #c3c4c7;border-bottom:1px solid #c3c4c7;"><span class="loginlockdown-widget-counter"> ' . esc_attr($stats['fails24']['total']) . '</span> Failed logins in last 24h</li>';
    echo '<li class="loginlockdown-widget-box" style="border-bottom:1px solid #c3c4c7;"><span class="loginlockdown-widget-counter"> ' . esc_attr($stats['locks24']['total']) . '</span> Lockdowns in last 24h</li>';
    echo '<li class="loginlockdown-widget-box" style="border-right:1px solid #c3c4c7; border-bottom: 1px solid #c3c4c7;"><span class="loginlockdown-widget-counter"> ' . esc_attr($stats['fails']['total']) . '</span> Failed logins since plugin installed</li>';
    echo '<li class="loginlockdown-widget-box" style="border-bottom: 1px solid #c3c4c7;"><span class="loginlockdown-widget-counter"> ' . esc_attr($stats['locks']['total']) . '</span> Lockdowns since plugin installed</li>';
    echo '</ul>';
    echo '<p>View the entire <a href="' . esc_url(admin_url('options-general.php?page=loginlockdown#loginlockdown_activity')) . '"> activity log</a> in the Login Lockdown plugin or change the <a href="' . esc_url(admin_url('options-general.php?page=loginlockdown#loginlockdown_login_form')) . '">login form protection settings.</a></p>';
  } // widget_content

  /**
   * Add settings link to plugins page

   * @return null
   */
  static function plugin_action_links($links)
  {
    $plugin_name = __('Login Lockdown Settings', 'login-lockdown');

    $settings_link = '<a href="' . admin_url('options-general.php?page=loginlockdown') . '" title="' . $plugin_name . '">' . __('Settings', 'login-lockdown') . '</a>';
    $buy_link = '<a href="' . admin_url('options-general.php?page=loginlockdown#open-pro-dialog') . '" title="' . $plugin_name . '"><b>' . __('Get EXTRA login protection', 'login-lockdown') . '</b></a>';

    array_unshift($links, $settings_link);
    array_unshift($links, $buy_link);

    return $links;
  } // plugin_action_links

  /**
   * Add links to plugin's description in plugins table

   * @return null
   */
  static function plugin_meta_links($links, $file)
  {
    if ($file !== 'login-lockdown/loginlockdown.php') {
      return $links;
    }

    $support_link = '<a href="https://wordpress.org/support/plugin/login-lockdown/#new-post" title="' . __('Get help', 'login-lockdown') . '">' . __('Support', 'login-lockdown') . '</a>';
    $links[] = $support_link;

    return $links;
  } // plugin_meta_links

  /**
   * Admin footer text

   * @return null
   */
  static function admin_footer_text($text)
  {
    if (!self::is_plugin_page()) {
      return $text;
    }

    $text = '<i class="loginlockdown-footer">Login Lockdown v' . self::$version . ' <a href="https://wploginlockdown.com" title="Visit Login Lockdown page for more info" target="_blank">WebFactory Ltd</a>. Please <a target="_blank" href="https://wordpress.org/support/plugin/login-lockdown/reviews/#new-post" title="Rate the plugin">rate the plugin <span>★★★★★</span></a> to help us spread the word. Thank you 🙌 from the WebFactory team!</i>';

    echo '<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>';

    return $text;
  } // admin_footer_text


  /**
   * Test if we're on plugin's page

   * @return null
   */
  static function is_plugin_page()
  {
    $current_screen = get_current_screen();

    if ($current_screen->id == 'settings_page_loginlockdown') {
      return true;
    } else {
      return false;
    }
  } // is_plugin_page

  /**
   * Settings Page HTML

   * @return null
   */
  static function main_page()
  {
    if (!current_user_can('manage_options')) {
      wp_die('You do not have sufficient permissions to access this page.');
    }

    $options = LoginLockdown_Setup::get_options();

    // auto remove welcome pointer when options are opened
    $pointers = get_option(LOGINLOCKDOWN_POINTERS_KEY);
    if (isset($pointers['welcome'])) {
      unset($pointers['welcome']);
      update_option(LOGINLOCKDOWN_POINTERS_KEY, $pointers);
    }

    echo '<div class="wrap">
        <div class="loginlockdown-header">
            <div class="loginlockdown-logo">
            <img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL) . '/images/loginlockdown-logo.png" alt="Login Lockdown" height="60" title="Login Lockdown">
        </div>';

    $stats = array();
    $stats['locks24'] = LoginLockdown_Stats::get_stats('locks', 1);
    $stats['locks'] =   LoginLockdown_Stats::get_stats('locks', 360);
    $stats['fails'] =   LoginLockdown_Stats::get_stats('fails', 1);

    echo '<div class="loginlockdown-header-stat">';
    echo '<div class="stat-title">Failed logins<span>in last 24h</span></div>';
    echo '<div class="stat-value" ' . ($stats['fails']['total'] == 0 ? 'style="color:#cfd4da"' : '') . '>' . esc_attr($stats['fails']['total']) . '</div>';
    echo '</div>';

    echo '<div class="loginlockdown-header-stat">';
    echo '<div class="stat-title">Lockdowns<span>since plugin installed</span></div>';
    echo '<div class="stat-value" ' . ($stats['locks']['total'] == 0 ? 'style="color:#cfd4da"' : '') . '>' . esc_attr($stats['locks']['total']) . '</div>';
    echo '</div>';

    echo '<div class="loginlockdown-header-stat loginlockdown-header-stat-last">';
    echo '<div class="stat-title">Lockdowns<span>in last 24h</span></div>';
    echo '<div class="stat-value" ' . ($stats['locks24']['total'] == 0 ? 'style="color:#cfd4da"' : '') . '>' . esc_attr($stats['locks24']['total']) . '</div>';
    echo '</div>';


    echo '</div>';

    echo '<h1></h1>';

    echo '<form method="post" action="options.php" enctype="multipart/form-data" id="loginlockdown_form">';
    settings_fields(LOGINLOCKDOWN_OPTIONS_KEY);

    $tabs = array();

    $tabs[] = array('id' => 'loginlockdown_login_form', 'icon' => 'loginlockdown-icon loginlockdown-enter', 'class' => '', 'label' => __('Login Protection', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Login_Form', 'display'));
    $tabs[] = array('id' => 'loginlockdown_activity', 'icon' => 'loginlockdown-icon loginlockdown-log', 'class' => '', 'label' => __('Activity', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Activity', 'display'));
    $tabs[] = array('id' => 'loginlockdown_geoip', 'icon' => 'loginlockdown-icon loginlockdown-globe', 'class' => '', 'label' => __('Country Blocking', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_GeoIP', 'display'));
    $tabs[] = array('id' => 'loginlockdown_2FA', 'icon' => 'loginlockdown-icon loginlockdown-insert-template', 'class' => '', 'label' => __('2FA', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_2FA', 'display'));
    $tabs[] = array('id' => 'loginlockdown_captcha', 'icon' => 'loginlockdown-icon loginlockdown-make-group', 'class' => '', 'label' => __('Captcha', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Captcha', 'display'));
    $tabs[] = array('id' => 'loginlockdown_cloud_protection', 'icon' => 'loginlockdown-icon loginlockdown-cloud-check', 'class' => '', 'label' => __('Cloud Protection', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Cloud_Protection', 'display'));
    $tabs[] = array('id' => 'loginlockdown_temp_access', 'icon' => 'loginlockdown-icon loginlockdown-hour-glass', 'class' => '', 'label' => __('Temp Access', 'login-lockdown'), 'callback' => array('LoginLockdown_Tab_Temporary_Access', 'display'));
    $tabs[] = array('id' => 'loginlockdown_pro', 'class' => 'open-upsell nav-tab-pro', 'icon' => '<span class="dashicons dashicons-star-filled"></span>', 'label' => __('PRO', 'login-lockdown'), 'callback' => '');

    $tabs = apply_filters('loginlockdown_tabs', $tabs);
    echo '<div id="loginlockdown_tabs_wrapper" class="ui-tabs">';

    echo '<div id="loginlockdown_tabs" class="ui-tabs" style="display: none;">';
    echo '<ul class="loginlockdown-main-tab">';
    foreach ($tabs as $tab) {
      echo '<li><a href="#' . esc_attr($tab['id']) . '" class="' . esc_attr($tab['class']) . '">';
      if (strpos($tab['icon'], 'dashicon')) {
        LoginLockdown_Utility::wp_kses_wf($tab['icon']);
      } else {
        echo '<span class="icon"><i class="' . esc_attr($tab['icon']) . '"></i></span>';
      }
      echo '<span class="label">' . esc_attr($tab['label']) . '</span></a></li>';
    }
    echo '</ul>';

    foreach ($tabs as $tab) {
      if (is_callable($tab['callback'])) {
        echo '<div style="display: none;" id="' . esc_attr($tab['id']) . '">';
        call_user_func($tab['callback']);
        echo '</div>';
      }
    } // foreach

    echo '</div>'; // loginlockdown_tabs
    echo '</div>';

    echo '<div id="loginlockdown_tabs_sidebar" style="display:none;">';
    echo '<div class="sidebar-box pro-ad-box">
            <p class="text-center"><a href="#" data-pro-feature="sidebar-box-logo" class="open-pro-dialog">
            <img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL . '/images/loginlockdown-logo.png') . '" alt="Login Lockdown PRO" title="Login Lockdown PRO"></a><br>PRO version is here! Grab the launch discount.<br><b>All prices are LIFETIME!</b></p>
            <ul class="plain-list">
                <li>Firewall</li>
                <li>Login Page Customization</li>
                <li>GDPR Compatible Captcha</li>
                <li>Email Based 2FA</li>
                <li>Country Blocking</li>
                <li>Temporary Access Links</li>
                <li>Cloud Blacklists</li>
                <li>Licenses &amp; Sites Manager (remote SaaS dashboard)</li>
                <li>White-label Mode</li>
                <li>Complete Codeless Plugin Rebranding</li>
                <li>Email support from plugin developers</li>
            </ul>

            <p class="text-center"><a href="#" class="open-pro-dialog button button-buy" data-pro-feature="sidebar-box">Get PRO Now</a></p>
            </div>';

    if (!defined('EPS_REDIRECT_VERSION') && !defined('WF301_PLUGIN_FILE')) {
      echo '<div class="sidebar-box pro-ad-box box-301">
            <h3 class="textcenter"><b>Problems with redirects?<br>Moving content around or changing posts\' URL?<br>Old URLs giving you problems?<br><br><u>Improve your SEO &amp; manage all redirects in one place!</u></b></h3>

            <p class="text-center"><a href="#" class="install-wp301">
            <img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL . '/images/wp-301-logo.png') . '" alt="WP 301 Redirects" title="WP 301 Redirects"></a></p>

            <p class="text-center"><a href="#" class="button button-buy install-wp301">Install and activate the <u>free</u> WP 301 Redirects plugin</a></p>

            <p><a href="https://wordpress.org/plugins/eps-301-redirects/" target="_blank">WP 301 Redirects</a> is a free WP plugin maintained by the same team as this Login Lockdown plugin. It has <b>+250,000 users, 5-star rating</b>, and is hosted on the official WP repository.</p>
            </div>';
    }

    echo '<div class="sidebar-box" style="margin-top: 35px;">
            <p>Please <a href="https://wordpress.org/support/plugin/login-lockdown/reviews/#new-post" target="_blank">rate the plugin ★★★★★</a> to <b>keep it up-to-date &amp; maintained</b>. It only takes a second to rate. Thank you! 👋</p>
            </div>';
    echo '</div>';
    echo '</form>';

    echo ' <div id="loginlockdown-pro-dialog" style="display: none;" title="Login Lockdown PRO is here!"><span class="ui-helper-hidden-accessible"><input type="text"/></span>

        <div class="center logo"><a href="https://wploginlockdown.com/?ref=loginlockdown-free-pricing-table" target="_blank"><img src="' . esc_url(LOGINLOCKDOWN_PLUGIN_URL . '/images/loginlockdown-logo.png') . '" alt="Login Lockdown PRO" title="Login Lockdown PRO"></a><br>

        <span>Limited PRO Launch Discount - <b>all prices are LIFETIME</b>! Pay once &amp; use forever!</span>
        </div>

        <table id="loginlockdown-pro-table">
        <tr>
        <td class="center">Lifetime Personal License</td>
        <td class="center">Lifetime Team License</td>
        <td class="center">Lifetime Agency License</td>
        </tr>

        <tr class="prices">
        <td class="center"><del>$89 /year</del><br><span>$89</span> <b>/lifetime</b></td>
        <td class="center"><del>$119 /year</del><br><span>$99</span> <b>/lifetime</b></td>
        <td class="center"><del>$299 /year</del><br><span>$179</span> <b>/lifetime</b></td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span><b>1 Site License</b>  ($89 per site)</td>
        <td><span class="dashicons dashicons-yes"></span><b>5 Sites License</b>  ($19 per site)</td>
        <td><span class="dashicons dashicons-yes"></span><b>100 Sites License</b>  ($1.8 per site)</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>
        <td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>
        <td><span class="dashicons dashicons-yes"></span>All Plugin Features</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>
        <td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>
        <td><span class="dashicons dashicons-yes"></span>Lifetime Updates &amp; Support</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Firewall</td>
        <td><span class="dashicons dashicons-yes"></span>Firewall</td>
        <td><span class="dashicons dashicons-yes"></span>Firewall</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Login Page Customization</td>
        <td><span class="dashicons dashicons-yes"></span>Login Page Customization</td>
        <td><span class="dashicons dashicons-yes"></span>Login Page Customization</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Temporary Access Links</td>
        <td><span class="dashicons dashicons-yes"></span>Temporary Access Links</td>
        <td><span class="dashicons dashicons-yes"></span>Temporary Access Links</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Country Blocking</td>
        <td><span class="dashicons dashicons-yes"></span>Country Blocking</td>
        <td><span class="dashicons dashicons-yes"></span>Country Blocking</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Cloud Blacklists</td>
        <td><span class="dashicons dashicons-yes"></span>Cloud Blacklists</td>
        <td><span class="dashicons dashicons-yes"></span>Cloud Blacklists</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-yes"></span>Dashboard</td>
        <td><span class="dashicons dashicons-yes"></span>Dashboard</td>
        <td><span class="dashicons dashicons-yes"></span>Dashboard</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-no"></span>White-label Mode</td>
        <td><span class="dashicons dashicons-yes"></span>White-label Mode</td>
        <td><span class="dashicons dashicons-yes"></span>White-label Mode</td>
        </tr>

        <tr>
        <td><span class="dashicons dashicons-no"></span>Full Plugin Rebranding</td>
        <td><span class="dashicons dashicons-no"></span>Full Plugin Rebranding</td>
        <td><span class="dashicons dashicons-yes"></span>Full Plugin Rebranding</td>
        </tr>

        <tr>
        <td><a class="button button-buy" data-href-org="https://wploginlockdown.com/buy/?product=personal-launch&ref=pricing-table" href="https://wploginlockdown.com/buy/?product=personal-launch&ref=pricing-table" target="_blank">Lifetime License<br>$89 -&gt; BUY NOW</a>
        <br>or <a class="button-buy" data-href-org="https://wploginlockdown.com/buy/?product=personal-monthly&ref=pricing-table" href="https://wploginlockdown.com/buy/?product=personal-monthly&ref=pricing-table" target="_blank">only $7.99 <small>/month</small></a></td>
        <td><a class="button button-buy" data-href-org="https://wploginlockdown.com/buy/?product=team-launch&ref=pricing-table" href="https://wploginlockdown.com/buy/?product=team-launch&ref=pricing-table" target="_blank">Lifetime License<br>$99 -&gt; BUY NOW</a></td>
        <td><a class="button button-buy" data-href-org="https://wploginlockdown.com/buy/?product=agency-launch&ref=pricing-table" href="https://wploginlockdown.com/buy/?product=agency-launch&ref=pricing-table" target="_blank">Lifetime License<br>$179 -&gt; BUY NOW</a></td>
        </tr>

        </table>

        <div class="center footer"><b>100% No-Risk Money Back Guarantee!</b> If you don\'t like the plugin over the next 7 days, we will happily refund 100% of your money. No questions asked! Payments are processed by our merchant of records - <a href="https://paddle.com/" target="_blank">Paddle</a>.</div>
      </div>';

    echo '</div>'; // wrap
  } // options_page

  /**
   * Reset pointers

   * @return null
   */
  static function reset_pointers()
  {
    $pointers = array();
    $pointers['welcome'] = array('target' => '#menu-settings', 'edge' => 'left', 'align' => 'right', 'content' => 'Thank you for installing the <b style="font-weight: 800;">Login Lockdown</b> plugin! Please open <a href="' . admin_url('options-general.php?page=loginlockdown') . '">Settings - Login Lockdown</a> to enhance your site security.');

    update_option(LOGINLOCKDOWN_POINTERS_KEY, $pointers);
  } // reset_pointers

  /**
   * Settings footer submit button HTML

   * @return null
   */
  static function footer_save_button()
  {
    echo '<p class="submit">';
    echo '<button class="button button-primary button-large">' . __('Save Changes', 'login-lockdown') . ' <i class="loginlockdown-icon loginlockdown-checkmark"></i></button>';
    echo '</p>';
  } // footer_save_button

} // class
