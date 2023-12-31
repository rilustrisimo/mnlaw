<?php
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

require_once($GLOBALS['DUPX_INIT'] . '/classes/config/class.archive.config.php');

//ARCHIVE FILE
$arcCheck = (file_exists($GLOBALS['FW_PACKAGE_PATH'])) ? 'Pass' : 'Fail';
$arcSize = @filesize($GLOBALS['FW_PACKAGE_PATH']);
$arcSize = is_numeric($arcSize) ? $arcSize : 0;

//REQUIRMENTS
$req = array();
$req['01'] = DUPX_Server::is_dir_writable($GLOBALS['DUPX_ROOT']) ? 'Pass' : 'Fail';
$req['02'] = 'Pass'; //Place-holder for future check
$req['03'] = (!DUPX_Server::$php_safe_mode_on) ? 'Pass' : 'Fail';
$req['04'] = function_exists('mysqli_connect') ? 'Pass' : 'Fail';
$req['05'] = DUPX_Server::$php_version_safe ? 'Pass' : 'Fail';
$all_req = in_array('Fail', $req) ? 'Fail' : 'Pass';

//NOTICES
$openbase = ini_get("open_basedir");
$scanfiles = @scandir($GLOBALS['DUPX_ROOT']);
$scancount = is_array($scanfiles) ? (count($scanfiles)) : -1;
$datetime1 = $GLOBALS['DUPX_AC']->created;
$datetime2 = date("Y-m-d H:i:s");
$fulldays = round(abs(strtotime($datetime1) - strtotime($datetime2))/86400);
$root_path = SnapLibIOU::safePath($GLOBALS['DUPX_ROOT'], true);
$archive_path = SnapLibIOU::safePath($GLOBALS['FW_PACKAGE_PATH'], true);
$wpconf_path = "{$root_path}/wp-config.php";
$max_time_zero = set_time_limit(0);
$max_time_size = 314572800;  //300MB
$max_time_ini = ini_get('max_execution_time');
$max_time_warn = (is_numeric($max_time_ini) && $max_time_ini < 31 && $max_time_ini > 0) && $arcSize > $max_time_size;

$notice = array();
if (!$GLOBALS['DUPX_AC']->exportOnlyDB) {
	$notice['01'] = !file_exists($wpconf_path) ? 'Good' : 'Warn';
	$notice['02'] = $scancount <= 20 ? 'Good' : 'Warn';
}
$notice['03'] = $fulldays <= 180 ? 'Good' : 'Warn';
$notice['04'] = 'Good'; //Place-holder for future check
$notice['05'] = 'Good'; //Place-holder for future check $GLOBALS['DUPX_AC']->version_os == PHP_OS ? 'Good' : 'Warn';
$notice['06'] = empty($openbase) ? 'Good' : 'Warn';
$notice['07'] = !$max_time_warn ? 'Good' : 'Warn';
$all_notice = in_array('Warn', $notice) ? 'Warn' : 'Good';

//SUMMATION
$req_success = ($all_req == 'Pass');
$req_notice = ($all_notice == 'Good');
$all_success = ($req_success && $req_notice);
$agree_msg = "To enable this button the checkbox above under the 'Terms & Notices' must be checked.";

$shell_exec_unzip_path = DUPX_Server::get_unzip_filepath();
$shell_exec_zip_enabled = ($shell_exec_unzip_path != null);
$zip_archive_enabled = class_exists('ZipArchive') ? 'Enabled' : 'Not Enabled';

/* @var $archive_config DUPX_ArchiveConfig */
$archive_config = DUPX_ArchiveConfig::getInstance();




//MULTISITE
$show_multisite = ($archive_config->mu_mode !== 0) && (count($archive_config->subsites) > 0);
$multisite_disabled = ($archive_config->getLicenseType() != DUPX_LicenseType::BusinessGold);


/** FORWARD: To one-click installer
  $oneclick = ($GLOBALS['FW_ONECLICK'] && $req_success) && (! isset($_GET['view']));
  if ($oneclick && ! $_GET['debug']) {
  DUPX_HTTP::post_with_html(DUPX_HTTP::get_request_uri(), array('view' => 'deploy'));
  exit;
  } */
?>


