<?php
if (!defined('DUPLICATOR_PRO_VERSION')) exit; // Exit if accessed directly

/**
 * Class to create a zip file using PHP ZipArchive
 *
 * Standard: PSR-2 (almost)
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP_PRO
 * @subpackage classes/package
 * @copyright (c) 2017, Snapcreek LLC
 * @license	https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @since 1.0.0
 *
 * @notes: Trace process time
 *	$timer01 = DUP_PRO_U::getMicrotime();
 *	DUP_PRO_LOG::trace("SCAN TIME-B = " . DUP_PRO_U::elapsedTime(DUP_PRO_U::getMicrotime(), $timer01));
 *
 */
class DUP_PRO_ZipArchive extends DUP_PRO_Archive
{
	private $global;
	private $optPHP7CompressionOn	 = false;
	private $optServerThrottleOn	 = false;
	private $optMaxBuildTimeOn		 = true;
	private $maxBuildTimeFileSize	 = 100000;
	private $urlFAQ;

	public function __construct()
	{
		$this->global				 = DUP_PRO_Global_Entity::get_instance();
		$this->optPHP7CompressionOn	 = (DUP_PRO_U::PHP70() && !$this->global->archive_compression);
		$this->optServerThrottleOn	 = ($this->global->server_load_reduction != DUP_PRO_Server_Load_Reduction::None);
		$this->optMaxBuildTimeOn	 = ($this->global->max_package_runtime_in_min > 0);
		$this->urlFAQ                = 'https://snapcreek.com/duplicator/docs/faqs-tech';
	}

	/**
     * Creates the zip file and adds the SQL file to the archive
	 *
	 * @param object $archive A copy of the current archive object
	 * @param object $build_progress A copy of the current build progress
	 *
     * @returns bool	Returns true if the process was successful
     */
	public function create(DUP_PRO_Archive $archive, $build_progress)
	{
		try {

			if (!class_exists('ZipArchive')) {
				DUP_PRO_LOG::trace("Zip archive doesn't exist?");
				return false;
			}

			$archive->Package->safe_tmp_cleanup(true);

			if ($archive->Package->ziparchive_mode == DUP_PRO_ZipArchive_Mode::SingleThread) {
				return $this->createSingleThreaded($archive, $build_progress);
			} else {
				return $this->createMultiThreaded($archive, $build_progress);
			}
		} catch (Exception $ex) {
			DUP_PRO_Log::error("Runtime error in class-package-archive-zip.php.", "Exception: {$ex}");
		}
	}

