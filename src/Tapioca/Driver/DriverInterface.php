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
   * Request API
   *
   * @param  string     $url          service to call
   * @param  mixed      $query        a document ref or a Query instance
   * @param  string     $locale       asked locale
   * @return object                   Returns Document or Collection object
   */
  public function find( $url, $query = null, $locale = null );
}