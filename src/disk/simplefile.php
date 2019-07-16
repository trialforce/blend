<?php

namespace Disk;

class SimpleFile extends \Disk\File
{

    public function getPath()
    {
        return $this->path;
    }

    public function getUrl($type = 1)
    {
        return $this->path;
    }

}
