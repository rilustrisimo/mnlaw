<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
error_reporting(E_ALL);
set_error_handler("terminate_missing_variables");

require_once(dirname(__FILE__) . '/class.daws.constants.php');

require_once(DAWSConstants::$LIB_DIR . '/snaplib/snaplib.all.php');

require_once(DAWSConstants::$DUPARCHIVE_CLASSES_DIR . '/class.duparchive.loggerbase.php');
require_once(DAWSConstants::$DUPARCHIVE_CLASSES_DIR . '/class.duparchive.engine.php');
require_once(DAWSConstants::$DUPARCHIVE_CLASSES_DIR . '/class.duparchive.mini.expander.php');
require_once(DAWSConstants::$DUPARCHIVE_STATES_DIR . '/class.duparchive.state.simplecreate.php');

require_once(DAWSConstants::$DAWS_ROOT . '/class.daws.state.expand.php');

DupArchiveUtil::$TRACE_ON = false;

class DAWS_Logger extends DupArchiveLoggerBase
{
    public function log($s, $flush = false, $callingFunctionOverride = null)
    {
        SnapLibLogger::log($s, $flush, $callingFunctionOverride);
    }
}

class DAWS
{

    private $lock_handle = null;

    function __construct()
    {
        date_default_timezone_set('UTC'); // Some machines don’t have this set so just do it here.
        
        SnapLibLogger::init(DAWSConstants::$LOG_FILEPATH);

        DupArchiveEngine::init(new DAWS_Logger());
    }

    public function processRequest()
    {
        try {
            $retVal = new StdClass();

            $retVal->pass = false;

            if (isset($_REQUEST['action'])) {
                $params = $_REQUEST;
                SnapLibLogger::log('b');
            } else {
                $json = file_get_contents('php://input');
                $params = json_decode($json, true);
            }

            SnapLibLogger::logObject('params', $params);
            SnapLibLogger::logObject('keys', array_keys($params));

            $action = $params['action'];

            $initializeState = false;

            $isClientDriven = SnapLibUtil::getArrayValue($params, 'client_driven', false);

            if ($action == 'start_expand') {

                $initializeState = true;

                DAWSExpandState::purgeStatefile();
                SnapLibLogger::clearLog();

                SnapLibIOU::rm(DAWSConstants::$PROCESS_CANCEL_FILEPATH);
                $archiveFilepath = SnapLibUtil::getArrayValue($params, 'archive_filepath');
                $restoreDirectory = SnapLibUtil::getArrayValue($params, 'restore_directory');
                $workerTime = SnapLibUtil::getArrayValue($params, 'worker_time', false, DAWSConstants::$DEFAULT_WORKER_TIME);
                $filteredDirectories = SnapLibUtil::getArrayValue($params, 'filtered_directories', false, array());

                $action = 'expand';
            }

			$throttleDelayInMs = SnapLibUtil::getArrayValue($params, 'throttle_delay', false, 0);

            $spawnAnotherThread = false;


            if ($action == 'expand') {

                /* @var $expandState DAWSExpandState */
                $expandState = DAWSExpandState::getInstance($initializeState);

				$this->lock_handle = SnapLibIOU::fopen(DAWSConstants::$PROCESS_LOCK_FILEPATH, 'c+');
				SnapLibIOU::flock($this->lock_handle, LOCK_EX);

				if($initializeState || $expandState->working) {

					if ($initializeState) {

						$expandState->archivePath = $archiveFilepath;
						$expandState->working = true;
						$expandState->timeSliceInSecs = $workerTime;
						$expandState->basePath = $restoreDirectory;
						$expandState->working = true;
						$expandState->filteredDirectories = $filteredDirectories;
                        $expandState->fileModeOverride = 0644;
                        $expandState->directoryModeOverride = 0755;

						$expandState->save();
					}

					$expandState->throttleDelayInUs = 1000 * $throttleDelayInMs;

                    DupArchiveUtil::tlogObject('Expand State In', $expandState);
					DupArchiveEngine::expandArchive($expandState);
				}

                $spawnAnotherThread = $expandState->working && !$isClientDriven;

                if (!$expandState->working) {

                    $deltaTime = time() - $expandState->startTimestamp;
                    SnapLibLogger::log("###### Processing ended.  Seconds taken:$deltaTime");

                    if (count($expandState->failures) > 0) {
                        SnapLibLogger::log('Errors detected');

                        foreach ($expandState->failures as $failure) {
                            SnapLibLogger::log("{$failure->subject}:{$failure->description}");
                        }
                    } else {
                        SnapLibLogger::log('Expansion done, archive checks out!');
                    }
                }


                SnapLibIOU::flock($this->lock_handle, LOCK_UN);

                $retVal->pass = true;
                $retVal->status = $this->getStatus($expandState);
            } else if ($action == 'get_status') {
                /* @var $expandState DAWSExpandState */
                $expandState = DAWSExpandState::getInstance($initializeState);

                $retVal->pass = true;
                $retVal->status = $this->getStatus($expandState);
            } else if ($action == 'cancel') {
                SnapLibIOU::touch(DAWSConstants::$PROCESS_CANCEL_FILEPATH);
                $retVal->pass = true;
            } else {
                throw new Exception('Unknown command.');
            }

            session_write_close();

            if ($spawnAnotherThread) {

                $url = "http://$_SERVER[HTTP_HOST]" . strtok($_SERVER["REQUEST_URI"], '?');
                $data = array('action' => $action, 'client_driven' => '0');

                SnapLibLogger::log("SPAWNING CUSTOM WORKER AT $url FOR ACTION $action");
                SnapLibNetU::postWithoutWait($url, $data);
            } else {
                SnapLibLogger::log("NOT SPAWNING ANOTHER THREAD");
            }
        } catch (Exception $ex) {
            $error_message = "Error Encountered:" . $ex->getMessage() . '<br/>' . $ex->getTraceAsString();

            SnapLibLogger::log($error_message);

            $retVal->pass = false;
            $retVal->error = $error_message;
        }

        echo json_encode($retVal);
    }

    private function getStatus($expandState)
    {
        /* @var $expandState DAWSExpandState */

        $ret_val = new stdClass();

        $ret_val->archive_offset = $expandState->archiveOffset;
        $ret_val->archive_size = @filesize($expandState->archivePath);
        $ret_val->failures = $expandState->failures;
        $ret_val->file_index = $expandState->fileWriteCount;
        $ret_val->is_done = !$expandState->working;
        $ret_val->timestamp = time();

        return $ret_val;
    }
}

function generateCallTrace()
{
    $e = new Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();

    for ($i = 0; $i < $length; $i++) {
        $result[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }

    return "\t" . implode("\n\t", $result);
}

function terminate_missing_variables($errno, $errstr, $errfile, $errline)
{
    echo "<br/>ERROR: $errstr $errfile $errline<br/>";
    //  if (($errno == E_NOTICE) and ( strstr($errstr, "Undefined variable"))) die("$errstr in $errfile line $errline");


    SnapLibLogger::log("ERROR $errno, $errstr, {$errfile}:{$errline}");
    SnapLibLogger::log(generateCallTrace());
    //  DaTesterLogging::clearLog();

    exit(1);
    //return false; // Let the PHP error handler handle all the rest
}
$daws = new DAWS();

$daws->processRequest();
