<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

/*
| CHECK THIS OUT!
| http://blog.laravel.com/csrf-vulnerability-in-laravel-4/
|
*/


// REST API ROUTES
Route::group(array('prefix' => 'api/v1', 'before' => array('session.remove')), function()
{
    // language
    Route::get('language/{language}', 'HomeController@getTranslations');

    // Messages from session
    Route::get('messages', array('before' => 'OptionalJWTauth', 'uses' => 'HomeController@message'));

    // Login with username and password
    Route::post('login', 'UserController@login');
    // Logout
    Route::any('logout', 'UserController@logout');

    // Register user
    Route::post('register', 'UserController@register');

    // User data
    Route::get('me', array('before' => 'JWTauth', 'uses' => 'UserController@me'));

    // Update user data
    Route::post('me', array('before' => 'JWTauth', 'uses' => 'UserController@updateUser'));
    //Route::post('me/image', array('before' => 'JWTauth', 'uses' => 'UserController@changeImage'));
    Route::post('me/password', array('before' => 'JWTauth', 'uses' => 'UserController@changePassword'));
    Route::post('me/{node}', array('before' => 'JWTauth', 'uses' => 'UserController@updateUser'));
    Route::delete('me/{node}/{id}', array('before' => 'JWTauth', 'uses' => 'UserController@deleteFromNode'));

    // Forgotten password
    Route::post('password', 'UserController@newPassword');

    // Ads by user interest
    Route::get('jobs/possibilities', array('before' => 'OptionalJWTauth', 'uses' => 'AdController@getPossibilities'));

    // Special access endpoints
    Route::get('users', array('before' => 'ApiToken', 'uses' => 'UserController@getAllUsers'));
    Route::get('profiles', array('before' => 'ApiToken', 'uses' => 'ProfileController@getProfiles'));
    Route::get('matches/{profile}', array('before' => 'ApiToken', 'uses' => 'ProfileController@getMatchingProfilesByJob'));

    // Single ad
    Route::get('job/{id}', array('before' => 'OptionalJWTauth', 'uses' => 'AdController@getAd'));
    Route::get('job/{id}/profile/{profileId}', array('before' => 'OptionalJWTauth', 'uses' => 'AdController@getAd'));

    // Users Matches
    Route::get('match/profiles', array('before' => 'JWTauth', 'uses' => 'ProfileController@getMatchingProfiles'));
    Route::get('jobs/matches/{profileId}', array('before' => 'JWTauth', 'uses' => 'AdController@getAdsByProfile'));

    // Tests
    Route::get('tests', array('before' => 'JWTauth', 'uses' => 'TestController@getTests'));
    Route::post('test/reset/{userTestId}', array('before' => 'JWTauth', 'uses' => 'TestController@resetTest'));
    Route::post('test/discount', array('before' => 'JWTauth', 'uses' => 'TestController@claimDiscountCode'));

    // Tags
    Route::get('tags', 'HomeController@getTags');
});

// Login / register / get data with LinkedIn
Route::get('linkedin', 'UserController@linkedin');

// Redirect user to test
Route::get('cute/test', array('before' => 'InputAuth', 'uses' => 'TestController@test'));

// Handle test response
Route::get('cute/result/{testid}/mazhr_token/{mazhr_token}', array('before' => 'InputAuth', 'uses' => 'TestController@testResult'));
Route::get('test/result/', array('before' => 'InputAuth', 'uses' => 'TestController@ajaxTestResult'));
// Public profile
// Login / register / get data with LinkedIn
Route::get('profile/{profileToken}', 'UserController@userProfile');


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes to the admin interface
|
*/

// Login to admin
Route::post('admin/login', 'UserController@login');
Route::post('admin/logout', 'UserController@logout');
Route::get('admin/language/{language}', 'HomeController@getTranslations');

// Admin group - Needs to be logged in with admin status
Route::group(array('before' => 'auth.admin', 'prefix' => 'admin'), function()
{
    // User
    Route::get('me', 'UserController@me');
    Route::get('user/{id}', 'UserController@user');
    Route::post('user', 'UserController@updateUser');
    Route::post('user/{node}', 'UserController@updateUser');

    // Users
    Route::get('users', 'UserController@getUsers');
    Route::post('password', 'UserController@newPassword');

    // Profiles
    Route::get('profile', 'ProfileController@getProfiles');
    Route::delete('profile/{id}', 'ProfileController@removeProfessionCode');
    Route::post('profile/id/{profileId}/code/{code}', 'ProfileController@addProfessionCode');
    Route::post('profile', 'ProfileController@saveProfile');

    // Tests
    Route::get('tests', 'TestController@getTestsWithDiscounts');
    Route::post('test/id/{id}', 'TestController@updateTest');
    Route::post('test/id/{id}/{node}', 'TestController@updateTest');

    // Payments
    Route::get('payments', 'PaymentController@paymentLog');
    Route::get('payments/csv', 'PaymentController@paymentLogCsv');
});


/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
|
*/

Route::group(array('prefix' => 'paytrail', 'before' => 'InputAuth'), function()
{
    Route::get('payment', 'PaymentController@newPayment');
    Route::get('callback/success', 'PaymentController@paymentSuccess');
    Route::get('callback/failure', 'PaymentController@paymentFailure');
});

Route::get('paytrail/callback/notify', 'PaymentController@paymentNotify');

/*
|--------------------------------------------------------------------------
| Api Routes (example for now)
|--------------------------------------------------------------------------
|
*/

Route::group(array('prefix' => 'demoapi/v1/fi_fi'), function()
{
    Route::get('me/{node}', 'ExampleController@mgetMe');
    Route::get('me', 'ExampleController@getMe');
    Route::post('me', 'ExampleController@createMe');
    Route::get('linkedin/callback/success', 'ExampleController@populateMe');
    Route::get('linkedin', function(){
        return View::make('examples/linkedin');
    });
    Route::get('jobs/{param}', 'ExampleController@getJobs');
    Route::get('jobs', 'ExampleController@getJobs');
    //Route::get('createads', 'ExampleController@copyAds');
});
