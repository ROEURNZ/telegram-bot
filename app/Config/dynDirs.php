<?php
/*
 * @ROEURNZ => File name & directory
 * backend/app/Config/dynDirs.php
 */

class XDirLevel
{
    public function dynamicDirectoryLevels($levels)
    {
        for ($i = 1; $i <= $levels; $i++) {
            global ${'n' . $i};
            ${'n' . $i} = $this->repeatString('/..', $i);
        }
    }
    // Custom method to repeat a string by a given factor
    private function repeatString($string, $count)
    {
        $result = '';
        for ($j = 0; $j < $count; $j++) {
            $result .= $string;
        }
        return $result;
    }

    // Method to create dynamic multiplied variables and assign them
    public function mDirectories($path, $factor)
    {
        $multipliedPaths = [];

        // Loop to create multiplied paths
        for ($i = 1; $i <= $factor; $i++) {
            $multipliedPaths['m' . $i] = $this->repeatString($path, $i);
        }

        // Loop to assign multiplied paths to dynamically named variables
        for ($i = 1; $i <= $factor; $i++) {
            global ${'m' . $i};
            ${'m' . $i} = $multipliedPaths['m' . $i];
        }

        return $multipliedPaths;
    }
}

// Usage class and functions 
$xDirLevel = new XDirLevel();
$xDirLevel->dynamicDirectoryLevels(10);
$xDirLevel->mDirectories($n2, 10);