<form id="s1-input-form" method="post" class="content-form">
    <input type="hidden" name="view" value="step1" />
    <input type="hidden" name="ctrl_action" value="ctrl-step1" />

    <div class="hdr-main">
        Step <span class="step">1</span> of 4: Deployment
        <!--div style="float:right; font-size:14px"><a href="javascript:void(0)">One-Click Install</a></div-->
    </div>
    <br/>

    <!-- ====================================
    ARCHIVE
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-archive-file">
        <a id="s1-area-archive-file-link"><i class="fa fa-plus-square"></i>Archive</a>
        <div class="<?php echo ( $arcCheck == 'Pass') ? 'status-badge-pass' : 'status-badge-fail'; ?>">
            <?php echo ($arcCheck == 'Pass') ? 'Pass' : 'Fail'; ?>
        </div>
    </div>
    <div id="s1-area-archive-file" style="display:none">
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Server</a></li>
                <!--li><a href="#tabs-2">Cloud</a></li-->
            </ul>
            <div id="tabs-1">

				<table class="s1-archive-local">
					<tr>
						<td colspan="2"><div class="hdr-sub3">Site Details</div></td>
					</tr>
					 <tr>
						<td>Site:</td>
						<td><?php echo $GLOBALS['DUPX_AC']->blogname;?> </td>
					</tr>
                    <tr>
                        <td>Notes:</td>
                        <td><?php echo strlen($GLOBALS['DUPX_AC']->package_notes) ? "{$GLOBALS['DUPX_AC']->package_notes}" : " - no notes - "; ?></td>
                    </tr>
					<?php if ($GLOBALS['DUPX_AC']->exportOnlyDB) :?>
					<tr>
						<td>Mode:</td>
						<td>Archive only database was enabled during package package creation.</td>
					</tr>
					<?php endif; ?>
				</table>

                <table class="s1-archive-local">
					<tr>
						<td colspan="2"><div class="hdr-sub3">File Details</div></td>
					</tr>
                    <tr>
                        <td>Size:</td>
                        <td><?php echo DUPX_U::readableByteSize($arcSize);?> </td>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><?php echo "{$GLOBALS['FW_PACKAGE_NAME']}"; ?> </td>
                    </tr>
                    <tr>
                        <td>Path:</td>
                        <td><?php echo $root_path; ?> </td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td>
                            <?php if ($arcCheck != 'Fail') : ?>
                            <span class="dupx-pass">Archive file successfully detected.</span>
                            <?php else : ?>
                            <span class="dupx-fail" style="font-style:italic">
                                The archive file named below must be the <u>exact</u> name of the archive file placed in the root path (character for character).
                                When downloading the package files make sure both files are from the same package line.
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

            </div>
            <!--div id="tabs-2"><p>Content Here</p></div-->
        </div>
    </div><br/><br/>

    <!-- ====================================
    VALIDATION
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-sys-setup">
        <a id="s1-area-sys-setup-link"><i class="fa fa-plus-square"></i>Validation</a>
        <div class="<?php echo ( $req_success) ? 'status-badge-pass' : 'status-badge-fail'; ?>	">
            <?php echo ( $req_success) ? 'Pass' : 'Fail'; ?>
        </div>
    </div>
    <div id="s1-area-sys-setup" style="display:none">
        <div class='info-top'>The system validation checks help to make sure the system is ready for install.</div>

        <!-- REQUIREMENTS -->
        <div class="s1-reqs" id="s1-reqs-all">
            <div class="header">
                <table class="s1-checks-area">
                    <tr>
                        <td class="title">Requirements <small>(must pass)</small></td>
                        <td class="toggle"><a href="javascript:void(0)" onclick="DUPX.toggleAll('#s1-reqs-all')">[toggle]</a></td>
                    </tr>
                </table>
            </div>

            <!-- REQ 1 -->
            <div class="status <?php echo strtolower($req['01']); ?>"><?php echo $req['01']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-reqs01"><i class="fa fa-caret-right"></i> Directory Writable</div>
            <div class="info" id="s1-reqs01">
                <table>
                    <tr>
                        <td><b>Deployment Path:</b> </td>
                        <td><i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i> </td>
                    </tr>
                    <tr>
                        <td><b>Suhosin Extension:</b> </td>
                        <td><?php echo extension_loaded('suhosin') ? "<i class='dupx-fail'>Enabled</i>'" : "<i class='dupx-pass'>Disabled</i>"; ?> </td>
                    </tr>
                </table><br/>

                The deployment path must be writable by PHP in order to extract the archive file.  Incorrect permissions and extension such as
                <a href="https://suhosin.org/stories/index.html" target="_blank">suhosin</a> can sometimes inter-fear with PHP being able to write/extract files.
                Please see the <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q" target="_blank">FAQ permission</a> help link for complete details.
            </div>

            <!-- REQ 2
            <div class="status <?php echo strtolower($req['02']); ?>"><?php echo $req['02']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-reqs02"><i class="fa fa-caret-right"></i> Place Holder</div>
            <div class="info" id="s1-reqs02"></div>-->

            <!-- REQ 3 -->
            <div class="status <?php echo strtolower($req['03']); ?>"><?php echo $req['03']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-reqs03"><i class="fa fa-caret-right"></i> PHP SafeMode</div>
            <div class="info" id="s1-reqs03">
                PHP with <a href='http://php.net/manual/en/features.safe-mode.php' target='_blank'>safe mode</a> must be disabled.  If this test fails
                please contact your hosting provider or server administrator to disable PHP safe mode.
            </div>

            <!-- REQ 4 -->
            <div class="status <?php echo strtolower($req['04']); ?>"><?php echo $req['04']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-reqs04"><i class="fa fa-caret-right"></i> PHP mysqli</div>
            <div class="info" id="s1-reqs04">
                Support for the PHP <a href='http://us2.php.net/manual/en/mysqli.installation.php' target='_blank'>mysqli extension</a> is required.
                Please contact your hosting provider or server administrator to enable the mysqli extension.  <i>The detection for this call uses
                    the function_exists('mysqli_connect') call.</i>
            </div>

            <!-- REQ 5 -->
            <div class="status <?php echo strtolower($req['05']); ?>"><?php echo $req['05']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-reqs05"><i class="fa fa-caret-right"></i> PHP Version</div>
            <div class="info" id="s1-reqs05">
                This server is running PHP: <b><?php echo DUPX_Server::$php_version ?></b>. <i>A minimum of PHP 5.2.17 is required</i>.
                Contact your hosting provider or server administrator and let them know you would like to upgrade your PHP version.
            </div>
        </div><br/>


        <!-- ====================================
        NOTICES  -->
        <div class="s1-reqs" id="s1-notice-all">
            <div class="header">
                <table class="s1-checks-area">
                    <tr>
                        <td class="title">Notices <small>(optional)</small></td>
                        <td class="toggle"><a href="javascript:void(0)" onclick="DUPX.toggleAll('#s1-notice-all')">[toggle]</a></td>
                    </tr>
                </table>
            </div>

			<?php if (!$GLOBALS['DUPX_AC']->exportOnlyDB) :?>
				<!-- NOTICE 1 -->
				<div class="status <?php echo ($notice['01'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['01']; ?></div>
				<div class="title" data-type="toggle" data-target="#s1-notice01"><i class="fa fa-caret-right"></i> Configuration File</div>
				<div class="info" id="s1-notice01">
					Duplicator Pro works best by placing the installer and archive files into an empty directory.  If a wp-config.php file is found in the extraction
					directory it might indicate that a pre-existing WordPress site exists which can lead to a bad install.
					<br/><br/>
					<b>Options:</b>
					<ul style="margin-bottom: 0">
						<li>If the archive was already manually extracted then <a href="javascript:void(0)" onclick="DUPX.getManaualArchiveOpt()">[Enable Manual Archive Extraction]</a></li>
						<li>If the wp-config file is not needed then remove it.</li>
					</ul>
				</div>

				<!-- NOTICE 2 -->
				<div class="status <?php echo ($notice['02'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['02']; ?></div>
				<div class="title" data-type="toggle" data-target="#s1-notice02"><i class="fa fa-caret-right"></i> Directory Setup</div>
				<div class="info" id="s1-notice02">
					<b>Deployment Path:</b> <i><?php echo "{$GLOBALS['DUPX_ROOT']}"; ?></i>
					<br/><br/>
					There are currently <?php echo "<b>[{$scancount}]</b>"; ?>  items in the deployment path. These items will be overwritten if they also exist
					inside the archive file.  The notice is to prevent overwriting an existing site or trying to install on-top of one which
					can have un-intended results. <i>This notice shows if it detects more than 20 items.</i>
					<br/><br/>
					<b>Options:</b>
					<ul style="margin-bottom: 0">
						<li>If the archive was already manually extracted then <a href="javascript:void(0)" onclick="DUPX.getManaualArchiveOpt()">[Enable Manual Archive Extraction]</a></li>
						<li>If the files/directories are not the same as those in the archive then this notice can be ignored.</li>
						<li>Remove the files if they are not needed and refresh this page.</li>
					</ul>
				</div>

			<?php endif; ?>

            <!-- NOTICE 3 -->
            <div class="status <?php echo ($notice['03'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['03']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-notice03"><i class="fa fa-caret-right"></i> Package Age</div>
            <div class="info" id="s1-notice03">
                <?php echo "The package is {$fulldays} day(s) old. Packages older than 180 days might be considered stale"; ?>
            </div>

            <!-- NOTICE 4
            <div class="status <?php echo ($notice['04'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['04']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-notice04"><i class="fa fa-caret-right"></i> Placeholder</div>
            <div class="info" id="s1-notice04">
            </div>-->

            <!-- NOTICE 5
            <div class="status <?php echo ($notice['05'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['05']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-notice05"><i class="fa fa-caret-right"></i> OS Compatibility</div>
            <div class="info" id="s1-notice05">
            <?php
            $currentOS = PHP_OS;
            echo "The current OS (operating system) is '{$currentOS}'.  The package was built on '{$GLOBALS['DUPX_AC']->version_os}'.  Moving from one OS to another
				is typically very safe and normal, however if any issues do arise be sure that you don't have any items on your site that were OS specific";
            ?>
            </div>-->

            <!-- NOTICE 6 -->
            <div class="status <?php echo ($notice['06'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['06']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-notice06"><i class="fa fa-caret-right"></i> PHP Open Base</div>
            <div class="info" id="s1-notice06">
                <b>Open BaseDir:</b> <i><?php echo $notice['06'] == 'Good' ? "<i class='dupx-pass'>Disabled</i>" : "<i class='dupx-fail'>Enabled</i>"; ?></i>
                <br/><br/>

                If <a href="http://www.php.net/manual/en/ini.core.php#ini.open-basedir" target="_blank">open_basedir</a> is enabled and your
                having issues getting your site to install properly; please work with your host and follow these steps to prevent issues:
                <ol style="margin:7px; line-height:19px">
                    <li>Disable the open_basedir setting in the php.ini file</li>
                    <li>If the host will not disable, then add the path below to the open_basedir setting in the php.ini<br/>
                        <i style="color:maroon">"<?php echo str_replace('\\', '/', dirname( __FILE__ )); ?>"</i>
                    </li>
                    <li>Save the settings and restart the web server</li>
                </ol>
                Note: This warning will still show if you choose option #2 and open_basedir is enabled, but should allow the installer to run properly.  Please work with your
                hosting provider or server administrator to set this up correctly.
            </div>

            <!-- NOTICE 7 -->
            <div class="status <?php echo ($notice['07'] == 'Good') ? 'pass' : 'fail' ?>"><?php echo $notice['07']; ?></div>
            <div class="title" data-type="toggle" data-target="#s1-notice07"><i class="fa fa-caret-right"></i> PHP Timeout</div>
            <div class="info" id="s1-notice07">
                <b>Archive Size:</b> <?php echo DUPX_U::readableByteSize($arcSize) ?>  <small>(detection limit is set at <?php echo DUPX_U::readableByteSize($max_time_size) ?>) </small><br/>
                <b>PHP max_execution_time:</b> <?php echo "{$max_time_ini}"; ?> <small>(zero means not limit)</small> <br/>
                <b>PHP set_time_limit:</b> <?php echo ($max_time_zero) ? '<i style="color:green">Success</i>' : '<i style="color:maroon">Failed</i>' ?>
                <br/><br/>

                The PHP <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank">max_execution_time</a> setting is used to
                determine how long a PHP process is allowed to run.  If the setting is too small and the archive file size is too large then PHP may not have enough
                time to finish running before the process is killed causing a timeout.
                <br/><br/>

                Duplicator Pro attempts to turn off the timeout by using the
                <a href="http://php.net/manual/en/function.set-time-limit.php" target="_blank">set_time_limit</a> setting.   If this notice shows as a warning then it is
                still safe to continue with the install.  However, if a timeout occurs then you will need to consider working with the max_execution_time setting or extracting the
                archive file using the 'Manual Archive Extraction' method.
                Please see the	<a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-100-q" target="_blank">FAQ timeout</a> help link for more details.
            </div>
        </div>
    </div>
    <br/><br/>

    <!-- ====================================
    MULTISITE PANEL
    ==================================== -->
    <?php if($show_multisite) : ?>
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-multisite">
        <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Multisite</a>
    </div>
    <div id="s1-multisite">
        <input id="full-network" onclick="DUPX.enableSubsiteList(false);" type="radio" name="multisite-install-type" value="0" checked>
        <label for="full-network">Restore entire multisite network</label><br/>

        <input <?php if($multisite_disabled) { echo 'disabled';
    } ?> id="multisite-install-type" onclick="DUPX.enableSubsiteList(true);"  type="radio" name="multisite-install-type" value="1">
        <label for="multisite-install-type">Convert subsite
            <select id="subsite-id" name="subsite-id" style="width:200px" disabled>
                <?php foreach($archive_config->subsites as $subsite) : ?>
                <option value="<?php echo $subsite->id; ?>"><?php echo "{$subsite->name}" ?></option>
    <?php endforeach; ?>
            </select>
            into a standalone site<?php if($multisite_disabled) { echo '*';} ?>
        </label>
        <?php
        if($multisite_disabled)
        {
        $license_string = ' This installer was created with ';
        switch($archive_config->getLicenseType())
        {
        case DUPX_LicenseType::Unlicensed:
        $license_string .= "an Unlicensed copy of Duplicator Pro.";
        break;

        case DUPX_LicenseType::Personal:
        $license_string .= "a Personal license of Duplicator Pro.";
        break;

        case DUPX_LicenseType::Freelancer:
        $license_string .= "a Freelancer license of Duplicator Pro.";
        break;

        default:
        $license_string = '';
        }
        echo "<p class='note'>*Requires Business or Gold license. $license_string</p>";
        }
        ?>
    </div>
    <br/><br/>
