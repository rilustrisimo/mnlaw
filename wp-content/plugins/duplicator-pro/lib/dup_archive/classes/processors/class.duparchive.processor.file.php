<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


require_once(dirname(__FILE__).'/../headers/class.duparchive.header.file.php');
require_once(dirname(__FILE__).'/../headers/class.duparchive.header.glob.php');

class DupArchiveFileProcessor
{

    public static function writeFilePortionToArchive($createState, $archiveHandle, $sourceFilepath, $relativeFilePath)
    {
        /* @var $createState DupArchiveCreateState */

        DupArchiveUtil::tlog("writeFileToArchive for {$sourceFilepath}");

        // profile ok

        // switching to straight call for speed
        $sourceHandle = @fopen($sourceFilepath, 'rb');

        // end profile ok

        if($sourceHandle === false)
        {
            $createState->archiveOffset     = SnapLibIOU::ftell($archiveHandle);
            $createState->currentFileIndex++;
            $createState->currentFileOffset = 0;
            $createState->skippedFileCount++;
            $createState->addFailure(DupArchiveFailureTypes::File, $sourceFilepath, "Couldn't open $sourceFilepath", false);

            return;
        }

        if ($createState->currentFileOffset > 0) {
            DupArchiveUtil::tlog("Continuing {$sourceFilepath} so seeking to {$createState->currentFileOffset}");

            SnapLibIOU::fseek($sourceHandle, $createState->currentFileOffset);
        } else {
            DupArchiveUtil::tlog("Starting new file entry for {$sourceFilepath}");


            // profile ok
            $fileHeader = DupArchiveFileHeader::createFromFile($sourceFilepath, $relativeFilePath);
            // end profile ok

            // profile ok
            $fileHeader->writeToArchive($archiveHandle);
            // end profile ok
        }

        // profile ok
        $sourceFileSize = filesize($sourceFilepath);

        DupArchiveUtil::tlog("writeFileToArchive for {$sourceFilepath}, size {$sourceFileSize}");

        $moreFileDataToProcess = true;

        while ((!$createState->timedOut()) && $moreFileDataToProcess) {

            if($createState->throttleDelayInUs !== 0) {
                usleep($createState->throttleDelayInUs);
            }
            
            DupArchiveUtil::tlog("Writing offset={$createState->currentFileOffset}");

            // profile ok
            $moreFileDataToProcess = self::appendGlobToArchive($createState, $archiveHandle, $sourceHandle, $sourceFilepath, $sourceFileSize);
            // end profile ok

            // profile ok
            if ($moreFileDataToProcess) {

                DupArchiveUtil::tlog("Need to keep writing {$sourceFilepath} to archive");
                $createState->currentFileOffset += $createState->globSize;
                $createState->archiveOffset = SnapLibIOU::ftell($archiveHandle); //??
            } else {

                DupArchiveUtil::tlog("Completed writing {$sourceFilepath} to archive");
                $createState->archiveOffset     = SnapLibIOU::ftell($archiveHandle);
                $createState->currentFileIndex++;
                $createState->currentFileOffset = 0;
            }

            // end profile ok

            if ($createState->currentFileIndex % 100 == 0) {
                DupArchiveUtil::log("Archive Offset={$createState->archiveOffset}; Current File Index={$createState->currentFileIndex}; Current File Offset={$createState->currentFileOffset}");
            }

            // Only writing state after full group of files have been written - less reliable but more efficient
            // $createState->save();
        }

        // profile ok
        SnapLibIOU::fclose($sourceHandle);
        // end profile ok
    }

