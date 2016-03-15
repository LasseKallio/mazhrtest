<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => array(
		'domain' => '',
		'secret' => '',
	),

	'mandrill' => array(
		'secret' => 'c_1twviXRNr5qKY5zGhqiw',
	),

	'stripe' => array(
		'model'  => 'User',
		'secret' => '',
	),
	'cut-e' => array(
		'clientId' => '316',
		'secureCode' => '26a76fb68757bdcc932afaf481071695',
		'projectId'	=> '44961',
		'candidatePrefix' => 'mzrtest_',
	),
	'paytrail' => array(
		'merchantId' => 13466,
		'merchantSecureCode' => '6pKF4jkv97zmqBJ3ZL8gUw5DfT2NMQ'
	),	

);