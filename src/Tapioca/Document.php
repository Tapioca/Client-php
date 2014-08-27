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

class Document 
{
  /**
   * @var  array  Original document
   */
  protected $_original;

  /**
   * @var  string  Document _ref
   */
  protected $_ref = null;

  /**
   * Constructor
   *
   * @access public
   * @param  array   Tapioca document
   * @return void
   */
  public function __construct( $document )
  {
    $this->_original = $document;

    if( isset( $document['_tapioca']['ref'] ) )
    {
      $this->_ref = $document['_tapioca']['ref'];
    }
  }

  /**
   * Checks if the Field is set or not.
   *
   * @param   string  Field name
   * @return  bool
   */
  public function __isset( $field )
  {
    return array_key_exists( $field, $this->_original );
  }

  /**
   * Magic get method to allow getting class properties but still having them protected
   * to disallow writing.
   *
   * @return  mixed
   */
  public function __get( $name )
  {
    return isset( $this->_original[ $name ] ) ?  $this->_original[ $name ] : '';
  }

  /**
   * Copy document properties and remove _ref
   * for public display
   *
   * @return  Document object
   */
  public function __clone()
  {
    unset( $this->_original['_tapioca'] );
  }

  /**
   * @return string   document identifier
   */
  public function __toString()
  {
    return ( isset( $this->_ref ) ) ? $this->_ref : false;
  }

  /**
   * Get document Tapioca's related  property based on key
   *
   * @param string    The index sequence we are navigating down
   * @param mixed     value to return by default
   * @return mixed
   */
  public function tapioca( $key = null, $default = null )
  {
    if( is_null( $key ) )
    {
      return $this->_original['_tapioca'];
    }

    return Utils::get( $this->_original['_tapioca'], $key, $default );
  }  

  /**
   * Navigate through the document looking for a particular index
   *
   * @param string    The index sequence we are navigating down
   * @param mixed     value to return by default
   * @return mixed
   */
  public function get( $key = null, $default = null )
  {
    return Utils::get( $this->_original, $key, $default );
  }  
}