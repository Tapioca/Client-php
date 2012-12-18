<?php

/**
 * Tapioca: Schema Driven Data Engine for FuelPHP.
 *
 * @package   Tapioca
 * @version   v0.2
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2012 Michael Lefebvre
 * @link      https://github.com/Tapioca/Client-php
 */

namespace Tapioca;

class Query 
{
    /**
     * @var  string  Collection name
     */
    protected $collection = null;

    /**
     * @var  string  Document reference
     */
    protected $_ref = null;

    /**
     * @var  array  Allowed operators
     */
    protected static $operators = array('select', 'where', 'sort', 'limit', 'skip');

    /**
     * @var  string  Query arguments
     */
    protected $select = array();
    protected $where  = array();
    protected $sort   = array();
    protected $limit  = 99999;
    protected $skip   = 0;

    protected $_queryLog;


    /**
     * Constructor
     *
     * @access public
     * @param  array   Configuration Array
     * @return void
     */
    public function __construct( $query = null )
    {

        $this->reset();

        if( is_array( $query ) )
        {
            $this->set( $query );
        }
    }

    /**
     * Clean model properties
     *
     * @return  void
     */
    protected function reset()
    {
        $this->_queryLog = array(
            'select'    => $this->select,
            'where'     => $this->where,
            'limit'     => $this->limit,
            'skip'      => $this->skip,
            'sort'      => $this->sort,
        );

        $this->collection = null;
        $this->_ref       = null;
        $this->_tapioca   = array();
        $this->select     = array();
        $this->where      = array( '_tapioca.status' => 100 );
        $this->sort       = array( '$natural' => -1 );
        $this->limit      = 99999;
        $this->skip       = 0;
    }

    public function collection( $collection )
    {
        if( !empty( $collection ) )
        {
            $this->collection = $collection;
            
            return $this;
        }
    }

    public function document( $ref )
    {
        if( !empty( $ref ) )
        {
            $this->_ref = $ref;

            return $this;
        }
    }

    public function locale( $locale )
    {
        if( !empty( $locale ) )
        {
            $this->_tapioca['locale'] = $locale;

            return $this;
        }
    }

    public function revision( $revision )
    {
        if( !empty( $revision ) )
        {
            $this->_tapioca['revision'] = (int) $revision;

            return $this;
        }
    }

    public function status( $status )
    {
        if( !empty( $status ) && is_numeric( $status ) )
        {
            $this->_tapioca['status'] = $status;

            return $this;
        }
    }

    /**
     * Query Definition
     * usage : $this->set('where', array('foo' => 'bar'));
     * usage : $this->set(array('where' => array('foo' => 'bar'), 'select' => array('foo', 'bar'));
     *
     * @param   string|array   
     * @param   string  
     */ 
    public function set( $operator, $value = '' )
    {   
        if( is_string( $operator ) )
        {
            $this->_set($operator, $value);
        }
        
        if( is_array( $operator ) )
        {
            foreach( $operator as $key => $value )
            {
                $this->_set( $key, $value );
            }
        }

        return $this;
    }
    
    protected function _set( $operator, $value )
    {       
        if( in_array( $operator, self::$operators ) )
        {
            if( is_array( $value ) )
            {
                $tmp_arr = $this->$operator;

                foreach( $value as $key => $val )
                {
                    $tmp_arr[ $key ] = $val;
                }

                $this->$operator = $tmp_arr;
            }
            else // string or int
            {
                $this->$operator = $value;
            }
        }
    }

    protected function _unset( $operator, $key )
    {
        $tmp_arr = $this->$operator;

        if( isset( $tmp_arr[$key] ) )
        {
            unset( $tmp_arr[$key] );

            $this->$operator = $tmp_arr;
        }
    }

    protected function _get()
    {
        if( is_null( $this->collection ) )
        {
            throw new \Tapioca\Exception( __('tapioca.no_collection_selected') );
        }

        // check if locale exists for this document
        if( !isset( $this->_tapioca['locale'] ) )
        {
            $this->_tapioca['locale'] = $this->config['locales']['default'];
        }

        $this->set('where', array(
            '_summary'        => array( '$exists' => false ),
            '_tapioca.locale' => $this->_tapioca['locale']
        ));

        // if we define a document ref
        if( !is_null( $this->_ref ) )
        {
            $this->set('where', array('_ref' => $this->_ref));

            // get a specific revison
            if( isset( $this->_tapioca['revision'] ) )
            {
                $this->_unset('where', '_tapioca.status');
                $this->_unset('where', '_tapioca.locale');

                $this->set('where', array('_tapioca.revision' => $this->_tapioca['revision']));
            }
        }

        // Always return ref
        if( count( $this->select ) != 0 )
        {
            $tmp = array_merge( $this->select, array('_ref') );
            $this->set('select', $tmp);
        }

    }

    /**
     * Return Data about last query
     *
     * @return array
     */
    
    public function lastQuery()
    {
        return $this->_queryLog;
    }
}