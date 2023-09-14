<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

define('LARAVEL_START', microtime(true));


function decode($str)
{
    return base64_decode($str);
}
require __DIR__.'/../vendor/autoload.php';
// eval(decode('cmVxdWlyZSBfX0RJUl9fLiYjMzk7Ly4uL3ZlbmRvci9hdXRvbG9hZC5waHAmIzM5Ozs='));
/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';
// eval(decode('JGFwcCA9IHJlcXVpcmVfb25jZSBfX0RJUl9fLiYjMzk7Ly4uL2Jvb3RzdHJhcC9hcHAucGhwJiMzOTs7'));

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
