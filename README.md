# Static Files StackPHP Middleware

[![Build Status](https://travis-ci.org/phramz/staticfiles-middleware.svg)](https://travis-ci.org/phramz/staticfiles-middleware) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phramz/staticfiles-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phramz/staticfiles-middleware/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/phramz/staticfiles-middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/phramz/staticfiles-middleware/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/e3ae1420-edd0-4087-9d23-80f2323ceaa5/mini.png)](https://insight.sensiolabs.com/projects/e3ae1420-edd0-4087-9d23-80f2323ceaa5)

This is a StackPHP middleware for the `phramz/staticfile` webserver (https://github.com/phramz/staticfiles)

## Install

Install with Composer:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar require phramz/staticfiles-middleware
```

## Example

```php
<?php

use Symfony\Component\HttpFoundation\Request;

// your static files will be served from this folder
$webroot = '/var/www';

// if we cannot guess the files mime-type we'll use this default
$defaultMimetype = 'application/octed-stream';

// files with the following extensions will not be delivered. We'll get a 404 instead.
$exclude = ['php', 'key'];

// if true requests to non existing ressources will be passed to the next app in stack.
// if false the middleware will return a 404 response
$ignoreNotFound = true;

// create your application ... whatever it is e.g. Silex, Symfony2 etc.
$app = new Application();

// build the stack
$app = (new Stack\Builder())
    ->push(
        'Phramz\Staticfiles\Middleware\HttpServer', 
        $webroot, 
        $defaultMimetype, 
        $exclude,
        $ignoreNotFound
    )
    ->resolve($app);

// dispatch the request
$request = Request::createFromGlobals();

$response = $app->handle($request);
$response->send();

// and shutdown
$app->terminate($request, $response);
```

## LICENSE

This project is under MIT license. Please read the LICENSE file for further information.

## Credits

Some of the 3rd party libraries in use:

* https://github.com/stackphp
* https://github.com/symfony
* https://github.com/webmozart
* https://github.com/phpunit