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

/**
* @codeCoverageIgnore
*/
class Exception extends \Exception {}

class Client 
{
    /**
     * @var  Library version
     */
    protected static $version = '0.2.0';

    /**
     * @var  Client
     */
    protected static $_instance;

    /**
     * @var  array  contains references to all instantiations of Client
     */
    protected static $_instances = array();

    /**
     * Create Client object
     *
     * @param   string    Identifier for this client's instence
     * @param   array     Configuration array
     * @return  Fieldset
     */
    public static function client( $name = 'default', $config = array() )
    {
        if( is_array( $name ) )
        {
            $config = $name;
            $name   = 'default';
        }

        if ( $exists = static::instance( $name ) )
        {
            return $exists;
        }

        if( !isset( $config['driver'] ) || empty($config['driver'] ) )
        {
            throw new Exception('No Tapioca driver given.');
        }

        $driver = 'Tapioca\\Driver_'.$config['driver'];

        if( ! class_exists($driver, true))
        {
            throw new Exception('Could not find Tapioca driver: '.$config['driver']. ' ('.$driver.')');
        }

        // Default config
        // DO NOT EDIT
        $_defaults = array( 
            'driver'      => 'rest', 
            'slug'        => false,
            'server'      => false,
            'object'      => false,
            'collections' => array(
                'apps'         => 'apps',
                'previews'     => 'previews',
            ),
            'rest'        => array(
                'clientId'     => false,
                'clientSecret' => false,
            ),
            'mongo'       => array(
                'username'     => false,
                'password'     => false,
                'database'     => false,
                'port'         => 27017,
            ),
            'cache'      => array(
                'ttl'          => 3600,
                'path'         => false,
            ),
        );

        static::config( $config, $_defaults );

        static::$_instances[ $name ] = new $driver( $config );

        if ( $name == 'default' )
        {
            static::$_instance = static::$_instances[$name];
        }

        return static::$_instances[ $name ];
    }


    /**
     * Return a specific instance, or the default instance (is created if necessary)
     *
     * @param   string  instance name
     * @return  Tapioca
     */
    public static function instance( $instance = 'default' )
    {

        if ( ! array_key_exists($instance, static::$_instances) )
        {
            return false;
        }

        return static::$_instances[ $instance ];
    }

    /**
     * Deep merge of default config with user config
     * $a will be result. $a will be edited. 
     * It's to avoid a lot of copying in recursion
     *
     * @param   array    user settings
     * @param   array    default config
     * @return  Tapioca
     */
    private static function config( &$a, $b )
    { 
        foreach( $b as $child => $value )
        {
            if( isset( $a[$child] ) )
            { 
                // merge if they are both arrays
                if( is_array( $a[ $child ] ) && is_array( $value ) )
                {
                    static::config( $a[ $child ], $value );
                }
            }
            else
            {
                 // add if not exists
                $a[ $child ] = $value;
            }
        }
    }
}