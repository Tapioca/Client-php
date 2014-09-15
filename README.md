# Tapioca PHP Client

This page is a draft of upcoming Tapioca's client in PHP. Feel free to contribute and/or suggest ideas.

## Requirements

PHP 5.3.3 (or higher) is required.

## Installing via Composer

The recommended way to install Tapioca's PHP Clieny is through [Composer](http://getcomposer.org).

1. Add ``tapioca/client-php`` as a dependency in your project's ``composer.json`` file:

```json
    {
        "require": {
            "tapioca/client-php": "dev-master"
        }
    }
```


2. Download and install Composer:

        curl -s http://getcomposer.org/installer | php

3. Install your dependencies:

        php composer.phar install

4. Require Composer's autoloader

    Composer also prepares an autoload file that's capable of autoloading all of the classes in any of the libraries that it downloads. To use it, just add the following line to your code's bootstrap process:

        require 'vendor/autoload.php';

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at [getcomposer.org](http://getcomposer.org).


## Configuration

Minimal configuration array:

```php

    $config = array(
	    'slug'         => 'acme' // you App's slug
	  , 'clientId'     => '540e011b8597d'
	  , 'clientSecret' => 'dd4111734d012012b271cdce8aded611'
	  , 'fileStorage'  => 'http://www.yousite.com/file/path/' // public path for your files storage
    );
```
  
Complet configuration array:
  

```php

	$config = array(
        'slug'         => ...
      , 'driver'       => 'Guzzle' // cUrl client
	  , 'url'          => 'http://www.tapioca.io/' // server's URL, change it if you run your own Tapioca server
      , 'api'          => 'api/0.1/'  // API path + version
      , 'apiVersion'   => 0.1
      , 'clientId'     => ...
      , 'clientSecret' => ...
      , 'fileStorage'  => ...
      , 'cache'        => array(
            'strategy'     => 'filesystem' // cache method
          , 'ttl'          => 3600 // cache time to live
          , 'prefix'       => 'tapioca::' // cache key prefix
        )
      // filesystem specific config
      , 'filesystem'   => array(
          'path'         => __DIR__ . '/cache/' // cache files path
        , 'extention'    => 'cache' // cache file extenTion
      )
      // debug specific config
      , 'memory'       => array()
	);
```

## Instance

Create a new instance based on <code>$config</code> array.

```php

	include('vendor/autoload.php');
	
	use Tapioca\Client as Tapioca;
	use Tapioca\Query as Query;
	use Tapioca\Exception as TapiocaException;
	use Tapioca\Cache\Filesystem as Cache;
	
	try
	{
	  $clientTapioca = Tapioca::client( $config );
	}
	catch( TapiocaException\InvalidArgumentException $e )
	{
	  exit($e->getMessage());
	}
	catch( TapiocaException\ErrorResponseException $e )
	{
	  exit($e->getMessage());
	}
```

## Locale

You can define a global <code>Locale</code> for the whole instance: 

```php

    $clientTapioca->setlocale('fr_FR');
```

You can override this on each query.

## Query

### Collection

The easiest collection query, just pass the collection's <code>slug</code> as first argument: 

```php

	try
	{
	  $collection = $clientTapioca->collection( 'acme' );
	}
	catch( TapiocaException\ErrorResponseException $e )
	{
	  exit($e->getMessage());
	}
```

You can refine your query by passing a <code>Query</code> object as second parameter.  
_Complete list of query methods below._

```php

	$query = new Query();

	$query
		->select( 'title', 'desc', 'image' )
		->setlocale('en_GB') // override global locale
		->limit(10)
		->skip(10);

	try
	{
	  $collection = $clientTapioca->collection( 'acme', $query );
	}
	catch( TapiocaException\ErrorResponseException $e )
	{
	  exit($e->getMessage());
	}
```

These will return a Tapioca\Collection object based on API result.  
The iteration over this object will allow you to handle each documents as an object. 

#### API result

```json

	{
	    "_tapioca": {
	        "total": 11,
	        "limit": 10,
	        "offset": 10,
	        "locale": "fr_FR",
	        "dependencies": [
	            {
	                "dependency": "acme--library",
	                "path": "image-seul"
	            }
	        ]
	    },
	    "documents": [
	        {
	            "_tapioca": {
	                "ref": "5414bcc54a15a",
	                "revision": "5414bfbb06eef",
	                "published": true,
	                "created": 1410645189,
	                "updated": 1410645947,
	                "user": {
	                    "id": 3,
	                    "email": "michael@test.zz",
	                    "username": "Michael",
	                    "avatar": "http://www.tapioca.io/avatars/3.jpg",
	                    "url": "http://www.tapioca.io/api/0.1/user/3?token=Twa8NwYgJ7PLOfTQ7QgQ0VRJxOFzb8AMcPnNYf1U&",
	                    "role": "admin"
	                },
	                "locale": "fr_FR",
	                "resources": {
	                    "url": "http://www.tapioca.io/api/0.1/ours-roux/document/test/5414bcc54a15a?token=Twa8NwYgJ7PLOfTQ7QgQ0VRJxOFzb8AMcPnNYf1U&",
	                    "revisions": "http://www.tapioca.io/api/0.1/ours-roux/document/test/revisions/5414bcc54a15a?token=Twa8NwYgJ7PLOfTQ7QgQ0VRJxOFzb8AMcPnNYf1U&"
	                }
	            },
	            "title": "DO IT YOURSELF TORNADO KIT",
	            "description": "Easily create your own tornadoes, anywhere, with the ACME Do It Yourself Tornado kit.",
	            "image": {
	                "id": "54146b3c7324c",
	                "category": "image",
	                "filename": "54146b3c7324c.jpg",
	                "extension": "jpg",
	                "basename": "tornado",
	                "length": 41290,
	                "height": 640,
	                "width": 640
	            }
	        },
	        {
	            "_tapioca": { […] },
	            "title": "ACME DISINTEGRATING PISTOL",
	            "desccription": "ACME Disintegrating Pistols, when they disintegrate, they distinegrate!"
	        }
	    ]
	}
```

#### Client usage

```php

    echo $collection->count() .' on '.$collection->total().' documents<br>';
    // 1 on 11 documents

    echo '<ul>';
    foreach( $collection as $product)
    {
        echo '<li>';
        echo $product->title.' || ';
        echo $product->description;
        echo $product->undefinedField; // return empty string
        echo '</li>';

    }
    echo '</ul>';
```

#### Helpers

You can directly access to <code>collection</code> items by there <code>ref</code> or there <code>index</code> in the result:

```php

    try
    {
      // print the document's title with the '5414bcc54a15a' _tapioca.ref
      print_r( $collection->get( '5414bcc54a15a' )->get('title') ); 
    }
    catch( TapiocaException\DocumentNotFoundException $e )
    {
      echo $e->getMessage();
    }


    try
    {
      // print the second document
      print_r( $collection->at( 1 )->get() );
    }
    catch( TapiocaException\DocumentNotFoundException $e )
    {
      echo $e->getMessage();
    }
    catch( TapiocaException\InvalidArgumentException $e )
    {
      // if index is everything else than numeric
      echo $e->getMessage();
    }
```

#### Debug

Count results:
```php

    echo $collection->count(); // total count of returned documents
    echo $collection->total(); // total count of documents matching the query without offset limit (for pagination)
```

Print your query paramters:  

```php

    $collection->query());
```

Print interpreted query parameters by the server:  

```php

    $collection->debug();
```

Dot notation to access to document property:

```php

    echo $collection->at(0)->get('title');                             // get title value
    echo $collection->at(0)->get('undefinedField', 'a default value'); // return the default value
    echo $collection->at(0)->get('image.basename');                    // walk through the document object
    echo $collection->at(0)->tapioca('user.username');                 // walk through tapioca object
```

<hr>

### Document

Simply pass the collection <code>slug</code> and the document <code>ref</code>

```php
    try
    {
      $document = $clientTapioca->document( 'acme', '5414bcc54a15a' );
    }
    catch( TapiocaException\ErrorResponseException $e )
    {
      echo $e->getMessage();
    }
```

It will return a Tapioca\Document Object with almost the same helpers:

```php

    echo $document->tapioca('ref');
    echo $document->tapioca('user.username');
    echo $document->title;
    echo $document->description;
    echo $document->undefinedField; // return empty string
```

<hr>

### Preview

If passed <code>token</code> is valid, return a document's preview as <code>Tapioca\Document</code> object.  
<code>_tapioca</code> part is no reliable.

```php
    try
    {
      $preview = $clientTapioca->preview( 'fb1e19a3991780e4513147c6867ab37876d6a0ca' );
    }
    catch( TapiocaException\ErrorResponseException $e )
    {
      echo $e->getMessage();
    }
```

<hr>

### File

Get file's details from library.

```php
	$file = $instance->library('13147c6867ab37876d');
```

<hr>

### Clear Cache

To clear all cache files

```php
    $resp = $clientTapioca->clearCache();
```

