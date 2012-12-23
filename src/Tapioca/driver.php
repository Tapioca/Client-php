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

abstract class Driver
{
    /**
     * Document status
     */
    const TAPP_OUTOFDATE = -1;
    const TAPP_DRAFT     = 1;
    const TAPP_PUBLISHED = 100;

    /**
     * Return Data about last query
     *
     * @return array
     */
    
    public function lastQuery( $format = null )
    {
        if( $format == 'json' )
            return json_encode( $this->_queryLog );

        return $this->_queryLog;
    }

    /**
     * Commun tasks for all the drivers
     *
     * @return  void
     */
    public function commun( $config )
    {
        $this->_config = $config;
        $this->_slug   = $config['slug'];

        $this->object  = ( isset( $config['object'] ) && $config['object'] );

        if( isset( $config['collection']['app'] )  && !empty( $config['collection']['app'] ))
        {
            static::$appCollection = $config['collection']['app'];
        }

        if( isset( $config['collection']['preview'] )  && !empty( $config['collection']['preview'] ))
        {
            static::$previewCollection = $config['collection']['preview'];
        }

        // allow to pass app's data via config
        // use in shared environment
        if( isset( $config['app'] ) && is_array( $config['app'] ))
        {
            $this->_app = $config['app'];
        }
    }

    /**
     * @var  Array 
     */
    protected $_config = null;

    /**
     * @var  Array  App's data
     */
    protected $_app = null;

    /**
     * @var  string  App name
     */
    protected $_slug;

    /**
     * @var  string  Collection name
     */
    protected $_collection;

    /**
     * @var  array  Document information
     */
    protected $_tapioca;

    /**
     * @var  string  Document reference
     */
    protected $_ref;

    /**
     * @var  array  Allowed operators
     */
    protected static $operators = array('select', 'where', 'sort', 'limit', 'skip');

    /**
     * @var  string  Query arguments
     */
    protected $_select;
    protected $_where;
    protected $_sort;
    protected $_limit;
    protected $_skip;

    protected $_queryLog;

    /**
     * @var  array  Preview Collection
     */
    protected static $previewCollection = 'previews';

    /**
     * @var  array  App Collection
     */
    protected static $appCollection = 'apps';

    /**
     * Magic get method to allow getting class properties but still having them protected
     * to disallow writing.
     *
     * @return  mixed
     */
    public function __get( $property )
    {
        return $this->$property;
    }

    /**
     * Clean model properties
     *
     * @return  void
     */
    public function reset()
    {
        $this->_queryLog = array(
            'select'    => $this->_select,
            'where'     => $this->_where,
            'limit'     => $this->_limit,
            'skip'      => $this->_skip,
            'sort'      => $this->_sort,
        );

        $this->_collection = null;
        $this->_ref        = null;
        $this->_tapioca    = array();
        $this->_select     = array();
        $this->_where      = array( '_tapioca.status' => 100 );
        $this->_sort       = array( '$natural' => -1 );
        $this->_limit      = 99999;
        $this->_skip       = 0;
    }

    public function collection( $collection )
    {
        if( !empty( $collection ) )
        {
            $this->_collection = $collection;
            
            return $this;
        }
    }

    public function document( $collection, $ref )
    {
        if( !empty( $ref ) && !empty( $collection ) )
        {
            $this->_ref = $ref;

            $hash = $this->get( $collection );

            if( $hash->total == 1 )
            {
                return $hash->results[0];
            }
        }
    
        return false;
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

    public function defaultLocale()
    {
        $locales = $this->app('locales');

        foreach ($locales as $locale)
        {
            if( isset( $locale['default'] ) )
                return $locale['key'];
        }

        return false;
    }

    /**
     * Query Definition
     * usage : $this->query('where', array('foo' => 'bar'));
     * usage : $this->query(array('where' => array('foo' => 'bar'), 'select' => array('foo', 'bar'));
     *
     * @param   string|array   
     * @param   string  
     */ 
    public function query( $operator, $value = '' )
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
            $operator = '_'.$operator;

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
        $operator = '_'.$operator;
        $tmp_arr  = $this->$operator;

        if( isset( $tmp_arr[$key] ) )
        {
            unset( $tmp_arr[$key] );

            $this->$$operator = $tmp_arr;
        }
    }

    protected function _get()
    {
        $this->query('where', array(
            '_summary'        => array( '$exists' => false ),
        ));

        // check if locale exists for this document
        if( isset( $this->_tapioca['locale'] ) )
        {
            $this->query('where', array(
                '_tapioca.locale' => $this->_tapioca['locale']
            ));
        }

        // if we define a document ref
        if( !is_null( $this->_ref ) )
        {
            $this->query('where', array('_ref' => $this->_ref));

            // get a specific revison
            if( isset( $this->_tapioca['revision'] ) )
            {
                $this->_unset('where', '_tapioca.status');
                $this->_unset('where', '_tapioca.locale');

                $this->query('where', array( '_tapioca.revision' => $this->_tapioca['revision'] ));
            }
        }

        // Always return ref
        if( count( $this->_select ) != 0 )
        {
            $tmp = array_merge( $this->_select, array('_ref') );
            $this->query('select', $tmp);
        }

    }

    abstract public function app( $property );

    abstract public function get( $options );

    abstract protected function format( $results );

    abstract public function preview( $token );
}