<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once(dirname(__FILE__).'/../util/class.duparchive.util.php');
require_once(dirname(__FILE__).'/class.duparchive.header.u.php');

//require_once(dirname(__FILE__).'/class.HeaderBase.php');
// Format: #A#{version:5}#{isCompressed}!
class DupArchiveHeader// extends HeaderBase
{
    public $version;
    public $isCompressed;

    //   public $directoryCount;
    // public $fileCount;

    // Format Version History
    // 1 = Initial alpha format
    // 2 = Pseudo xml based format
    const LatestVersion = 2;
    const MaxHeaderSize = 60;

    private function __construct()
    {
        // Prevent instantiation
    }

  //  public static function create($isCompressed, $directoryCount, $fileCount, $version = self::LatestVersion)
    public static function create($isCompressed, $version = self::LatestVersion)
    {
        $instance = new DupArchiveHeader();

   //     $instance->directoryCount = $directoryCount;
        //  $instance->fileCount      = $fileCount;
        $instance->version        = $version;
        $instance->isCompressed   = $isCompressed;

        return $instance;
    }

    public static function readFromArchive($archiveHandle)
    {
        $instance = new DupArchiveHeader();

        $startElement = fgets($archiveHandle, 4);

        if ($startElement != '<A>') {
            throw new Exception("Invalid archive header marker found {$startElement}");
        }

        $instance->version           = (int)DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'V');
        $instance->isCompressed      = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'C') == 'true' ? true : false;

        // Skip the </A>
        fgets($archiveHandle, 5);

        return $instance;
    }

    public function writeToArchive($archiveHandle)
    {
        $isCompressedString = DupArchiveUtil::boolToString($this->isCompressed);

        $paddedVersion = sprintf("%04d", $this->version);
        //$paddedFileCount = sprintf("%09d", $this->fileCount);
        //$paddedDirectoryCount = sprintf("%09d", $this->directoryCount);
        //    SnapLibIOU::fwrite($archiveHandle, "?A#{$paddedVersion}#{$isCompressedString}#{$paddedDirectoryCount}#{$paddedFileCount}#A!");

        //SnapLibIOU::fwrite($archiveHandle, "?A#{$paddedVersion}#{$isCompressedString}#A!");
        SnapLibIOU::fwrite($archiveHandle, "<A><V>{$paddedVersion}</V><C>{$isCompressedString}</C></A>");
    }
}