<?php

namespace Db;

use DataHandle\Config;

/**
 * File cache with catalog content
 * To avoid consult database catalog
 */
class CacheSql
{

    /**
     * Memcache
     *
     * @var \Memcached
     */
    protected static $memcache;

    /**
     * Get a file from cache
     *
     * @param string $table
     * @param string $query
     * @return \Disk\File
     */
    public static function getFile( $table, $query )
    {
        $fileName = strtolower( $table . DS . md5( $query ) );
        $file = \Disk\File::getFromStorage( 'cache' . DS . $fileName . '.sqlcache' );
        return $file;
    }

    /**
     * \Memcached
     *
     * @return \Memcached
     */
    public static function getMemcache()
    {
        $host = Config::get( 'dbSsqlCacheMemcacheHost' );
        $port = Config::get( 'dbSsqlCacheMemcachePort' ) ? Config::get( 'dbSsqlCacheMemcachePort' ) : 11211;

        if ( !self::$memcache )
        {
            self::$memcache = new \Memcached();
            self::$memcache->addServer( $host, $port );
        }

        return self::$memcache;
    }

    public static function clearForTable( $table )
    {
        if ( Config::get( 'dbSsqlCacheMemcacheHost' ) )
        {
            //\Log::debug( 'clear memcache' . $table );
        }
        else
        {
            //\Log::debug( 'clear file' . $table );
            $file = self::getFile( $table, NULL );
            $folder = $file->getFolder();

            $folder->clear();
        }
    }

    /**
     * Get the content of cache
     *
     * @return mixed|null
     */
    public static function get( $table, $query )
    {
        if ( Config::get( 'dbSsqlCacheMemcacheHost' ) )
        {
            //\Log::debug( 'set file' . $table . '- ' . $query );
            $memcached = self::getMemcache();
            return $memcached->get( $table . '_' . $query );
        }
        else
        {
            //\Log::debug( 'get file' . $table . '- ' . $query );
            $file = self::getFile( $table, $query );

            if ( $file->isFile() )
            {
                $file->load();

                return unserialize( $file->getContent() );
            }
        }

        return NULL;
    }

    /**
     * Define the content of cache
     *
     * @param $content
     */
    public static function set( $table, $query, $content )
    {
        if ( Config::get( 'dbSsqlCacheMemcacheHost' ) )
        {
            //\Log::debug( 'set memcache' . $table . '- ' . $query );
            $memcached = self::getMemcache();
            return $memcached->set( $table . '_' . $query, $content );
        }
        else
        {
            //\Log::debug( 'set file' . $table . '- ' . $query );
            $file = self::getFile( $table, $query );
            return $file->save( serialize( $content ) );
        }
    }

}
