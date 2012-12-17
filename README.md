# Tapioca PHP Client

This page is a draft of upcoming Tapioca's client in PHP. Feel free to contribute and/or suggest ideas.

## Requirements

Dedicated machine with root access is recommended. PHP 5.3.3 (or higher) is required.

## Installation

Installation using Composer:

```json
	{
	    "require": {
	        "Tapioca/Client-php": "dev"
	    }
	}
```

## Configuration

Complet configuration array:

```php
	$config = array(
		'slug'   => 'acme',
		'server' => 'tapiocapp.com',
		'rest'   => array(
             'clientId'     => 'YOUR_CLIENT_ID',
             'clientSecret' => 'YOUR_CLIENT_SECRET',
         ),
		'mongo'  => array(
			'user' => 'YOUR_USER_NAME',
			'pass' => 'YOUR_PASSWORD',
		),
		'cache'  => array(
			'ttl'  => 3600,
			'path' => '/path/to/folder',
		)
	);
```

Rest need `curl` to be enable.

## Query

You can query your collection by passing an array to the `query`method or use the assignation methods.

```php
	$instance = Tapioca::client( 'rest', $config );

	$query = $instance->query( array(
				'select' => array('title', 'bio')
				'where'  => array('title' => 'hello')
			));

	$query->order('title', 'DESC');

	$results = $query->get();
```
