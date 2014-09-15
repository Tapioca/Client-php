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

class Collection
  implements \Iterator
{
  /**
   * @var  int  total documents matching query
   */
  protected $_total = 0;

  /**
   * @var  int  total rows
   */
  protected $_count = 0;

  /**
   * @var  array  query log from client
   */
  protected $_query = array();

  /**
   * @var  array  query log from server response
   */
  protected $_debug = array();

  /**
   * @var  array  Documents
   */
  protected $_documents = array();

  /**
   * @var  array  Documents's ref indexes
   */
  protected $_index = array();

  /**
   * Constructor
   *
   * @access public
   * @param  array   Tapioca API return
   * @param  array   Tapioca API return
   * @return void
   */
  public function __construct( $hash, $query_log )
  {
    $this->_total     = $hash['_tapioca']['total'];
    $this->_count     = count( $hash['documents'] );

    foreach( $hash['documents'] as $key => &$document ) 
    {
      $this->_index[ $document['_tapioca']['ref'] ] = $key;

      $document = new Document( $document );
    }

    $this->_documents = $hash['documents'];

    $this->_query = $query_log;
    $this->_debug = $hash['debug'];
  }

  public function rewind()
  {
    reset( $this->_documents );
  }

  public function current()
  {
    return current( $this->_documents);
  }

  public function key()
  {
    return key( $this->_documents );
  }

  public function next()
  {
    return next( $this->_documents );
  }

  public function valid()
  {
    $key      = key($this->_documents);
    $document = ($key !== NULL && $key !== FALSE);

    return $document;
  }

  /**
   * Return Client query
   *
   * @return array
   */
  public function query()
  {
    return $this->_query;
  }

  /**
   * Return API log
   *
   * @return array
   */
  public function debug()
  {
    return $this->_debug;
  }

  /**
   * total documents in distant collection
   *
   * @return int
   */
  public function total()
  {
    return $this->_total;
  }

  /**
   * total API call rows
   *
   * @return int
   */
  public function count()
  {
    return $this->_count;
  }

  /**
   * Access Document object by array index
   *
   * @return object
   */
  public function at( $index )
  {
    if( !is_numeric( $index ) )
    {
      throw new Exception\InvalidArgumentException( 'index is not numeric' );
    }

    if( isset( $this->_documents[ $index ] ) )
    {
      return $this->_documents[ $index ];
    }
    
    throw new Exception\DocumentNotFoundException( 'Document not found' );
  }

  /**
   * Access Document object by his ref property
   *
   * @param  string  document ref
   * @return object
   */
  public function get( $ref )
  {
    if( isset( $this->_index[ $ref ] ) )
    {
      return $this->at( $this->_index[ $ref ] );
    }
    
    throw new Exception\DocumentNotFoundException( 'Document not found' );
    
  }
}