<?php

namespace App\Helpers;

class File
{
    public static function generateFilename($filename)
    {
        $timestamp = now()->timestamp;
        $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
        $result = $timestamp.uniqid().'_'.$filenameWithoutExtension;
        $result = preg_replace('/[^A-Za-z0-9 ]/', '_', $result);
        $result = str_replace(' ', '_', $result);
        $result = $result.'.'.$fileExtension;

        return $result;
    }
}
