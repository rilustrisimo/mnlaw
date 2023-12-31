<?php
global $wp_version;
global $wpdb;

$nonce_action = 'duppro-settings-schedule-edit';
$action_updated = null;
$action_response = DUP_PRO_U::__("Schedule Settings Saved");

/* @var $global DUP_PRO_Global_Entity */
$global = DUP_PRO_Global_Entity::get_instance();

//SAVE RESULTS
if (isset($_POST['action']) && $_POST['action'] == 'save')
{
    check_admin_referer($nonce_action);
    $global->send_email_on_build_mode = (int)$_REQUEST['send_email_on_build_mode'];	
	$global->notification_email_address = $_REQUEST['notification_email_address'];
    $action_updated = $global->save();
}
?>

<style>    
    table.form-table tr td { padding-top: 25px; }
</style>

<form id="dup-settings-form" action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" method="post" data-parsley-validate>

    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="page"   value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
    <input type="hidden" name="tab"   value="schedule">

    <?php if ($action_updated) : ?>
        <div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
    <?php endif; ?>	


    <!-- ===============================
    SCHEDULE SETTINGS -->
    <h3 class="title"><?php DUP_PRO_U::_e("Notifications") ?> </h3>
    <hr size="1" />
    <table class="form-table">	
        <tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("Send Build Email"); ?></label></th>
            <td>
                <input type="radio" name="send_email_on_build_mode" value="<?php echo DUP_PRO_Email_Build_Mode::No_Emails; ?>" <?php echo DUP_PRO_UI::echoChecked($global->send_email_on_build_mode == DUP_PRO_Email_Build_Mode::No_Emails); ?> />
                <label for="send_email_on_build_mode"><?php DUP_PRO_U::_e("Never"); ?></label> &nbsp;
                <input type="radio" name="send_email_on_build_mode" value="<?php echo DUP_PRO_Email_Build_Mode::Email_On_Failure; ?>" <?php echo DUP_PRO_UI::echoChecked($global->send_email_on_build_mode == DUP_PRO_Email_Build_Mode::Email_On_Failure); ?> />
                <label for="send_email_on_build_mode"><?php DUP_PRO_U::_e("On Failure"); ?></label> &nbsp;
                <input type="radio" name="send_email_on_build_mode"  value="<?php echo DUP_PRO_Email_Build_Mode::Email_On_All_Builds; ?>" <?php echo DUP_PRO_UI::echoChecked($global->send_email_on_build_mode == DUP_PRO_Email_Build_Mode::Email_On_All_Builds); ?> />
                <label for="send_email_on_build_mode"><?php DUP_PRO_U::_e("Always"); ?></label> &nbsp;
                <p class="description">
                    <?php
                    DUP_PRO_U::_e("When to send emails after a scheduled build.");
                    ?>
                </p>
            </td>
        </tr>
		<tr valign="top">           
            <th scope="row"><label><?php DUP_PRO_U::_e("Email Address"); ?></label></th>
            <td>
                <input style="display:block;margin-right:6px; width:25em;" data-parsley-errors-container="#notification_email_address_error_container" data-parsley-type="email" type="email" name="notification_email_address" id="notification_email_address" value="<?php echo $global->notification_email_address; ?>" />                 
               <p class="description">  <?php DUP_PRO_U::_e('Admin email will be used if empty.'); ?>  </p>
                <div id="notification_email_address_error_container" class="duplicator-error-container"></div>
                
            </td>
        </tr>		
        
    </table>


    <p class="submit" style="margin: 20px 0px 0xp 5px;">
        <br/>
        <input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::_e('Save Schedule Settings') ?>" style="display: inline-block;" />
    </p>

</form>
<script>
    jQuery(document).ready(function ($) {


    });
</script>
