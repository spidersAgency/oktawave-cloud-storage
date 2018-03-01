<?php

/*
 * Copyright (C) 2014 Oktawave Sp. z o.o. - oktawave.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Rafał Lorenz <rlorenz@octivi.com>
 */
class Oktawave_OCS_Helper_DirFilter extends RecursiveFilterIterator
{
    protected $exclude;

    public function __construct($iterator, array $exclude)
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }

    public function accept()
    {
        return !($this->isDir() && in_array($this->getFilename(), $this->exclude));
    }

    public function getChildren()
    {
        return new Oktawave_OCS_Helper_DirFilter($this->getInnerIterator()->getChildren(), $this->exclude);
    }
}
