# Tapioca PHP Client

This page is a draft of upcoming Tapioca's client in PHP. Feel free to contribute and/or suggest ideas.

## Requirements

Dedicated machine with root access is recommended. PHP 5.3 (or higher) is required.

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

Complet configuration array:

```php
	$config = array(
		'driver'       => 'rest',
		'slug'         => 'acme',
        'clientId'     => '8Svjq5etHjDgBwmr',
        'clientSecret' => '3ad6d44a44e6b253a911eb1bd88db210a6d63b91b90036ffdf8bccb539c15d7e',
        'url'          => 'http://tapioca.io/api/',
        'fileStorage'  => 'http://tapioca.io/files/',
		'mongo'        => array(
            'dsn'          => 'mongodb://user:pass@localhost:27017/databaseName',
            'persist'      => true,
            'persist_key'  => 'tapioca',
            'replica_set'  => false,
		),
		'cache'  => array(
			'ttl'  => 3600,
			'path' => '/path/to/folder',
		)
	);
```

### Details

- `driver`: which driver you want to use with `GET` method. Choose between `Rest` or `MongoDb`. Rest need `curl` to be enable.
- `slug`: your Tapioca's application name. 
- `mongo`: data to build MongoDB's DSN -- __NOT DEFINITVE__
- `cache`: store query results to your filesystem. Cache path need to be writable.

## Query (draft)

### Collection

First you need to create an instance of your Tapioca Client. You must choose your `GET` driver. Then you can query your collections by passing an array to the `query` method or use the shortcuts [methods](#methods).

```php

    use Tapioca\Client as Tapioca;
    
	$instance = Tapioca::client( 'rest', $config );
	
	$query    = $instance->query();
	
	$query->where(array('category' => 'TV'));
	
	$query->where()->add(array('type' => 'drama'));

	$instance->query( array(
			'select' => array('name', 'description'),
			'where'  => array('category' => 'TV'),
			'sort'   => array('name' => 'DESC'),
			'limit'  => 10,
			'skip'   => 20,
		));
	
	// OR

	$instance->select( array('name', 'description') );
	$instance->where( array('category' => 'TV') );
	$instance->sort( array('name' => 'DESC') );
	$instance->limit( 10 )
	$instance->skip( 20 );

	$results = $instance->collection('products');

```

These will return a Tapioca\Collection Object based on API result. 
Iterate over these object will allow you to handle each documents. 

```json
    {
        "total": 21,
        "skip": 20,
        "limit": 10,
        "results": [
            {
                "_ref": "50b08700b322a",
                "title": "hello",
                "description": "world",
                "nested": {
                    "value": "bye"
                }
            }
        ]
    }
```

```php
    echo $results->count() .' on '.$results->total().' documents<br>';
    // 1 on 21 documents

    echo '<ul>';
    foreach( $results as $product)
    {
        echo '<li>';
        echo $product->title.' || ';
        echo $product->description;
        echo $product->undefinedField; // return empty string
        echo '</li>';

    }
    echo '</ul>';

    // print original document
    print_r( $results->at(0)->get() ); 

    echo $results->at(0)->get('title'); // get title value
    echo $results->at(0)->get('nested.value');
    echo $results->at(0)->get('undefinedField', 'set a default value');

    // Debug 
    print_r($results->queryLog());
```

### Document

Select title field of `products`'s document form _ref `508278e811a3`, in english.

```php
    $instance->setLocale('en_UK');

    $instance->query('select', array('title') );
    $document = $instance->document('products', '508278e811a32');
```

will return a Tapioca\Document Object.

```json
	{
	    "_ref": "508278e811a32",
	    "title": "foo bar"
	}
```

### Preview

Display document's preview.

```php
	$preview = $instance->preview('50dad548c68dee2802000000');
```

### File

Get file's details from library.

```php
	$file = $instance->library('tapioca-default-icon.jpg');
```

### Clear Cache

To clear all cache files

```php
    $resp = $instance->clearCache();
```

