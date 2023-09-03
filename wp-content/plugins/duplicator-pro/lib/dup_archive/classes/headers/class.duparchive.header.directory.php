<?php
require_once(dirname(__FILE__).'/../util/class.duparchive.util.php');
require_once(dirname(__FILE__).'/class.duparchive.header.u.php');

// Format
// #F#{$file_size}#{$mtime}#{$file_perms}#{$md5}#{$relative_filepath_length}#{$relative_filepath}!
class DupArchiveDirectoryHeader// extends HeaderBase
{
    public $mtime;
    public $permissions;
    public $relativePathLength;
    public $relativePath;

    const MaxHeaderSize                = 8192;
    const MaxPathLength                = 4100;
    //const MaxStandardHeaderFieldLength = 128;

    private function __construct()
    {
        // Prevent direct instantiation
    }

    static function createFromDirectory($directoryPath, $relativePath)
    {
        $instance = new DupArchiveDirectoryHeader();

        $instance->permissions        = substr(sprintf('%o', fileperms($directoryPath)), -4);
        $instance->mtime              = SnapLibIOU::filemtime($directoryPath);
        $instance->relativePath       = $relativePath;
        $instance->relativePathLength = strlen($instance->relativePath);

        return $instance;
    }

    static function readFromArchive($archiveHandle, $skipStartElement = false)
    {
        $instance = new DupArchiveDirectoryHeader();

        if(!$skipStartElement)
        {
           // $marker = fgets($archiveHandle, 4);
            // <A>
           $startElement = fread($archiveHandle, 3);

            if ($startElement === false) {
                if (feof($archiveHandle)) {
                    return false;
                } else {
                    throw new Exception('Error reading directory header');
                }
            }

            //if ($marker != '?D#') {
            if ($startElement != '<D>') {
                throw new Exception("Invalid directory header marker found [{$startElement}] : location ".ftell($archiveHandle));
            }
        }

        // ?D#{mtime}#{permissions}#{$this->relativePathLength}#{$relativePath}#D!";

//        $instance->mtime              = self::readStandardHeaderField($archiveHandle);
//        $instance->permissions        = self::readStandardHeaderField($archiveHandle);
//        $instance->relativePathLength = self::readStandardHeaderField($archiveHandle);

        $instance->mtime              = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'MT');
        $instance->permissions        = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'P');
        $instance->relativePathLength = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'RPL');

        // Skip the <RP>
        fread($archiveHandle, 4);

        $instance->relativePath       = fread($archiveHandle, $instance->relativePathLength);

        // Skip the </RP>
        fread($archiveHandle, 5);

        // Skip the </D>
        fread($archiveHandle, 4);

        return $instance;
    }

    public function writeToArchive($archiveHandle)
    {
        if($this->relativePathLength == 0)
        {
            // Don't allow a base path to be written to the archive
            return;
        }
        
     //   $headerString = "?D#{$this->mtime}#{$this->permissions}#{$this->relativePathLength}#{$this->relativePath}#D!";
         $headerString = "<D><MT>{$this->mtime}</MT><P>{$this->permissions}</P><RPL>{$this->relativePathLength}</RPL><RP>{$this->relativePath}</RP></D>";

        SnapLibIOU::fwrite($archiveHandle, $headerString);
    }

}