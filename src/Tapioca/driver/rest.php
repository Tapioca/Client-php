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

use \Guzzle\Http\Client as GuzzleClient;

class Driver_Rest extends \Tapioca\Driver
{
    /**
     * @var  Guzzle Query Builder Object
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
        $this->_driver = self::REST;
        $this->commun( $config );

        $url  = ( $config['rest']['https'] ) ? 'https' : 'http';
        $url .= '://';

        if( empty( $config['server'] ) )
        {
            throw new Exception( 'The server must be set to connect to Tapioca Rest API' );
        }

        $url .= $config['server'];

        if( substr( $url, -1 ) != '/')
        {
            $url .= '/';
        }

        $url .= 'api/';

        if( empty( $config['rest']['clientId'] ) )
        {
            throw new Exception( 'You must provide your Client Id' );
        }

        if( empty( $config['rest']['clientSecret'] ) )
        {
            throw new Exception( 'You must provide your Secret' );
        }

        static::$qb = new GuzzleClient( $url, array(
                            'key' => $config['rest']['clientId']
                        ) );
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
    }

    /**
     * @param  string   collection name
     * @return object
     */
    protected function getRest( $collection = null )
    {
        // base url
        $url = $this->_slug . '/document/' . $collection;

        if( is_null( $this->_ref ) )
        {
            $query = array(
                        'q' => json_encode( 
                                array(
                                    'select'    => $this->_select,
                                    'where'     => $this->_where,
                                    'limit'     => $this->_limit,
                                    'skip'      => $this->_skip,
                                    'sort'      => $this->_sort,
                                ) 
                        )
                    );
    
            $request = static::$qb->get(array( $url . '{?key,q}', $query));

            // Send the request and get the response
            $hash = $request->send()->json();

            // format document as object
            if( $this->_object )
            {
                $hash = $this->format( $hash );
            }

            return $hash;
        }
        else
        {
            $url .= '/' . $this->_ref;
            $arg  = array('l' => null);

            if( isset( $this->_tapioca['locale'] ) )
            {
                $arg['l'] = $this->_tapioca['locale'];
            }

            $request = static::$qb->get( array( $url . '{?key,l}', $arg ) );

            // Send the request and get the response
            $ret = $request->send()->json();

            // format document as object
            if( $this->_object )
            {
                $ret = $this->format( $ret );
            }

            return $ret;
        }

    }

    /**
     * Query the library
     *
     * @param  string   file name
     * @return object|array
     */
    protected function libraryRest( $filename = null )
    {
        // base url
        $url = $this->_slug . '/library';

        if( !is_null( $filename ) )
        {
            $url .= '/'.$filename;
        }
        
        $request = static::$qb->get( $url . '{?key}' );

        // Send the request and get the response
        $result = $request->send()->json();

        // format document as object
        if( $this->_object )
        {
            $result = $this->format( $result );
        }

        return $result;
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
        // base url
        $url = $this->_slug . '/preview/' . $token;
        
        $request = static::$qb->get( $url . '{?key}' );

        // Send the request and get the response
        $result = $request->send()->json();

        // format document as object
        if( $this->_object && $result )
        {
            return $this->format( $result );
        }

        return $result;
    }
}