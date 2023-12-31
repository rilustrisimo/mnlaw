<?php

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/ctrls/ctrl.base.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.scan.check.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/utilities/class.u.json.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/package/class.pack.php');
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/entities/class.package.template.entity.php');

/**
 * Controller for Tools
 * @package Dupicator\ctrls
 */
class DUP_PRO_CTRL_Package extends DUP_PRO_CTRL_Base
{
	/**
     *  Init this instance of the object
     */
	function __construct()
	{
		add_action('wp_ajax_DUP_PRO_CTRL_Package_addQuickFilters', array($this, 'addQuickFilters'));
	}


	/**
     * Removed all reserved installer files names
	 *
	 * @param string $_POST['dir_paths']		A semi-colon seperated list of dir paths
	 * @param string $_POST['file_paths']		A semi-colon seperated list of file paths
	 *
	 * @return string	Returns all of the active directory filters as a ";" seperated string
     */
	public function addQuickFilters($post)
	{
		/* @var $template DUP_PRO_Package_Template_Entity*/

		$post = $this->postParamMerge($post);
		check_ajax_referer($post['action'], 'nonce');
		$result = new DUP_PRO_CTRL_Result($this);

		try {
			//CONTROLLER LOGIC

            // Need to update both the template and the temporary package because:
            // 1) We need to preserve preferences of this build for future manual builds - the manual template is used for this.
            // 2) Temporary package is used during this build - keeps all the settings/storage information.  Will be inserted into the package table after they ok the scan results.
			$template = DUP_PRO_Package_Template_Entity::get_manual_template();

			$template->archive_filter_dirs = DUP_PRO_Archive::parseDirectoryFilter("{$template->archive_filter_dirs};{$post['dir_paths']}");
			$template->archive_filter_files = DUP_PRO_Archive::parseFileFilter("{$template->archive_filter_files};{$post['file_paths']}");
            $template->archive_filter_on = 1;
		
            $template->save();

            /* @var $temporary_package DUP_PRO_Package */
            $temporary_package = DUP_PRO_Package::get_temporary_package();

            $temporary_package->Archive->FilterDirs = $template->archive_filter_dirs;
            $temporary_package->Archive->FilterFiles = $template->archive_filter_files;
            $temporary_package->Archive->FilterOn = 1;

            $temporary_package->set_temporary_package();

			//Result
			$payload['filter-dirs'] = $temporary_package->Archive->FilterDirs;
			$payload['filter-files'] = $temporary_package->Archive->FilterFiles;

            //RETURN RESULT
			//$test = ($success) ? DUP_PRO_CTRL_Status::SUCCESS : DUP_PRO_CTRL_Status::FAILED;
            $test = DUP_PRO_CTRL_Status::SUCCESS;
			$result->process($payload, $test);

		} catch (Exception $exc) {
			$result->processError($exc);
		}
	}

}