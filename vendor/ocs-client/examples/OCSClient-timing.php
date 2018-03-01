<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Simple timing test.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
require_once __DIR__.'/../ocs_init.php';

if (count($argv) != 5) {
    echo('You must provide correct arguments:'.PHP_EOL);
    echo('$ php OCSClient-timing.php username password bucket path/to/files');
    die;
}

// Create new OSC Client instance for "somebucket" bucket
$OCSClient = new Oktawave_OCS_OCSClient($argv[3]);

// Authenticate your OCS user
$OCSClient->authenticate($argv[1], $argv[2]);

// Upload objects from given directory - recursively
$urls = $OCSClient->createObjectsFromDir($argv[4], 'timing/'.date('c'), true);

echo('Stats:'.PHP_EOL);
var_dump($OCSClient->getStats());
