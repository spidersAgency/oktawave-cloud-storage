<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Controller/SettingsController.php';
require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/Controller/MigrateController.php';

/**
 * Build admin menu for Ocs plugin.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Observer_MenuObserver
{
    /**
     * @var Oktawave_OCS_Controller_SettingsController
     */
    protected $settings;

    /**
     * @var Oktawave_OCS_Controller_MigrateController
     */
    protected $migrate;

    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'buildMenu'));

            $this->settings = new Oktawave_OCS_Controller_SettingsController();
            add_action('admin_init', array($this->settings, 'pagesInit'));

            $this->migrate = new Oktawave_OCS_Controller_MigrateController();
        }
    }

    public function buildMenu()
    {
        add_menu_page(
                __('Oktawave OCS', 'ocs_oktawave'), __('Oktawave OCS', 'ocs_oktawave'), 'manage_options', 'oktawave_ocs_settings', array($this->settings, 'settingsPage'), 'dashicons-upload'
        );

        add_submenu_page(
                'oktawave_ocs_settings', __('Oktawave OCS', 'ocs_oktawave'), __('Settings', 'ocs_oktawave'), 'manage_options', 'oktawave_ocs_settings', array($this->settings, 'settingsPage')
        );

        add_submenu_page(
                'oktawave_ocs_settings', __('Oktawave OCS - Migrate', 'ocs_oktawave'), __('Migrate', 'ocs_oktawave'), 'manage_options', 'oktawave_ocs_migrate', array($this->migrate, 'migratePage')
        );
    }
}