    // Assumption is that this is called at the beginning of a glob header since file header already writtern
    public static function writeToFile($expandState, $archiveHandle)
    {
        /* @var $expandState DupArchiveExpandState */
        $destFilepath = "{$expandState->basePath}/{$expandState->currentFileHeader->relativePath}";

        $parentDir = dirname($destFilepath);
 
        $moreGlobstoProcess = true;
        
        if (!file_exists($parentDir)) {
 
            SnapLibIOU::mkdir($parentDir, 0755, true);
        }

        if ($expandState->currentFileHeader->fileSize > 0) {

            if ($expandState->currentFileOffset > 0) {
                $destFileHandle = SnapLibIOU::fopen($destFilepath, 'r+b');

                DupArchiveUtil::tlog("Continuing {$destFilepath} so seeking to {$expandState->currentFileOffset}");

                SnapLibIOU::fseek($destFileHandle, $expandState->currentFileOffset);
            } else {
                DupArchiveUtil::tlog("Starting to write new file {$destFilepath}");
                $destFileHandle = SnapLibIOU::fopen($destFilepath, 'w+b');
            }

            DupArchiveUtil::tlog("writeToFile for {$destFilepath}, size {$expandState->currentFileHeader->fileSize}");

            while (!$expandState->timedOut()) {
                   
                $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;
                    
                if ($moreGlobstoProcess) {
                    DupArchiveUtil::tlog("Need to keep writing to {$destFilepath} because current file offset={$expandState->currentFileOffset} and file size={$expandState->currentFileHeader->fileSize}");
                
                    if($expandState->throttleDelayInUs !== 0) {
                        usleep($expandState->throttleDelayInUs);
                    }

                    DupArchiveUtil::tlog("Writing offset={$expandState->currentFileOffset}");

                    self::appendGlobToFile($expandState, $archiveHandle, $destFileHandle, $destFilepath);

                    DupArchiveUtil::tlog("After glob write");

                    $expandState->currentFileOffset = ftell($destFileHandle);
                    $expandState->archiveOffset     = SnapLibIOU::ftell($archiveHandle);

                    $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

                    if (rand(0, 1000) > 990) {
                        DupArchiveUtil::log("Archive Offset={$expandState->archiveOffset}; Current File={$destFilepath}; Current File Offset={$expandState->currentFileOffset}");
                    }   
                } else {
                    // No more globs to process
                    
                    // Reset the expand state here to ensure it stays consistent
                    DupArchiveUtil::tlog("Writing of {$destFilepath} to archive is done");

                    // rsr todo record fclose error
                    @fclose($destFileHandle);
                    $destFileHandle = null;

                    self::setFileMode($expandState, $destFilepath);

                    if ($expandState->validationType == DupArchiveValidationTypes::Full) {
                        self::validateExpandedFile($expandState);
                    }

                    $expandState->fileWriteCount++;

                    $expandState->resetForFile();

                    DupArchiveUtil::tlog("No more globs to process");
                     
                    break;
                }                  
            }
            
            DupArchiveUtil::tlog("Out of glob loop");

            if ($destFileHandle != null) {
                // rsr todo record file close error
                @fclose($destFileHandle);
                $destFileHandle = null;
            }

            if (!$moreGlobstoProcess && $expandState->validateOnly && ($expandState->validationType == DupArchiveValidationTypes::Full)) {
                // rsr todo record delete error
                SnapLibIOU::rm($destFilepath, false);
            }

        } else {
            // 0 length file so just touch it
            $moreGlobstoProcess = false;

            if (touch($destFilepath) === false) {
                throw new Exception("Couldn't create $destFilepath");
            }

            self::setFileMode($expandState, $destFilepath);
        }


        return !$moreGlobstoProcess;
    }

    public static function setFileMode($expandState, $filePath)
    {
        $mode = $expandState->currentFileHeader->permissions;

        if($expandState->fileModeOverride != -1) {

            $mode = $expandState->fileModeOverride;
        }

        @chmod($filePath, $mode);
    }

    public static function standardValidateFileEntry($expandState, $archiveHandle)
    {       
        /* @var $expandState DupArchiveExpandState */

        $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

        if (!$moreGlobstoProcess) {

            $expandState->fileWriteCount++;
        } else {

            while ((!$expandState->timedOut()) && $moreGlobstoProcess) {

                // Read in the glob header but leave the pointer at the payload

                // profile ok
                $globHeader = DupArchiveGlobHeader::readFromArchive($archiveHandle, false);                

                // profile ok
                $globContents = fread($archiveHandle, $globHeader->storedSize);

                if ($globContents === false) {
                    throw new Exception("Error reading glob from $destFilePath");
                }

                $md5 = md5($globContents);
               
                if ($md5 != $globHeader->md5) {
                    $expandState->addFailure(DupArchiveFailureTypes::File, $expandState->currentFileHeader->relativePath, 'MD5 mismatch on DupArchive file entry', false);
                    DupArchiveUtil::tlog("Glob MD5 fails");
                } else {
                    //    DupArchiveUtil::tlog("Glob MD5 passes");
                }

                $expandState->currentFileOffset += $globHeader->originalSize;

                // profile ok
                $expandState->archiveOffset = SnapLibIOU::ftell($archiveHandle);
                

                $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

                if ($moreGlobstoProcess) {
                    //      DupArchiveUtil::tlog("Need to keep validating {$expandState->currentFileHeader->relativePath} because current file offset={$expandState->currentFileOffset} and file size={$expandState->currentFileHeader->fileSize}");
                } else {
                    // Reset the expand state here to ensure it stays consistent
                    //     DupArchiveUtil::tlog("Validating of {$expandState->currentFileHeader->relativePath} to archive is done");

                    $expandState->fileWriteCount++;

                    // profile ok
                    $expandState->resetForFile();
                }
            }
        }

        return !$moreGlobstoProcess;
    }

