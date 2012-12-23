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
class TapiocaException extends \Exception {}

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
}