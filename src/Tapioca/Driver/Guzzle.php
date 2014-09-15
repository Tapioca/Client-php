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
  // required 
  /**
   * @var  object  Tapioca Client instance
   */
  private $_inst;

  /**
   * @var  string access token
   */
  private $_accessToken;

  /**
   * @var  int token expire timestamp
   */
  private $_tokenExpire;

  // class needs
  /**
   * @var  object  HttpClient instance
   */
  private $_client;

  /**
   * @var  object Guzzle request
   */
  private $_request;

  /**
   * Setup required variables
   * plus Guzzle specific needs
   *
   */
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
   */
  public function getToken()
  {
    $config  = $this->_inst->getConfig();

    // build request
    $this->_request = $this->_client->get(array('oauth{?client_id,client_secret}', array(
        'client_id'     => $config['clientId']
      , 'client_secret' => $config['clientSecret']
    )));

    // call server
    $token = $this->doCall();

    // save token
    $this->setToken( $token );

    return $token;
  }

  /**
   * @{inheritDoc}
   *
   */
  public function get( $url )
  {
    // build request
    $this->_request = $this->_client->get( $url );

    return $this->doCall();
  }

  /**
   * @{inheritDoc}
   *
   */
  public function find( $context, $url, $query = null, $locale = null, $debug = false )
  {
    if( is_array( $query ) )
    {
      $query =  json_encode( $query );
    }

    $asked = array(
        'query'  => $query
      , 'locale' => $locale
      , 'debug'  => 'true'
    );

    if( !$response = $this->_inst->getCache( $context, $asked ) )
    {
      // if no result append current token
      $askedPlusToken = array(
        'token'  => $this->_accessToken
      ) + $asked;

      // build request
      $this->_request = $this->_client->get( array( $url . '{?token,query,locale,debug}', $askedPlusToken ) );

      // call server
      $response = $this->doCall();

      // store response in cache
      $this->_inst->setCache( $context, $asked, $response );
    }

    return $response;
  }

  /**
   * Generic Guzzle request
   *
   * @throws Exception\ErrorResponseException
   */
  private function doCall()
  {
    try
    {
      $response = $this->_request->send();
    }
    catch( GuzzleException\ClientErrorResponseException $e )
    {
      throw new Exception\ErrorResponseException( $e->getMessage() );
    }
    catch( GuzzleException\BadResponseException $e )
    {
exit( 'Exception');
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
}