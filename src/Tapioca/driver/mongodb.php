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

class Driver_MongoDB extends \Tapioca\Driver
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
        $this->_driver = self::MONGODB;
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
     * @param  string   collection name
     * @return object
     */
    protected function getMongoDB( $collection )
    {
        // true collection name
        $collection = $this->_slug.'-'.$collection;

        if( is_null( $this->_ref ) )
        {
            // query MongoDb
            $hash = static::$qb
                            ->select( $this->_select )
                            ->where( $this->_where )
                            ->orderBy( $this->_sort )
                            ->limit( $this->_limit )
                            ->offset( $this->_skip )
                            ->hash( $collection );

            // format document as object
            if( $this->_object )
            {
                $hash->results = $this->format( $hash->results );
            }

            return $hash;
        }
        else
        {
            $ret =  static::$qb
                            ->select( $this->_select )
                            ->where( $this->_where )
                            ->get( $collection );

            if( count( $ret ) == 1 )
            {
                unset( $ret[0]['_id'] );

                $ret = $ret[0];

                // format document as object
                if( $this->_object )
                {
                    $ret = $this->format( $ret );
                }

                return $ret;
            }

            return false;
        }
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