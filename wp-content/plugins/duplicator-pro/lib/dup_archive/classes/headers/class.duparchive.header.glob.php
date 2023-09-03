<?php
require_once(dirname(__FILE__).'/../util/class.duparchive.util.php');
require_once(dirname(__FILE__).'/class.duparchive.header.u.php');

// Format
// #C#{$originalSize}#{$storedSize}!
class DupArchiveGlobHeader //extends HeaderBase
{
    //	public $marker;
    public $originalSize;
    public $storedSize;
    public $md5;

    const MaxHeaderSize = 255;

    private function __construct()
    {

    }

    public static function createFromFile($originalSize, $storedSize, $md5)
    {
        $instance = new DupArchiveGlobHeader;

        $instance->originalSize = $originalSize;
        $instance->storedSize   = $storedSize;
        $instance->md5 = $md5;

        return $instance;
    }

    public static function readFromArchive($archiveHandle, $skipGlob)
    {
        $instance = new DupArchiveGlobHeader();

        DupArchiveUtil::log('Reading glob starting at ' . ftell($archiveHandle));

        $startElement = fread($archiveHandle, 3);

        //if ($marker != '?G#') {
        if ($startElement != '<G>') {
            throw new Exception("Invalid glob header marker found {$startElement}. location:" . ftell($archiveHandle));
        }

        $instance->originalSize           = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'OS');
        $instance->storedSize             = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'SS');
        $instance->md5                    = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'MD5');

        // Skip the </G>
        fread($archiveHandle, 4);
        
        if ($skipGlob) {
            SnapLibIOU::fseek($archiveHandle, $instance->storedSize, SEEK_CUR);
        }

        return $instance;
    }

    public function writeToArchive($archiveHandle)
    {
        // <G><OS>x</OS>x<SS>x</SS><MD5>x</MD5></G>

//        $headerString = "?G#{$this->originalSize}#{$this->storedSize}#{$this->md5}#G!";
        $headerString = "<G><OS>{$this->originalSize}</OS><SS>{$this->storedSize}</SS><MD5>{$this->md5}</MD5></G>";

        SnapLibIOU::fwrite($archiveHandle, $headerString);
    }
}