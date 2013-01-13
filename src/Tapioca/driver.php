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
     * Driver name
     */
    const MONGODB = 'MongoDB';
    const REST    = 'Rest';

    /**
     * Document status
     */
    const OUTOFDATE = -1;
    const DRAFT     = 1;
    const PUBLISHED = 100;

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
    public function init( $config )
    {
        $this->_config = $config;

        // Application Slug - required
        if( !$config['slug'] || empty( $config['slug'] ))
        {
            throw new Exception("You must provid your Application's slug");
        }

        $this->_slug   = $config['slug'];

        // sould we return object or array (default php mongodb driver behavior)
        $this->_object  = ( isset( $config['object'] ) && $config['object'] );

        // set cache config
        if( is_array( $config['cache'] ) && $config['cache']['path'] )
        {
            try
            {
                //Make sure it exists and is writeable  
                $this->_cache  = new Cache( $config['cache']['path'] );
            }
            catch( TapiocaCacheException $e )
            {
                throw new Exception( $e->getMessage() );
            }
        }

        if( !is_array( $config['collections'] ) )
        {
            throw new Exception('Collections name must be an array');
        }

        // Set `apps` collection name
        if( isset( $config['collections']['apps'] ) && !empty( $config['collections']['apps'] ))
        {
            static::$appCollection = $config['collections']['apps'];
        }
        else
        {
            throw new Exception('Apps collections name must be provided');
        }

        // Set `library` collection name
        if( isset( $config['collections']['library'] )  && !empty( $config['collections']['library'] ))
        {
            static::$libraryCollection = $config['collections']['library'];
        }
        else
        {
            throw new Exception('Library collections name must be provided');
        }

        // Set `previews` collection name
        if( isset( $config['collections']['previews'] )  && !empty( $config['collections']['previews'] ))
        {
            static::$previewCollection = $config['collections']['previews'];
        }
        else
        {
            throw new Exception('Previews collections name must be provided');
        }

        // allow to pass app's data via config
        // usefull in shared environment/reduce remote query
        if( isset( $config['app'] ) && is_array( $config['app'] ))
        {
            $this->_app = $config['app'];
        }

        $this->reset();
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
    // protected $_collection;

    /**
     * @var  array  Document information
     */
    protected $_tapioca;

    /**
     * @var  string  Document reference
     */
    protected $_ref;

    /**
     * @var  object  Cache class instance
     */
    protected $_cache = false;

    /**
     * @var  array  Allowed operators
     */
    protected static $operators = array('select', 'where', 'sort', 'limit', 'skip');

    /**
     * @var  array|int  Query arguments
     */
    protected $_select;
    protected $_where;
    protected $_sort;
    protected $_limit;
    protected $_skip;

    /**
     * @var  array  Store the last query settings
     */
    protected $_queryLog;

    /**
     * @var  string  Preview Collection
     */
    protected static $previewCollection;

    /**
     * @var  string  App Collection
     */
    protected static $appCollection;

    /**
     * @var  string  Library Collection
     */
    protected static $libraryCollection;

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

    /**
     * Get Document
     *
     * @access   public
     * @param    string     Collection name
     * @param    string     Document ID
     * @return   object|array
     */
    public function document( $collection, $ref, $locale = null, $status = null )
    {
        if( !empty( $ref ) && !empty( $collection ) )
        {
            $this->_ref = $ref;

            return $this->collection( $collection );
        }
    
        return false;
    }


    /**
     * Document locale shortcuts
     *
     * @access   public
     * @param    string    locale key
     * @return   void
     */
    public function locale( $locale )
    {
        if( !empty( $locale ) )
        {
            $this->_tapioca['locale'] = $locale;

            return $this;
        }
    }

    /**
     * Document revision shortcuts
     *
     * @access   public
     * @param    int    revision ID
     * @return   void
     */
    public function revision( $revision )
    {
        if( !empty( $revision ) )
        {
            $this->_tapioca['revision'] = (int) $revision;

            return $this;
        }
    }

    /**
     * Document status shortcuts
     *
     * @access   public
     * @param    int
     * @return   void
     */
    public function status( $status )
    {
        if( !empty( $status ) && is_numeric( $status ) )
        {
            $this->_tapioca['status'] = $status;

            return $this;
        }
    }

    /**
     * Get App default locale key
     *
     * @access   public
     * @return   void
     */
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

    /**
     * Shortcuts for query('select', ...)
     *
     * @access   public
     * @return   void
     */
    public function select( array $value )
    {
        $this->query('select', $value);
        return $this;
    }

    /**
     * Shortcuts for query('where', ...)
     *
     * @access   public
     * @return   void
     */
    public function where( array $value )
    {
        $this->query('where', $value);
        return $this;
    }

    /**
     * Shortcuts for query('sort', ...)
     *
     * @access   public
     * @return   void
     */
    public function sort( array $value )
    {
        $this->query('sort', $value);
        return $this;
    }

    /**
     * Shortcuts for query('limit', ...)
     *
     * @access   public
     * @return   void
     */
    public function limit( $value )
    {
        if( is_numeric( $value ))
            $this->query('limit', $value);

        return $this;
    }

    /**
     * Shortcuts for query('skip', ...)
     *
     * @access   public
     * @return   void
     */
    public function skip( $value )
    {
        if( is_numeric( $value ))
           $this->query('skip', $value);
       
        return $this;
    }

    /**
     * Add elements from Query operator
     *
     * @return  void
     */
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

    /**
     * Remove elements from Query operator
     *
     * @return  void
     */
    protected function _unset( $operator, $key )
    {
        $operator = '_'.$operator;
        $tmp_arr  = $this->$operator;

        if( isset( $tmp_arr[$key] ) )
        {
            unset( $tmp_arr[$key] );

            $this->$operator = $tmp_arr;
        }
    }

    /**
     * Merge User query settings with 
     * Tapioca required fields
     *
     * @return  void
     */
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

    /**
     * Get Documents based on query
     *
     * @access public
     * @param  string   collection name
     * @return object
     */
    public function collection( $collection = null )
    {
        if( is_null( $collection ))
        {
            throw new Exception( 'In order to retrieve documents from Tapioca, a collection name must be passed' );
        }

        try
        {
            $this->_get();
        }
        catch(Exception $e )
        {
            throw new Exception( $e->getMessage() );
        }

        // ask from cache
        if( $this->_cache )
        {
            $key   = $this->collectionKey( $collection );

            $cache = $this->_cache->get( $key, $this->_config['cache']['ttl'] );

            if( $cache )
            {
                $this->reset();

                return $cache;
            }
        }

        // call driver implementation
        $hash = call_user_func( array( $this, 'get'.$this->_driver ), $collection );

        // reset query
        $this->reset();

        // store results to cache
        if( $this->_cache )
        {
            $this->_cache->set( $key, $hash );
        }

        return $hash;
    }

    /**
     * Query the library
     *
     * @access public
     * @param  string   file name
     * @return object|array
     */
    public function library( $filename = null )
    {
        // ask from cache
        if( $this->_cache )
        {
            // contact for key string
            $key = $this->libraryKey( $filename );

            $cache = $this->_cache->get( $key, $this->_config['cache']['ttl'] );

            if( $cache )
            {
                return $cache;
            }
        }

        // call driver implementation
        $library = call_user_func( array( $this, 'library'.$this->_driver ), $filename );

        // store results to cache
        if( $this->_cache )
        {
            $this->_cache->set( $key, $library );
        }

        $this->reset();

        return $library;
    }

    /**
     * Merge App's slug + collection name + query
     * to get an unique MD5 hash as cache key
     *
     * @param   string     Collection name
     * @return  string
     */
    protected function collectionKey( $collection )
    {
        $query = array(
            'select'    => $this->_select,
            'where'     => $this->_where,
            'limit'     => $this->_limit,
            'skip'      => $this->_skip,
            'sort'      => $this->_sort,
        );

        return $this->_slug . $collection . serialize( $query );
    }

    /**
     * Merge App's slug + collection name + filename
     * to get an unique MD5 hash as cache key
     *
     * @param   string     filename name
     * @return  string
     */
    protected function libraryKey( $filename = null )
    {
        // contact for key string
        $key = $this->_slug . static::$libraryCollection;

        if( !is_null( $filename ) )
        {
            $key .= $filename;
        }

        return $key;
    }

    public function clearCache()
    {

    }

    /**
     * format array as object
     *
     * @param  array   document array
     * @return object
     */
    protected function format( $results )
    {
        return json_decode( json_encode( $results ) );
    }

    abstract public function app( $property );

    abstract public function preview( $token );
}