<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Observer that hooks on events associated with attachments uploads, removals etc.
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */
class Oktawave_OCS_Utils_FilesPath
{
    /**
     * Makes upload path to files.
     * Cuts of base directory of uploads from the full file path.
     *
     * @param string $filepath
     *
     * @return string
     */
    public static function makeUploadPathFromFilepath($filepath)
    {
        $uploadPath = $filepath;

        $uploadDir = wp_upload_dir();
        $baseDir = $uploadDir['basedir'];
        $baseDirLength = strlen($baseDir);

        if (0 === strpos($filepath, $baseDir)) {
            $uploadPath = substr($filepath, $baseDirLength);
            $uploadPath = ltrim($uploadPath, "/\\");
        }

        $uploadPath = str_replace("\\", "/", $uploadPath);

        return $uploadPath;
    }

    public static function getBaseUploadDir()
    {
        $uploadDir = wp_upload_dir();

        return rtrim($uploadDir['basedir'], "/\\");
    }

    public static function getBaseUploadUrl()
    {
        $uploadDir = wp_upload_dir();

        return rtrim($uploadDir['baseurl'], "/\\");
    }
}
