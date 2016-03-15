<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Application Debug Mode
	|--------------------------------------------------------------------------
	|
	| When your application is in debug mode, detailed error messages with
	| stack traces will be shown on every error that occurs within your
	| application. If disabled, a simple generic error page is shown.
	|
	*/

	'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url'       => 'http://localhost/backend',

    /*
    |--------------------------------------------------------------------------
    | Headers
    |--------------------------------------------------------------------------
    |
    | Application headers
    |
    */

    'headers' => array
    (
        'Access-Control-Allow-Origin: http://localhost:9000',
        'Access-Control-Allow-Methods: OPTIONS, POST, GET, PUT, DELETE',
        'Access-Control-Allow-Headers: Authorization, MazhrSession',
        'Access-Control-Allow-Credentials: true'
    ),

    /*
    |--------------------------------------------------------------------------
    | Admin Headers
    |--------------------------------------------------------------------------
    |
    | Headers for admin requests will be added to reqular headers if
    | host is admin host.
    |
    */

    'admin_headers' => array
    (
    ),     

);
