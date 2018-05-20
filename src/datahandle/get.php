<?php

namespace DataHandle;

/**
 * Simples class to deal with Get super global
 */
class Get extends DataHandle
{

    /**
     * Construct the get
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct($_GET);
    }

}