<?php

/**
 * Login Lockdown
 * https://wploginlockdown.com/
 * (c) WebFactory Ltd, 2022 - 2023, www.webfactoryltd.com
 */

class LoginLockdown_AJAX extends LoginLockdown
{

  /**
   * Run one tool via AJAX call
   *
   * @return null
   */
  static function ajax_run_tool()
  {
    global $wpdb, $current_user;

    check_ajax_referer('loginlockdown_run_tool');
    set_time_limit(300);

    $tool = sanitize_text_field(trim(@$_REQUEST['tool']));

    $options = LoginLockdown_Setup::get_options();

    $update['last_options_edit'] = current_time('mysql', true);
    update_option(LOGINLOCKDOWN_OPTIONS_KEY, array_merge($options, $update));

    if ($tool == 'activity_logs') {
      self::get_activity_logs();
    } else if ($tool == 'locks_logs') {
      self::get_locks_logs();
    } else if ($tool == 'recovery_url') {
      if (sanitize_text_field($_POST['reset']) == 'true') {
        sleep(1);
        $options['global_unblock_key'] = 'll' . md5(time() . rand(10000, 9999));
        update_option(LOGINLOCKDOWN_OPTIONS_KEY, array_merge($options, $update));
      }
      wp_send_json_success(array('url' => '<a href="' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '">' . site_url('/?loginlockdown_unblock=' . $options['global_unblock_key']) . '</a>'));
    } else if ($tool == 'empty_log') {
      self::empty_log(sanitize_text_field($_POST['log']));
      wp_send_json_success();
    } else if ($tool == 'unlock_lockdown') {
      $lockdown_ip = $wpdb->get_var($wpdb->prepare("SELECT lockdown_IP FROM " . $wpdb->lockdown_lockdowns . " WHERE lockdown_ID = %d", array(intval($_POST['lock_id']))));
      $wpdb->delete(
        $wpdb->lockdown_login_fails,
        array(
          'login_attempt_IP' => $lockdown_ip
        )
      );
      $wpdb->update(
        $wpdb->lockdown_lockdowns,
        array(
          'unlocked' => 1
        ),
        array(
          'lockdown_ID' => intval($_POST['lock_id'])
        )
      );
      wp_send_json_success(array('id' => intval($_POST['lock_id'])));
    } else if ($tool == 'delete_lock_log') {
      $wpdb->delete(
        $wpdb->lockdown_lockdowns,
        array(
          'lockdown_ID' => intval($_POST['lock_id'])
        )
      );
      wp_send_json_success(array('id' => intval($_POST['lock_id'])));
    } else if ($tool == 'delete_fail_log') {
      $wpdb->delete(
        $wpdb->lockdown_login_fails,
        array(
          'login_attempt_ID' => intval($_POST['fail_id'])
        )
      );
      wp_send_json_success(array('id' => intval($_POST['fail_id'])));
    } else if ($tool == 'loginlockdown_dismiss_pointer') {
      delete_option(LOGINLOCKDOWN_POINTERS_KEY);
      wp_send_json_success();
    } else if ($tool == 'verify_captcha') {
        if (isset($_POST['captcha_response']) && $_POST['captcha_response'] === $_COOKIE['loginlockdown_captcha']) {
            wp_send_json_success();
        } else {
            wp_send_json_error(__('Captcha verification failed.', 'login-lockdown'));
        }
    } else if ($tool == 'email_test') {
      $subject  = 'Login Lockdown test email';
      $message  = '<p>This is a test email from ' . get_bloginfo('title') . ' (' . home_url() . '). Since you have received it, emails work! 🎉</p>';

      add_filter('wp_mail_content_type', function () {
        return "text/html";
      });

      if (wp_mail($current_user->user_email, $subject, $message)) {
        wp_send_json_success(array('sent' => true, 'title' => 'Email sent successfully', 'text' => 'An email has been sent to <strong>' . $current_user->user_email . '</strong>. Please check your Inbox as well as your Spam folder. If you have not received the email, there is an issue with your email configuration on your website.'));
      } else {
        wp_send_json_success(array('sent' => false, 'title' => 'Email failed', 'text' => 'We tried to send an email to <strong>' . $current_user->user_email . '</strong> but it appears to have failed. Please check your Inbox as well as your Spam folder. If you have not received the email, there is an issue with your email configuration on your website.'));
      }
    } else {
      wp_send_json_error(__('Unknown tool.', 'login-lockdown'));
    }
    die();
  } // ajax_run_tool

  /**
   * Get rule row html
   *
   * @return string row HTML
   *
   * @param array $data with rule settings
   */
  static function get_date_time($timestamp)
  {
    $interval = current_time('timestamp') - $timestamp;
    return '<span class="loginlockdown-dt-small">' . self::humanTiming($interval, true) . '</span><br />' . date('Y/m/d', $timestamp) . '<br><span class="loginlockdown-dt-small">' . date('h:i:s A', $timestamp) . '</span>';
  }

