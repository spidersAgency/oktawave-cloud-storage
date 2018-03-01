<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Old-school classes loading for oldskool Wordpress and Magento instances :-).
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */

// Exception classes
require_once __DIR__.'/src/Oktawave/OCS/Exception/OCSException.php';
require_once __DIR__.'/src/Oktawave/OCS/Exception/FormatNotSupportedException.php';
require_once __DIR__.'/src/Oktawave/OCS/Exception/HttpException.php';
require_once __DIR__.'/src/Oktawave/OCS/Exception/NotAuthenticatedException.php';

// Main client class
require_once __DIR__.'/src/Oktawave/OCS/OCSClient.php';
