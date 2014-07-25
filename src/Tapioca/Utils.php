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

namespace Tapioca;

class Utils
{
  /**
   * Navigate through array, looking for a particular index
   *
   * @param string    The index sequence we are navigating down
   * @param mixed     value to return by default
   * @return mixed
   */
  public function get( $key = null, $default = null )
  {
    // if no key were passed - return the orignal document
    if ( is_null( $key ) )
    {
      return $this->_original;
    }
    else
    {
      $path = explode('.', $key);
      $data = $this->_original;

      foreach($path as $k)
      {
        if( isset( $data[ $k ] ) )
        {
          $data =& $data[ $k ];
        }
        else
        {
          return $default;
        }
      }

      return $data;
    }
  }

  public function set( $key, $value )
  {
    
  }
}