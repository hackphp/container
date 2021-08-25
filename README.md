# HackPHP Container
HackPHP dependency injection container.

## Installation
```sh
composer require hackphp/container
```

## Usage
```php

use Hackphp\Container\Container;

$container = Container::getInstance();

// bind class to the container by key = class name 
$container->bind(ApiHandler::class, fn () => new ApiHandler("123"));

// bind by key = interface.
$conainer->bind(ApiInterface::class, TestApi::class);

// bind by key = string
$container->bind("api", fn () => new Api);

// bind singleton
$container->singleton(ApiInterface::class, fn () => new ApiHandler("123"));

// bind class without instructuions
$container->bind(TestApi::class);
$container->singleton(TestApi::class);

// pass the object instead of closure or string
$apiHandler = new ApiHandler("123");
$container->instance(ApiInterface::class, $apiHandler);

// resolve binding
$container->make("api"); // use string key
$container->make(ApiInterface::class);

// PSR-11
$container->get("api");
$container->has("api") // bool
```