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

    'url' => 'http://testipenkki.esmes.fi/mzr/back',
 
    /*
    |--------------------------------------------------------------------------
    | AadminURL
    |--------------------------------------------------------------------------
    |
    | This URL is used to set right kind of headers to admin requests and to
    | validate the source host. This should match the "adminheaders" allowed
    | origin.
    |
    */

    'admin_url' => '',

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
        'Access-Control-Allow-Origin: *',
        'Access-Control-Allow-Methods: OPTIONS, POST, GET, PUT, DELETE',
        'Access-Control-Allow-Headers: Authorization, MazhrSession',
        'Expires: Mon, 26 Jul 1990 05:00:00 GMT',
        'Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT',
        'Cache-Control: no-store, no-cache, must-revalidate',
        array('Cache-Control: post-check=0, pre-check=0', false),
        'Pragma: no-cache'
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

    /*
    |--------------------------------------------------------------------------
    | Language file URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the api to provide translations
    |
    */

    'language_url'   => '/var/www/html/mzr/newapp/languages',

);
