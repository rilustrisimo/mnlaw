<?php
require_once(DUPLICATOR_PRO_PLUGIN_PATH.'/classes/entities/class.secure.global.entity.php');

global $wp_version;
global $wpdb;

$global = DUP_PRO_Global_Entity::get_instance();
$sglobal = DUP_PRO_Secure_Global_Entity::getInstance();

$nonce_action		= 'duppro-settings-package-edit';
$action_updated		= null;
$action_response	= DUP_PRO_U::__("Package Settings Saved");
$is_zip_available	= (DUP_PRO_Zip_U::getShellExecZipPath() != null);
$max_execution_time = ini_get("max_execution_time");
$max_execution_time = empty($max_execution_time) ? 30 : $max_execution_time;
$beta_display		=  ($global->debug_beta) ? '' : 'display:none;';
$max_worker_cap_in_sec = (int) (0.7 * (float) $max_execution_time);

//SAVE RESULTS
if (isset($_POST['action']) && $_POST['action'] == 'save') {
	check_admin_referer($nonce_action);

	//VISUAL
	$global->package_ui_created			 = isset($_REQUEST['package_ui_created']) ? (int) $_REQUEST['package_ui_created'] : 1;

	//DATABASE
	$enable_mysqldump					 = isset($_REQUEST['_package_dbmode']) && $_REQUEST['_package_dbmode'] == 'mysql' ? "1" : "0";
	$global->package_mysqldump			 = $enable_mysqldump ? 1 : 0;
	$global->package_phpdump_qrylimit	 = isset($_REQUEST['_package_phpdump_qrylimit']) ? (int) $_REQUEST['_package_phpdump_qrylimit'] : 100;
	$global->package_mysqldump_path		 = trim($_REQUEST['_package_mysqldump_path']);

	//ARCHIVE	
	DUP_PRO_U::initStorageDirectory();
	$global->archive_compression = isset($_REQUEST['archive_compression']) ? (bool) $_REQUEST['archive_compression'] : true;
	$prelim_build_mode = (int) $_REQUEST['archive_build_mode'];

	if (!$is_zip_available && ($prelim_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec)) {
		// Something has changed which invalidates Shell exec so move it to ZA
		$global->archive_build_mode = DUP_PRO_Archive_Build_Mode::ZipArchive;
	} else {
		$global->archive_build_mode = $prelim_build_mode;
	}
	
	$global->ziparchive_mode		= isset($_REQUEST['ziparchive_mode']) ? (int) $_REQUEST['ziparchive_mode'] : 0;
	$global->ziparchive_validation	= isset($_REQUEST['ziparchive_validation']);
	if (isset($_REQUEST['ziparchive_chunk_size_in_mb'])) {
		$global->ziparchive_chunk_size_in_mb = (int) $_REQUEST['ziparchive_chunk_size_in_mb'];
	}
	
	//PROCESSING
	$global->max_package_runtime_in_min	 = (int) $_REQUEST['max_package_runtime_in_min'];
	$global->server_load_reduction		 = (int) $_REQUEST['server_load_reduction'];

	//ADVANCED
	$global->lock_mode					 = (int) $_REQUEST['lock_mode'];
	$global->json_mode					 = (int) $_REQUEST['json_mode'];
	$global->php_max_worker_time_in_sec	 = $_REQUEST['php_max_worker_time_in_sec'];
	$global->ajax_protocol				 = $_REQUEST['ajax_protocol'];
	$global->custom_ajax_url			 = $_REQUEST['custom_ajax_url'];
	$global->clientside_kickoff			 = isset($_REQUEST['_clientside_kickoff']);

    // Auto setting the max package runtime in case of client kickoff is turned off and the max package runtime is less than 180 minutes - 3 hours
	if($global->clientside_kickoff && $global->max_package_runtime_in_min < 180 ) {
		$global->max_package_runtime_in_min = 180;
	}

	$global->basic_auth_enabled			 = isset($_REQUEST['_basic_auth_enabled']) ? 1 : 0;
	if ($global->basic_auth_enabled == true) {
		$global->basic_auth_user	 = trim($_REQUEST['basic_auth_user']);
		$sglobal->basic_auth_password = $_REQUEST['basic_auth_password'];
	} else {
		$global->basic_auth_user	 = '';
		$sglobal->basic_auth_password = '';
	}
	$global->basic_auth_enabled	 = isset($_REQUEST['_basic_auth_enabled']) ? 1 : 0;
	$global->installer_base_name = isset($_REQUEST['_installer_base_name']) ? $_REQUEST['_installer_base_name'] : 'installer.php';


	$action_updated = $global->save();
    $sglobal->save();
    
	$global->adjust_settings_for_system();
}

