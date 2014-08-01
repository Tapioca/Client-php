<?php

/**
* Tapioca: Schema Driven Data Engine 
* PHP Client.
*
* @package   Tapioca
* @version   v0.3
* @author    Michael Lefebvre
* @license   MIT License
* @copyright 2014 Michael Lefebvre
* @link      https://github.com/Tapioca/Client-php
*/

namespace Tapioca\Cache;

use Tapioca\Exception;

class Filesystem
  extends CacheInterface
{
  /**
   * @var  string  path to cache directory
   */
  private $_path = false;

  /**
   * @var  string  file's extention
   */
  private $_ext = false;

  /**
   * Constructor
   *
   * @access public
   * @param  string   App's slug
   * @param  string   cache directory
   * @throws TapiocaCacheException
   * @return void
   */
  public function __construct( $slug, $prefix, $expiration = 3600, $options )
  {
    $this->_slug   = $slug;
    $this->_prefix = $prefix;
    $this->_expire = $expiration;

    $default = array(
        'path'      => false
      , 'extention' => 'cache'
    );

    $settings = array_merge( $default, $options );

    if( !is_dir( $settings['path'] ) OR !is_writable( $settings['path'] ) )
    {
      throw new Exception\CacheException( 'Cache directory does not exists or is not writable.' );
    }

    $this->_path = $settings['path'];
    $this->_ext  = $settings['extention'];
  }

  /**
   * File's path
   *
   * @param  string   file name
   * @return string
   */
  private function _name( $key )
  {
    return sprintf( "%s%s-%s.%s", $this->_path, $this->_prefix, sha1( $key ), $this->_ext );
  }

  public function set( $collection, $query, $data )
  {

    $key = $this->genKey( $collection, $query );

    $cache_path = $this->_name( $key );

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

  /**
   * Get cache from filesystem if exist
   *
   * @param  string   file name
   * @param  string   file time to live
   * @return object|array
   */
  public function get( $collection, $query )
  {
    if( !$this->_path )
      return false;
    
    $key = $this->genKey( $collection, $query );

    $cache_path = $this->_name( $key );

    if( !@file_exists( $cache_path ) )
    {
      return false;
    }

    if( filemtime( $cache_path ) < ( time() - $this->_expire ) )
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

  public function clear( $key = null )
  {
    if( is_null( $key ))
    {
      $files = glob($this->_path . "/*.cache" );

      foreach($files as $file) 
        unlink($file); 

      return true; 
    }

    $cache_path = $this->_name( $key );

    if( file_exists( $cache_path ) )
    {
      unlink( $cache_path );
      
      return true;
    }

    return false;
  }
}
