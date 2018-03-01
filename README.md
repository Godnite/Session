# Session Package

[![Build Status](https://travis-ci.org/rancoud/Session.svg?branch=master)](https://travis-ci.org/rancoud/Session) [![Coverage Status](https://coveralls.io/repos/github/rancoud/Session/badge.svg?branch=master)](https://coveralls.io/github/rancoud/Session?branch=master)

Session.  

## Installation
```php
composer require rancoud/session
```

## Informations
By default Session is in read only (session option read_and_close).  
Session will automatically start in read only when using `get, has, hasKeyAndValue, getAll`
Session will automatically start in write mode when using `set, remove, getAndRemove, keepFlash, gc, regenerate`

## How to use it?
Set and get value from $_SESSION
```php
Session::set('key', 'value');
$value = Session::get('key');
```
In read only
```php
Session::setReadWrite(); // before starting session
$value = Session::get('key');
```
With custom options
```php
Session::setOption('name', 'custom_session_name');
Session::start(['cookie_lifetime' => 1440]);
Session::set('key', 'value');
$value = Session::get('key');
```
With encryption on default php session
```php
Session::useDefaultEncryptionDriver('keyForEncryption');
Session::set('key', 'value');
$value = Session::get('key');
```
With File driver
```php
Session::useFileDriver();
Session::set('key', 'value');
$value = Session::get('key');
```
With Database driver (you have to install rancoud/database)
```php
$conf = new \Rancoud\Database\Configurator([
    'engine'   => 'mysql',
    'host'     => '127.0.0.1',
    'user'     => 'root',
    'password' => '',
    'database' => 'test_database'
]);
$db = new \Rancoud\Database\Database($conf);

// for using a current connection
Session::useCurrentDatabaseDriver($db);

// for creating a new connection
//Session::useNewDatabaseDriver($conf);

Session::set('key', 'value');
$value = Session::get('key');
```
With Redis driver (you have to install predis/predis)
```php
$params = [
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
];
$redis = new Predis\Client($params);

// for using a current connection
Session::useCurrentRedisDriver($redis);

// for creating a new connection
//Session::useNewRedisDriver($params);

Session::set('key', 'value');
$value = Session::get('key');
```
With your own driver implementing SessionHandlerInterface
```php
$driver = new MyCustomDriver();
Session::useCustomDriver($driver);
Session::set('key', 'value');
$value = Session::get('key');
```

## Session Methods
### General Commands  
* method(name: type, [optionnal: type = defalut]):outputType  

## Default
Use SessionHandler
## File
Extends SessionHandler
## Database
You need to install
```php
composer require rancoud/database
```
```sql
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(128) NOT NULL,
  `id_user` int(10) unsigned DEFAULT NULL,
  `last_access` datetime NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

```
## Redis
You need to install
```php
composer require predis/predis
```

## How to Dev
`./run_all_commands.sh` for php-cs-fixer and phpunit and coverage  
`./run_php_unit_coverage.sh` for phpunit and coverage    