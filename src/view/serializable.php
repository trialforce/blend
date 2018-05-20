<?php

namespace View;

/**
 * Make the view serializable
 */
interface Serializable
{

    /**
     * Function called before serialize
     */
    public function preSerialize();
}


