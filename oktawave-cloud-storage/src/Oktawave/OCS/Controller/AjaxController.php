<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Migrator/UrlsReplacer.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Migrator/FilesUploader.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/OktawaveOCS.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Helper/Message.php';

/**
 * Controller handles ajax requests.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Controller_AjaxController
{
    /**
     * @var Oktawave_OCS_Migrator_UrlsReplacer
     */
    protected $urlsReplacer;

    /**
     * @var Oktawave_OCS_Helper_Message
     */
    protected $message;

    /**
     * @var Oktawave_OCS_Migrator_FilesUploader
     */
    protected $uploader;

    public function __construct()
    {
        if (is_admin()) {
            add_action('wp_ajax_ocs_check_account', array($this, 'checkAccount'));
            add_action('wp_ajax_ocs_test_sample_image', array($this, 'testSampleImage'));
            add_action('wp_ajax_ocs_run_on_posts', array($this, 'runOnPosts'));
            add_action('wp_ajax_ocs_migrate_files', array($this, 'migrateFiles'));
            add_action('wp_ajax_ocs_skip_upload_files', array($this, 'skipUploadFiles'));
            add_action('wp_ajax_ocs_get_files_stats', array($this, 'getFilesStats'));
        }
        $this->urlsReplacer = new Oktawave_OCS_Migrator_UrlsReplacer();
        $this->message = new Oktawave_OCS_Helper_Message();
        $this->uploader = new Oktawave_OCS_Migrator_FilesUploader();
    }

    /**
     * Checks if user account data is correct.
     *
     * return ajax response
     */
    public function checkAccount()
    {
        check_ajax_referer('check_account_nonce', 'security');

        $data = $_POST['data'];
        $username = $data['username'];
        $password = $data['password'];
        $bucket = $data['bucket'];

        try {
            $url = Oktawave_OCS_OktawaveOCS::checkAccount($username, $password, $bucket);

            if ($url !== false) {
                wp_send_json_success($url);
            } else {
                wp_send_json_error(__('Configuration for specified user and bucket already set in other site!', 'ocs_oktawave'));
            }
        } catch (Oktawave_OCS_Exception_HttpException $e) {
            $message = $this->message->getError($e->getHttpCode());

            wp_send_json_error($message);
        }
    }

    /**
     * Handles url replace via ajax.
     *
     * return ajax response
     */
    public function runOnPosts()
    {
        check_ajax_referer('run_on_posts_nonce', 'security');

        if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
            wp_send_json_error(sprintf(__('First you need to configure your OCS account <a href="%s/wp-admin/admin.php?page=oktawave_ocs_settings">here</a>.', 'ocs_oktawave'), get_site_url()));
        }

        $offset = $_POST['offset'];

        try {
            $response = $this->urlsReplacer->runOnPosts($offset);

            wp_send_json_success($response);
        } catch (Exception $e) {
            wp_send_json_error(__('Error! An error occurred, please try again.', 'ocs_oktawave'));
        }
    }

    /**
     * Uploads files to OCS via ajax.
     *
     * return ajax response
     */
    public function migrateFiles()
    {
        check_ajax_referer('migrate_files_nonce', 'security');

        if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
            wp_send_json_error(sprintf(__('First you need to configure your OCS account <a href="%s/wp-admin/admin.php?page=oktawave_ocs_settings">here</a>.', 'ocs_oktawave'), get_site_url()));
        }

        $offset = $_POST['offset'];

        try {
            $response = $this->uploader->uploadFiles($offset);

            if ($response['count'] === 0) {
                Oktawave_OCS_OktawaveOCS::setMigrated();
            }

            wp_send_json_success($response);
        } catch (Exception $e) {
            wp_send_json_error(__('Error! An error occurred, please try again.', 'ocs_oktawave'));
        }
    }

    /**
     * Uploads files to OCS via ajax.
     *
     * return ajax response
     */
    public function skipUploadFiles()
    {
        check_ajax_referer('skip_upload_files_nonce', 'security');

        if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
            wp_send_json_error(sprintf(__('First you need to configure your OCS account <a href="%s/wp-admin/admin.php?page=oktawave_ocs_settings">here</a>.', 'ocs_oktawave'), get_site_url()));
        }

        Oktawave_OCS_OktawaveOCS::setMigrated();

        wp_send_json_success();
    }

    /**
     * Read files stats.
     *
     * return ajax response
     */
    public function getFilesStats()
    {
        check_ajax_referer('get_files_stats_nonce', 'security');

        if (!Oktawave_OCS_OktawaveOCS::isConfigured()) {
            wp_send_json_error(sprintf(__('First you need to configure your OCS account <a href="%s/wp-admin/admin.php?page=oktawave_ocs_settings">here</a>.', 'ocs_oktawave'), get_site_url()));
        }

        try {
            $response = $this->uploader->getFilesStats();

            wp_send_json_success($response);
        } catch (Exception $e) {
            wp_send_json_error(__('Error! An error occurred, please try again.', 'ocs_oktawave'));
        }
    }

    /**
     * Uploads sample image and test availability.
     *
     * return ajax response
     */
    public function testSampleImage()
    {
        check_ajax_referer('test_sample_image_nonce', 'security');

        $data = $_POST['data'];
        $username = $data['username'];
        $password = $data['password'];
        $bucket = $data['bucket'];
        $url = $data['url'];

        $response = Oktawave_OCS_OktawaveOCS::testSampleImage($username, $password, $bucket, $url);

        if (array_key_exists('error', $response)) {
            wp_send_json_error($response['error']);
        } else {
            wp_send_json_success($response['success']);
        }
    }
}
