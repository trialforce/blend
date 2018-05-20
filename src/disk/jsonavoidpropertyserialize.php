<?php

namespace Disk;

/**
 * Avoid property to be serialize
 */
interface JsonAvoidPropertySerialize
{

    /**
     * List property to be avoided in serialize
     *
     * @return  array with property names
     */
    public function listAvoidPropertySerialize();
}