<?php endif; ?>

    <!-- ====================================
    OPTIONS
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-adv-opts">
        <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Options</a>
    </div>
    <div id="s1-area-adv-opts" style="display:none">
        <div class="help-target">
            <a href="<?php echo $GLOBALS['_HELP_URL_PATH']; ?>#help-s1" target="_blank"><i class="fa fa-question-circle"></i></a>
        </div><br/>

        <div class="hdr-sub3">General</div>
        <table class="dupx-opts dupx-advopts">
			<?php if($archive_config->isZipArchive()): ?>
				<tr>
					<td>Extraction:</td>
					<td>

						<select id="archive_engine" name="archive_engine" size="3">
							<option value="manual">Manual Archive Extraction</option>
							<?php
							//ZIP-ARCHIVE
							if (!$zip_archive_enabled){
								echo '<option value="ziparchive" disabled="true">PHP ZipArchive (not detected on server)</option>';
							} elseif ($zip_archive_enabled &&!$shell_exec_zip_enabled) {
								echo '<option value="ziparchive" selected="true">PHP ZipArchive</option>';
							} else {
								echo '<option value="ziparchive">PHP ZipArchive</option>';
							}

							//SHELL-EXEC UNZIP
							if (!$shell_exec_zip_enabled){
								echo '<option value="shellexec_unzip" disabled="true">Shell Exec Unzip (not detected on server)</option>';
							} else {
								echo '<option value="shellexec_unzip" selected="true">Shell Exec Unzip</option>';
							}
							?>
						</select>
					</td>
				</tr>
            <?php else: ?>
				<input id="archive_engine" type="hidden" name="archive_engine" value="duparchive" />
			<?php endif; ?>
            <tr>
                <td>Permissions:</td>
                <td>
                    <input type="checkbox" name="set_file_perms" id="set_file_perms" value="1" onclick="jQuery('#file_perms_value').prop('disabled', !jQuery(this).is(':checked'));"/>
                    <label for="set_file_perms">All Files</label><input name="file_perms_value" id="file_perms_value" style="width:30px; margin-left:7px;" value="644" disabled> &nbsp;
                    <input type="checkbox" name="set_dir_perms" id="set_dir_perms" value="1" onclick="jQuery('#dir_perms_value').prop('disabled', !jQuery(this).is(':checked'));"/>
                    <label for="set_dir_perms">All Directories</label><input name="dir_perms_value" id="dir_perms_value" style="width:30px; margin-left:7px;" value="755" disabled>
                </td>
            </tr>
        </table><br/><br/>

        <div class="hdr-sub3">Advanced</div>
        <table class="dupx-opts dupx-advopts">
            <tr>
                <td>Config Files:</td>
                <td>
                    <input type="checkbox" name="retain_config" id="retain_config" value="1" />
                    <label for="retain_config" style="font-weight: normal">Retain original .htaccess, .user.ini and web.config</label>
                </td>
            </tr>

            <tr>
                <td>File Times:</td>
                <td>
                    <input type="radio" name="zip_filetime" id="zip_filetime_now" value="current" checked="checked" />
                    <label class="radio" for="zip_filetime_now" title='Set the files current date time to now'>Current</label> &nbsp;
                    <input type="radio" name="zip_filetime" id="zip_filetime_orginal" value="original" />
                    <label class="radio" for="zip_filetime_orginal" title="Keep the files date time the same">Original</label>
                </td>
            </tr>
            <tr>
                <td>Logging:</td>
                <td>
                    <input type="radio" name="logging" id="logging-light" value="1" checked="true"> <label for="logging-light" class="radio">Light</label> &nbsp;
                    <input type="radio" name="logging" id="logging-detailed" value="2"> <label for="logging-detailed" class="radio">Detailed</label> &nbsp;
                    <input type="radio" name="logging" id="logging-debug" value="3"> <label for="logging-debug" class="radio">Debug</label>
                </td>
            </tr>
			<?php if(!$archive_config->isZipArchive()): ?>
            <tr>
                <td>Client-side Kickoff:</td>
                <td>
                    <input type="checkbox" name="clientside_kickoff" id="clientside_kickoff" value="1" checked/>
                    <label for="clientside_kickoff" style="font-weight: normal">Browser drives the archive engine.</label>
                </td>
            </tr>
            <?php endif;?>
        </table>
    </div>
    <br/><br/>



    <!-- ====================================
    TERMS & NOTICES
    ==================================== -->
    <div class="hdr-sub1 toggle-hdr" data-type="toggle" data-target="#s1-area-warnings">
        <a href="javascript:void(0)"><i class="fa fa-plus-square"></i>Notices</a>
    </div>
    <div id="s1-area-warnings" style="display:none">
        <div id='s1-warning-area'>
            <div id="s1-warning-msg">
                <b>TERMS &amp; NOTICES</b> <br/><br/>

                <b>Disclaimer:</b>
                This plugin require above average technical knowledge. Please use it at your own risk and always back up your database and files beforehand Duplicator.
                If you're not sure about how to use this tool then please enlist the guidance of a technical professional.  <u>Always</u> test
                this installer in a sandbox environment before trying to deploy into a production setting.
                <br/><br/>

                <b>Database:</b>
                Do not connect to an existing database unless you are 100% sure you want to remove all of it's data. Connecting to a database that already exists will permanently
                DELETE all data in that database. This tool is designed to populate and fill a database with NEW data from a duplicated database using the SQL script in the
                package name above.
                <br/><br/>

                <b>Setup:</b>
                Only the archive and installer file should be in the install directory, unless you have manually extracted the package and selected the
                'Manual Archive Extraction' option under options. All other files will be OVERWRITTEN during install.  Make sure you have full backups of all your databases and files
                before continuing with an installation. Manual extraction requires that all contents in the package are extracted to the same directory as the installer file.
                Manual extraction is only needed when your server does not support the ZipArchive extension.  Please see the online help for more details.
                <br/><br/>

                <b>After Install:</b> When you are done with the installation you must remove the these files/directories:
                <ul>
                    <li>dpro-installer</li>
                    <li>installer.php</li>
                    <li>installer-data.sql</li>
                    <li>installer-backup.php</li>
                    <li>installer-log.txt</li>
                    <li>database.sql</li>
                </ul>

                These files contain sensitive information and should not remain on a production system for system integrity and security protection.
                <br/><br/>

                <b>License Overview</b><br/>
                Duplicator Pro is licensed under the GPL v3 https://www.gnu.org/licenses/gpl-3.0.en.html including the following disclaimers and limitation of liability.
                <br/><br/>

                <b>Disclaimer of Warranty</b><br/>
                THERE IS NO WARRANTY FOR THE PROGRAM, TO THE EXTENT PERMITTED BY APPLICABLE LAW. EXCEPT WHEN OTHERWISE STATED IN WRITING THE COPYRIGHT HOLDERS AND/OR OTHER PARTIES
                PROVIDE THE PROGRAM “AS IS” WITHOUT WARRANTY OF ANY KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
                FITNESS FOR A PARTICULAR PURPOSE. THE ENTIRE RISK AS TO THE QUALITY AND PERFORMANCE OF THE PROGRAM IS WITH YOU. SHOULD THE PROGRAM PROVE DEFECTIVE, YOU ASSUME
                THE COST OF ALL NECESSARY SERVICING, REPAIR OR CORRECTION.
                <br/><br/>

                <b>Limitation of Liability</b><br/>
                IN NO EVENT UNLESS REQUIRED BY APPLICABLE LAW OR AGREED TO IN WRITING WILL ANY COPYRIGHT HOLDER, OR ANY OTHER PARTY WHO MODIFIES AND/OR CONVEYS THE PROGRAM AS
                PERMITTED ABOVE, BE LIABLE TO YOU FOR DAMAGES, INCLUDING ANY GENERAL, SPECIAL, INCIDENTAL OR CONSEQUENTIAL DAMAGES ARISING OUT OF THE USE OR INABILITY TO USE THE
                PROGRAM (INCLUDING BUT NOT LIMITED TO LOSS OF DATA OR DATA BEING RENDERED INACCURATE OR LOSSES SUSTAINED BY YOU OR THIRD PARTIES OR A FAILURE OF THE PROGRAM TO
                OPERATE WITH ANY OTHER PROGRAMS), EVEN IF SUCH HOLDER OR OTHER PARTY HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES.
                <br/><br/>

            </div>
        </div>
    </div>
    <div id="s1-warning-check">
        <input id="accept-warnings" name="accpet-warnings" type="checkbox" onclick="DUPX.acceptWarning()" />
        <label for="accept-warnings">I have read and accept all terms &amp; notices <small style="font-style:italic">(required to continue)</small></label><br/>
    </div>
    <br/><br/><br/>
    <br/><br/>


