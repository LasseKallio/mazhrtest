## MAZHR BACKEND

###Messages

**Payment (paytrail/payment)**

* payment_claimed: free test has been claimed
* payment_startfail: payment processing failed	        	
* payment_logfail: unable to log the payments
* payment_unfinished: user has unfinished payment
* payment_instrumentnotfound: wrong test instument
* payment_generalfail: something wrong with the service
* payment_success
* payment_error: error processing the returned data
* payment_cancelled

**Test (cute/test)**

* test_notfound: test not found with the given id
* test_notpaid: test is not paid
* test_cancel: test has been cancelled
* test_savefail: saving the test result failed

**LinkedIn (linkedin)**

* linkedin_samemail: same email address has been used in the service
* linkedin_detailsfail: unable to get details from LinkedIn
* linkedin_accesstokenfail: unable to get the access token from LinkedIn

###"Constants"

**user_tests.status**

* 0 new
* 1 paid
* 2 reseted
* 3 unfinished payment
* 4 payment reseted

**discount_codes.status**

* 1 active
* 2 deleted
* 3 limit reached
* 4 future: discount code is not yet active
* 5 outdated

**payment.status**

* 0 created: user has pressed purchase button
* 1 started: user has proceeded to paytrail
* 2 cancelled: user has cancelled the payent
* 3 error: error in payment
* 4 system error: something is wrong with the service
* 5 success

**users.education_level**

* 1 Peruskoulu
* 2 Ammattitutkinto
* 3 Ylioppilastutkinto
* 4 Alempi Ammattikorkeakoulututkinto
* 5 Alempi korkeakoulututkinto
* 6 Ylempi ammattikorkeakoulututkinto
* 7 Ylempi korkeakoulututkinto


## About Laravel PHP Framework

[![Build Status](https://travis-ci.org/laravel/framework.svg)](https://travis-ci.org/laravel/framework)
[![Total Downloads](https://poser.pugx.org/laravel/framework/downloads.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Stable Version](https://poser.pugx.org/laravel/framework/v/stable.svg)](https://packagist.org/packages/laravel/framework)
[![Latest Unstable Version](https://poser.pugx.org/laravel/framework/v/unstable.svg)](https://packagist.org/packages/laravel/framework)
[![License](https://poser.pugx.org/laravel/framework/license.svg)](https://packagist.org/packages/laravel/framework)

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as authentication, routing, sessions, and caching.

Laravel aims to make the development process a pleasing one for the developer without sacrificing application functionality. Happy developers make the best code. To this end, we've attempted to combine the very best of what we have seen in other web frameworks, including frameworks implemented in other languages, such as Ruby on Rails, ASP.NET MVC, and Sinatra.

Laravel is accessible, yet powerful, providing powerful tools needed for large, robust applications. A superb inversion of control container, expressive migration system, and tightly integrated unit testing support give you the tools you need to build any application with which you are tasked.

## Official Documentation

Documentation for the entire framework can be found on the [Laravel website](http://laravel.com/docs).

### Contributing To Laravel

**All issues and pull requests should be filed on the [laravel/framework](http://github.com/laravel/framework) repository.**

### License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)