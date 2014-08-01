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

/**
 * Query class is inspired/stollen from Monga
 * a swift MongoDB Abstraction for PHP 5.3+
 *
 * @package    Monga
 * @version    1.0
 * @author     Frank de Jonge
 * @license    MIT License
 * @copyright  2011 - 2012 Frank de Jonge
 * @link       http://github.com/FrenkyNet/Monga
 */

namespace Tapioca;

class Query extends Where
{
  /**
   *  @var  array  $sort  collection ordering
   */
  protected $sort = array();

  /**
   *  @var  int  $skip  query offset
   */
  protected $skip = 0;

  /**
   *  @var  int  $limit  query limit
   */
  protected $limit = 20;

  /**
   * @var  array  $fields  fields include exclude array
   */
  protected $fields = array();

  /**
   * @var  string locale identifier
   */
  protected $locale = null;

  /**
   * Set the result limit
   *
   * @param   integer  $amount  limit
   * @return  object   $this
   */
  public function limit( $amount )
  {
    $this->limit = $amount;

    return $this;
  }

  /**
   * Set the amount to skip in the result.
   *
   * @param   integer  $amount  skip
   * @return  object   $this
   */
  public function skip( $amount )
  {
    $this->skip = $amount;

    return $this;
  }

  /**
   *  Orders a collection
   *
   *  @param  string $field       field to order by
   *  @param  string $direction  asc/desc/1/-1
   *  @return object             current instance
   */
  public function orderBy( $field, $direction = 1 )
  {
    if (is_string($direction))
    {
      $direction = $direction === 'asc' ? 1 : -1;
    }

    $this->sort[ $field ] = $direction;

    return $this;
  }

  /**
   *  Specifies fields to select
   *
   *  @param  string $field field to select
   *
   *  @return object        current instance
   */
  public function select( $field )
  {
    $fields = func_get_args();

    foreach ((array) $fields as $field)
    {
      $this->fields[$field] = 1;
    }

    return $this;
  }

  /**
   *  Specifies fields to exclude
   *
   *  @param  string $field fields to exclude
   *
   *  @return object        current instance
   */
  public function exclude( $field )
  {
    $fields = func_get_args();

    foreach ($fields as $field)
    {
      $this->fields[$field] = -1;
    }

    return $this;
  }

  /**
   * 
   */
  public function setLocale( $locale )
  {
    $this->locale = $locale;
    
    return $this;
  }

  /**
   *  Get the post-find actions.
   *
   *  @return  array  post-find actions
   */
  public function getQuery()
  {
    return array(
        'select'    => $this->fields
      , 'where'     => $this->getWhere()
      , 'limit'     => $this->limit
      , 'skip'      => $this->skip
      , 'sort'      => $this->sort
      , 'locale'    => $this->locale
    );
  }
}