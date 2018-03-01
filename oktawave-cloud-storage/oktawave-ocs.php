<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
  Plugin Name: Oktawave OCS
  Plugin URI: http://oktawave.com/
  Description: Oktawave OCS to host your media files in the cloud!
  Version: 1.0.0
  Author: Oktawave Sp. z o.o.
  Author URI: http://oktawave.com/
  License: GPLv3
  Text Domain: oktawave-ocs
 */

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    exit;
}

define('OKTAWAVEOCS_VERSION', '1.0.1');
define('OKTAWAVEOCS__MINIMUM_WP_VERSION', '3.0');
define('OKTAWAVEOCS__PLUGIN_URL', plugin_dir_url(__FILE__));
define('OKTAWAVEOCS__PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, array('OktawaveOCS', 'pluginActivation'));
register_deactivation_hook(__FILE__, array('OktawaveOCS', 'pluginDeactivation'));

require_once OKTAWAVEOCS__PLUGIN_DIR.'/src/Oktawave/OCS/OktawaveOCS.php';

add_action('init', array('Oktawave_OCS_OktawaveOCS', 'init'));
