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
		'driver' => 'rest',
		'slug'   => 'acme',
		'server' => 'tapioca.io',
		'object' => true,
		'rest'   => array(
			'https'        => true,
			'clientId'     => 'YOUR_CLIENT_ID',
			'clientSecret' => 'YOUR_CLIENT_SECRET',
         ),
		'mongo'  => array(
			'username' => 'YOUR_USER_NAME',
			'password' => 'YOUR_PASSWORD',
			'database' => 'acme',
			'port'     => 27017,
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
- `object`: should we return array (MongoDB php driver default behavior) or object (Rest's JSON default behavior).
- `rest`: your credentials for API connection and POST/PUT signature -- __NOT DEFINITVE__
- `mongo`: data to build MongoDB's DSN -- __NOT DEFINITVE__
- `cache`: store query results to your filesystem. Cache path need to be writable.

## Query

First you need to create an instance of your Tapioca Client. You must choose your `GET` driver. Then you can query your collections by passing an array to the `query`method or use the assignation [methods](#methods).

```php
	$instance = Tapioca::client( 'rest', $config );

	$instance->query( array(
			'select' => array('name', 'desc'),
			'where'  => array('category' => 'TV'),
			'limit'  => 10,
			'skip'   => 20,
		));
	
	// OR

	$instance->select( array('name', 'desc') );
	$instance->where( array('category' => 'TV') );
	$instance->limit( 10 )
	$instance->skip( 20 );

	$results = $instance->get('products');

```

Select title field of `products`'s document form _ref `508278e811a3`, in english.

```php
    $instance->set('select', array('title') );
    $instance->locale('en_UK');
    $document = $instance->document('products', '508278e811a32');
```

Display document's preview.

```php
	$preview = $instance->preview('50dad548c68dee2802000000');
```

Get file's details from library.

```php
	$library = $instance->library('tapioca-default-icon.jpg');
```