    private static function validateExpandedFile(&$expandState)
    {
        /* @var $expandState DupArchiveExpandState */
        $destFilepath = "{$expandState->basePath}/{$expandState->currentFileHeader->relativePath}";

        if ($expandState->currentFileHeader->md5 !== '00000000000000000000000000000000') {
            $md5 = md5_file($destFilepath);

            if ($md5 !== $expandState->currentFileHeader->md5) {
                $expandState->addFailure(DupArchiveFailureTypes::File, $destFilepath, 'MD5 mismatch', false);
            } else {
                DupArchiveUtil::tlog("MD5 Match for $destFilepath");
            }
        } else {
            DupArchiveUtil::tlog("MD5 non match is 0's");
        }
    }

    private static function appendGlobToArchive($createState, $archiveHandle, $sourceFilehandle, $sourceFilepath, $fileSize)
    {
        DupArchiveUtil::tlog("Appending file glob to archive for file {$sourceFilepath} at file offset {$createState->currentFileOffset}");

        if ($fileSize > 0) {
            $fileSize -= $createState->currentFileOffset;

            // profile ok
            $globContents = fread($sourceFilehandle, $createState->globSize);
            // end profile ok

            if ($globContents === false) {
                throw new Exception("Error reading $sourceFilepath");
            }

            // profile ok
            $originalSize = strlen($globContents);
            // end profile ok

            if ($createState->isCompressed) {
                // profile ok
                $globContents = gzdeflate($globContents, 2);    // 2 chosen as best compromise between speed and size
                $storeSize    = strlen($globContents);
                // end profile ok
            } else {
                $storeSize = $originalSize;
            }

            // profile ok
            $md5 = md5($globContents);
            // end profile ok

            // profile ok
            $globHeader = DupArchiveGlobHeader::createFromFile($originalSize, $storeSize, $md5);
            // end profile ok

            // profile ok
            $globHeader->writeToArchive($archiveHandle);
            // end profile ok

            // profile ok
            if (fwrite($archiveHandle, $globContents) === false) {
                throw new Exception("Error writing $sourceFilepath to archive");
            }
            // end profile ok

            $fileSizeRemaining = $fileSize - $createState->globSize;

            $moreFileRemaining = $fileSizeRemaining > 0;

            return $moreFileRemaining;
        } else {
            // 0 Length file
            return false;
        }
    }

    // Assumption is that archive handle points to a glob header on this call
    private static function appendGlobToFile($expandState, $archiveHandle, $destFileHandle, $destFilePath)
    {
        /* @var $expandState DupArchiveExpandState */
     //   DupArchiveUtil::tlogObject('Expand State', $expandState);
        DupArchiveUtil::tlog("Appending file glob to file {$destFilePath} at file offset {$expandState->currentFileOffset}");

        // Read in the glob header but leave the pointer at the payload
        $globHeader = DupArchiveGlobHeader::readFromArchive($archiveHandle, false);

        $globContents = fread($archiveHandle, $globHeader->storedSize);

        if ($globContents === false) {
            throw new Exception("Error reading glob from $destFilePath");
        }

        //  Util::tlog("about to write contents to $destFilePath: " . $globContents);

        if ($expandState->isCompressed) {
            $globContents = gzinflate($globContents);
        }

        if (fwrite($destFileHandle, $globContents) === false) {
            throw new Exception("Error writing glob to $destFilePath");
        } else {
            DupArchiveUtil::tlog("Successfully wrote glob");
        }
    }
}