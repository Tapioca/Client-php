<?php

/**
 * Tapioca: Schema Driven Data Engine for FuelPHP.
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
     *     // Load the REST client
     *     $client = Tapioca::client('Rest', 'acme');
     *
     * @param   string    Connection mode (Rest|Mongo)
     * @param   string    Identifier for this client's app
     * @param   array     Configuration array
     * @return  Fieldset
     */
    public static function client( $driver = 'mongo', $name = 'default', array $config)
    {
        if ( $exists = static::instance( $name ) )
        {
            return $exists;
        }

        $class = 'Tapioca\\Drivers_'.$driver;

        static::$_instances[ $name ] = new $class($name, $config);

        if ( $name == 'default' )
        {
            static::$_instance = static::$_instances[$name];
        }

        return static::$_instances[ $name ];
    }


    /**
     * Return a specific instance, or the default instance (is created if necessary)
     *
     * @param   string  driver id
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
     * Abstracts
     */

    abstract public function find( $options );
    abstract protected function format( $results );
}