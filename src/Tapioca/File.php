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

class File 
{
    /**
     * @var  array  Original document
     */
    protected $_original;

    /**
     * @var  string  File ref
     */
    protected $_ref = null;

    /**
     * @var  string  Files public storage path
     */
    protected $_storage = false;

    /**
     * Constructor
     *
     * @access public
     * @param  array   Tapioca document
     * @return void
     */
    public function __construct( $file, $storage )
    {
        $this->_original = $file;
        $this->_storage  = $storage;

        if( isset( $file['_ref'] ) )
        {
            $this->_ref = $file['_ref'];
        }
    }

    /**
     * Checks if the Field is set or not.
     *
     * @param   string  Field name
     * @return  bool
     */
    public function __isset($field)
    {
        return array_key_exists($field, $this->_original);
    }

    /**
     * Magic get method to allow getting class properties but still having them protected
     * to disallow writing.
     *
     * @return  mixed
     */
    public function __get( $name )
    {
        return isset( $this->_original[ $name ] ) ?  $this->_original[ $name ] : '';
    }

    /**
     * Copy document properties and remove _ref
     * for public display
     *
     * @return  Document object
     */
    public function __clone()
    {
        unset( $this->_original['_ref'] );
    }

    /**
     * @return string   document identifier
     */
    public function __toString()
    {
        $relativePath = $this->_original['category'].'/'.$this->_original['filename'];
        $fullPath     = $this->_storage.$relativePath;

        return ( $this->_storage ) ? $fullPath : $relativePath;
    }

    /**
     * Navigate through the document looking for a particular index
     *
     * @param string    The index sequence we are navigating down
     * @param mixed     value to return by default
     * @return mixed
     */
    public function get( $key = null, $default = null )
    {
        // if no key were passed - return the orignal document
        if ( is_null( $key ) )
        {
            return $this->_original;
        }
        else
        {
            $path = explode('.', $key);
            $data = $this->_original;

            foreach($path as $k)
            {
                if( isset( $data[ $k ] ) )
                {
                    $data =& $data[ $k ];
                }
                else
                {
                    return $default;
                }
            }

            return $data;
        }
    }
}