<?php if (!$req_success || $arcCheck == 'Fail') : ?>
    <div class="s1-err-msg">
        <i>
            This installation will not be able to proceed until the archive and validation sections both pass. Please adjust your servers settings or contact your
            server administrator, hosting provider or visit the resources below for additional help.
        </i>
        <div style="padding:10px">
            &raquo; <a href="https://snapcreek.com/duplicator/docs/faqs-tech/" target="_blank">Technical FAQs</a> <br/>
            &raquo; <a href="https://snapcreek.com/support/docs/" target="_blank">Online Documentation</a> <br/>
        </div>
    </div>
<?php else : ?>
    <div class="footer-buttons" >
        <button id="s1-deploy-btn" type="button" title="<?php echo $agree_msg; ?>" onclick="DUPX.runExtraction()"  class="default-btn"> Next <i class="fa fa-caret-right"></i> </button>
    </div>
<?php endif; ?>

</form>


<!-- =========================================
VIEW: STEP 1 - AJAX RESULT
Auto Posts to view.step2.php
========================================= -->
<form id='s1-result-form' method="post" class="content-form" style="display:none">

    <div class="dupx-logfile-link"><a href="../installer-log.txt" target="dpro-installer">installer-log.txt</a></div>
    <div class="hdr-main">
        Step <span class="step">1</span> of 4: Extraction
    </div>

    <!--  POST PARAMS -->
    <div class="dupx-debug">
        <input type="hidden" name="view" value="step2" />
        <input type="hidden" name="archive_name" value="<?php echo $GLOBALS['FW_PACKAGE_NAME'] ?>" />
        <input type="hidden" name="logging" id="ajax-logging"  />
        <input type="hidden" name="retain_config" id="ajax-retain-config"  />
        <input type="hidden" name="json"    id="ajax-json" />
        <input type="hidden" name="subsite-id" id="ajax-subsite-id" value="-1" />
        <textarea id='ajax-json-debug' name='json_debug_view'></textarea>
        <input type='submit' value='manual submit'>
    </div>

    <!--  PROGRESS BAR -->
    <div id="progress-area">
        <div style="width:500px; margin:auto">
            <div style="font-size:1.7em; margin-bottom:20px"><i class="fa fa-circle-o-notch fa-spin"></i> Extracting Archive Files<span id="progress-pct"></span></div>
            <div id="progress-bar"></div>
            <h3> Please Wait...</h3><br/><br/>
            <i>Keep this window open during the extraction process.</i><br/>
            <i>This can take several minutes.</i>
        </div>
    </div>

    <!--  AJAX SYSTEM ERROR -->
    <div id="ajaxerr-area" style="display:none">
        <p>Please try again an issue has occurred.</p>
        <div style="padding: 0px 10px 10px 0px;">
            <div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the installer-log.txt file for more details.</div>
            <div style="text-align:center; margin:10px auto 0px auto">
                <input type="button" class="default-btn" onclick="DUPX.hideErrorResult()" value="&laquo; Try Again" /><br/><br/>
                <i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com/ticket' target='_blank'>snapcreek.com</a></i>
            </div>
        </div>
    </div>
