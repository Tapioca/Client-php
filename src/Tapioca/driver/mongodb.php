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

/**
 *
 * Based on Alex Bilbie's Mongo Query Builder 
 *
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
        $this->init( $config );

        if ( ! class_exists('\Mongo'))
        {
            throw new Exception('The MongoDB PECL extension has not been installed or enabled');
        }

        // connection string
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

        // DB handler
        $options = array();

        if( $config['mongo']['persist'] === true )
        {
            $options['persist'] = $config['mongo']['persist_key'];
        }

        if( $config['mongo']['replica_set'] !== false )
        {
            $options['replicaSet'] = $config['mongo']['replica_set'];
        }

        try
        {
            if( phpversion('Mongo') >= 1.3 )
            {
                $_connection = new \MongoClient($dsn, $options);
                static::$qb  = $_connection->{ $config['mongo']['database'] };
            }

            else
            {
                $_connection = new \Mongo($dsn, $options);
                static::$qb  = $_connection->{ $config['mongo']['database'] };
            }
        }
        catch (MongoConnectionException $Exception)
        {
            throw new Exception('Unable to connect to MongoDB: ' . $Exception->getMessage());
        }
    }

    /**
     * Get App Data from Database
     * TO REMOVE
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

        // Always exclude Mongo Id
        $this->_select['_id'] = 0;

        if( is_null( $this->_ref ) )
        {

            $hash = $this->getHash( $collection );

            // format document as object
            if( $this->_object )
            {
                $hash = $this->format( $hash );
            }

            return $hash;
        }
        else
        {
            $results =  static::$qb
                            ->{$collection}
                            ->find($this->_where, $this->_select);

            $ret = static::readCursor( $results );

            if( count( $ret ) == 1 )
            {
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
     * Query the library
     *
     * @param  string   file name
     * @return object|array
     */
    protected function libraryMongoDB( $filename = null )
    {
        // Unset document query
        $this->_unset('where', '_tapioca.status');
        $this->_unset('where', '_tapioca.locale');

        // base url
        $url = $this->_slug . '/library/';

        $collection = $this->_slug.'--'.static::$libraryCollection;

        if( !is_null( $filename ) )
        {
            $result = static::$qb
                        ->{$collection}
                        ->find(array('filename' => $filename), array('_id' => 0))
                        ->limit(1);

            $hash = static::readCursor( $result );

            if( count( $hash ) == 1 )
            {
                $hash = $hash[0];
            }
        }
        else
        {

            $hash = $this->getHash( $collection );
        }

        // format document as object
        if( $this->_object )
        {
            $hash = $this->format( $hash );
        }

        return $hash;

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
        $where  = array('_id' => new \MongoId( trim( $token ) ) );

        $result = static::$qb
                    ->{static::$previewCollection}
                    ->find( $where , array('_id' => 0));

        $result = static::readCursor( $result );

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


    /**
     * Parse MongoDB cursor
     *
     * @param  object   MongoDB cursor
     * @return array
     */
    private static function readCursor( $results )
    {
        $documents = array();

        while( $results->hasNext() )
        {
            try
            {
                $documents[] = $results->getNext();
            }
            catch (\MongoCursorException $Exception)
            {
                throw new Exception( $Exception->getMessage() );
            }
        }

        return $documents;
    }

    /**
     * Query MongoDB, return API compilant hash
     *
     * @param  string   Collection Name
     * @return array
     */
    private function getHash( $collection )
    {
        // Always exclude Mongo Id
        $this->_select['_id'] = 0;

        // query MongoDb
        $cursor = static::$qb
                        ->{$collection}
                        ->find($this->_where, $this->_select);

        $total      = $cursor->count();

        $results  = $cursor
                        ->limit($this->_limit)
                        ->skip($this->_skip)
                        ->sort($this->_sort);

        // hash to return
        // use array for REST compilant
        return array(
            'total'   => $total,
            'skip'    => $this->_skip,
            'limit'   => $this->_limit,
            'results' => static::readCursor( $results )
        );

    }
}