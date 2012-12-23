# Tapioca PHP Client

This page is a draft of upcoming Tapioca's client in PHP. Feel free to contribute and/or suggest ideas.

## Requirements

Dedicated machine with root access is recommended. PHP 5.3.3 (or higher) is required.

## Installation

Installation using Composer:

```json
	{
	    "require": {
	        "tapioca/client-php": "dev-master"
	    }
	}
```

## Configuration

Complet configuration array:

```php
	$config = array(
		'slug'   => 'acme',
		'server' => 'tapiocapp.com',
		'object' => true,
		'rest'   => array(
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
