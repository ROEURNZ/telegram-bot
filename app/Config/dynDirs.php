<?php

if (!class_exists('XDirLevel')) {
    class XDirLevel
    {
        public function dynamicDirectoryLevels($levels)
        {
            for ($i = 1; $i <= $levels; $i++) {
                global ${'n' . $i}, ${'z' . $i};
                ${'n' . $i} = $this->repeatString('/..', $i);
                ${'z' . $i} = $this->repeatString('/..', $i) . '/'; // append forward slash at the end repeat of '/..'
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
                $multipliedPaths['mdl' . $i] = $this->repeatString($path, $i);
            }

            // Loop to assign multiplied paths to dynamically named variables
            for ($i = 1; $i <= $factor; $i++) {
                global ${'mdl' . $i};
                ${'mdl' . $i} = $multipliedPaths['mdl' . $i];
            }

            return $multipliedPaths;
        }
    }
}


// Usage class and functions 
$xDirLevel = new XDirLevel();
$xDirLevel->dynamicDirectoryLevels(100);
$xDirLevel->mDirectories($n2, 100);
