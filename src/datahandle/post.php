<?php

namespace DataHandle;

/**
 * Simples class to manage post super global
 */
class Post extends DataHandle
{

    /**
     * Construct the post
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __construct()
    {
        parent::__construct($_POST);
    }

}