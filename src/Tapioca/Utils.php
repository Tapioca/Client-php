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
   * @param array     where to search
   * @param string    The index sequence we are navigating down
   * @param mixed     value to return by default
   * @return mixed
   */
  public static function get( array $source, $key = null, $default = null )
  {
    // if no key were passed - return the orignal document
    if ( is_null( $key ) )
    {
      return $source;
    }
    else
    {
      $path = explode('.', $key);
      $data = $source;

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

  /**
   * Deep merge of default config with user config
   * $a will be result. $a will be edited. 
   * It's to avoid a lot of copying in recursion
   *
   * @param   array    user settings
   * @param   array    default config
   * @return  Tapioca
   */
  public static function merge( &$a, $b )
  { 
    foreach( $b as $child => $value )
    {
      if( isset( $a[$child] ) )
      { 
        // merge if they are both arrays
        if( is_array( $a[ $child ] ) && is_array( $value ) )
        {
            self::merge( $a[ $child ], $value );
        }
      }
      else
      {
         // add if not exists
        $a[ $child ] = $value;
      }
    }
  }
}