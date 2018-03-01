<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Helper class to retrieve error messages.
 *
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Helper_Message
{
    protected static $errors = array(
        '400' => 'Error! The request cannot be fulfilled due to bad syntax',
        '401' => 'Error! Username or password is incorrect',
        '403' => 'Error! Wrong bucket name',
        '404' => 'Error! Bucket could not be found',
        '500' => 'Error! Internal Server Error',
        '503' => 'Error! The server is currently unavailable',
    );

    public function __construct()
    {
    }

    public function getError($code)
    {
        return __(self::$errors[$code]);
    }

    /**
     * Return succes message for check account button.
     *
     * @return string
     */
    public function getSynchronizeUrlsSuccesMessage($type)
    {
        switch ($type) {
            case 'success':
                return __('Synchronize success. The Media URLs configuration has been saved.', 'ocs_oktawave');
            default :
                return __('Synchronize error! Please reapeat the Media URLs configuration.', 'ocs_oktawave');
        }
    }
}
