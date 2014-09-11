<?php

/**
 * Tapioca: Schema Driven Data Engine 
 * PHP Client.
 *
 * @package   Tapioca
 * @version   v0.3
 * @author    Michael Lefebvre
 * @license   MIT License
 * @copyright 2013 Michael Lefebvre
 * @link      https://github.com/Tapioca/Client-php
 */

namespace Tapioca\Driver;

use Tapioca\Exception;
use Guzzle\Http\Client as HttpClient;
use Guzzle\Http\Exception as GuzzleException;

class Guzzle 
  implements DriverInterface
{

  /**
   * @var  object  Tapioca Client instance
   */
  private $_inst;

  /**
   * @var  object  HttpClient instance
   */
  private $_client;

  /**
   * @var  string access token
   */
  private $_accessToken;

  /**
   * @var  int token expire timestamp
   */
  private $_tokenExpire;

  public function __construct( $instance )
  {
    // Throw exception if the instance arg isn't an instance of Tapioca\Client
    if( !$instance instanceof \Tapioca\Client )
    {
      throw new Exception\InvalidArgumentException( "client arg isn't an instance of Tapioca\Client" );
    }

    $this->_inst    = $instance;
    $this->_client  = new HttpClient( $this->_inst->getConfig('service') );
  }

  /**
   * @{inheritDoc}
   *
   * @throws Exception\StateException
   */
  public function getToken()
  {
    $config  = $this->_inst->getConfig();

    $request = $this->_client->get(array('oauth{?client_id,client_secret}', array(
        'client_id'     => $config['clientId']
      , 'client_secret' => $config['clientSecret']
    )));

    try
    {
      $response = $request->send();
    }
    catch( GuzzleException\ClientErrorResponseException $e )
    {
      throw new Exception\ErrorResponseException( 'Client authentication failed' );
    }

    try 
    {
      $token = $response->json();
    }
    catch( \Guzzle\Common\Exception\RuntimeException $e )
    {
      throw new Exception\ErrorResponseException( $e->getMessage() );
    }

    $this->setToken( $token );

    return $token;
  }

  /**
   * @{inheritDoc}
   *
   * @throws Exception\StateException
   */
  public function setToken( $token )
  {
    $this->_accessToken = $token['access_token'];
    $this->_tokenExpire = $token['expires'];

    return $token;
  }

  /**
   * @{inheritDoc}
   *
   * @throws Exception\StateException
   */
  public function get( $url )
  {
    $request = $this->_client->get( $url );

    try
    {
      $response = $request->send();
    }
    catch( GuzzleException\ClientErrorResponseException $e )
    {
      throw new Exception\ErrorResponseException( $e->getMessage() );
    }

    try 
    {
      return $response->json();
    }
    catch( \Guzzle\Common\Exception\RuntimeException $e )
    {
      throw new Exception\ErrorResponseException( $e->getMessage() );
    }
  }

  /**
   * @{inheritDoc}
   *
   * @throws Exception\ErrorResponseException
   */
  public function find( $url, $query = null, $locale = null, $debug = false )
  {
    if( is_array( $query ) )
    {
      $query =  json_encode( $query );
    }

    $asked = array(
        'token'  => $this->_accessToken
      , 'query'  => $query
      , 'locale' => $locale
      , 'debug'  => 'true'
    );

    if( !$response = $this->_inst->getCache( $this->_inst->getConfig('slug'), $asked ) )
    {
      $request = $this->_client->get( array( $url . '{?token,query,locale,debug}', $asked ) );
echo 'no cache for '. $url."<br>";
      try
      {
        $response = $request->send();
      }
      catch( GuzzleException\ClientErrorResponseException $e )
      {
        throw new Exception\ErrorResponseException( $e->getMessage() );
      }

      try 
      {
        $response = $response->json();
      }
      catch( \Guzzle\Common\Exception\RuntimeException $e )
      {
        throw new Exception\ErrorResponseException( $e->getMessage() );
      }

      $this->_inst->setCache( $this->_inst->getConfig('slug'), $asked, $response );
    }

    return $response;
  }
}