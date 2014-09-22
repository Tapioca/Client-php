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

interface DriverInterface
{
  // to declare in implemented class

  /**
   * @var  object  Tapioca Client instance
   */
  // private $_inst;

  /**
   * @var  string access token
   */
  // private $_accessToken;

  /**
   * @var  int token expire timestamp
   */
  // private $_tokenExpire;

  /**
    * Get oauth access token
    *
    * Response:
    *
    * <code>
    * Array
    * (
    *     [access_token] => (string) the access token
    *     [token_type] => (string) token type
    *     [expires] => (int) token expiration date
    *     [expires_in] => (int) tolen ttl
    * )
    * </code>
    *
    * @throws 
    * @return array               Returns array on success
    */
  public function getToken();

  /**
    * Set oauth access token
    *
    * @throws 
    * @return void
    */
  public function setToken( $token );

  /**
   * Request services who don't need oauth or cache
   * e.g.: preview
   *
   * @param  string     $url          service to call
   * @return object                   Returns asked page
   */
  public function get( $url );
  
  /**
   * Request API
   *
   * @param  string     $context      call context, 'collection-slug', 'document-slug' or 'library'
   * @param  string     $url          service to call
   * @param  mixed      $query        a document ref or a Query instance
   * @param  string     $locale       asked locale
   * @return object                   Returns a Collection or a Document or a Library object
   */
  public function find( $context, $url, $query = null, $locale = null );
}