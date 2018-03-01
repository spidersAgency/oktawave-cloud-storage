<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Observer/AttachmentsObserver.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Observer/MenuObserver.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Controller/AjaxController.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/vendor/ocs-client/ocs_init.php';

/**
 * Main class of the Oktawave OCS Plugin.
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */
class Oktawave_OCS_OktawaveOCS
{
    /**
     * @var Oktawave_OCS_OCSClient
     */
    protected static $ocs;

    /**
     * @var Oktawave_OCS_Observer_AttachmentsObserver
     */
    protected static $attachmentsObserver;

    /**
     * @var Oktawave_OCS_Controller_AjaxController
     */
    protected static $ajaxController;

    /**
     * @var Oktawave_OCS_Observer_MenuBuilder
     */
    protected static $menuObserver;
    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::initObservers();
        }
    }

    /**
     * Inits observers.
     */
    protected static function initObservers()
    {
        self::$attachmentsObserver = new Oktawave_OCS_Observer_AttachmentsObserver();
        self::$menuObserver = new Oktawave_OCS_Observer_MenuObserver();
        self::$ajaxController = new Oktawave_OCS_Controller_AjaxController();
    }

    /**
     * Method to retrieve Attachments Observer.
     *
     * @return Oktawave_OCS_Observer_AttachmentsObserver
     */
    public static function getAttachmentsObserver()
    {
        return self::$attachmentsObserver;
    }

    /**
     * Method to retrieve OCS Client instance.
     *
     * @return Oktawave_OCS_OCSClient
     */
    public static function getOCS()
    {
        if (!self::$ocs) {
            $bucket = self::getBucket();
            $username = self::getUsername();
            $password = self::getPassword();

            self::$ocs = new Oktawave_OCS_OCSClient($bucket);
            self::$ocs->authenticate($username, $password);
        }

        return self::$ocs;
    }

    /**
     * Retrieve username from saved Options.
     *
     * @return string
     */
    public static function getUsername()
    {
        $ocsSettings = get_option('ocs_settings');

        if (array_key_exists('username', $ocsSettings)) {
            return $ocsSettings['username'];
        } else {
            return;
        }
    }

    /**
     * Retrieve password from saved Options.
     *
     * @return string
     */
    public static function getPassword()
    {
        $ocsSettings = get_option('ocs_settings');

        if (array_key_exists('password', $ocsSettings)) {
            return $ocsSettings['password'];
        } else {
            return;
        }
    }

    /**
     * Retrieve bucket from saved Options.
     *
     * @return string
     */
    public static function getBucket()
    {
        $ocsSettings = get_option('ocs_settings');

        if (array_key_exists('bucket', $ocsSettings)) {
            return $ocsSettings['bucket'];
        } else {
            return;
        }
    }

    /**
     * Retrieve media url from saved Options.
     *
     * @return string
     */
    public static function getMediaUrl()
    {
        $ocsSettings = get_option('ocs_settings');

        if (array_key_exists('media_url', $ocsSettings)) {
            return rtrim($ocsSettings['media_url'], '/');
        } else {
            return;
        }
    }

    /**
     * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook().
     */
    public static function plugin_activation()
    {
    }

    /**
     * Removes all connection options.
     */
    public static function plugin_deactivation()
    {
    }

    /**
     * Checks if plugin is configured.
     *
     * @return boolean
     */
    public static function isConfigured()
    {
        return get_option('ocs_settings_isconfigured');
    }

    /**
     * Checks if files are migrated to OCS.
     *
     * @return boolean
     */
    public static function isMigrated()
    {
        return get_option('ocs_settings_ismigrated');
    }

    /**
     * Set migrated option.
     */
    public static function setMigrated()
    {
        update_option('ocs_settings_ismigrated', true);
    }

    /**
     * Checks if user account data is correct and if configuration already used in other site.
     *
     * @param string $username
     * @param string $password
     * @param string $bucket
     *
     * @return string
     */
    public static function checkAccount($username, $password, $bucket)
    {
        if (self::isConfigurationUsed($username, $bucket)) {
            return false;
        }

        $ocsClient = new Oktawave_OCS_OCSClient($bucket);

        $ocsClient->authenticate($username, $password);

        $randomDirName = uniqid('', true);

        $url = $ocsClient->createDirectory('wordpresstest/'.$randomDirName);
        $isDeleted = $ocsClient->deleteObject('wordpresstest/'.$randomDirName);

        return $ocsClient->getStorageUrl();
    }

    /**
     * Checks if given access is public.
     *
     * @param string $username
     * @param string $password
     * @param string $bucket
     *
     * @return string
     */
    public static function testSampleImage($username, $password, $bucket, $url)
    {
        try {
            $ocs = self::createOCS($username, $password, $bucket);

            $unique = md5(self::getUsername().self::getBucket());
            $imageURL = $ocs->createObject(dirname(__FILE__).'/../../../images/ocs_sample.png', 'oktawave_logo_'.$unique.'.png');
        } catch (Oktawave_OCS_Exception_HttpException $e) {
            return array('error' => __('Couldn\'t upload sample image to your OCS. Please, check your configuration and try again.', 'ocs_oktawave'));
        }

        $urlToRemove = $ocs->getStorageUrl().'/'.$bucket.'/';
        $imageURL = $url.str_replace($urlToRemove, '', $imageURL);

        $httpCode = self::getHttpCode($imageURL);

        if ($httpCode === 200) {
            return array('success' => $imageURL);
        } elseif ($httpCode === 404) {
            return array('error' => sprintf(__('Couldn\'t download sample image. It seems that your Oktawave OCS is not configured for public access. Go to your %sOktawave OCS administration panel</a> and turn on "Enable public access."', 'ocs_oktawave'), '<a href="https://admin.oktawave.com/Pages/Services/EditStorageContainer.aspx?cntNm='.self::getBucket().'" class="external">'));
        } else {
            return array('error' => sprintf(__('Couldn\'t connect to server (HTTP Error %s). Please try again later', 'ocs_oktawave'), $httpCode));
        }
    }

    /**
     * Create ocs client for given input.
     *
     * @param string $username
     * @param string $password
     * @param string $bucket
     *
     * @return Oktawave_OCS_OCSClient
     */
    public static function createOCS($username, $password, $bucket)
    {
        $ocsClient = new Oktawave_OCS_OCSClient($bucket);

        $ocsClient->authenticate($username, $password);

        return $ocsClient;
    }

    /**
     * Check if configuration already used in other site.
     *
     * @global type $wpdb
     *
     * @param string $username
     * @param string $bucket
     *
     * @return boolean
     */
    protected static function isConfigurationUsed($username, $bucket)
    {
        if (is_multisite()) {
            global $wpdb;
            $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs} WHERE site_id = '{$wpdb->siteid}'");

            foreach ($blogs as $blog) {
                if (get_current_blog_id() != $blog->blog_id) {
                    $options = get_blog_option($blog->blog_id, 'ocs_settings');

                    if ($username == $options['username'] && $bucket == $options['bucket']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Get http code from url.
     *
     * @param string $url
     *
     * @return string
     */
    protected static function getHttpCode($url)
    {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FAILONERROR, 1);
        curl_setopt($process, CURLOPT_CAINFO, dirname(__FILE__).'/ca-bundle.crt');

        $return = curl_exec($process);
        $httpCode = curl_getinfo($process, CURLINFO_HTTP_CODE);

        curl_close($process);

        return $httpCode;
    }
}
