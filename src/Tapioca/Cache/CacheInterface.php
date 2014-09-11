<?php

/**
 * Tapioca: Schema Driven Data Engine 
 * PHP Client.
 *
 * @package   Tapioca
 * @version   v0.3
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/Client-php
 */

namespace Tapioca\Cache;

abstract class CacheInterface 
{

  /**
   * @var  string  App's slug
   */
  private $_slug = false;

  /**
   * @var  string  key prefix
   */
  private $_prefix = false;

  /**
   * @var  string  Cache TTL
   */
  private $_expire = 3600;

  /**
   * Merge App's slug + collection name + query
   * to get an unique MD5 hash as cache key
   *
   * @param   string     Collection slug
   * @return  string
   */
  protected function genKey( $collection, $query )
  {
    $key = $this->_slug . $collection . serialize( $query );
    return $key;
  }

 /**
  * Get cache 
  *
  * @param  string   storage key
  * @param  int      time to live
  * @return array
  */
 public function get( $collection, $query ) {}

 /**
  * Set cache 
  *
  * @param  string   storage key
  * @param  mixed    storage value
  * @return array
  */
 public function set( $collection, $query, $data ) {}

 /**
  * Clear cache, if no key clear all 
  *
  * @param  string   storage key
  * @return void
  */
 public function clear( $key = null ) {}
}