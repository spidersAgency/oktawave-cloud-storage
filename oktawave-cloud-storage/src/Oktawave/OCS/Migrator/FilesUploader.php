<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Utils/FilesPath.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Helper/DirFilter.php';

/**
 * Uploads all files from Wordpress upload directory to Oktawave OCS.
 *
 * If file already exists in OCS (compares files hashes), it won't be uploaded
 * again - that speeds up the process :-)
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */
class Oktawave_OCS_Migrator_FilesUploader
{
    const PER_PAGE = 1;
    const CHECK_PER_PAGE = 100;

    public function __construct()
    {
    }

    public function getFilesStats()
    {
        $filesSize = 0;
        $filesCount = 0;
        $files = $this->getUploadedFiles();

        foreach ($files as $name => $file) {
            // don't count directories
            if (is_file($file)) {
                $filesCount++;
                $filesSize += filesize($file);
            }
        }

        return array(
            'bytes' => $filesSize,
            'count' => $filesCount,
        );
    }

    public function uploadFiles($offset = 0, $perPage = self::PER_PAGE, $checkPerPage = self::CHECK_PER_PAGE)
    {
        $filesSize = 0;
        $processed = 0;
        $checked = 0;
        $filesCounter = 0;
        $files = $this->getUploadedFiles();

        foreach ($files as $name => $file) {
            // don't upload directories
            if (is_file($file)) {
                if ($filesCounter >= $offset) {
                    $destination = Oktawave_OCS_Utils_FilesPath::makeUploadPathFromFilepath($file);
                    $fileHash = null;

                    try {
                        $metadata = Oktawave_OCS_OktawaveOCS::getOCS()->getObjectMetadata($destination);
                        $fileHash = md5_file($file);
                    } catch (Oktawave_OCS_Exception_HttpException $e) {
                        if (!$e->isNotFound()) {
                            throw $e;
                        }
                    }

                    // File already exists on OCS? Don't upload him again
                    if (!isset($metadata['hash']) || $metadata['hash'] !== $fileHash) {
                        Oktawave_OCS_OktawaveOCS::getOCS()->createObject($file, $destination);
                        $processed++;
                    }

                    $checked++;
                    $filesSize += filesize($file);

                    if ($processed >= $perPage || $checked >= $checkPerPage) {
                        break;
                    }
                }

                $filesCounter++;
            }
        }

        return array(
            'bytes' => $filesSize,
            'count' => $processed + $checked,
        );
    }

    protected function getUploadedFiles()
    {
        $baseUploadDir = Oktawave_OCS_Utils_FilesPath::getBaseUploadDir();
        $directory = new RecursiveDirectoryIterator($baseUploadDir, RecursiveDirectoryIterator::SKIP_DOTS);

        if (is_multisite() && is_main_site()) {
            $excludes = array("sites");
            $directory = new Oktawave_OCS_Helper_DirFilter($directory, $excludes);
        }

        $objects = new RecursiveIteratorIterator(
                $directory, RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        return $objects;
    }
}
