<?php

/**
* Tapioca: Schema Driven Data Engine 
* PHP Client.
*
* @package   Tapioca
* @version   v0.3
* @author    Michael Lefebvre
* @license   MIT License
* @copyright 2014 Michael Lefebvre
* @link      https://github.com/Tapioca/Client-php
*/

namespace Tapioca;

class Client 
{
  const INST_NAME = 'defautl';

  /**
   * @var  Library version
   */
  protected static $version = '0.3.0';

  /**
   * @var  Client
   */
  protected static $_instance;

  /**
   * @var  array  contains references to all instantiations of Client
   */
  protected static $_instances = array();

  /**
   * @var  array  instance config
   */
  protected $_config;

  /**
   * @var  object  Driver instance
   */
  protected $_driver;

  /**
   * @var  object  Cache instance
   */
  protected $_cahe;

  /**
   * @var  string  Local globally defined
   */
  protected $_locale = null;

  /**
   * @var array  Oauth token
   */
  protected $_token;

  /**
   * Create Client object
   *
   * @param   string    Identifier for this client's instence
   * @param   array     Configuration array
   * @return  Fieldset
   */
  public static function client( $name = self::INST_NAME, $config = array() )
  {
    if( is_array( $name ) )
    {
      $config = $name;
      $name   = self::INST_NAME;
    }

    if ( $exists = static::instance( $name ) )
    {
      return $exists;
    }

    // Default config
    // DO NOT EDIT
    $_defaults = array( 
        'slug'         => false
      , 'driver'       => 'Guzzle'
      , 'url'          => false
      , 'apiVersion'   => 0.1
      , 'clientId'     => false
      , 'clientSecret' => false
      , 'fileStorage'  => false
      , 'cache'        => array(
            'strategy'     => 'filesystem'
          , 'ttl'          => 3600
          , 'prefix'       => 'tapioca::'
        )
      // filesystem specific config
      , 'filesystem'   => array(
          'path'         => false
        , 'extention'    => 'cache'
      )
    );

    Utils::merge( $config, $_defaults );

    static::$_instances[ $name ] = new self( $config, $name );

    if ( $name == self::INST_NAME )
    {
      static::$_instance = static::$_instances[ $name ];
    }

    return static::$_instances[ $name ];
  }

  /**
   * Return a specific instance, or the default instance (is created if necessary)
   *
   * @param   string  instance name
   * @return  Tapioca
   */
  public static function instance( $instance = self::INST_NAME )
  {
    if ( ! array_key_exists($instance, static::$_instances) )
    {
      return false;
    }

    return static::$_instances[ $instance ];
  }

  /**
   * Create a Tapioca Client
   *
   * @param array   Client config
   * @return void
   */
  private function __construct( $config, $name )
  {
    // test if driver is available
    $driver = 'Tapioca\\Driver\\' . ucfirst( $config['driver'] );

    if( ! class_exists( $driver, true ) )
    {
      throw new Exception\InvalidArgumentException('Could not find Tapioca driver: ' . $config['driver'] . ' (' . $driver . ')');
    }

    // test if cache's strategy is available    
    $strategy = 'Tapioca\\Cache\\' . ucfirst( $config['cache']['strategy'] );
    
    if( ! class_exists( $strategy, true ) )
    {
      throw new Exception\InvalidArgumentException('Could not find Tapioca cache strategy: ' . $config['cache']['strategy'] . ' (' . $strategy . ')');
    }

    try
    {
      $this->_cache = new $strategy( $config['slug'], $config['slug'],  $config['cache']['ttl'], $config[ $config['cache']['strategy'] ] ); 
    }
    catch( Exception\CacheException $e )
    {
      throw new Exception\InvalidArgumentException( $e->getMessage() );
    }

    // store config
    $this->_config = $config;

    // get Driver instance
    $this->_driver = new $driver( $this );

    if( !$tokenArr = $this->_cache->get( $config['slug'], 'token' ) )
    {
      // get a fresh new Oauth's token
      $tokenArr = $this->_driver->getToken();
    }
    else
    {
      // if token expired
      if( !$this->isTokenValid( $tokenArr ) )
      {
        // get a fresh new Oauth's token
        $tokenArr = $this->_driver->getToken();
      }

      // Oauth's token from cache
      $tokenArr = $this->_driver->setToken( $tokenArr );
    }

    $this->_cache->set( $config['slug'], 'token', $tokenArr );
    $this->_token = $tokenArr;
  }

  public function isTokenValid( $tokenArr = null )
  {
    $expires = ( is_null( $tokenArr ) ) ? $this->_token['expires'] : $tokenArr['expires'];

    return ( time() > $expires );
  }

  /**
   * Return entry config
   *
   */
  public function getConfig( $key = null, $default = null )
  {
    return Utils::get( $this->_config, $key, $default );
  }

  /**
   * Set global localisation
   *
   * @return void
   */
  public function setLocale( $str )
  {
    $this->_locale = $str;
  }

  /**
   * return formated API URL
   *
   * @param  string  API request, `document`, `library` or `preview`
   * @param  string  Collection's slug
   * @param  string  Document/Token's ref
   * @return string  
   */
  private function baseUrl( $type, $collection = null, $id = null )
  {
    $url = $this->_config['slug'] . '/' . $type;

    if( !is_null( $collection ) )
      $url .= '/' . $collection;

    if( !is_null( $id ) )
      $url .= '/' . $id;

    return $url;
  }

  /**
   * Get a Collection of Documents based on query
   *
   * @param  string  Collection's slug
   * @param  object  a Query instance
   * @return object  a Collection instance
   * @throws Exception\ErrorResponseException
   */
  public function collection( $collection, $query = null )
  {
    $url    = $this->baseUrl( 'document', $collection );

    $locale = $this->_locale;
    $log    = null;

    if( $query instanceof Query )
    {
      $query = $log = $query->getQuery();

      if( !is_null( $query['locale'] ) )
      {
        $locale = $query['locale'];
        unset( $query['locale'] );
      }
    }

    $response = $this->_driver->find( $url, $query, $locale );

    return new Collection( $response, $query, $locale );
  }

  /**
   * Return a document based on his ref
   *
   * @param  string  Collection's slug
   * @param  string  Document ref
   * @return object  a Document instance
   * @throws Exception\ErrorResponseException
   */
  public function document( $collection, $ref, $locale = null )
  {
    $url = $this->baseUrl( 'document', $collection, $ref );

    $response = $this->_driver->find( $url, null, $locale );

    return new Document( $response );
  }

  /**
   * Return the Library content based on query
   *
   * @param  object  a Query instance
   * @return object  a Library instance
   * @throws Exception\ErrorResponseException
   */
  public function library( $query = null )
  {
    $url    = $this->baseUrl( 'library', $collection );

    if( $query instanceof Query )
    {
      $query = $log = $query->getQuery();

      if( !is_null( $query['locale'] ) )
      {
        $locale = $query['locale'];
        unset( $query['locale'] );
      }
    }

    $response = $this->_driver->find( $url, $query, $locale );

    return new Library( $response, $query, $locale );
  }

  /**
   * Return a preview
   *
   * @param  string  Collection's slug
   * @param  object  Preview's token
   * @return object  a Preview instance
   * @throws Exception\ErrorResponseException
   */
  public function preview( $token )
  {
    $url      = $this->baseUrl( 'preview', $collection, $token );
    $response = $this->_driver->find( $url );

    return new Preview( $response, $query, $locale );
  }
}