</form>

<script>
    DUPX.getManaualArchiveOpt = function ()
    {
        $("html, body").animate({scrollTop: $(document).height()}, 1500);
        $("a[data-target='#s1-area-adv-opts']").find('i.fa').removeClass('fa-plus-square').addClass('fa-minus-square');
        $('#s1-area-adv-opts').show(1000);
        $('select#archive_engine').val('manual').focus();
    };

    DUPX.enableSubsiteList = function (enable)
    {
        if (enable) {
            $("#subsite-id").prop('disabled', false);
        } else {
            $("#subsite-id").prop('disabled', 'disabled');
        }
    };


    DUPX.runExtraction = function ()
    {
        var zipEnabled = <?php echo SnapLibStringU::boolToString($archive_config->isZipArchive()); ?>;

        if (zipEnabled) {
            DUPX.runZipExtraction();
        } else {
            DUPX.kickOffDupArchiveExtract();
        }
    };

    DUPX.updateProgressPercent = function (percent)
    {
        var percentString = '';

        if (percent > 0) {
            percentString = ' (' + percent + '%)';
        }

        $("#progress-pct").text(percentString);
    };

    DUPX.clearDupArchiveStatusTimer = function ()
    {
        if (DUPX.statusIntervalID != -1) {
            clearInterval(DUPX.statusIntervalID);
            DUPX.statusIntervalID = -1;
        }
    };

    DUPX.getCriticalFailureText = function(failures) {

        var retVal = null;

		if((failures !== null) && (typeof failures !== 'undefined')) {
			var len = failures.length;

			for(var j = 0; j < len; j++) {
				failure = failures[j];

				if(failure.isCritical) {
					retVal = failure.description;
					break;
				}
			}
		}

        return retVal;
    };

    DUPX.DAWSProcessingFailed = function(errorText) {

        DUPX.clearDupArchiveStatusTimer();
        $('#ajaxerr-data').html(errorText);
        DUPX.hideProgressBar();
    }

    DUPX.handleDAWSProcessingProblem = function(errorText, pingDAWS) {

        DUPX.DAWSFailureCount++;

        if(DUPX.DAWSFailureCount <= DUPX.Const.DAWS.MaxRetries) {
            var callback = DUPX.pingDAWS;

            if(pingDAWS) {
                console.log('!!!PING FAILURE #' + DUPX.DAWSFailureCount);
            } else {
                console.log('!!!KICKOFF FAILURE #' + DUPX.DAWSFailureCount);
                callback = DUPX.kickOffDupArchiveExtract;
            }

            DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
            console.log('Relaunching in ' + DUPX.Const.DAWS.RetryDelayInMs);
            setTimeout(callback, DUPX.Const.DAWS.RetryDelayInMs);
        }
        else {
            console.log('Too many failures.');

            DUPX.DAWSProcessingFailed(errorText);
        }
    };


    DUPX.handleDAWSCommunicationProblem = function(xHr, pingDAWS) {

        DUPX.DAWSFailureCount++;

        if(DUPX.DAWSFailureCount <= DUPX.Const.DAWS.MaxRetries) {

            var callback = DUPX.pingDAWS;

            if(pingDAWS) {
                console.log('!!!PING FAILURE #' + DUPX.DAWSFailureCount);
            } else {
                console.log('!!!KICKOFF FAILURE #' + DUPX.DAWSFailureCount);
                callback = DUPX.kickOffDupArchiveExtract;
            }

            console.log(xHr);

            DUPX.throttleDelay = 9;	// Equivalent of 'low' server throttling
            console.log('Relaunching in ' + DUPX.Const.DAWS.RetryDelayInMs);
            setTimeout(callback, DUPX.Const.DAWS.RetryDelayInMs);
        }
        else {
            console.log('Too many failures.');

            DUPX.ajaxCommunicationFailed(xHr);
        }
    };

