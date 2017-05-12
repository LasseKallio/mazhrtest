<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{

    $resHeaders = Config::get('app.headers');
    $reqHeaders = getallheaders();

    foreach($resHeaders as $header)
    {
        if(is_array($header))
        {
            header($header[0], $header[1]);
        }
        else
        {
            header($header);
        }
    }

    if(isset($reqHeaders['Referer']))
    {
        $url = parse_url($reqHeaders['Referer']);
        $from = $url['scheme'] . '://' . $url['host'];

        if($from == Config::get('app.admin_url')) {
            $adminHeaders = Config::get('app.admin_headers');
            foreach($adminHeaders as $header)
            {
                if(is_array($header))
                {
                    header($header[0], $header[1]);
                }
                else
                {
                    header($header);
                }
            }
        }
    }
});


App::after(function($request, $response)
{
    //dd($request);
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
    if (Auth::guest())
    {
        return Response::make('Unauthorized', 401);
    }
});

Route::filter('auth.admin', function()
{
    if (Auth::guest() || Auth::user()->user_status != User::USER_ADMIN)
    {
        return Response::make('Unauthorized', 401);
    }
});

Route::filter('auth.basic', function()
{
    return Auth::basic();
});

Route::filter('JWTauth', function()
{

    // for some reason the original jwt-auth does not find the "Authorization" header
    $headers = getallheaders();

    // check auth token
    if(isset($headers['Authorization']))
    {
        $authParts = explode(' ', $headers['Authorization']);
        $token = $authParts[1];
        try
        {
            JWTAuth::authenticate($token);
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage() . "\n\n" . $e->getTraceAsString());
            $tmpToken = getTmpToken();
            return Response::json(array("tmpToken"=>$tmpToken),401);
        }
    }
    else
    {
        $tmpToken = getTmpToken();
        return Response::json(array("tmpToken"=>$tmpToken),401);
    }
});

Route::filter('ApiToken', function()
{
    $headers = getallheaders();

    if(!isset($headers['Authorization']) || $headers['Authorization'] != 'deadbeef')
    {
      return Response::make('Unauthorized', 401);
    }
});

Route::filter('OptionalJWTauth', function()
{
    // check auth token
    $headers = getallheaders();
    if(isset($headers['Authorization']))
    {
        $authParts = explode(' ', $headers['Authorization']);
        $token = $authParts[1];
        try
        {
            JWTAuth::authenticate($token);
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage() . "\n\n" . $e->getTraceAsString());
        }
    }
});

Route::filter('InputAuth', function($route)
{
    $success = false;

    $token = parseInputToken($route->getParameter('mazhr_token'));

    if(!empty($token))
    {
        if($mazhrSession = MazhrSession::get($token))
        {
            if(!empty($mazhrSession->user_id))
            {
                $success = true;
            }
        }
    }

    if(!$success)
    {
        return Response::make('Unauthorized', 401);
    }
});

Route::filter('session.remove', function()
{
    Auth::logout();
    return Config::set('session.driver', 'array');
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
    if (Auth::check()) return Redirect::to('/');
});


/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
    if (Session::token() != Input::get('_token'))
    {
        throw new Illuminate\Session\TokenMismatchException;
    }
});
