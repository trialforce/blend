<?php

namespace Log;

/**
 * Control the data for idx error controle messages
 *
 *  */
class IndexData
{

    protected $data = array();

    public function addIndex($name, $message)
    {
        $this->data[$name] = $message;
    }

    public function getIndex($name)
    {
        if (isset($name) && isset($this->data[$name]))
        {
            return $this->data[$name];
        }

        return null;
    }

}
