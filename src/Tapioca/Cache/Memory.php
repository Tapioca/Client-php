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

/**
 * Tapioca's Debug cache class, nothing is save
 *
 */

class Memory
  extends CacheInterface
{
  /**
   * @{inheritDoc}
   *
   */
  public function __construct( $slug, $prefix, $expiration = 3600, $options ) {}

  /**
   * @{inheritDoc}
   *
   */
  public function set( $collection, $query, $data )
  {
    return true;
  }

  /**
   * @{inheritDoc}
   *
   */
  public function get( $collection, $query )
  {
    return false;
  }

  /**
   * @{inheritDoc}
   *
   */
  public function clear( $key = null )
  {
    return true;
  }
}
