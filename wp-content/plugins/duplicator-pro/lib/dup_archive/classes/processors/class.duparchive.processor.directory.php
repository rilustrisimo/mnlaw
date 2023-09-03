<?php

require_once(dirname(__FILE__).'/../headers/class.duparchive.header.directory.php');

if (!class_exists('class')) {

    class DupArchiveDirectoryProcessor
    {
        public static function writeDirectoryToArchive($createState, $archiveHandle, $sourceDirectoryPath, $relativeDirectoryPath)
        {
            //DupArchiveUtil::writeToPLog("writedirectoryto archive start");

            /* @var $createState DupArchiveCreateState */

            $directoryHeader = DupArchiveDirectoryHeader::createFromDirectory($sourceDirectoryPath, $relativeDirectoryPath);
            
            $directoryHeader->writeToArchive($archiveHandle);

            // Just increment this here - the actual state save is on the outside after timeout or completion of all directories
            $createState->currentDirectoryIndex++;

            //DupArchiveUtil::writeToPLog("writedirectoryto archive end");

        }
    }
}