<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DupArchiveHeaderU
{
    const MaxStandardHeaderFieldLength = 128;
    
    public static function readStandardHeaderField($archiveHandle, $ename)
    {
        $expectedStart = "<{$ename}>";
        $expectedEnd = "</{$ename}>";

        $startingElement = fread($archiveHandle, strlen($expectedStart));

        if($startingElement !== $expectedStart) {
            throw new Exception("Invalid starting element. Was expecting {$expectedStart} but got {$startingElement}");
        }

        return SnapLibStreamU::streamGetLine($archiveHandle, self::MaxStandardHeaderFieldLength, $expectedEnd);
    }
}