  /**
   * Get human readable timestamp like 2 hours ago
   *
   * @return int time
   *
   * @param string timestamp
   */
  static function humanTiming($time)
  {
    $tokens = array(
      31536000 => 'year',
      2592000 => 'month',
      604800 => 'week',
      86400 => 'day',
      3600 => 'hour',
      60 => 'minute',
      1 => 'second'
    );

    if ($time < 1) {
      return 'just now';
    }
    foreach ($tokens as $unit => $text) {
      if ($time < $unit) continue;
      $numberOfUnits = floor($time / $unit);
      return $numberOfUnits . ' ' . $text . (($numberOfUnits > 1) ? 's' : '') . ' ago';
    }
  }

  static function empty_log($log)
  {
    global $wpdb;

    if ($log == 'fails') {
      $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_login_fails);
    } else {
      $wpdb->query('TRUNCATE TABLE ' . $wpdb->lockdown_lockdowns);
    }
  }

  /**
   * Fetch activity logs and output JSON for datatables
   *
   * @return null
   */
  static function get_locks_logs()
  {
    global $wpdb;

    $aColumns = array('lockdown_ID', 'unlocked', 'lockdown_date', 'release_date', 'reason', 'lockdown_IP');
    $sIndexColumn = "lockdown_ID";

    // paging
    $sLimit = '';
    if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
      $sLimit = "LIMIT " . esc_sql($_GET['iDisplayStart']) . ", " .
        esc_sql($_GET['iDisplayLength']);
    } // paging

    // ordering
    $sOrder = '';
    if (isset($_GET['iSortCol_0'])) {
      $sOrder = "ORDER BY  ";
      for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
          $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " "
            .  esc_sql($_GET['sSortDir_' . $i]) . ", ";
        }
      }

      $sOrder = substr_replace($sOrder, '', -2);
      if ($sOrder == "ORDER BY") {
        $sOrder = '';
      }
    } // ordering

    // filtering
    $sWhere = '';
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
      $sWhere = "WHERE (";
      for ($i = 0; $i < count($aColumns); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch']) . "%' OR ";
      }
      $sWhere  = substr_replace($sWhere, '', -3);
      $sWhere .= ')';
    } // filtering

    // individual column filtering
    for ($i = 0; $i < count($aColumns); $i++) {
      if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == '') {
          $sWhere = "WHERE ";
        } else {
          $sWhere .= " AND ";
        }
        $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch_' . $i]) . "%' ";
      }
    } // individual columns

    // build query
    $wpdb->sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) .
      " FROM " . $wpdb->lockdown_lockdowns . " $sWhere $sOrder $sLimit";

    $rResult = $wpdb->get_results($wpdb->sQuery);

    // data set length after filtering
    $wpdb->sQuery = "SELECT FOUND_ROWS()";
    $iFilteredTotal = $wpdb->get_var($wpdb->sQuery);

    // total data set length
    $wpdb->sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM " . $wpdb->lockdown_lockdowns;
    $iTotal = $wpdb->get_var($wpdb->sQuery);

    // construct output
    $output = array(
      "sEcho" => intval(@$_GET['sEcho']),
      "iTotalRecords" => $iTotal,
      "iTotalDisplayRecords" => $iFilteredTotal,
      "aaData" => array()
    );

    foreach ($rResult as $aRow) {
      $row = array();
      $row['DT_RowId'] = $aRow->lockdown_ID;

      if (strtotime($aRow->release_date) < time()) {
        $row['DT_RowClass'] = 'lock_expired';
      }

      for ($i = 0; $i < count($aColumns); $i++) {

        if ($aColumns[$i] == 'unlocked') {
          $unblocked = $aRow->{$aColumns[$i]};
          if ($unblocked == 0 && strtotime($aRow->release_date) > time()) {
            $row[] = '<div class="tooltip unlock_lockdown" data-lock-id="' . $aRow->lockdown_ID . '" title="Unlock"><i class="loginlockdown-icon loginlockdown-lock"></i></div>';
          } else {
            $row[] = '<div class="tooltip unlocked_lockdown" title="Unlock"><i class="loginlockdown-icon loginlockdown-unlock"></i></div>';
          }
        } else if ($aColumns[$i] == 'lockdown_date') {
          $row[] = self::get_date_time(strtotime($aRow->{$aColumns[$i]}));
        } else if ($aColumns[$i] == 'reason') {
          $row[] = $aRow->{$aColumns[$i]};
        } else if ($aColumns[$i] == 'lockdown_IP') {
          $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="locks-log-user-location">Available in PRO</a>';
          $row[] = $aRow->lockdown_IP;
          $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="locks-log-user-agent">Available in PRO</a>';
        }
      }
      $row[] = '<div data-lock-id="' . $aRow->lockdown_ID . '" class="tooltip delete_lock_entry" title="Delete Lockdown?" data-msg-success="Lockdown deleted" data-btn-confirm="Delete Lockdown" data-title="Delete Lockdown?" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this Lockdown"><i class="loginlockdown-icon loginlockdown-trash"></i></div>';
      $output['aaData'][] = $row;
    } // foreach row

    // json encoded output
    @ob_end_clean();
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    echo json_encode($output);
    die();
  }


  /**
   * Fetch activity logs and output JSON for datatables
   *
   * @return null
   */
  static function get_activity_logs()
  {
    global $wpdb;
    $options = LoginLockdown_Setup::get_options();

    $aColumns = array('login_attempt_ID', 'login_attempt_date', 'failed_user', 'login_attempt_IP', 'reason');
    $sIndexColumn = "login_attempt_ID";

    // paging
    $sLimit = '';
    if (isset($_GET['iDisplayStart']) && $_GET['iDisplayLength'] != '-1') {
      $sLimit = "LIMIT " . esc_sql($_GET['iDisplayStart']) . ", " .
        esc_sql($_GET['iDisplayLength']);
    } // paging

    // ordering
    $sOrder = '';
    if (isset($_GET['iSortCol_0'])) {
      $sOrder = "ORDER BY  ";
      for ($i = 0; $i < intval($_GET['iSortingCols']); $i++) {
        if ($_GET['bSortable_' . intval($_GET['iSortCol_' . $i])] == "true") {
          $sOrder .= $aColumns[intval($_GET['iSortCol_' . $i])] . " "
            .  esc_sql($_GET['sSortDir_' . $i]) . ", ";
        }
      }

      $sOrder = substr_replace($sOrder, '', -2);
      if ($sOrder == "ORDER BY") {
        $sOrder = '';
      }
    } // ordering

    // filtering
    $sWhere = '';
    if (isset($_GET['sSearch']) && $_GET['sSearch'] != '') {
      $sWhere = "WHERE (";
      for ($i = 0; $i < count($aColumns); $i++) {
        $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch']) . "%' OR ";
      }
      $sWhere  = substr_replace($sWhere, '', -3);
      $sWhere .= ')';
    } // filtering

    // individual column filtering
    for ($i = 0; $i < count($aColumns); $i++) {
      if (isset($_GET['bSearchable_' . $i]) && $_GET['bSearchable_' . $i] == "true" && $_GET['sSearch_' . $i] != '') {
        if ($sWhere == '') {
          $sWhere = "WHERE ";
        } else {
          $sWhere .= " AND ";
        }
        $sWhere .= $aColumns[$i] . " LIKE '%" . esc_sql($_GET['sSearch_' . $i]) . "%' ";
      }
    } // individual columns

    // build query
    $wpdb->sQuery = "SELECT SQL_CALC_FOUND_ROWS " . str_replace(" , ", " ", implode(", ", $aColumns)) .
      " FROM " . $wpdb->lockdown_login_fails . " $sWhere $sOrder $sLimit";

    $rResult = $wpdb->get_results($wpdb->sQuery);

    // data set length after filtering
    $wpdb->sQuery = "SELECT FOUND_ROWS()";
    $iFilteredTotal = $wpdb->get_var($wpdb->sQuery);

    // total data set length
    $wpdb->sQuery = "SELECT COUNT(" . $sIndexColumn . ") FROM " . $wpdb->lockdown_login_fails;
    $iTotal = $wpdb->get_var($wpdb->sQuery);

    // construct output
    $output = array(
      "sEcho" => intval(@$_GET['sEcho']),
      "iTotalRecords" => $iTotal,
      "iTotalDisplayRecords" => $iFilteredTotal,
      "aaData" => array()
    );

    foreach ($rResult as $aRow) {
      $row = array();
      $row['DT_RowId'] = $aRow->login_attempt_ID;

      for ($i = 0; $i < count($aColumns); $i++) {
        if ($aColumns[$i] == 'login_attempt_date') {
          $row[] = self::get_date_time(strtotime($aRow->{$aColumns[$i]}));
        } elseif ($aColumns[$i] == 'failed_user') {
          $failed_login = '';
          $failed_login .= htmlspecialchars($aRow->failed_user);
          $row[] = $failed_login;
        } elseif ($aColumns[$i] == 'login_attempt_IP') {
          $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="fail-log-location">Available in PRO</a>';
          $row[] = $aRow->login_attempt_IP;
          $row[] = '<a href="#" class="open-pro-dialog pro-feature" data-pro-feature="fail-log-user-agent">Available in PRO</a>';
        } elseif ($aColumns[$i] == 'reason') {
          $row[] = LoginLockdown_Functions::pretty_fail_errors($aRow->{$aColumns[$i]});
        }
      }

      $row[] = '<div data-failed-id="' . $aRow->login_attempt_ID . '" class="tooltip delete_failed_entry" title="Delete failed login attempt log entry" data-msg-success="Failed login attempt log entry deleted" data-btn-confirm="Delete failed login attempt log entry" data-title="Delete failed login attempt log entry" data-wait-msg="Deleting. Please wait." data-name="" title="Delete this failed login attempt log entry"><i class="loginlockdown-icon loginlockdown-trash"></i></div>';
      $output['aaData'][] = $row;
    } // foreach row

    // json encoded output
    @ob_end_clean();
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    echo json_encode($output);
    die();
  }
} // class
