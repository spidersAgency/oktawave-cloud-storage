Oktawave - PHP OCS Client
================================================

php-ocsclient is a PHP library to communicate with [Oktawave OCS](https://www.oktawave.com/storage.html).


Installation
------------------------------------------------

### Installing via Composer

The recommended way to install OCS Client is through [Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php

# Add OCS Client dependency to your project
php composer.phar require oktawave/php-ocsclient:*
```

After installing, you need to require Composer's autoloader:

```php
<?php

require_once 'vendor/autoload.php';
```

### Standalone installation

1. Download the neweset release
2. Include initialization script in your code that loads all required classes
```php
<?php

require_once 'path/to/ocs/ocs_init.php';
```

Usage
------------------------------------------------

For all example cases of using client you should take a look at the 
example code from [examples/OCSClient.php](examples/OCSClient.php).

### Authentication

To use OCS you must authenticate your account.

```php
<?php
// Create new OSC Client instance for "somebucket" bucket
$OCSClient = new Oktawave_OCS_OCSClient('somebucket');

// Authenticate your OCS user
$OCSClient->authenticate('account:user', 'pa$$w0rd');
```

### Objects manipulation

```php
<?php
// Upload single object
$url = $OCSClient->createObject('/path/to/file.txt', 'destination/path/file.txt');

// Download object
$fileContent = $OCSClient->downloadObject('destination/path/file.txt');

//  Get a list of all objects
$list = $OCSClient->listObjects();
```

Copyright
------------------------------------------------

Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com

Released under GNU General Public License v3.0. For the full copyright and 
license information, please view the [LICENSE file](LICENSE) that was distributed with this source code.