// Will either query for status or push it to continue the extraction
    DUPX.pingDAWS = function ()
    {
        var request = new Object();

        var isClientSideKickoff = DUPX.isClientSideKickoff();

        console.log('Ping DAWS');

        if (isClientSideKickoff) {
            request.action = "expand";
            request.client_driven = 1;
			request.throttle_delay = DUPX.throttleDelay;
			request.worker_time = DUPX.Const.DAWS.PingWorkerTimeInSec;
        } else {
            request.action = "get_status";
        }

        console.log("Ping daws with action " + request.action);

        $.ajax({
            type: "POST",
            timeout: DUPX.Const.DAWS.PingWorkerTimeInSec * 2000, // Double worker time and convert to ms
            dataType: "json",
            url: DUPX.Const.DAWS.Url,
            data: JSON.stringify(request),
            success: function (data) {

                DUPX.DAWSFailureCount = 0;
				console.log("Resetting failure count");

                // DATA FIELDS
                // archive_offset
                // archive_size
                // failures
                // file_index
                // is_done
                // timestamp

                if (typeof (data) != 'undefined' && data.pass == 1) {

                    var status = data.status;

                    var percent = Math.round((status.archive_offset * 100.0) / status.archive_size);

                    DUPX.updateProgressPercent(percent);

                    var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

                    if (criticalFailureText === null) {
                        if (status.is_done) {

                            if(status.failures.length > 0) {

                                console.log(status.failures);

                                var errorMessage = "Problems during extract. These may be non-critical so continue with install.\n------\n";

                                var len = status.failures.length;

                                for(var j = 0; j < len; j++) {
                                    failure = status.failures[j];

                                    errorMessage += failure.subject + ":" + failure.description + "\n";
                                }

                                alert(errorMessage);
                            }

                            var dataJSON = JSON.stringify(data);

                            DUPX.clearDupArchiveStatusTimer();

                            // Don't stop for non-critical failures - just display those at the end

                            $("#ajax-logging").val($("input:radio[name=logging]:checked").val());
                            $("#ajax-retain-config").val($("#retain_config").is(":checked") ? 1 : 0);
                            $("#ajax-json").val(escape(dataJSON));

                            <?php if($show_multisite) : ?>
                            if ($("#full-network").is(":checked")) {
                                $("#ajax-subsite-id").val(-1);
                            } else {
                                $("#ajax-subsite-id").val($('#subsite-id').val());
                            }
                            <?php endif; ?>

                            <?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
                            setTimeout(function () {
                                $('#s1-result-form').submit();
                            }, 500);
                            <?php endif; ?>
                            $('#progress-area').fadeOut(1000);
                            //Failures aren't necessarily fatal - just record them for later display

                            $("#ajax-json-debug").val(dataJSON);
                        } else if (isClientSideKickoff) {
                            console.log('Continue ping DAWS in 500');
                            setTimeout(DUPX.pingDAWS, 500);
                        }
                    }
                    else {
                        // If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                        var errorString = 'Error Processing Step 1<br/>';

                        errorString += criticalFailureText;

                        DUPX.DAWSProcessingFailed(errorString);
                    }
                } else {
                    var errorString = 'Error Processing Step 1<br/>';
                    errorString += data.error;

                    DUPX.handleDAWSProcessingProblem(errorString, true);
                }
            },
            error: function (xHr, textStatus) {
                console.log('AJAX error. textStatus=');
                console.log(textStatus);
                DUPX.handleDAWSCommunicationProblem(xHr, true);
            }
        });
    };


    DUPX.isClientSideKickoff = function()
    {
        return $('#clientside_kickoff').is(':checked');
    }

    DUPX.kickOffDupArchiveExtract = function ()
    {
        console.log('kickoff dup archive extraction');
        var $form = $('#s1-input-form');

        var request = new Object();

        var isClientSideKickoff = DUPX.isClientSideKickoff();

        request.action = "start_expand";
        request.archive_filepath = '<?php echo $archive_path; ?>';
        request.restore_directory = '<?php echo $root_path; ?>';
        request.worker_time = DUPX.Const.DAWS.KickoffWorkerTimeInSec;
        request.client_driven = isClientSideKickoff ? 1 : 0;
		request.throttle_delay = DUPX.throttleDelay;

        request.filtered_directories = ['dpro-installer'];

    	var requestString = JSON.stringify(request);

        if (!isClientSideKickoff) {

            console.log('Setting timer');
            // If server is driving things we need to poll the status
            DUPX.statusIntervalID = setInterval(DUPX.pingDAWS, DUPX.Const.DAWS.StatusPeriodInMS);
        }

        $.ajax({
            type: "POST",
            timeout: DUPX.Const.DAWS.KickoffWorkerTimeInSec * 2000,  // Double worker time and convert to ms
            dataType: "json",
            url: DUPX.Const.DAWS.Url,
            data: requestString,
            beforeSend: function () {
                DUPX.showProgressBar();
                $form.hide();
                $('#s1-result-form').show();
                DUPX.updateProgressPercent(0);
            },
            success: function (data) {

                if (typeof (data) != 'undefined' && data.pass == 1) {

                    var criticalFailureText = DUPX.getCriticalFailureText(status.failures);

                    if (criticalFailureText === null) {

                        var dataJSON = JSON.stringify(data);

                        //RSR TODO:Need to check only for FATAL errors right now - have similar failure check as in pingdaws
                        DUPX.DAWSFailureCount = 0;
                        console.log("Resetting failure count");

                        $("#ajax-json-debug").val(dataJSON);
                        if (typeof (data) != 'undefined' && data.pass == 1) {

                            if (isClientSideKickoff) {
                                console.log('Initial ping DAWS in 500');
                                setTimeout(DUPX.pingDAWS, 500);
                            }

                        } else {
                            $('#ajaxerr-data').html('Error Processing Step 1');
                            DUPX.hideProgressBar();
                        }
                    } else {
                        // If we get a critical failure it means it's something we can't recover from so no purpose in retrying, just fail immediately.
                        var errorString = 'Error Processing Step 1<br/>';

                        errorString += criticalFailureText;

                        DUPX.DAWSProcessingFailed(errorString);
                    }
                } else {
                    var errorString = 'Error Processing Step 1<br/>';
                    errorString += data.error;

                    DUPX.handleDAWSProcessingProblem(errorString, false);
                }
            },
            error: function (xHr, textStatus) {

                console.log('AJAX error. textStatus=');
                console.log(textStatus);
                DUPX.handleDAWSCommunicationProblem(xHr, false);
            }
        });
    };


    /**
     * Performs Ajax post to extract files and create db
     */
    DUPX.runZipExtraction = function ()
    {
        var $form = $('#s1-input-form');

        //1800000 = 30 minutes
        //If the extraction takes longer than 30 minutes then user
        //will probably want to do a manual extraction or even FTP
        $.ajax({
            type: "POST",
            timeout: 1800000,
            dataType: "json",
            url: window.location.href,
            data: $form.serialize(),
            beforeSend: function () {
                DUPX.showProgressBar();
                $form.hide();
                $('#s1-result-form').show();
            },
            success: function (data) {
                var dataJSON = JSON.stringify(data);
                $("#ajax-json-debug").val(dataJSON);
                if (typeof (data) != 'undefined' && data.pass == 1) {
                    $("#ajax-logging").val($("input:radio[name=logging]:checked").val());
                    $("#ajax-retain-config").val($("#retain_config").is(":checked") ? 1 : 0);
                    $("#ajax-json").val(escape(dataJSON));

                    <?php if($show_multisite) : ?>
                    if ($("#full-network").is(":checked")) {
                        $("#ajax-subsite-id").val(-1);
                    } else {
                        $("#ajax-subsite-id").val($('#subsite-id').val());
                    }
                    <?php endif; ?>

                    <?php if (!$GLOBALS['DUPX_DEBUG']) : ?>
                    setTimeout(function () {
                        $('#s1-result-form').submit();
                    }, 500);
                    <?php endif; ?>
                    $('#progress-area').fadeOut(1000);
                } else {
                    $('#ajaxerr-data').html('Error Processing Step 1');
                    DUPX.hideProgressBar();
                }
            },
            error: function (xHr) {

                DUPX.ajaxCommunicationFailed(xHr);
            }
        });
    };

    DUPX.ajaxCommunicationFailed = function (xhr)
    {
        var status = "<b>Server Code:</b> " + xhr.status + "<br/>";
        status += "<b>Status:</b> " + xhr.statusText + "<br/>";
        status += "<b>Response:</b> " + xhr.responseText + "<hr/>";

        if ((xhr.status == 403) || (xhr.status == 500)) {
            status += "<b>Recommendation:</b><br/>";
            status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-120-q'>this FAQ item</a> for possible resolutions.<br/><br/>"
        } else if ((xhr.status == 0) || (xhr.status == 200)) {
            status += "<b>Recommendation:</b><br/>";
            status += "Possible server timeout! Performing a 'Manual Extraction' can avoid timeouts.";
            status += "See <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q'>this FAQ item</a> for a complete overview.<br/><br/>"
        } else {
            status += "<b>Additional Resources:</b><br/> ";
            status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>";
            status += "&raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>";
        }

        $('#ajaxerr-data').html(status);
        DUPX.hideProgressBar();
    };

    /** Go back on AJAX result view */
    DUPX.hideErrorResult = function ()
    {
        $('#s1-result-form').hide();
        $('#s1-input-form').show(200);
    }

    /**
     * Accetps Usage Warning */
    DUPX.acceptWarning = function ()
    {
        if ($("#accept-warnings").is(':checked')) {
            $("#s1-deploy-btn").removeAttr("disabled");
            $("#s1-deploy-btn").removeAttr("title");
        } else {
            $("#s1-deploy-btn").attr("disabled", "true");
            $("#s1-deploy-btn").attr("title", "<?php echo $agree_msg; ?>");
        }
    };



