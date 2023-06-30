<?php

namespace Disk;

/*
 * Make file cache
 */

class Cache extends \Disk\File
{

    /**
     * @param $relativeFileName
     */
    public function __construct($relativeFileName)
    {
        parent::__construct(self::getStoragePath() . '/cache/' . $relativeFileName, TRUE);
    }

    /**
     * @param $content
     */
    public function setContent($content)
    {
        parent::setContent(serialize($content));
    }

    public function getContent()
    {
        $content = parent::getContent();

        if (strlen($content) > 0)
        {
            return unserialize(parent::getContent());
        }

        return NULL;
    }

}
