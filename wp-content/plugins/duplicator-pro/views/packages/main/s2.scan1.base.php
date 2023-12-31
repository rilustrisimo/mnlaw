<?php

wp_enqueue_script('dup-pro-handlebars');
require_once (DUPLICATOR_PRO_PLUGIN_PATH . 'classes/class.io.php');
require_once (DUPLICATOR_PRO_PLUGIN_PATH . 'classes/package/class.pack.php');
require_once (DUPLICATOR_PRO_PLUGIN_PATH . 'classes/entities/class.global.entity.php');
require_once (DUPLICATOR_PRO_PLUGIN_PATH . 'classes/entities/class.storage.entity.php');

if(empty($_POST)) {
    // Refresh 'fix'
    $redirect = self_admin_url('admin.php?page=duplicator-pro&tab=packages&inner_page=new1');
    die("<script>window.location.href='{$redirect}';</script>");
}

global $wp_version;
$global	 = DUP_PRO_Global_Entity::get_instance();
$Package = null;

if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'template-create') {

	$storage_ids = isset($_REQUEST['_storage_ids']) ? $_REQUEST['_storage_ids'] : array();
	$template_id = (int) $_REQUEST['template_id'];
	$template	 = DUP_PRO_Package_Template_Entity::get_by_id($template_id);

	// always set the manual template since it represents the last thing that was run
	DUP_PRO_Package::set_manual_template_from_post($_REQUEST);

	$global->manual_mode_storage_ids = $storage_ids;
	$global->save();

	$name_chars	 = array(".", "-");
	$name		 = ( isset($_REQUEST['package-name']) && !empty($_REQUEST['package-name'])) ? $_REQUEST['package-name'] : DUP_PRO_Package::get_default_name();
	$name		 = substr(sanitize_file_name($name), 0, 40);
	$name		 = str_replace($name_chars, '', $name);

	DUP_PRO_Package::set_temporary_package_from_template_and_storages($template_id, $storage_ids, $name);
}

$Package = DUP_PRO_Package::get_temporary_package();
$package_list_url = 'admin.php?page=' . DUP_PRO_Constants::$PACKAGES_SUBMENU_SLUG;
$archive_export_onlydb = isset($_POST['export-onlydb']) ? 1 :0;

switch ($global->archive_build_mode) {

	case DUP_PRO_Archive_Build_Mode::ZipArchive:
		$archive_build_mode = ($global->ziparchive_mode == DUP_PRO_ZipArchive_Mode::Multithreaded)
			? DUP_PRO_U::__("ZipArchive: Multi-Threaded")
			: DUP_PRO_U::__("ZipArchive: Single-Threaded");
		break;

	case DUP_PRO_Archive_Build_Mode::DupArchive :
		$archive_build_mode = DUP_PRO_U::__('DupArchive (*BETA*)');
        break;
    
	default:
		$archive_build_mode = DUP_PRO_U::__("Shell Exec");
		break;
}

?>

