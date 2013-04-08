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

class Collection implements \Iterator
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
     * @var  array  query
     */
    protected $_query = array();

    /**
     * @var  array  Documents
     */
    protected $_documents = array();
    
    /**
     * Constructor
     *
     * @access public
     * @param  array   Tapioca API return
     * @return void
     */
    public function __construct( $hash, $query_log )
    {
        $this->_total     = $hash['total'];
        $this->_count     = count( $hash['results'] );        
        $this->_documents = $hash['results'];

        $this->_query = $query_log;
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
     * Return API query
     *
     * @return array
     */
    public function queryLog()
    {
        return $this->_query;
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
        if( isset( $this->_documents[ $index ] ) )
        {
            return $this->_documents[ $index ];
        }
        
        return false;
    }
}