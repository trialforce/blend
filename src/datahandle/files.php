<?php

namespace DataHandle;

/**
 * Simple class to deal with files super global
 */
class Files extends DataHandle
{

    /**
     * Construct the files
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct($_FILES);
    }

}