<?php

/**
 * Tapioca: Schema Driven Data Engine
 * PHP Client.
 *
 * @package   Tapioca
 * @version   v0.2
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2012 Michael Lefebvre
 * @link      https://github.com/Tapioca/Client-php
 */

namespace Tapioca;

/**
 * Based on Jon Gales's JG_Cache
 * http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
 */

class TapiocaCacheException extends \Exception {}

class Cache
{
    /**
     * @var  string  path to cache directory
     */
    private $dir = false;

    /**
     * Constructor
     *
     * @access public
     * @param  string   cache directory
     * @throws TapiocaCacheException
     * @return void
     */
    public function __construct( $dir )
    {
        if ( !is_dir($dir) OR !is_writable($dir))
        {
            throw new TapiocaCacheException( 'Cache directory does not exists or is not writable.' );
        }
        
        $this->dir = $dir;
    }

    /**
     * File's path
     *
     * @param  string   file name
     * @return string
     */
    private function _name( $key )
    {
        return sprintf("%s/%s", $this->dir, sha1( $key ) );
    }

    /**
     * Get cache from filesystem if exist
     *
     * @param  string   file name
     * @param  string   file time to live
     * @return object|array
     */
    public function get( $key, $expiration = 3600 )
    {
        if( !$this->dir )
            return false;

        $cache_path = $this->_name($key);

        if( !@file_exists( $cache_path ) )
        {
            return false;
        }

        if( filemtime( $cache_path ) < ( time() - $expiration ) )
        {
            $this->clear( $key );

            return false;
        }

        if( !$fp = @fopen( $cache_path, 'rb' ) )
        {
            return false;
        }

        flock( $fp, LOCK_SH );

        $cache = '';

        if( filesize( $cache_path ) > 0 )
        {
            $cache = unserialize( fread( $fp, filesize( $cache_path ) ) );
        }
        else
        {
            $cache = NULL;
        }

        flock( $fp, LOCK_UN );
        fclose( $fp );

        return $cache;
    }

    public function set( $key, $data )
    {
        if( !$this->dir )
            return false;

        $cache_path = $this->_name($key);

        if( !$fp = fopen( $cache_path, 'wb' ) )
        {
            return false;
        }

        if( flock( $fp, LOCK_EX ) )
        {
            fwrite( $fp, serialize( $data ) );
            flock( $fp, LOCK_UN );
        }
        else
        {
            return false;
        }

        fclose( $fp);
        @chmod( $cache_path, 0777 );

        return true;
    }

    public function clear( $key )
    {
        $cache_path = $this->_name( $key );

        if(file_exists( $cache_path ) )
        {
            unlink( $cache_path );
            
            return true;
        }

        return false;
    }
}