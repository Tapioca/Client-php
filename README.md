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
		'server' => 'tapiocapp.com',
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

Rest need `curl` to be enable. Cache path need to be writable.

## Query

You can query your collections by passing an array to the `query`method or use the assignation methods.

```php
	$instance = Tapioca::client( 'rest', $config );

	$instance->query( array(
			'select' => array('name', 'desc'),
			'where'  => array('category' => 'TV')
		));

	$results = $instance->get('products');

```

Ask `title` field of `products`'s document, _ref `508278e811a3`, in english.

```php
    $instance->set('select', array('title') );
    $instance->locale('en_UK');
    $instance->document('products', '508278e811a32');
```
