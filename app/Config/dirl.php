<?php

class XDirLevel 
{
    public function __construct()
    {
        // Loop to define directory levels
        for ($i = 1; $i <= 10; $i++) {
            global ${'x' . $i}; 
            ${'x' . $i} = str_repeat('/..', $i); 
        }
    }
}