$phpdump_chunkopts				 = array("20", "100", "500", "1000", "2000");
$mysqlDumpPath					 = DUP_PRO_DB::getMySqlDumpPath();
$mysqlDumpFound					 = ($mysqlDumpPath) ? true : false;
$archive_compression_disabled	 = ($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::ZipArchive);
$package_ui_created				 = is_numeric($global->package_ui_created) ? $global->package_ui_created : 1;

//var_dump($global);
//var_dump($GLOBALS);
?>

<style>    
    input#package_mysqldump_path_found {margin-top:5px}
    div.dup-feature-found {padding:3px; border:1px solid silver; background: #f7fcfe; border-radius: 3px; width:500px; font-size: 12px}
    div.dup-feature-notfound {padding:5px; border:1px solid silver; background: #fcf3ef; border-radius: 3px; width:550px; font-size:13px; line-height: 18px}	
	select#package_ui_created {font-family: monospace}
	input#_package_mysqldump_path {width:500px}
	#dpro-ziparchive-mode-st, #dpro-ziparchive-mode-mt {height: 28px; padding-top:5px; display: none}
	i.description {font-size: 12px}
</style>

<form id="dup-settings-form" action="<?php echo self_admin_url('admin.php?page=' . DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG); ?>" method="post" data-parsley-validate>

    <?php wp_nonce_field($nonce_action); ?>
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="page"   value="<?php echo DUP_PRO_Constants::$SETTINGS_SUBMENU_SLUG ?>">
    <input type="hidden" name="tab"   value="package">

    <?php if ($action_updated) : ?>
        <div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
    <?php endif; ?>	

	
	<!-- ===============================
    VISUAL -->
    <h3 class="title"><?php DUP_PRO_U::_e("Visual") ?> </h3>
    <hr size="1" />
    <table class="form-table">   
	    <tr valign="top">           
            <th scope="row"><label><?php DUP_PRO_U::_e("Created Format"); ?></label></th>
            <td>
                <select name="package_ui_created" id="package_ui_created">
					<!-- YEAR -->
					<optgroup label="<?php DUP_PRO_U::_e("By Year"); ?>">
						<option value="1">Y-m-d H:i &nbsp;	[2000-01-05 12:00]</option>
						<option value="2">Y-m-d H:i:s		[2000-01-05 12:00:01]</option>
						<option value="3">y-m-d H:i &nbsp;	[00-01-05   12:00]</option>
						<option value="4">y-m-d H:i:s		[00-01-05   12:00:01]</option>
					</optgroup>
					<!-- MONTH -->
					<optgroup label="<?php DUP_PRO_U::_e("By Month"); ?>">
						<option value="5">m-d-Y H:i  &nbsp; [01-05-2000 12:00]</option>
						<option value="6">m-d-Y H:i:s		[01-05-2000 12:00:01]</option>
						<option value="7">m-d-y H:i  &nbsp; [01-05-00   12:00]</option>
						<option value="8">m-d-y H:i:s		[01-05-00   12:00:01]</option>
					</optgroup>
					<!-- DAY -->
					<optgroup label="<?php DUP_PRO_U::_e("By Day"); ?>">
						<option value="9"> d-m-Y H:i &nbsp;	[05-01-2000 12:00]</option>
						<option value="10">d-m-Y H:i:s		[05-01-2000 12:00:01]</option>
						<option value="11">d-m-y H:i &nbsp;	[05-01-00	12:00]</option>
						<option value="12">d-m-y H:i:s		[05-01-00	12:00:01]</option>
					</optgroup>	
				</select>
                <p class="description">
                    <?php DUP_PRO_U::_e("The date format shown in the 'Created' column on the Packages screen."); ?>
                </p>
            </td>
        </tr>	
    </table>


	<!-- ===============================
    DATABASE -->
    <h3 class="title"><?php DUP_PRO_U::_e("Database") ?> </h3>
    <hr size="1" />
    <table class="form-table">
		<tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("SQL Script"); ?></label></th>
            <td>
				<!-- MYSQLDUMP IN-ACTIVE -->
                <?php if (! DUP_PRO_Shell_U::isShellExecEnabled()) : ?>
					<input type="radio" disabled="true"/>
                    <label><?php DUP_PRO_U::_e("Mysqldump"); ?> <i class="description"><?php DUP_PRO_U::_e("(recommended)"); ?></i></label>
					<div class="sub-opts">
						<div class="dup-feature-notfound">
							<?php
								DUP_PRO_U::_e("In order to use mysqldump the PHP function shell_exec needs to be enabled. This server currently does not allow shell_exec to run. ");
								DUP_PRO_U::_e("Please contact your host or server admin to enable this feature. For a list of approved providers that support shell_exec ");
								echo "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>" . DUP_PRO_U::__("click here") . "</a>.";
							?>
						</div>
					</div><br/>
				<!-- MYSQLDUMP ACTIVE -->
                <?php else : ?>
                    <input type="radio" name="_package_dbmode" value="mysql" id="package_mysqldump" <?php echo DUP_PRO_UI::echoChecked($global->package_mysqldump); ?> />
                    <label for="package_mysqldump"><?php DUP_PRO_U::_e("Mysqldump"); ?> <i class="description"><?php DUP_PRO_U::_e("(recommended)"); ?></i></label>
                    <div class="sub-opts">
                        <?php if ( $mysqlDumpFound) : ?>
                            <div class="dup-feature-found">
                                <?php DUP_PRO_U::_e("Successfully Found:"); ?> &nbsp;
                                <i><?php echo $mysqlDumpPath ?></i>
                            </div><br/>
                        <?php else : ?>
                            <div class="dup-feature-notfound">
                                <?php
									DUP_PRO_U::_e('The mysqldump program was not found at its default location or the custom path below.  Please enter a valid path where mysqldump can run. ');
									DUP_PRO_U::_e("If the problem persist contact your server admin for the correct path. For a list of approved providers that support mysqldump ");
									echo "<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>" . DUP_PRO_U::__("click here") . "</a>.";
                                ?>
                            </div><br/>
                        <?php endif; ?>


                        <label><?php DUP_PRO_U::_e("Custom Path"); ?></label>
                        <i class="fa fa-question-circle"
							data-tooltip-title="<?php DUP_PRO_U::_e("mysqldump"); ?>"
							data-tooltip="<?php DUP_PRO_U::_e('An optional path to the mysqldump program.  Add a custom path if the path to mysqldump is not properly detected or needs to be changed.   For all paths including Windows use a forward slash.'); ?>"></i>
                        <br/>
                        <input class="wide-input" type="text" name="_package_mysqldump_path" id="_package_mysqldump_path" value="<?php echo $global->package_mysqldump_path; ?>"  placeholder="<?php DUP_PRO_U::_e("/usr/bin/mypath/mysqldump.exe"); ?>" />
						<br/><br/>

                    </div>
                <?php endif; ?>

				<!-- PHP OPTION -->
				<input type="radio" name="_package_dbmode" id="package_phpdump" value="php" <?php echo DUP_PRO_UI::echoChecked(!$global->package_mysqldump); ?> />
                <label for="package_phpdump"><?php DUP_PRO_U::_e("PHP Code"); ?></label> &nbsp;
                <div class="sub-opts">

                    <label for="_package_phpdump_qrylimit"><?php DUP_PRO_U::_e("Query Limit Size"); ?></label>
                    <i style="margin-right:7px" class="fa fa-question-circle"
					   data-tooltip-title="<?php DUP_PRO_U::_e("PHP Query Limit Size"); ?>"
					   data-tooltip="<?php DUP_PRO_U::_e('A higher limit size will speed up the database build time, however it will use more memory.  If your host has memory caps start off low.'); ?>"></i>
                    <select name="_package_phpdump_qrylimit" id="_package_phpdump_qrylimit">
                        <?php
                        foreach ($phpdump_chunkopts as $value)
                        {
                            $selected = ( $global->package_phpdump_qrylimit == $value ? "selected='selected'" : '' );
                            echo "<option {$selected} value='{$value}'>" . number_format($value) . '</option>';
                        }
                        ?>
                    </select>
                </div>

            </td>
        </tr>
    </table>

	<!-- ===============================
    ARCHIVE -->
    <h3 class="title"><?php DUP_PRO_U::_e("Archive") ?> </h3>
    <hr size="1" />
    <table class="form-table">
		<tr>
            <th scope="row">
				<label><?php DUP_PRO_U::_e("Compression"); ?></label>
			</th>
            <td>
				<input type="radio" name="archive_compression" id="archive_compression_off" value="0" <?php echo DUP_PRO_UI::echoChecked($global->archive_compression == false); ?> />
				<label for="archive_compression_off"><?php DUP_PRO_U::_e("Off"); ?></label> &nbsp;
				<input type="radio" name="archive_compression"  id="archive_compression_on" value="1" <?php echo DUP_PRO_UI::echoChecked($global->archive_compression == true); ?>  />
				<label for="archive_compression_on"><?php DUP_PRO_U::_e("On"); ?></label>
				<i style="margin-right:7px;" class="fa fa-question-circle"
					data-tooltip-title="<?php DUP_PRO_U::_e("Shell Exec Archive Compression:"); ?>"
					data-tooltip="<?php DUP_PRO_U::_e('Controls archive compression. This setting can be toggled when using ZipArchive on a PHP 7+ system or Shell Exec Zip.'); ?>"></i>
            </td>
		</tr>
		<tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("Engine"); ?></label></th>
            <td>

				<!-- SHELL EXEC: ENABLED  -->
                <?php if ($is_zip_available) : ?>
					<input onclick="DupPro.UI.SetArchiveOptionStates();" type="radio" name="archive_build_mode" id="archive_build_mode1" value="<?php echo DUP_PRO_Archive_Build_Mode::Shell_Exec; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::Shell_Exec); ?> />
					<label for="archive_build_mode1"><?php DUP_PRO_U::_e("Shell Zip"); ?> <i class="description"><?php DUP_PRO_U::_e("(recommended)"); ?></i></label> <br/>

					<br/>

				<!-- SHELL EXEC: DISABLED  -->
                <?php else : ?>

					<input id="archive_build_mode1" type="radio" disabled="true"/><label><?php DUP_PRO_U::_e("Shell Zip"); ?> <i class="description"><?php DUP_PRO_U::_e("(recommended)"); ?></i></label> <br/>
					<div class="sub-opts">
						<p class="description" >
							<?php
                            
								$problem_fixes = DUP_PRO_Zip_U::getShellExecZipProblems();
								$shell_exec_zip_tooltip = '';
								if(count($problem_fixes) > 0 && ((strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')))
								{
                                    $shell_exec_zip_tooltip .= DUP_PRO_U::__("Server doesn't support more powerful Shell Zip engine - only older ZipArchive engine can be used.<br/><br/>To make Shell Zip available, ask your host to:<br/>");
                            
									$i = 1;
									foreach($problem_fixes as $problem_fix)
									{
										$shell_exec_zip_tooltip .= "{$i}. {$problem_fix->fix}<br/>";
										$i++;
									}
                                    $shell_exec_zip_tooltip .= '<br/>';
								} else {
                                    $shell_exec_zip_tooltip .= DUP_PRO_U::__("Server doesn't support more powerful Shell Zip engine - only older ZipArchive engine can be used.<br/>");
                                }
								?>
								<?php
  
                                 echo "{$shell_exec_zip_tooltip}";
                                 
                                 DUP_PRO_U::_e("<a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>List of providers that support Shell Zip by default.</a>");							
							?>
						</p>
					</div><br/>
                <?php endif; ?>

				<!-- ZIP ARCHIVE  -->
				<input onclick="DupPro.UI.SetArchiveOptionStates();" type="radio" name="archive_build_mode" id="archive_build_mode2"  value="<?php echo DUP_PRO_Archive_Build_Mode::ZipArchive; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::ZipArchive); ?> />
				<label for="archive_build_mode2"><?php DUP_PRO_U::_e("PHP ZipArchive"); ?></label> &nbsp;

				<div class="sub-opts">
					<label>Mode:</label> 
					<select  name="ziparchive_mode" id="ziparchive_mode"  onchange="DupPro.UI.setZipArchiveMode();">
						<option <?php echo DUP_PRO_UI::echoSelected($global->ziparchive_mode == DUP_PRO_ZipArchive_Mode::Multithreaded); ?> value="<?php echo DUP_PRO_ZipArchive_Mode::Multithreaded ?>">
							<?php DUP_PRO_U::_e("Multi-Threaded"); ?>
						</option>
						<option <?php echo DUP_PRO_UI::echoSelected($global->ziparchive_mode == DUP_PRO_ZipArchive_Mode::SingleThread); ?> value="<?php echo DUP_PRO_ZipArchive_Mode::SingleThread ?>">
							<?php DUP_PRO_U::_e("Single-Threaded"); ?>
						</option>
					</select>

					<div id="dpro-ziparchive-mode-st">
						<input type="checkbox" id="ziparchive_validation" name="ziparchive_validation" <?php echo DUP_PRO_UI::echoChecked($global->ziparchive_validation); ?>>
						<label for="ziparchive_validation">Enable file validation</label>
					</div>
	
					<div id="dpro-ziparchive-mode-mt">
						<label><?php DUP_PRO_U::_e("Buffer Size"); ?></label>
						<input style="width:40px;" 
							   data-parsley-required data-parsley-errors-container="#ziparchive_chunk_size_error_container" data-parsley-min="5" data-parsley-type="number"
							   type="text" name="ziparchive_chunk_size_in_mb" id="ziparchive_chunk_size_in_mb" value="<?php echo $global->ziparchive_chunk_size_in_mb; ?>" />
						<label><?php DUP_PRO_U::_e('MB'); ?></label>
						<i style="margin-right:7px" class="fa fa-question-circle"
							data-tooltip-title="<?php DUP_PRO_U::_e("PHP ZipArchive"); ?>"
							data-tooltip="<?php DUP_PRO_U::_e('The buffer size only applies to multi-threaded requests.  The buffer indicates how large an archive will get before a close is registered with the ZipArchive call.  Higher values are faster but can be more unstable based on the hosts max_execution time.'); ?>"></i>
						<div id="ziparchive_chunk_size_error_container" class="duplicator-error-container"></div>
					</div>
					
				</div>

                <!-- DUPARCHIVE -->
				<div style="<?php echo $beta_display; ?>">
					<br/>
					<input onclick="DupPro.UI.SetArchiveOptionStates();" type="radio" name="archive_build_mode" id="archive_build_mode3"  value="<?php echo DUP_PRO_Archive_Build_Mode::DupArchive; ?>" <?php echo DUP_PRO_UI::echoChecked($global->archive_build_mode == DUP_PRO_Archive_Build_Mode::DupArchive); ?> />
					<label for="archive_build_mode3"><?php DUP_PRO_U::_e("DupArchive (BETA)"); ?></label> &nbsp;
					<div class="sub-opts">
						<p class="description">
							<?php DUP_PRO_U::_e('Custom format geared at handling larger sites. <b>Do not use if you are using scheduled backups.</b>'); ?>
						</p>
					</div>
				</div>

            </td>
        </tr>
    </table>

		
    <!-- ===============================
    PROCESSING -->
    <h3 class="title"><?php DUP_PRO_U::_e("Processing") ?> </h3>
    <hr size="1" />
    <table class="form-table">       
        <tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("Server Throttle"); ?></label></th>
            <td>
                <input type="radio" name="server_load_reduction" value="<?php echo DUP_PRO_Email_Build_Mode::No_Emails; ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::None); ?> />
                <label for="server_load_reduction"><?php DUP_PRO_U::_e("Off"); ?></label> &nbsp;
                <input type="radio" name="server_load_reduction" value="<?php echo DUP_PRO_Server_Load_Reduction::A_Bit; ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::A_Bit); ?> />
                <label for="server_load_reduction"><?php DUP_PRO_U::_e("Low"); ?></label> &nbsp;
                <input type="radio" name="server_load_reduction"  value="<?php echo DUP_PRO_Server_Load_Reduction::More; ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::More); ?> />
                <label for="server_load_reduction"><?php DUP_PRO_U::_e("Medium"); ?></label> &nbsp;
                <input type="radio" name="server_load_reduction"  value="<?php echo DUP_PRO_Server_Load_Reduction::A_Lot ?>" <?php echo DUP_PRO_UI::echoChecked($global->server_load_reduction == DUP_PRO_Server_Load_Reduction::A_Lot); ?> />
                <label for="server_load_reduction"><?php DUP_PRO_U::_e("High"); ?></label> &nbsp;
                <p class="description"><?php  DUP_PRO_U::_e("Throttle to prevent resource complaints on budget hosts. The higher the value the slower the backup.");  ?></p>
            </td>
        </tr>
		
        <tr valign="top">           
            <th scope="row"><label><?php DUP_PRO_U::_e("Max Build Time"); ?></label></th>
            <td>
                <input style="float:left;display:block;margin-right:6px;" data-parsley-required data-parsley-errors-container="#max_package_runtime_in_min_error_container" data-parsley-min="0" data-parsley-type="number" class="narrow-input" type="text" name="max_package_runtime_in_min" id="max_package_runtime_in_min" value="<?php echo $global->max_package_runtime_in_min; ?>" />                 
                <p style="margin-left:4px;"><?php DUP_PRO_U::_e('Minutes'); ?></p>
                <div id="max_package_runtime_in_min_error_container" class="duplicator-error-container"></div>
                <p class="description">  <?php DUP_PRO_U::_e('Max build and storage time until package is auto-cancelled. Set to 0 for no limit.'); ?>  </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::_e("Max Worker Time"); ?></label></th>
            <td>
                <input style="float:left;display:block;margin-right:6px;" data-parsley-required data-parsley-errors-container="#php_max_worker_time_in_sec_error_container" data-parsley-min="10" data-parsley-type="number" class="narrow-input" type="text" name="php_max_worker_time_in_sec" id="php_max_worker_time_in_sec" value="<?php echo $global->php_max_worker_time_in_sec; ?>" />
                <p style="margin-left:4px;"><?php DUP_PRO_U::_e('Seconds'); ?></p>
                <div id="php_max_worker_time_in_sec_error_container" class="duplicator-error-container"></div>
                <p class="description">
					<?php
					DUP_PRO_U::_e("Lower is more reliable but slower. Recommended max is $max_worker_cap_in_sec sec based on PHP setting 'max_execution_time'.");
					?>
                </p>
            </td>
        </tr>
    </table><br/>


    <!-- ===============================
    ADVANCED SETTINGS -->
    <h3 class="title"><?php DUP_PRO_U::_e("Advanced") ?> </h3>
    <hr size="1" />
	<p class="description" style="color:maroon">
		<?php DUP_PRO_U::_e("Please do not modify advanced settings unless you know the expected result or have talked to support."); ?>
	</p>
    <table class="form-table">	
		
	
		<tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("Thread Lock"); ?></label></th>
            <td>
                <input type="radio" name="lock_mode" value="<?php echo DUP_PRO_Thread_Lock_Mode::Flock; ?>" <?php echo DUP_PRO_UI::echoChecked($global->lock_mode == DUP_PRO_Thread_Lock_Mode::Flock); ?> />
                <label for="lock_mode"><?php DUP_PRO_U::_e("File"); ?></label> &nbsp;
                <input type="radio" name="lock_mode" value="<?php echo DUP_PRO_Thread_Lock_Mode::SQL_Lock; ?>" <?php echo DUP_PRO_UI::echoChecked($global->lock_mode == DUP_PRO_Thread_Lock_Mode::SQL_Lock); ?> />
                <label for="lock_mode"><?php DUP_PRO_U::_e("SQL"); ?></label> &nbsp;
            </td>
        </tr>
		<tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("JSON"); ?></label></th>
            <td>
                <input type="radio" name="json_mode" value="<?php echo DUP_PRO_JSON_Mode::PHP; ?>" <?php echo DUP_PRO_UI::echoChecked($global->json_mode == DUP_PRO_JSON_Mode::PHP); ?> />
                <label for="json_mode"><?php DUP_PRO_U::_e("PHP"); ?></label> &nbsp;
                <input type="radio" name="json_mode" value="<?php echo DUP_PRO_JSON_Mode::Custom; ?>" <?php echo DUP_PRO_UI::echoChecked($global->json_mode == DUP_PRO_JSON_Mode::Custom); ?> />
                <label for="json_mode"><?php DUP_PRO_U::_e("Custom"); ?></label> &nbsp;
            </td>
        </tr>
		<tr>
            <th scope="row"><label><?php DUP_PRO_U::_e("Ajax"); ?></label></th>
            <td>
                <input type="radio" name="ajax_protocol" value="admin" <?php echo DUP_PRO_UI::echoChecked($global->ajax_protocol == 'admin'); ?> />
                <label for="ajax_protocol"><?php DUP_PRO_U::_e("Auto"); ?></label> &nbsp;
                <input type="radio" name="ajax_protocol" value="http" <?php echo DUP_PRO_UI::echoChecked($global->ajax_protocol == 'http'); ?> />
                <label for="ajax_protocol"><?php DUP_PRO_U::_e("HTTP"); ?></label> &nbsp;
                <input type="radio" name="ajax_protocol"  value="https" <?php echo DUP_PRO_UI::echoChecked($global->ajax_protocol == 'https'); ?> />
                <label for="ajax_protocol"><?php DUP_PRO_U::_e("HTTPS"); ?></label> &nbsp;
                <input type="radio" name="ajax_protocol"  value="custom" <?php echo DUP_PRO_UI::echoChecked($global->ajax_protocol == 'custom'); ?> />
                <label for="ajax_protocol"><?php DUP_PRO_U::_e("Custom URL:"); ?></label> &nbsp;
                <input style="width:353px" type="text"id="custom_ajax_url" name="custom_ajax_url" placeholder="<?php DUP_PRO_U::_e('Consult support before changing.'); ?>" value="<?php echo $global->custom_ajax_url; ?>" />
                <p class="description">
					<?php DUP_PRO_U::_e("Used to kick off build worker. Only change if packages get stuck at start of build."); 	?>
                </p>
            </td>
        </tr>
		<tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::_e('Client-side Kickoff'); ?></label></th>
            <td>
                <input type="checkbox" name="_clientside_kickoff" id="_clientside_kickoff" <?php DUP_PRO_UI::echoChecked($global->clientside_kickoff); ?> />
                <label for="_clientside_kickoff"><?php DUP_PRO_U::_e("Enabled") ?> </label><br/>
				<p class="description">
					<?php DUP_PRO_U::_e('Initiate package build from client. Only check this if instructed to by Snap Creek support.'); ?>
                </p>
            </td>
        </tr> 
        <tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::_e("Basic Auth"); ?></label></th>
            <td>
                <input type="checkbox" name="_basic_auth_enabled" id="_basic_auth_enabled" <?php DUP_PRO_UI::echoChecked($global->basic_auth_enabled); ?> /> 
                <label for="_basic_auth_enabled"><?php DUP_PRO_U::_e("Enabled") ?> </label><br/>
                <input style="margin-top:8px;width:200px;" class="wide-input" autocomplete="off"  placeholder="<?php DUP_PRO_U::_e('User'); ?>" type="text" name="basic_auth_user" id="basic_auth_user" value="<?php echo $global->basic_auth_user; ?>" />
                <input id='auth_password' autocomplete="off" style="width:200px;" class="wide-input"  placeholder="<?php DUP_PRO_U::_e('Password'); ?>" type="password" name="basic_auth_password" id="basic_auth_password" value="<?php echo $sglobal->basic_auth_password; ?>" />
                <label for="auth_password">
                    <i class="dpro-edit-info">
                        <input type="checkbox" onclick="DupPro.UI.TogglePasswordDisplay(this.checked, 'auth_password');" /> <?php DUP_PRO_U::_e('Show Password') ?>
                    </i>
                </label>
            </td>
        </tr> 
		<tr valign="top">
            <th scope="row"><label><?php DUP_PRO_U::_e('Installer Name'); ?></label></th>
            <td>
               <input type="text" name="_installer_base_name" id="_installer_base_name" value="<?php echo $global->installer_base_name; ?>"
					  data-parsley-required 
					  data-parsley-minlength="10"
					  data-parsley-errors-container="#installer_base_name_error_container" /> 
				<div id="installer_base_name_error_container" class="duplicator-error-container"></div>
				<p class="description">
					<?php DUP_PRO_U::_e('The base name of the installer file. Only change if host prevents using installer.php'); ?>
                </p>
            </td>
        </tr> 
    </table>

    <p class="submit" style="margin: 20px 0px 0xp 5px;">
        <br/><input type="submit" name="submit" id="submit" class="button-primary" value="<?php DUP_PRO_U::_e('Save Package Settings') ?>" style="display: inline-block;" />
    </p>
</form>

<script>
jQuery(document).ready(function ($) 
{
	DupPro.UI.setZipArchiveMode = function ()
	{
		$('#dpro-ziparchive-mode-st, #dpro-ziparchive-mode-mt').hide();
		if ($('#ziparchive_mode').val() == 0) {
			$('#dpro-ziparchive-mode-mt').show();
		} else {
			$('#dpro-ziparchive-mode-st').show();
		}
	}

        DupPro.UI.SetArchiveOptionStates = function() {

            var php70 = <?php DUP_PRO_UI::echoBoolean(DUP_PRO_U::PHP70()); ?>;
            var isShellZipSelected   = $('#archive_build_mode1').is(':checked');
            var isDupArchiveSelected = $('#archive_build_mode3').is(':checked');

            if(isShellZipSelected || isDupArchiveSelected) {
                $("[name='archive_compression']").prop('disabled', false);
                $("[name='ziparchive_mode']").prop('disabled', true);
            } else {
                
                $("[name='ziparchive_mode']").prop('disabled', false);

                if(php70) {
                     $("[name='archive_compression']").prop('disabled', false);
                 } else {
                     $('#archive_compression_on').prop('checked', true);
                    $("[name='archive_compression']").prop('disabled', true);

                    console.log('archive compression on');
                }
            }

            DupPro.UI.setZipArchiveMode();
        }

	//INIT
	$('#package_ui_created').val(<?php echo $package_ui_created ?>);
        DupPro.UI.SetArchiveOptionStates();

});
</script>
