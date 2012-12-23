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
 *
 * Extends Alex Bilbie's Mongo Query Builder 
 * with `hash`method
 *
 */


class MongoQB extends \MongoQB\Builder
{

    /**
    * Get the documents based upon the passed parameters.
    *
    * Return Hash included total, offsets params and the found documents
    *
    * @param string $collection    Name of the collection
    *
    * @access public
    * @return array
    */
    public function hash($collection = '')
    {
        if (empty($collection)) {
            throw new \MongoQB\Exception('In order to retrieve documents from
             MongoDB, a collection name must be passed');
        }

        // Always exclude Mongo Id
        $this->_selects['_id'] = 0;

        $cursor = $this->_dbhandle
                            ->{$collection}
                            ->find($this->wheres, $this->_selects);

        $total      = $cursor->count();

        $results  = $cursor
                        ->limit($this->_limit)
                        ->skip($this->_offset)
                        ->sort($this->_sorts);


        // hash to return
        $obj            = new \stdClass;
        $obj->total     = $total;
        $obj->skip      = $this->_offset;
        $obj->limit     = $this->_limit;

        // Clear
        $this->_clear($collection, 'get');

        $documents = array();

        while ($results->hasNext()) {
            try {
                $documents[] = $results->getNext();
            }
            // @codeCoverageIgnoreStart
            catch (\MongoCursorException $Exception) {
                throw new \MongoQB\Exception($Exception->getMessage());
                // @codeCoverageIgnoreEnd
            }
        }

        $obj->results   = $documents;

        return $obj;
    }

    /**
     * Reset the class variables to default settings.
     *
     * @access private
     * @return void
     */
    private function _clear($collection, $action)
    {
        $this->_queryLog = array(
            'collection'    => $collection,
            'action'        => $action,
            'wheres'        => $this->wheres,
            'updates'       => $this->updates,
            'selects'       => $this->_selects,
            'limit'         => $this->_limit,
            'offset'        => $this->_offset,
            'sorts'         => $this->_sorts
        );

        $this->_selects = array();
        $this->updates  = array();
        $this->wheres   = array();
        $this->_limit   = 999999;
        $this->_offset  = 0;
        $this->_sorts   = array();
    }
}