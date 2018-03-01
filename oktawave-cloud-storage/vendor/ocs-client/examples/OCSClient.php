<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Example usages of OCS Client class.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
require_once __DIR__.'/../ocs_init.php';

// Create new OSC Client instance for "somebucket" bucket
$OCSClient = new Oktawave_OCS_OCSClient('somebucket');

// Authenticate your OCS user
$OCSClient->authenticate('account:user', 'pa$$w0rd');

// Upload objects from given directory - recursively
$urls = $OCSClient->createObjectsFromDir(__DIR__.'/data', 'data/nested', true);

// Upload objects from given directory - not recursively
$urls = $OCSClient->createObjectsFromDir(__DIR__.'/data', 'data', false);

// Upload objects from paths
$paths = array(__DIR__.'/data/test.txt' => 'data/test.txt');
$urls = $OCSClient->createObjectsFromPaths($paths);

// Copy object
$copyUrl = $OCSClient->copyObject('data/test.txt', 'data/copy/test.txt');

// Rename object
$newUrl = $OCSClient->renameObject('data/test.txt', 'test_renamed.txt');

// Upload single object
$url = $OCSClient->createObject(__DIR__.'/data/test.txt', 'data/test.txt');

// Create empty directory
$url = $OCSClient->createDirectory('data/testdir');

// Download object
$fileContent = $OCSClient->downloadObject('data/test.txt');

// Save object to file
$filePath = $OCSClient->downloadObjectToFile('data/test.txt', __DIR__.'/data/testDownloaded.txt');

// Check if object exists
$isExisting = $OCSClient->checkObject('data/test.txt');

// Delete object
$isDeleted = $OCSClient->deleteObject('data/test.txt');

//  List all objects
$list = $OCSClient->listObjects();
var_dump($list);

//  List objects from pseudo-directory
$list = $OCSClient->listObjects('data');
var_dump($list);

//  Remove all objects (files)
//foreach ($list as $object) {
//    $OCSClient->deleteObject($object['name']);
//}