//DOCUMENT LOAD
    $(document).ready(function ()
    {
        DUPX.Const.DAWS = new Object();

		DUPX.Const.DAWS.Url = document.URL.substr(0,document.URL.lastIndexOf('/')) + '/lib/dup_archive/daws/daws.php';
        DUPX.Const.DAWS.StatusPeriodInMS = 10000;
        DUPX.Const.DAWS.PingWorkerTimeInSec = 9;
        DUPX.Const.DAWS.KickoffWorkerTimeInSec = 6; // Want the initial progress % to come back quicker
        DUPX.Const.DAWS.MaxRetries = 10;
        DUPX.Const.DAWS.RetryDelayInMs = 8000;

        DUPX.statusIntervalID = -1;
        DUPX.DAWSFailureCount = 0;
		DUPX.throttleDelay = 0;

        //INIT Routines
        $("*[data-type='toggle']").click(DUPX.toggleClick);
        $("#tabs").tabs();
        DUPX.acceptWarning();
		$('#set_file_perms').trigger("click");
		$('#set_dir_perms').trigger("click");

		<?php echo ($arcCheck == 'Fail') ? "$('#s1-area-archive-file-link').trigger('click');" : ""; ?>
		<?php echo (!$all_success) ? "$('#s1-area-sys-setup-link').trigger('click');" : ""; ?>
    });
</script>
