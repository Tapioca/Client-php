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
        $this->init( $config );
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
        $url = 'document/' . $collection;

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
            
            $request = static::$rest->get(array( $url . '{?key,q}', $query));

            // Send the request and get the response
            $hash = $this->parseReturn( $request->send()->json() );

            return new Collection( $hash, array(
                            'select' => $this->_select,
                            'where'  => $this->_where,
                            'limit'  => $this->_limit,
                            'skip'   => $this->_skip,
                            'sort'   => $this->_sort,
                        ));
        }
        else
        {
            $url .= '/' . $this->_ref;
            $arg  = array('l' => null);

            if( isset( $this->_tapioca['locale'] ) )
            {
                $arg['l'] = $this->_tapioca['locale'];
            }

            $request = static::$rest->get( array( $url . '{?key,l}', $arg ) );

            // Send the request and get the response
            return new Document( $request->send()->json() );
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
        $url = 'library';

        if( !is_null( $filename ) )
        {
            $url .= '/'.$filename;
        }

        $query = array();

        if( isset( $this->_where['category'] ))
        {
            $query['category'] = $this->_where['category'];
        }

        $request = static::$rest->get( array( $url . '{?key,category}', $query ) );

        if( !is_null( $filename ) )
        {
            return new File( $request->send()->json(), $this->_fileStorage );
        }

        $hash = $this->parseReturn( $request->send()->json(), false);

        return new Collection( $hash, array(
                        'select' => $this->_select,
                        'where'  => $this->_where,
                        'limit'  => $this->_limit,
                        'skip'   => $this->_skip,
                        'sort'   => $this->_sort,
                    ));
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
        $url = 'preview/' . trim( $token );
        
        try
        {
            $request = static::$rest->get( $url . '{?key}' );
        }
        catch (\Guzzle\Http\Exception\ClientErrorResponseException $e)
        {
            return 'Uh oh! ' . $e->getMessage();
        }


        // Send the request and get the response
        return new Document( $request->send()->json() );
    }

    private function parseReturn( $return, $asDocument = true )
    {
        foreach( $return['results'] as &$document )
        {
            $document = ( $asDocument ) ?  new Document( $document ) : new File( $document, $this->_fileStorage );
        }

        return $return;
    }
}