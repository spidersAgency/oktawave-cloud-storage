<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Oktawave_OCS_Exception_FormatNotSupportedException.
 *
 * @author Antoni Orfin <aorfin@octivi.com>
 */
class Oktawave_OCS_Exception_FormatNotSupportedException extends Oktawave_OCS_Exception_OCSException
{
    /**
     * @var string[]
     */
    protected $supportedFormats;

    public function __construct($format, array $supportedFormats, $code = 0, Exception $previous = null)
    {
        $message = sprintf('Format "%s" is not supported. Supported formats are: %s', $format, implode(', ', $supportedFormats));
        parent::__construct($message, $code, $previous);
    }

    public function getSupportedFormats()
    {
        return $this->supportedFormats;
    }
}
