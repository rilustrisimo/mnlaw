<?php

require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/ctrls/ctrl.base.php'); 
require_once(DUPLICATOR_PRO_PLUGIN_PATH . '/classes/class.scan.check.php');

/**
 * Controller for Tools 
 * @package Dupicator\ctrls
 */
class DUP_PRO_CTRL_Tools extends DUP_PRO_CTRL_Base
{	 
	/**
     *  Init this instance of the object
     */
	function __construct() 
	{
		add_action('wp_ajax_DUP_PRO_CTRL_Tools_runScanValidator', array($this, 'runScanValidator'));
		add_action('wp_ajax_DUP_PRO_CTRL_Tools_deleteInstallerFiles', array($this, 'deleteInstallerFiles'));
	}
	
	/** 
     * Calls the ScanValidator and returns a JSON result
	 * 
	 * @param string $_POST['scan-path']		The path to start scanning from, defaults to DUPLICATOR_PRO_WPROOTPATH
	 * @param bool   $_POST['scan-recursive']	Recursivly search the path
	 * 
	 * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_PRO_CTRL_Tools_runScanValidator
     */
	public function runScanValidator($post)
	{
        @set_time_limit(0);
		$post = $this->postParamMerge($post);
		check_ajax_referer($post['action'], 'nonce');
		
		$result = new DUP_PRO_CTRL_Result($this);
		 
		try 
		{
			//CONTROLLER LOGIC
			$path = isset($post['scan-path']) ? $post['scan-path'] : DUPLICATOR_PRO_WPROOTPATH;
			if (!is_dir($path)) {
				throw new Exception("Invalid directory provided '{$path}'!");
			}
			$scanner = new DUP_ScanCheck();
			$scanner->recursion = (isset($post['scan-recursive']) && $post['scan-recursive'] != 'false') ? true : false;
			$payload = $scanner->run($path);

			//RETURN RESULT
			$test = ($payload->fileCount > 0)
					? DUP_PRO_CTRL_Status::SUCCESS
					: DUP_PRO_CTRL_Status::FAILED;
			$result->process($payload, $test);
		} 
		catch (Exception $exc) 
		{
			$result->processError($exc);
		}
    }
	
}