	/**
     * Creates the zip file using a single thread approach
	 *
	 * @param object $archive A copy of the current archive object
	 * @param object $build_progress A copy of the current build progress
	 *
     * @returns bool	Returns true if the process was successful
     */
	private function createSingleThreaded(DUP_PRO_Archive $archive, $build_progress)
	{
		$countFiles		 = 0;
		$timerAllStart	 = DUP_PRO_U::getMicrotime();

		$compressDir = rtrim(DUP_PRO_U::safePath($archive->PackDir), '/');
		$sqlPath	 = DUP_PRO_U::safePath("{$archive->Package->StorePath}/{$archive->Package->Database->File}");
		$zipPath	 = DUP_PRO_U::safePath("{$archive->Package->StorePath}/{$archive->File}");
		$zipArchive	 = new ZipArchive();
		$filterDirs	 = empty($archive->FilterDirs) ? 'not set' : rtrim(str_replace(';', "\n \t", $archive->FilterDirs));
		$filterExts	 = empty($archive->FilterExts) ? 'not set' : $archive->FilterExts;
		$filterFiles = empty($archive->FilterFiles) ? 'not set' : $archive->FilterFiles;
		$filterOn	 = ($archive->FilterOn) ? 'ON' : 'OFF';
		$validation  = ($this->global->ziparchive_validation) ? 'ON' : 'OFF';
		$compression = ($this->optPHP7CompressionOn) ? 'ON' : 'OFF';

		//PREVENT RETRIES PAST 3:  Default is 10 (DUP_PRO_Constants::MAX_BUILD_RETRIES)
		//since this is ST Mode no reason to keep trying like MT
		if ($build_progress->retries >= 3) {
			$err = DUP_PRO_U::__('Package build appears stuck so marking package as failed. Is the PHP or Web Server timeouts too low?');
			DUP_PRO_Log::error(DUP_PRO_U::__('Build Failure'), $err, false);
			DUP_PRO_LOG::trace($err);
			return $build_progress->failed = true;
		} else {
			if ($build_progress->retries > 0) {
				DUP_PRO_Log::infoTrace("**NOTICE: Rety count at: {$build_progress->retries}");
			}
			$build_progress->retries++;
			$archive->Package->update();
		}
		
		//LOAD SCAN REPORT
		$scan_result = $this->loadScanFile(DUPLICATOR_PRO_SSDIR_PATH_TMP."/{$archive->Package->NameHash}_scan.json");
		if (! $scan_result['success']) {
			return $build_progress->failed = true;
		} 
		$scanReport = $scan_result['report'];
		
		//============================================
		//ST: START ZIP
		//============================================
		if ($build_progress->archive_started === false) {
			DUP_PRO_Log::info("\n********************************************************************************");
			DUP_PRO_Log::info("ARCHIVE ZipArchive Single-Threaded");
			DUP_PRO_Log::info("********************************************************************************");
			DUP_PRO_Log::info("ARCHIVE DIR:  ".$compressDir);
			DUP_PRO_Log::info("ARCHIVE FILE: ".basename($zipPath));
			DUP_PRO_Log::info("COMPRESSION: *{$compression}*");
			DUP_PRO_Log::info("VALIDATION: *{$validation}*");
			DUP_PRO_Log::info("FILTERS: *{$filterOn}*");
			DUP_PRO_Log::info("DIRS:\t{$filterDirs}");
			DUP_PRO_Log::info("EXTS:  {$filterExts}");
			DUP_PRO_Log::info("FILES:  {$filterFiles}");
			DUP_PRO_Log::info("----------------------------------------");
			DUP_PRO_Log::info("COMPRESSING");
			DUP_PRO_Log::info("SIZE:\t".$scanReport->ARC->Size);
			DUP_PRO_Log::info("STATS:\tDirs ".$scanReport->ARC->DirCount." | Files ".$scanReport->ARC->FileCount." | Total ".$scanReport->ARC->FullCount);

			if (($scanReport->ARC->DirCount == '') || ($scanReport->ARC->FileCount == '') || ($scanReport->ARC->FullCount == '')) {
				DUP_PRO_Log::error('Invalid Scan Report Detected', 'Invalid Scan Report Detected', false);
				return $build_progress->failed = true;
			}
			$build_progress->archive_started = true;
		}

		//============================================
		//ST: ADD DATABASE FILE
		//============================================
		if ($build_progress->archive_has_database === false) {

			if ($zipArchive->open($zipPath, ZipArchive::CREATE)) {
				$isSQLInZip = DUP_PRO_Zip_U::addFileToZipArchive($zipArchive, $sqlPath, 'database.sql');
				if ($isSQLInZip) {
					DUP_PRO_Log::info("SQL ADDED: ".basename($sqlPath));
				} else {
					DUP_PRO_Log::error("Unable to add database.sql to archive.", "SQL File Path [".self::$sqlath."]", false);
					return $build_progress->failed = true;
				}
			} else {
				DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
				return $build_progress->failed = true;
			}

			if ($zipArchive->close()) {
				$build_progress->archive_has_database = true;
				$archive->Package->update();
			} else {
				$err = 'ZipArchive close failure during database.sql phase.';
				$fix = sprintf("%s <a href='{$this->urlFAQ}/#faq-package-165-q' target='_blank'>%s</a>",
					DUP_PRO_U::__('See FAQ:'),
					DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
				$this->setError($err, $fix);
				return $build_progress->failed = true;
			}
		}

		//============================================
		//ST: ZIP DIRECTORIES
		//Keep this loop tight: ZipArchive can handle over 10k+ dir entries in under 0.01 seconds.
		//Its really fast without files so no need to do status pushes or other checks in loop
		//============================================
		if ($build_progress->next_archive_dir_index < count($scanReport->ARC->Dirs)) {
			if ($zipArchive->open($zipPath, ZipArchive::CREATE)) {
				foreach ($scanReport->ARC->Dirs as $dir) {
					if (! $zipArchive->addEmptyDir(ltrim(str_replace($compressDir, '', $dir), '/'))) {
						if (strpos($dir, rtrim($compressDir, '/')) != 0) {
							DUP_PRO_Log::infoTrace("WARNING: Unable to zip directory: '{$dir}'");
						}
					}
					$build_progress->next_archive_dir_index++;
				}
			} else {
				DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
				return $build_progress->failed = true;
			}

			if ($zipArchive->close()) {
				$archive->Package->update();
			} else {
				$err = 'ZipArchive close failure during directory add phase.';
				$fix = sprintf("%s <a href='{$this->urlFAQ}/#faq-package-165-q' target='_blank'>%s</a>",
					DUP_PRO_U::__('See FAQ:'),
					DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
				$this->setError($err, $fix);
				return $build_progress->failed = true;
			}
		}

		//============================================
		//ST: ZIP FILES
		//============================================
		if ($build_progress->archive_built === false) {

			if ($zipArchive->open($zipPath, ZipArchive::CREATE) === false) {
				DUP_PRO_Log::error("Can not open zip file at: [{$zipPath}]", '', false);
				return $build_progress->failed = true;
			}

			// Since we have to estimate progress in Single Thread mode
			// set the status when we start archiving just like Shell Exec
			$archive->Package->Status = DUP_PRO_PackageStatus::ARCSTART;
			$archive->Package->update();
			$total_file_size = 0;
			$total_file_count_trip = ($scanReport->ARC->UFileCount + 1000);

			if($this->optServerThrottleOn) {
                $host_delay_in_us = DUP_PRO_Server_Load_Reduction::microseconds_from_reduction($this->global->server_load_reduction);
            } else {
                $host_delay_in_us = 0;
            }

			foreach ($scanReport->ARC->Files as $file) {
				
				//NON-ASCII check
				if (preg_match('/[^\x20-\x7f]/', $file)) {
					if (!$this->isUTF8FileSafe($file)) {
						continue;
					}
				}

				if ($this->global->ziparchive_validation) {
					if (!is_readable($file)) {
						DUP_PRO_LOG::infoTrace("NOTICE: File [{$file}] is unreadable!");
						continue;
					}
				}

				$local_name	 = ltrim(str_replace($compressDir, '', $file), '/');
				if (! $zipArchive->addFile($file, $local_name)) {
					// Assumption is that we continue?? for some things this would be fatal others it would be ok - leave up to user
					DUP_PRO_Log::info("WARNING: Unable to zip file: {$file}");
					continue;
				} 

				if ($this->optPHP7CompressionOn) {
					$zipArchive->setCompressionName($local_name, ZipArchive::CM_STORE);
				}
	
				$total_file_size += filesize($file);
				
				//ST: SERVER THROTTLE
				if ($host_delay_in_us !== 0) {
					usleep($host_delay_in_us);
				}

				//Prevent Overflow
				if ($countFiles++ > $total_file_count_trip) {
					DUP_PRO_Log::error("ZipArchive-ST: file loop overflow detected at {$countFiles}", '', false);
					return $build_progress->failed = true;
				}
			}

			//START ARCHIVE CLOSE
			$total_file_size_easy = DUP_PRO_U::byteSize($total_file_size);
			DUP_PRO_LOG::trace("Doing final zip close after adding $total_file_size_easy ({$total_file_size})");
			DUP_PRO_Log::info(print_r($zipArchive, true));

			if ($zipArchive->close()) {
				DUP_PRO_LOG::trace("Final zip closed.");
				$build_progress->next_archive_file_index = $countFiles;
				$build_progress->archive_built = true;
				$archive->Package->update();
			} else {
				if ($this->global->ziparchive_validation === false) {
					$this->global->ziparchive_validation = true;
					$this->global->save();
					DUP_PRO_LOG::infoTrace("**NOTICE: ZipArchive: validation mode enabled");
				} else {
					$err = 'ZipArchive close failure during file phase with file validation enabled';
					$fix = sprintf("%s <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-package-165-q' target='_blank'>%s</a>",
						DUP_PRO_U::__('See FAQ:'),
						DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
					$this->setError($err, $fix);
					return $build_progress->failed = true;
				}
			}
		}

		//============================================
		//ST: LOG FINAL RESULTS
		//============================================
		if ($build_progress->archive_built) {
			$timerAllEnd = DUP_PRO_U::getMicrotime();
			$timerAllSum = DUP_PRO_U::elapsedTime($timerAllEnd, $timerAllStart);
			$zipFileSize = @filesize($zipPath);

			DUP_PRO_Log::info("MEMORY STACK: " . DUP_PRO_Server::getPHPMemory());
			DUP_PRO_Log::info("FINAL SIZE: " . DUP_PRO_U::byteSize($zipFileSize));
			DUP_PRO_Log::info("ARCHIVE RUNTIME: {$timerAllSum}");
		
			if ($zipArchive->open($zipPath)) {
				$archive->file_count = $zipArchive->numFiles;
				DUP_PRO_LOG::traceObject('final zip archive dump', $zipArchive);
				$archive->Package->update();
				$zipArchive->close();
			} else {
				DUP_PRO_Log::error("ZipArchive open failure.", "Encountered when retrieving final archive file count.", '', false);
				return $build_progress->failed = true;
			}
		}

		return true;
	}

	/**
     * Creates the zip file using a multi-thread approach
	 *
	 * @param object $archive A copy of the current archive object
	 * @param object $build_progress A copy of the current build progress
	 *
     * @returns bool	Returns true if the process was successful
     */
	private function createMultiThreaded(DUP_PRO_Archive $archive, $build_progress)
	{
        // profile ok
		$timed_out		 = false;
		$countFiles		 = 0;
		$timerAllStart	 = DUP_PRO_U::getMicrotime();

		$compressDir = rtrim(DUP_PRO_U::safePath($archive->PackDir), '/');
		$sqlPath	 = DUP_PRO_U::safePath("{$archive->Package->StorePath}/{$archive->Package->Database->File}");
		$zipPath	 = DUP_PRO_U::safePath("{$archive->Package->StorePath}/{$archive->File}");
		$zipArchive	 = new ZipArchive();
		$filterDirs	 = empty($archive->FilterDirs) ? 'not set' : $archive->FilterDirs;
		$filterExts	 = empty($archive->FilterExts) ? 'not set' : $archive->FilterExts;
		$filterFiles = empty($archive->FilterFiles) ? 'not set' : $archive->FilterFiles;
		$filterOn	 = ($archive->FilterOn) ? 'ON' : 'OFF';
		$compression = ($this->optPHP7CompressionOn) ? 'ON' : 'OFF';
        // end profile ok

        // profile ok
		//LOAD SCAN REPORT
		$scan_result = $this->loadScanFile(DUPLICATOR_PRO_SSDIR_PATH_TMP."/{$archive->Package->NameHash}_scan.json");
		if (! $scan_result['success']) {
			return $build_progress->failed = true;
		}
		$scanReport = $scan_result['report'];

        // end profile ok

		//============================================
		//MT: START ZIP & ADD SQL FILE
		//============================================
		if ($build_progress->archive_started === false) {
			DUP_PRO_Log::info("\n********************************************************************************");
			DUP_PRO_Log::info("ARCHIVE Mode:ZipArchive Multi-Threaded");
			DUP_PRO_Log::info("********************************************************************************");
			DUP_PRO_Log::info("ARCHIVE DIR:  ".$compressDir);
			DUP_PRO_Log::info("ARCHIVE FILE: ".basename($zipPath));
			DUP_PRO_Log::info("COMPRESSION: *{$compression}*");
			DUP_PRO_Log::info("FILTERS: *{$filterOn}*");
			DUP_PRO_Log::info("DIRS:  {$filterDirs}");
			DUP_PRO_Log::info("EXTS:  {$filterExts}");
			DUP_PRO_Log::info("FILES:  {$filterFiles}");
			DUP_PRO_Log::info("----------------------------------------");
			DUP_PRO_Log::info("COMPRESSING");
			DUP_PRO_Log::info("SIZE:\t".$scanReport->ARC->Size);
			DUP_PRO_Log::info("STATS:\tDirs ".$scanReport->ARC->DirCount." | Files ".$scanReport->ARC->FileCount." | Total ".$scanReport->ARC->FullCount);

			if (($scanReport->ARC->DirCount == '') || ($scanReport->ARC->FileCount == '') || ($scanReport->ARC->FullCount == '')) {
				DUP_PRO_Log::error('Invalid Scan Report Detected', 'Invalid Scan Report Detected', false);
				return $build_progress->failed = true;
			}

            // profile ok
			if ($zipArchive->open($zipPath, ZipArchive::CREATE)) {
				$isSQLInZip = DUP_PRO_Zip_U::addFileToZipArchive($zipArchive, $sqlPath, 'database.sql');
				if ($isSQLInZip) {
					DUP_PRO_Log::info("SQL ADDED: ".basename($sqlPath));
				} else {
					DUP_PRO_Log::error("Unable to add database.sql to archive.", "SQL File Path [".self::$sqlath."]", false);
					return $build_progress->failed = true;
				}
			} else {
				DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
				return $build_progress->failed = true;
			}

			if ($zipArchive->close()) {
				$build_progress->archive_started = true;
				$archive->Package->update();
			} else {
				$err = 'ZipArchive close failure during database.sql phase.';
				$fix = sprintf("%s <a href='{$this->urlFAQ}/#faq-package-165-q' target='_blank'>%s</a>",
					DUP_PRO_U::__('See FAQ:'),
					DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
				$this->setError($err, $fix);
				return $build_progress->failed = true;
			}
            // end profile ok
		}

		//============================================
		//MT: ZIP DIRECTORIES
		//Keep this loop tight: ZipArchive can handle over 10k dir entries in under 0.01 seconds.
		//Its really fast without files no need to do status pushes or other checks in loop
		//============================================
		if ($zipArchive->open($zipPath, ZipArchive::CREATE)) {

            // profile ok
			foreach ($scanReport->ARC->Dirs as $dir) {
				if (! $zipArchive->addEmptyDir(ltrim(str_replace($compressDir, '', $dir), '/'))) {
					if (strpos($dir, rtrim($compressDir, '/')) != 0) {
						DUP_PRO_Log::infoTrace("WARNING: Unable to zip directory: '{$dir}'");
					}
				}
				$build_progress->next_archive_dir_index++;
			}

			$archive->Package->update();
			if ($build_progress->timed_out($this->global->php_max_worker_time_in_sec)) {
				$timed_out	 = true;
				$diff		 = time() - $build_progress->thread_start_time;
				DUP_PRO_LOG::trace("Timed out after hitting thread time of $diff {$this->global->php_max_worker_time_in_sec} so quitting zipping early in the directory phase");
			}
            // end profile ok
		} else {
			DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
			return $build_progress->failed = true;
		}

        // profile ok
		if ($zipArchive->close() === false) {
			$err = 'ZipArchive close failure during directory add phase.';
			$fix = sprintf("%s <a href='{$this->urlFAQ}/#faq-package-165-q' target='_blank'>%s</a>",
				DUP_PRO_U::__('See FAQ:'),
				DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
			$this->setError($err, $fix);
            
			return $build_progress->failed = true;
		}
        // end profile ok


		//============================================
		//MT: ZIP FILES
		//============================================
		if ($timed_out === false) {

			//PREVENT RETRIES (10x)
			if ($build_progress->retries > DUP_PRO_Constants::MAX_BUILD_RETRIES) {
				$error_msg = DUP_PRO_U::__('Package build appears stuck so marking package as failed. Is the Max Worker Time set too high?.');
				DUP_PRO_Log::error(DUP_PRO_U::__('Build Failure'), $error_msg, false);
				DUP_PRO_LOG::trace($error_msg);
				return $build_progress->failed = true;
			} else {
				$build_progress->retries++;
				$archive->Package->update();
			}

			$zip_is_open = false;
			$total_file_size				 = 0;
			$incremental_file_size			 = 0;
			$used_zip_file_descriptor_count	 = 0;
			$total_file_count = empty($scanReport->ARC->UFileCount) ? 0 : $scanReport->ARC->UFileCount;

            if($this->optServerThrottleOn) {
                $host_delay_in_us = DUP_PRO_Server_Load_Reduction::microseconds_from_reduction($this->global->server_load_reduction);
            }
            else {
                $host_delay_in_us = 0;
            }

            DUP_PRO_LOG::profile('createMultiThreaded > file loop', true);
			foreach ($scanReport->ARC->Files as $file) {
				if ($zip_is_open || ($countFiles == $build_progress->next_archive_file_index)) {
	
					if ($zip_is_open === false) {
						DUP_PRO_LOG::trace("resuming archive building at file # $countFiles");
						if ($zipArchive->open($zipPath, ZipArchive::CREATE) === false) {
							DUP_PRO_Log::error("Couldn't open $zipPath", '', false);
							$build_progress->failed = true;
							return true;
						}
						$zip_is_open = true;
					}

					//NON-ASCII check
					if (preg_match('/[^\x20-\x7f]/', $file)) {
						if (!$this->isUTF8FileSafe($file)) {
							continue;
						}
					} elseif (!file_exists($file)) {
						DUP_PRO_LOG::trace("NOTICE: ASCII file [{$file}] does not exist!");
						continue;
					}

					$local_name = ltrim(str_replace($compressDir, '', $file), '/');
					$file_size  = filesize($file);

					if (($file_size < DUP_PRO_Constants::ZIP_STRING_LIMIT)) {
						$zip_status = $zipArchive->addFromString($local_name, file_get_contents($file));
					} else {
						// Large files use addFile
						$zip_status	 = $zipArchive->addFile($file, $local_name);
						$used_zip_file_descriptor_count++;
					}
					
					if ($this->optPHP7CompressionOn) {
						$zipArchive->setCompressionName($local_name, ZipArchive::CM_STORE);
					}

					if ($zip_status) {
						$total_file_size += $file_size;
						$incremental_file_size += $file_size;
					} else {
						// Assumption is that we continue?? for some things this would be fatal others it would be ok - leave up to user
						DUP_PRO_Log::info("WARNING: Unable to zip file: {$file}");
					}

					$countFiles++;
					$chunk_size_in_bytes = $this->global->ziparchive_chunk_size_in_mb * 1000000;

					if (($incremental_file_size > $chunk_size_in_bytes) || ($used_zip_file_descriptor_count > DUP_PRO_Constants::ZIP_MAX_FILE_DESCRIPTORS)) {
						// Only close because of chunk size and file descriptors when in legacy mode
						DUP_PRO_LOG::trace("closing zip because ziparchive mode = {$this->global->ziparchive_mode} fd count = $used_zip_file_descriptor_count or incremental file size=$incremental_file_size and chunk size = $chunk_size_in_bytes");
						$incremental_file_size			 = 0;
						$used_zip_file_descriptor_count	 = 0;

						if ($zipArchive->close()) {
							$adjusted_percent = floor(DUP_PRO_PackageStatus::ARCSTART + ((DUP_PRO_PackageStatus::ARCDONE - DUP_PRO_PackageStatus::ARCSTART) * ($countFiles / (float) $total_file_count)));

							$build_progress->next_archive_file_index = $countFiles;
							$build_progress->retries				 = 0;
							$archive->Package->Status				 = $adjusted_percent;
							$archive->Package->update();
							$zip_is_open							 = false;
							DUP_PRO_LOG::trace("closed zip");
						} else {
							$err = 'ZipArchive close failure during file phase using multi-threaded setting.';
							$fix = sprintf("%s <a href='{$this->urlFAQ}/#faq-package-165-q' target='_blank'>%s</a>",
								DUP_PRO_U::__('See FAQ:'),
								DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
							$this->setError($err, $fix);
							return $build_progress->failed = true;
						}
					}

                    //MT: SERVER THROTTLE
                    if ($host_delay_in_us !== 0) {
                        usleep($host_delay_in_us);
                    }

                    //MT: MAX WORKER TIME (SECS)
                    if ($build_progress->timed_out($this->global->php_max_worker_time_in_sec)) {
                        // Only close because of timeout
                        $timed_out	 = true;
                        $diff		 = time() - $build_progress->thread_start_time;
                        DUP_PRO_LOG::trace("Timed out after hitting thread time of $diff so quitting zipping early in the file phase");
                        break;
                    }

                    //MT: MAX BUILD TIME (MINUTES)
                    //Only stop to check on larger files above 100K to avoid checking every single file
                    if ($file_size > $this->maxBuildTimeFileSize && $this->optMaxBuildTimeOn) {
                        $elapsed_sec	 = time() - $archive->Package->timer_start;
                        $elapsed_minutes = $elapsed_sec / 60;
                        if ($elapsed_minutes > $this->global->max_package_runtime_in_min) {
                            DUP_PRO_LOG::trace("ZipArchive: Multi-thread max build time {$this->global->max_package_runtime_in_min} minutes reached killing process.");
                            return false;
                        }
                    }
				} else {
					$countFiles++;
				}				
			}
            DUP_PRO_LOG::profile('createMultiThreaded > file loop', false);

			DUP_PRO_LOG::trace("total file size added to zip = $total_file_size");

			if ($zip_is_open) {
                // profile ok
				DUP_PRO_LOG::trace("Doing final zip close after adding $incremental_file_size");
				DUP_PRO_Log::info(print_r($zipArchive, true));

				if ($zipArchive->close()) {
					DUP_PRO_LOG::trace("Final zip closed.");
					$build_progress->next_archive_file_index = $countFiles;
					$build_progress->retries = 0;
					$archive->Package->update();
				} else {
					$err = 'ZipArchive close failure during file phase.';
					$fix = sprintf("%s <a href='{$this->urlFAQ}/#faq-package-165-q' target='_blank'>%s</a>",
						DUP_PRO_U::__('See FAQ:'),
						DUP_PRO_U::__("I'm getting a ZipArchive close failure when building. How can I resolve this?"));
					$this->setError($err, $fix);
					DUP_PRO_Log::error("ZipArchive close failure.", "This hosted server may have a disk quota limit.\nCheck to make sure this archive file can be stored.");
					return $build_progress->failed = true;
				}
                // end profile ok
			}
		}


		//============================================
		//MT: LOG FINAL RESULTS
		//============================================
		if ($timed_out === false) {
            DUP_PRO_LOG::profile('createMultiThreaded > log final results', true);
			$build_progress->archive_built	 = true;
			$build_progress->retries		 = 0;
			$archive->Package->update();

			$timerAllEnd = DUP_PRO_U::getMicrotime();
			$timerAllSum = DUP_PRO_U::elapsedTime($timerAllEnd, $timerAllStart);

			$zipFileSize = @filesize($zipPath);
			DUP_PRO_Log::info("COMPRESSED SIZE: ".DUP_PRO_U::byteSize($zipFileSize));
			DUP_PRO_Log::info("ARCHIVE RUNTIME: {$timerAllSum}");
			DUP_PRO_Log::info("MEMORY STACK: ".DUP_PRO_Server::getPHPMemory());

			if ($zipArchive->open($zipPath)) {
				$archive->file_count = $zipArchive->numFiles;
				DUP_PRO_LOG::traceObject('final zip archive dump', $zipArchive);
				$archive->Package->update();
				$zipArchive->close();
			} else {
				DUP_PRO_Log::error("ZipArchive open failure.", "Encountered when retrieving final archive file count.", '', false);
				return $build_progress->failed = true;
			}
            DUP_PRO_LOG::profile('createMultiThreaded > log final results', true);
		}

		return !$timed_out;
	}

	/**
     * Encodes a UTF8 file and then determines if it is safe to add to an archive
	 *
	 * @param object $file	The file to test
	 *
     * @returns bool	Returns true if the file is readable and safe to add to archive
     */
	private function isUTF8FileSafe($file)
	{
		$is_safe		 = true;
		$original_file	 = $file;
		DUP_PRO_LOG::trace("[{$file}] is non ASCII");

		// Necessary for adfron type files
		if (DUP_PRO_STR::hasUTF8($file)) {
			$file = utf8_decode($file);
		}

		if (file_exists($file) === false) {
			if (file_exists($original_file) === false) {
				DUP_PRO_LOG::trace("$file CAN'T BE READ!");
				DUP_PRO_Log::info("WARNING: Unable to zip file: {$file}. Cannot be read");
				$is_safe = false;
			}
		}

		return $is_safe;
	}
	
	/**
     * Checks to make sure the scanner file is valid and loads it for use
	 *
	 * @param string Path to the scanner file to use
	 *
     * @returns array	Returns a success flag and the report results
     */
	private function loadScanFile($scanPath)
	{
		$result = array('success' => true, 'report' => '');

		if (file_exists($scanPath)) {
			$json = file_get_contents($scanPath);

			if (empty($json)) {
				$err = DUP_PRO_U::__("Scan file $scanPath is empty!");
				$fix = DUP_PRO_U::__("Go to: Settings > Packages Tab > JSON to Custom.");
				$this->setError($err, $fix);
				$result['success'] = false;
			} else {
				$result['report'] = json_decode($json);
			}

		} else {
			DUP_PRO_LOG::trace("**** scan file $scanPath doesn't exist!!");
			$error_message = sprintf(DUP_PRO_U::__("ERROR: Can't find Scanfile %s. Please ensure there no non-English characters in the package or schedule name."), $scanPath);
			DUP_PRO_Log::error($error_message, '', false);
			$result['success'] = false;
		}

		return $result;
	}

	/**
     * Sends an error to the trace and build logs and sets the UI message
	 *
	 * @param string $message	The error message
	 * @param string $fix		The details for how to fix the issue
	 *
     * @returns null
     */
	private	function setError($message, $fix)
	{
		DUP_PRO_LOG::trace($message);
		DUP_PRO_Log::error("$message **RECOMMENDATION:  $fix.", '', false);

		$system_global = DUP_PRO_System_Global_Entity::get_instance();
		$system_global->add_recommended_text_fix($message, $fix);
		$system_global->save();
	}

}
