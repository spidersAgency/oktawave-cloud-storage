<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Utils/FilesPath.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/OktawaveOCS.php';

/**
 * Observer that hooks on events associated with attachments uploads, removals etc.
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */
class Oktawave_OCS_Observer_AttachmentsObserver
{
    public function __construct()
    {
        $this->initHooks();
    }

    /**
     * Init hooks.
     */
    public function initHooks()
    {
        if (Oktawave_OCS_OktawaveOCS::isConfigured() && Oktawave_OCS_OktawaveOCS::isMigrated()) {
            // Uploading new attachment
            add_action('wp_handle_upload', array($this, 'onHandleUpload'));

            // Removing attachment
            add_action('wp_delete_file', array($this, 'onDeleteFile'));

            // Generating array with upload directories
            add_action('upload_dir', array($this, 'onUploadDir'));

            // Occurs when creating thumbnails or editing attachment's image
            add_action('image_make_intermediate_size', array($this, 'onMakeIntermediateSize'));
        }
    }

    /**
     * Uploads file to Oktawave OCS after uploading WP's attachment.
     * Listens for 'wp_handle_upload' event.
     *
     * @param string[] $args
     *
     * @return string[]
     */
    public function onHandleUpload($args)
    {
        $uploadPath = Oktawave_OCS_Utils_FilesPath::makeUploadPathFromFilepath($args['file']);

        $ocs = Oktawave_OCS_OktawaveOCS::getOCS();

        $url = $ocs->createObject($args['file'], $uploadPath);
        $args['url'] = $url;

        return $args;
    }

    /**
     * Deletes file on Oktawave OCS while removing it from Wordpress.
     *
     * @param string $args
     *
     * @return string
     */
    public function onDeleteFile($args)
    {
        $uploadPath = Oktawave_OCS_Utils_FilesPath::makeUploadPathFromFilepath($args);

        try {
            $ocs = Oktawave_OCS_OktawaveOCS::getOCS();
            $ocs->deleteObject($uploadPath);
        } catch (Oktawave_OCS_Exception_HttpException $e) {
            error_log((string) $e);
            // We can't throw exception as it will can cause strange things in
            // the deletion process...
        }

        return $args;
    }

    /**
     * Changes url and baseurl of the array with generated upload directories.
     * Listens for 'upload_dir' event.
     *
     * @param string[] $args
     *
     * @return string[]
     */
    public function onUploadDir($args)
    {
        $mediaUrl = Oktawave_OCS_OktawaveOCS::getMediaUrl();

        $subDir = ltrim($args['subdir'], "/");

        $args['url'] = $mediaUrl."/".$subDir;
        $args['baseurl'] = $mediaUrl;

        return $args;
    }

    /**
     * Hooks on saving edited images - while generating thumbnails etc.
     * Listens for 'image_make_intermediate_size' event.
     *
     * @param string $filepath
     *
     * @return string
     */
    public function onMakeIntermediateSize($filepath)
    {
        $ocs = Oktawave_OCS_OktawaveOCS::getOCS();
        $url = $ocs->createObject($filepath, Oktawave_OCS_Utils_FilesPath::makeUploadPathFromFilepath($filepath));

        return $filepath;
    }
}
