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

use \Tapioca\MongoQB;

class Driver_Mongo extends \Tapioca\Driver
{
    /**
     * @var  MongoDB Query Builder Object
     */
    private static $qb;

    /**
     * Constructor
     *
     * @access public
     * @param  array   Configuration Array
     * @return void
     */
    public function __construct( $config )
    {
        $this->_driver = 'MongoDB';
        $this->commun( $config );
        
        $dsn = "mongodb://";

        if( empty( $config['server'] ) )
        {
            throw new Exception( 'The server must be set to connect to MongoDB' );
        }

        if( empty( $config['mongo']['database'] ) )
        {
            throw new Exception( 'The database must be set to connect to MongoDB' );
        }

        if( ! empty( $config['mongo']['username'] ) and ! empty( $config['mongo']['password'] ) )
        {
            $dsn .= "{$config['mongo']['username']}:{$config['mongo']['password']}@";
        }

        if( isset( $config['port'] ) and ! empty( $config['port'] ) )
        {
            $dsn .= "{$config['server']}:{$config['mongo']['port']}";
        }
        else
        {
            $dsn .= "{$config['server']}";
        }

        $dsn .= "/{$config['mongo']['database']}";

        static::$qb = new MongoQB(array(
            'dsn'   =>  trim( $dsn )
        ));

        $this->reset();
    }

    /**
     * Get App Data from Database
     *
     * @access public
     * @param  string   property to return
     * @return array
     */
    public function app( $property = null )
    {
        if( is_null( $this->_app ))
        {
            $app = static::$qb
                        ->select( array(), array('_id'))
                        ->where( array(
                                'slug' => $this->_slug
                            ))
                        ->get( static::$appCollection );

            if( count( $app ) == 1 )
            {
                $this->_app = $app[0];
            }
        }

        if( !is_null( $property ) )
        {
            return $this->_app[ $property ];
        }

        return  $this->_app;
    }

    /**
     * Get Document based on query
     *
     * @access public
     * @param  string   collection name
     * @return object
     */
    public function get( $collection = null )
    {
        // true collection name
        $collection = $this->_slug.'-'.$collection;

        try
        {
            $this->_get();
        }
        catch(Exception $e )
        {
            throw new \Tapioca\Exception( $e->getMessage() );
        }

        // ask from cache
        if( $this->_cache )
        {
            $key   = $this->cacheKey( $collection );

            $cache = $this->_cache->get( $key, $this->_config['cache']['ttl'] );

            if( $cache )
            {

                $this->reset();

                return $cache;
            }
        }

        // query MongoDb
        $hash = static::$qb
                        ->select( $this->_select )
                        ->where( $this->_where )
                        ->orderBy( $this->_sort )
                        ->limit( $this->_limit )
                        ->offset( $this->_skip )
                        ->hash( $collection );

        // reset query
        $this->reset();

        // format document as object
        if( $this->_object )
        {
            $hash->results = $this->format( $hash->results );
        }

        // store results to cache
        if( $this->_cache )
        {
            $this->_cache->set( $key, $hash );
        }

        return $hash;
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

    /**
     * Ask for a document preview
     *
     * @access public
     * @param  string   preview token
     * @return object|array
     */
    public function preview( $token )
    {
        $result = static::$qb
                    ->select( array(), array('_id'))
                    ->getWhere( static::$previewCollection, array(
                        '_id' => new \MongoId( $token ),
                    ));

        if( count( $result ) != 1 )
        {
            throw new \Tapioca\Exception( 'Not a valid preview token');
        }

        // format document as object
        if( $this->_object && $result )
        {
            return $this->format( $result[0] );
        }

        return $result[0];
    }
}