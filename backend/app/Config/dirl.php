<?php
/* 
 * @ROEURNZ => File name & directory
 * backend/app/Config/dirl.php
 */

class XDirLevel /**** X Directory Levels ****/
{
    public function __construct()
    {
        // Loop to define directory levels
        for ($i = 1; $i <= 4; $i++) {
            global ${'x' . $i}; 
            ${'x' . $i} = str_repeat('/..', $i); /****  Concatenates '/..' based on the level, connect with other . '/' when '/..' . '/' be '/../' ****/
        }
    }
}

