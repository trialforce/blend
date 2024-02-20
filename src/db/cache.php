<?php

namespace Db;

/**
 * File cache with catalog content
 * To avoid consult database catalog
 */
class Cache extends \Disk\File
{

    /**
     * @param $relativeFileName
     */
    public function __construct( $relativeFileName )
    {
        $fileName = strtolower( $relativeFileName );
        parent::__construct( self::getStoragePath() . '/cache/' . $fileName, TRUE );
    }

    /**
     * Get the content of cache
     *
     * @return mixed|null
     */
    public function getContent()
    {
        $content = parent::getContent();

        if ( strlen( $content ) > 0 )
        {
            return unserialize( parent::getContent() );
        }

        return NULL;
    }

    /**
     * Define the content of cache
     *
     * @param $content
     */
    public function setContent( $content )
    {
        parent::setContent( serialize( $content ) );
    }

}