<style>
	/*PROGRESS-BAR - RESULTS - ERROR */
	form#form-duplicator {text-align:center; max-width:650px; min-height:200px; margin:0px auto 0px auto; padding:0px;}
	div.dup-progress-title {font-size:22px; padding:5px 0 20px 0; font-weight:bold}
	div#dup-msg-success {padding:0 5px 5px 5px; text-align:left}
	div#dup-msg-success div.details {padding:10px 15px 10px 15px; margin:5px 0 15px 0; background:#fff; border-radius:5px; border:1px solid #ddd;box-shadow:0 8px 6px -6px #999; }
	div#dup-msg-success div.details-title {font-size:20px; border-bottom:1px solid #dfdfdf; padding:5px; margin:0 0 10px 0; font-weight:bold}
	div#dup-msg-success-subtitle {color:#999; margin:0; font-size:11px}
	div.dup-scan-filter-status {display:inline; float:right; font-size:11px; margin-right:10px; color:#630f0f;}
	div#dup-msg-error {color:#A62426; padding:5px; max-width:790px;}
	div#dup-msg-error-response-text { max-height:500px; overflow-y:scroll; border:1px solid silver; border-radius:3px; padding:10px;background:#fff}
	div.dup-hdr-error-details {text-align:left; margin:20px 0}
	i[data-tooltip].fa-question-circle {color:#555}

	/*SCAN ITEMS: Sections */
	div.scan-header { font-size:16px; padding:7px 5px 5px 7px; font-weight:bold; background-color:#E0E0E0; border-bottom:0px solid #C0C0C0 }
	div.scan-item {border:1px solid #E0E0E0; border-bottom:none;}
	div.scan-item-first { border-top-right-radius:4px; border-top-left-radius:4px}
	div.scan-item-last {border-bottom:1px solid #E0E0E0}
	div.scan-item div.title {background-color:#F1F1F1; width:100%; padding:4px 0 4px 0; cursor:pointer; height:20px;}
	div.scan-item div.title:hover {background-color:#ECECEC;}
	div.scan-item div.text {font-weight:bold; font-size:14px; float:left;  position:relative; left:10px}
	div.scan-item div.badge {float:right; border-radius:5px; color:#fff; min-width:40px; text-align:center; position:relative; right:10px; font-size:12px; padding:0 3px 0 3px}
	div.scan-item div.badge-pass {background:green;}
	div.scan-item div.badge-warn {background:#630f0f;}
	div.scan-item div.info {display:none; padding:10px; background:#fff}
	div.scan-good {display:inline-block; color:green;font-weight:bold;}
	div.scan-warn {display:inline-block; color:#630f0f;font-weight:bold;}
	div.dup-more-details {float:right; font-size:14px}
	div.dup-more-details:hover {color:#777; cursor:pointer}
	div.dup-more-details a {color:#222}
	div.dup-more-details a:hover {color:#777}

	/*DIALOG WINDOWS*/
	div#arc-details-dlg {font-size:12px}
	div#arc-details-dlg h2 {margin:0; padding:0 0 5px 0; border-bottom:1px solid #dfdfdf;}
	div#arc-details-dlg hr {margin:3px 0 10px 0}
	div#arc-details-dlg div.filter-area {height:260px; overflow-y:scroll; border:1px solid #dfdfdf; padding:8px; margin:2px 0}
	div#arc-details-dlg div.file-info {padding:0 0 10px 15px; width:500px; white-space:nowrap;}
	div#arc-details-dlg div.info{line-height:18px}
	div#arc-details-dlg div.info label{font-size:12px !important; font-weight: bold; display: inline-block; min-width: 100px}

	div#arc-paths-dlg textarea.path-dirs,
		textarea.path-files {font-size:12px; border: 1px solid silver; padding: 10px; background: #fff; margin:5px; height:125px; width:100%; white-space:pre}
	div#arc-paths-dlg div.copy-button {float:right;}
	div#arc-paths-dlg div.copy-button button {font-size:12px}

	/*FILES */
	div#data-arc-size1 {display:inline-block; float:right; font-size:11px; margin-right:5px;}
	i.data-size-help { float:right; margin-right:5px; display:block; font-size:11px}
	div.hb-files-style div.container {border:1px solid #E0E0E0; border-radius:4px; margin:5px 0 10px 0}
	div.hb-files-style div.container b {font-weight:bold}
	div.hb-files-style div.container div.divider {margin-bottom:2px; font-weight:bold}
	div.hb-files-style div.data {padding:8px; line-height:21px; height:175px; overflow-y:scroll; }
	div.hb-files-style div.hdrs {background:#fff; padding:0 4px 4px 6px; border-bottom:1px solid #E0E0E0; font-weight:bold}
	div.hb-files-style div.hdrs sup i.fa {font-size:11px}
	div.hb-files-style div.hdrs-up-down {float:right;  margin:2px 12px 0 0}
	div.hb-files-style i.dup-nav-toggle:hover {cursor:pointer; color:#999}
	div.hb-files-style div.directory {margin-left:12px}
	div.hb-files-style div.directory i.size {font-size:11px;  font-style:normal; display:inline-block; min-width:50px}
	div.hb-files-style div.directory i.count {font-size:11px; font-style:normal; display:inline-block; min-width:20px}
	div.hb-files-style div.directory i.empty {width:15px; display:inline-block}
	div.hb-files-style div.directory i.dup-nav {cursor:pointer}
	div.hb-files-style div.directory i.fa {width:8px}
	div.hb-files-style div.directory i.chk-off {width:20px; color:#777; cursor: help; margin:0; font-size:1.25em}
	div.hb-files-style div.directory label {font-weight:bold; cursor:pointer; vertical-align:top;display:inline-block; width:475px; white-space: nowrap; overflow:hidden; text-overflow:ellipsis;}
	div.hb-files-style div.directory label:hover {color:#025d02}
	div.hb-files-style div.files {padding:2px 0 0 35px; font-size:12px; display:none; line-height:18px}
	div.hb-files-style div.files i.size {font-style:normal; display:inline-block; min-width:50px}
	div.hb-files-style div.files label {font-weight: normal; font-size:11px; vertical-align:top;display:inline-block;width:450px; white-space: nowrap; overflow:hidden; text-overflow:ellipsis;}
	div.hb-files-style div.files label:hover {color:#025d02; cursor: pointer}
	div.hb-files-style div.apply-btn {text-align:right; margin: 1px 0 10px 0}

	div#size-more-details {display:none; margin:5px 0 20px 0; border:1px solid #dfdfdf; padding:8px; border-radius: 4px; background-color: #F1F1F1}
	div#size-more-details ul {list-style-type:circle; padding-left:20px; margin:0}
	div#size-more-details li {margin:0}

    /* 	SERVER-CHECKS */
    div.dup-scan-title {display:inline-block;  padding:1px; font-weight: bold;}
    div.dup-scan-title a {display:inline-block; min-width:200px; padding:3px; }
	div.dup-scan-title a:focus {outline: 1px solid #fff; box-shadow: none}
    div.dup-scan-title div {display:inline-block;  }
    div.dup-scan-info {display:none;}
    div.dup-scan-good {display:inline-block; color:green;font-weight: bold;}
    div.dup-scan-warn {display:inline-block; color:#AF0000;font-weight: bold;}
    span.dup-toggle {float:left; margin:0 2px 2px 0; }

    /*DATABASE*/
    table.dup-scan-db-details {line-height: 14px; margin:5px 0px 0px 20px;  width:98%}
    table.dup-scan-db-details td {padding:2px;}
    table.dup-scan-db-details td:first-child {font-weight: bold;  white-space: nowrap; width:105px}
    div#dup-scan-db-info {margin-top:5px}
	div#data-db-tablelist {max-height:250px; overflow-y:scroll; border:1px solid silver; padding:8px; background: #efefef; border-radius: 4px}
	div#data-db-tablelist td{padding:0 5px 3px 20px; min-width:100px}
    div#data-db-size1 {display: inline-block; float:right; font-size:11px; margin-right:5px; font-style: italic}
    /*FILES */
    div#data-arc-size1 {display: inline-block; float:right; font-size:11px; margin-right:5px; font-style: italic}
	i.data-size-help { float:right; margin-right:5px; display: block; font-size: 11px}

    /*Footer*/
    div.dup-button-footer {text-align:center; margin:0}
</style>


<!-- ====================
TOOL-BAR -->
<table class="dpro-edit-toolbar">
    <tr>
        <td>
            <div id="dup-wiz">
                <div id="dup-wiz-steps">
                    <div class="completed-step"><a>1-<?php DUP_PRO_U::_e('Setup'); ?></a></div>
                    <div class="active-step"><a>2-<?php DUP_PRO_U::_e('Scan'); ?> </a></div>
                    <div><a>3-<?php DUP_PRO_U::_e('Build'); ?> </a></div>
                </div>
                <div id="dup-wiz-title" style="white-space: nowrap">
                    <?php DUP_PRO_U::_e('Step 2: System Scan'); ?>
                </div> 
            </div>	
        </td>
        <td>
            <a href="<?php echo $packages_tab_url; ?>" class="add-new-h2"><i class="fa fa-archive"></i> <?php DUP_PRO_U::_e('Packages'); ?></a>
            <span> <?php _e("Create New"); ?></span>
        </td>
    </tr>
</table>
<hr class="dpro-edit-toolbar-divider"/>


<form id="form-duplicator" method="post" action="<?php echo $package_list_url ?>">
	<input type="hidden" name="create_from_temp" value="1" />

	<div id="dup-progress-area">

		<!--  PROGRESS BAR -->
		<div id="dup-progress-bar-area">
			<div style="font-size:1.7em; margin-bottom:20px"><i class="fa fa-circle-o-notch fa-spin"></i> <?php DUP_PRO_U::_e('Scanning Site'); ?></div>
			<div id="dup-progress-bar"></div>
			<b><?php DUP_PRO_U::_e('Please Wait...'); ?></b><br/><br/>
			<i><?php DUP_PRO_U::_e('Keep this window open during the scan process.'); ?></i><br/>
			<i><?php DUP_PRO_U::_e('This can take several minutes.'); ?></i><br/>
		</div>

		<!--  SCAN DETAILS REPORT -->
		<div id="dup-msg-success" style="display:none">
			<div style="text-align:center">
				<div class="dup-hdr-success"><i class="fa fa-check-square-o"></i> <?php DUP_PRO_U::_e('Scan Complete'); ?></div>
				<div id="dup-msg-success-subtitle">
					<?php DUP_PRO_U::_e("Process Time:"); ?> <span id="data-rpt-scantime"></span>
				</div>
			</div>
			<div class="details">
				<?php
					include('s2.scan2.server.php');
					echo '<br/>';
					include('s2.scan3.archive.php');
				?>
			</div>
		</div>

		<!--  ERROR MESSAGE -->
		<div id="dup-msg-error" style="display:none">
			<div class="dup-hdr-error"><i class="fa fa-exclamation-circle"></i> <?php DUP_PRO_U::_e('Scan Error'); ?></div>
			<i><?php DUP_PRO_U::_e('Please try again!'); ?></i><br/>
			<div style="text-align:left">
				<b><?php DUP_PRO_U::_e("Server Status:"); ?></b> &nbsp;
				<div id="dup-msg-error-response-status" style="display:inline-block"></div><br/>

				<b><?php DUP_PRO_U::_e("Error Message:"); ?></b>
				<div id="dup-msg-error-response-text"></div>
			</div>
		</div>
	</div>

	<div class="dup-button-footer" style="display:none">
		<input type="button" value="&#9664; <?php DUP_PRO_U::_e("Back") ?>" onclick="window.location.assign('?page=duplicator-pro&tab=packages&inner_page=new1')" class="button button-large" />
		<input type="button" value="<?php DUP_PRO_U::_e("Rescan") ?>" onclick="  DupPro.Pack.reRunScanner()" class="button button-large" />
		<input type="submit" onclick="jQuery('#form-duplicator').submit();jQuery('#dup-build-button').prop('disabled', true);return false;" class="button button-primary button-large" id="dup-build-button" value='<?php DUP_PRO_U::_e("Build")?> &#9654'/>
	</div>
</form>

<script>
jQuery(document).ready(function ($) 
{
	DupPro.Pack.WebServiceStatus = {
		Pass: 1,
		Warn: 2,
		Error: 3,
		Incomplete: 4,
		ScheduleRunning: 5
	}

	DupPro.Pack.runScanner = function () {
		var input = {action: 'duplicator_pro_package_scan'}

		$.ajax({
			type: "POST",
			cache: false,
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: input,
			complete: function () {},
			success: function (data) {

				var data    = data || new Object();
				var status  = data.Status  || 3;
				var message = data.Message || "Unable to read JSON from service. <br/> See: /wp-admin/admin-ajax.php?action=duplicator_pro_package_scan";
				console.log(data);

				if (status == DupPro.Pack.WebServiceStatus.Pass) {
					DupPro.Pack.loadScanData(data);
					 $('.dup-button-footer').show();
				} else if (status == DupPro.Pack.WebServiceStatus.ScheduleRunning) {
					// as long as its just saying that someone blocked us keep trying
					console.log('retrying scan in 300 ms...');
					setTimeout(DupPro.Pack.runScanner, 300);
				} else {
					message  = '<b><?php DUP_PRO_U::_e("Please Retry:") ?></b><br/>';
					message += '<?php DUP_PRO_U::_e("Unable to perform a full scan and read JSON file, please try the following actions.") ?><br/>';
					message += '<?php DUP_PRO_U::_e("1. Go back and create a root path directory filter to validate the site is scan-able.") ?><br/>';
					message += '<?php DUP_PRO_U::_e("2. Continue to add/remove filters to isolate which path is causing issues.") ?><br/>';
					message += '<?php DUP_PRO_U::_e("3. This message will go away once the correct filters are applied.") ?><br/><br/>';

					message += '<b><?php DUP_PRO_U::_e("Common Issues:") ?></b><br/>';
					message += '<?php DUP_PRO_U::_e("- On some budget hosts scanning over 30k files can lead to timeout/gateway issues. Consider scanning only your main WordPress site and avoid trying to backup other external directories.") ?><br/>';
					message += '<?php DUP_PRO_U::_e("- Symbolic link recursion can cause timeouts.  Ask your server admin if any are present in the scan path.  If they are add the full path as a filter and try running the scan again.") ?><br/><br/>';

					message += '<b><?php DUP_PRO_U::_e("Details:") ?></b><br/>';
					message += '<?php DUP_PRO_U::_e("JSON Service:") ?> /wp-admin/admin-ajax.php?action=duplicator_pro_package_scan<br/>';
					message += '<?php DUP_PRO_U::_e("Scan Path:") ?> [<?php echo rtrim(DUPLICATOR_PRO_WPROOTPATH, '/'); ?>]<br/><br/>';

					$('#dup-progress-bar-area, #dup-build-button').hide();
					$('#dup-msg-error-response-status').html(status);
					$('#dup-msg-error-response-text').html(message);
					$('#dup-msg-error').show();
					$('.dup-button-footer').show();
				}
			},
			error: function (data) {
				var status = data.status + ' -' + data.statusText;
				$('#dup-progress-bar-area, #dup-build-button').hide();
				$('#dup-msg-error-response-status').html(status)
				$('#dup-msg-error-response-text').html(data.responseText);
				$('#dup-msg-error, .dup-button-footer').show();
				console.log(data);
			}
		});
	}

	DupPro.Pack.reRunScanner = function ()
	{
		$('#dup-msg-success,#dup-msg-error,.dup-button-footer').hide();
		$('#dup-progress-bar-area').show();
		DupPro.Pack.runScanner();
	}

	DupPro.Pack.loadScanData = function (data)
	{
		try {
			var errMsg = "unable to read";
			$('#dup-progress-bar-area').hide();

			//****************
			//REPORT
			var base = $('#data-rpt-scanfile').attr('href');
			$('#data-rpt-scanfile').attr('href', base + '&scanfile=' + data.RPT.ScanFile);
			$('#data-rpt-scantime').text(data.RPT.ScanTime || 0);

			DupPro.Pack.intServerData(data);
			DupPro.Pack.initArchiveFilesData(data);

			//Addon Sites
			$('#data-arc-status-addonsites').html(DupPro.Pack.setScanStatus(data.ARC.Status.AddonSites));
			html = '<?php DUP_PRO_U::_e("No add on sites found.") ?>';
			if (data.ARC.FilterInfo.Dirs.AddonSites !== undefined && data.ARC.FilterInfo.Dirs.AddonSites.length > 0) {
				$("#addonsites-block").show();
				html = '';
				$.each(data.ARC.FilterInfo.Dirs.AddonSites, function (key, val) {
					html += '<?php DUP_PRO_U::_e("SITE PATH") ?> ' + key + ':<br/> &nbsp; &nbsp; <i class="fa fa-wordpress"></i> ' + val + '<br/>';
				});
			}
			$('#data-arc-addonsites-data').html(html);
			$('#dup-msg-success').show();
			
			//****************
			//DATABASE
			var html = "";
			var DB_TableRowMax  = <?php echo DUPLICATOR_PRO_SCAN_DB_TBL_ROWS; ?>;
			var DB_TableSizeMax = <?php echo DUPLICATOR_PRO_SCAN_DB_TBL_SIZE; ?>;
			if (data.DB.Status.Success) {
				$('#data-db-status-size1').html(DupPro.Pack.setScanStatus(data.DB.Status.Size));
				$('#data-db-size1').text(data.DB.Size || errMsg);
				$('#data-db-size2').text(data.DB.Size || errMsg);
				$('#data-db-rows').text(data.DB.Rows || errMsg);
				$('#data-db-tablecount').text(data.DB.TableCount || errMsg);
				//Table Details
				if (data.DB.TableList == undefined || data.DB.TableList.length == 0) {
					html = '<?php DUP_PRO_U::_e("Unable to report on any tables") ?>';
				} else {
					$.each(data.DB.TableList, function(i) {
						html += '<b>' + i  + '</b><br/>';
						html += '<table><tr>';
						$.each(data.DB.TableList[i], function(key,val) {
							switch(key) {
								case 'Case':
									color = (val == 1) ? 'red' : 'black';
									html += '<td style="color:' + color + '">Uppercase: ' + val + '</td>';
									break;
								case 'Rows':
									color = (val > DB_TableRowMax) ? 'red' : 'black';
									html += '<td style="color:' + color + '">Rows: ' + val + '</td>';
									break;
								case 'USize':
									color = (parseInt(val) > DB_TableSizeMax) ? 'red' : 'black';
									html += '<td style="color:' + color + '">Size: ' + data.DB.TableList[i]['Size'] + '</td>';
									break;
							}
						});
						html += '</tr></table>';
					});
				}
				$('#data-db-tablelist').append(html);
			} else {
				html = '<?php DUP_PRO_U::_e("Unable to report on database stats") ?>';
				$('#dup-scan-db').html(html);
			}

		}
		catch(err) {
			err += '<br/> Please try again!'
			$('#dup-msg-error-response-status').html("n/a")
			$('#dup-msg-error-response-text').html(err);
			$('#dup-msg-error, .dup-button-footer').show();
			$('#dup-build-button').hide();
		}
	}

	//Toggles each scan item to hide/show details
	DupPro.Pack.toggleScanItem = function(item)
	{
		var $info = $(item).parents('div.scan-item').children('div.info');
		var $text = $(item).find('div.text i.fa');
		if ($info.is(":hidden")) {
			$text.addClass('fa-caret-down').removeClass('fa-caret-right');
			$info.show();
		} else {
			$text.addClass('fa-caret-right').removeClass('fa-caret-down');
			$info.hide(250);
		}
	}

	//Set Good/Warn Badges and checkboxes
	DupPro.Pack.setScanStatus = function(status)
	{
		var result;
		switch (status) {
			case false :    result = '<div class="scan-warn"><i class="fa fa-exclamation-triangle"></i></div>'; break;
			case 'Warn' :   result = '<div class="badge badge-warn">Notice</div>'; break;
			case true :     result = '<div class="scan-good"><i class="fa fa-check"></i></div>'; break;
			case 'Good' :   result = '<div class="badge badge-pass">Good</div>'; break;
			default :
				result = 'unable to read';
		}
		return result;
	}

	//Page Init:
	DupPro.UI.AnimateProgressBar('dup-progress-bar');
	DupPro.Pack.runScanner();

});
</script>