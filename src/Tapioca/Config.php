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

class Config
{
  /**
   * Magic get method to allow getting class properties but still having them protected
   * to disallow writing.
   *
   * @return  mixed
   */
  public function __get( $name )
  {
    return isset( $this->{$name} ) ? $this->{$name} : false;
  }

  public function get( $key, $default = null )
  {

  }

  public function set( $key, $default = null )
  {
    
  }
}