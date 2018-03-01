<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Oktawave_OCS_Exception_HttpException hold exception from Oktawave OCS
 * HTTP response.
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Exception_HttpException extends Oktawave_OCS_Exception_OCSException
{
    protected $httpCode;
    protected $body;

    public function __construct($message, $code = 0, $body = '', $httpCode = -1, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->httpCode = $httpCode;
        $this->body = $body;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function isNotFound()
    {
        return 404 === $this->httpCode;
    }

    public function __toString()
    {
        return __CLASS__.": [{$this->code}]: {$this->message} with body \"{$this->body}\"";
    }
}
