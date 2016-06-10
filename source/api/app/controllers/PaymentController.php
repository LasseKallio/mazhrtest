<?php

class PaymentController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Payment Controller
	|--------------------------------------------------------------------------
	|
	| Paytrail payments
	|
	*/

    /**
     * Redirect user to payment provider
     *
     */
	public function newPayment()
	{
		$instrumentId = Input::get('instrument');
		$tests = Test::assocTests();
		$token = parseInputToken();
		$mazhrSession = MazhrSession::get($token);

		$user = User::find($mazhrSession->user_id);
		$config = Config::get('services.paytrail');
		$returnUrl = Input::has('return_url') ? urldecode(Input::get('return_url')) : Request::server('HTTP_REFERER');
		Session::put('payment_return_url', $returnUrl);

		if(isset($tests[$instrumentId]))
		{
			$test = $tests[$instrumentId];
			$priceToPay = $test->price;

			// Find or Create the test, but check first if unfinished is found
			$userTest = UserTest::where('user_id', '=', $user->id)
			->where('instrument_id', '=', $instrumentId)
			->where('status', '=', UserTest::TEST_UNFINISHED_PAYMENT)
			->first();

			if(!$userTest) {
				$userTest = UserTest::firstOrCreate(array(
					'user_id' => $user->id,
					'instrument_id' => $instrumentId,
					'status' => UserTest::TEST_NEW
				));
			}

			// check if user has reseted test (then use second price)
			$reseted = UserTest::where('status', '=', UserTest::TEST_RESETED)
            ->where('instrument_id', '=', $instrumentId)
            ->where('user_id', '=', $user->id)->first();

            if($reseted && ($test->second_price > 0))
            {
                $priceToPay = $test->second_price;
            }

			// check for discounts
			if(!empty($userTest->discount_code_id))
			{
				$discount = DiscountCode::find($userTest->discount_code_id);
				$priceToPay = $discount->price < $priceToPay ? $discount->price : $priceToPay;
			}

			// This test is free, no need to make the payment
			if($priceToPay == 0) {
				$userTest->status = UserTest::TEST_PAID;
				$userTest->save();
				$mazhrSession->setValue('message', 'payment_claimed');
				return Redirect::to($returnUrl);
			}

			$urlset = new Paytrail_Module_Rest_Urlset(
	    		url('paytrail/callback/success?mazhr_token=' . $token), // onnistuneen maksun paluuosoite
	    		url('paytrail/callback/failure?mazhr_token=' . $token), // epäonnistuneet maksun paluuosoite
	    		url('paytrail/callback/notify'),  // osoite, johon lähetetään maksuvarmistus Paytrailin palvelimelta
	   			""  // pending-osoite ei käytössä
			);

			$orderNumber = $userTest->id . '-' . $test->instrument . '-' . $user->id;

			if(PaymentLog::canProceed($orderNumber))
			{

				$payment = new Paytrail_Module_Rest_Payment_S1($orderNumber, $urlset, $priceToPay);
				$module = new Paytrail_Module_Rest($config['merchantId'], $config['merchantSecureCode']);
				$paylog = PaymentLog::newTransaction($orderNumber, $test->name, $userTest->id, $user->id, $priceToPay);
				$paylog->status = PaymentLog::PAYMENT_CREATED;
				$paylog->save();

				try {
				    $result = $module->processPayment($payment);
				    $paylog->status = PaymentLog::PAYMENT_STARTED;
				    $paylog->save();
				   	$userTest->status = UserTest::TEST_UNFINISHED_PAYMENT;
					$userTest->save();
				    // Everything ok, let´s go shopping
				    Auth::logout();
				    return Redirect::to($result->getUrl());
				}
				catch (Paytrail_Exception $pe) {
		        	Log::warning('Unable ta start the payment: ' . $pe->getMessage());;
	            	$mazhrSession->setValue('message', 'payment_startfail');
		        } catch (Exception $e)
				{
		        	Log::warning('Payment logging failure: ' . $e->getMessage());
	            	$mazhrSession->setValue('message', 'payment_logfail');
		        }
	    	}
	    	else
	    	{
				Log::info('Unfinished payment ('. $user->email .')');
	            $mazhrSession->setValue('message', 'payment_unfinished');
	    	}

		}
		else
		{
			Log::info('Instrument '.$instrumentId.' not found ('. $user->email .')');
            $mazhrSession->setValue('message', 'payment_instrumentnotfound');
		}
		Auth::logout();
		return Redirect::to($returnUrl);
	}

    /**
     * Handle successful payment
     *
     */
	public function paymentSuccess()
	{

		$token = parseInputToken();
		$mazhrSession = MazhrSession::get($token);

		$config = Config::get('services.paytrail');
		$module = new Paytrail_Module_Rest($config['merchantId'], $config['merchantSecureCode']);
		$returnUrl = Session::get('payment_return_url');

	    $rules = array(
	    	'ORDER_NUMBER' 		=> 'required',
	    	'TIMESTAMP' 		=> 'required',
	    	'PAID'				=> 'required',
	    	'METHOD'			=> 'required',
	    	'RETURN_AUTHCODE' 	=> 'required',
	    );

	    $validator = Validator::make(Input::all(), $rules);

	    if ($validator->fails())
	    {
	    	Log::info('PaymentSuccess called with wrong input! Order: ' . Input::get('ORDER_NUMBER'));
	    	$mazhrSession->setValue('message', 'payment_generalfail');
	    	return Redirect::to($returnUrl);
	    }

		$paylog = PaymentLog::where(array('order_number' => Input::get('ORDER_NUMBER')))->firstOrFail();

		try
		{
			if ($module->confirmPayment(Input::get('ORDER_NUMBER'), Input::get('TIMESTAMP'), Input::get('PAID'), Input::get('METHOD'), Input::get('RETURN_AUTHCODE'))) {

				$orderNumberParts = explode('-', Input::get('ORDER_NUMBER'));
				$userTestId = $orderNumberParts[0];

				$test = UserTest::findOrFail($userTestId);
				$test->status = UserTest::TEST_PAID;
				$test->save();

				$paylog->json_response = json_encode(Input::all());
				$paylog->status = PaymentLog::PAYMENT_SUCCESSFUL;
				$paylog->save();

				$mazhrSession->setValue('message', 'payment_success');

			} else {
				$paylog->status = PaymentLog::PAYMENT_ERROR;
				$paylog->save();
				$test->status = UserTest::TEST_NEW;
				$test->save();
				Log::info('Payment '. Input::get('ORDER_NUMBER') . 'has failed!');
				$mazhrSession->setValue('message', 'payment_error');
			}
		}
		catch (Exception $e) {
			$paylog->status = PaymentLog::PAYMENT_SYSTEM_ERROR;
			$paylog->save();
			$test->status = UserTest::TEST_NEW;
			$test->save();
			Log::warning('Payment failed ('. Input::get('ORDER_NUMBER') .'): ' . $e->getMessage());
            $mazhrSession->setValue('message', 'payment_generalfail');
		}

		return Redirect::to($returnUrl);
	}
    /**
     * Handle failed payment
     *
     */
	public function paymentFailure()
	{
		$token = parseInputToken();
		$mazhrSession = MazhrSession::get($token);

	    $rules = array(
	    	'ORDER_NUMBER' 		=> 'required',
	    	'TIMESTAMP' 		=> 'required',
	    	'RETURN_AUTHCODE' 	=> 'required',
	    );
	    $returnUrl = Session::get('payment_return_url');
	    $validator = Validator::make(Input::all(), $rules);

	    if ($validator->fails())
	    {
	    	Log::info('PaymentFailure called with wrong input!');
	    	$mazhrSession->setValue('message', 'payment_generalfail');
	    	return Redirect::to($returnUrl);
	    }
	    else
	    {
			$orderNumberParts = explode('-', Input::get('ORDER_NUMBER'));
			$userTestId = $orderNumberParts[0];
			$test = UserTest::findOrFail($userTestId);

	    	$paylog = PaymentLog::where(array('order_number' => Input::get('ORDER_NUMBER')))->firstOrFail();
	    	$paylog->status = PaymentLog::PAYMENT_CANCELLED;
			$paylog->save();
			$test->status = UserTest::TEST_NEW;
			$test->save();
			Log::info('Payment '. Input::get('ORDER_NUMBER') . ' has been cancelled!');
			$mazhrSession->setValue('message', 'payment_cancelled');
			return Redirect::to($returnUrl);
	    }
	}
    /**
     * Handle payment notify from Paytrail
     *
     */
	public function paymentNotify()
	{
		$config = Config::get('services.paytrail');
		$module = new Paytrail_Module_Rest($config['merchantId'], $config['merchantSecureCode']);

	    $rules = array(
	    	'ORDER_NUMBER' 		=> 'required',
	    	'TIMESTAMP' 		=> 'required',
	    	'PAID'				=> 'required',
	    	'METHOD'			=> 'required',
	    	'RETURN_AUTHCODE' 	=> 'required',
	    );

	    $validator = Validator::make(Input::all(), $rules);

	    if ($validator->fails())
	    {
	    	Log::info('PaymentNotify called with wrong input!');
	    	return Response::make('Bad request!', '400');
	    }

		$paylog = PaymentLog::where(array('order_number' => Input::get('ORDER_NUMBER')))->firstOrFail();

		try
		{
			if ($module->confirmPayment(Input::get('ORDER_NUMBER'), Input::get('TIMESTAMP'), Input::get('PAID'), Input::get('METHOD'), Input::get('RETURN_AUTHCODE'))) {

				$orderNumberParts = explode('-', Input::get('ORDER_NUMBER'));
				$userTestId = $orderNumberParts[0];

				$test = UserTest::findOrFail($userTestId);
				if($test->status == UserTest::TEST_UNFINISHED_PAYMENT)
				{
					$test->status = UserTest::TEST_PAID;
					$test->save();

					$paylog->json_response = json_encode(Input::all());
					$paylog->status = PaymentLog::PAYMENT_SUCCESSFUL;
					$paylog->save();

					Log::info('Payment '. Input::get('ORDER_NUMBER') . 'is successful with notify!');
				} else if ($test->paid == UserTest::TEST_PAID) {
					Log::info('Payment '. Input::get('ORDER_NUMBER') . 'is already paid!');
				}

			} else {
				$paylog->status = PaymentLog::PAYMENT_ERROR;
				$paylog->save();
				$test->status = UserTest::TEST_NEW;
				$test->save();
				Log::info('Payment '. Input::get('ORDER_NUMBER') . 'has failed!');
			}
		}
		catch (Exception $e) {
			$paylog->status = PaymentLog::PAYMENT_SYSTEM_ERROR;
			$paylog->save();
			$test->status = UserTest::TEST_NEW;
			$test->save();
			Log::warning('Payment failed ('. Input::get('ORDER_NUMBER') .'): ' . $e->getMessage());
		}

		return Response::make('Ok', '200');
	}
	/**
	* Payment log
	*
	* @return json response
	*/
	public function PaymentLog()
	{
		$logData = PaymentLog::leftJoin('user_tests', 'payment_log.product_id', '=', 'user_tests.id')
			->leftJoin('discount_codes', 'user_tests.discount_code_id', '=', 'discount_codes.id')
			->select('payment_log.*', 'discount_codes.code')
			->get();
		$response = MzrRestResponse::get(array(), $logData);
		return Response::json($response);
	}
	/**
	* Payment log csv export
	*
	* @return json response
	*/
	public function PaymentLogCsv()
	{
		$csv = "";
		$logData = PaymentLog::leftJoin('user_tests', 'payment_log.product_id', '=', 'user_tests.id')
			->leftJoin('discount_codes', 'user_tests.discount_code_id', '=', 'discount_codes.id')
			->select(
				'payment_log.id',
				'payment_log.updated_at',
				'payment_log.product_id',
				'payment_log.product_name',
				'payment_log.order_number',
				'payment_log.sum',
				'discount_codes.code',
				'payment_log.status'
			)
			->get();
	    $headers = array(
	        'Content-Type' => 'application/csv',
	        'Content-Disposition' => 'attachement; filename="payments_' . date('ymdHis') . '.csv";'
	    );

	    $csv = toCsv($logData);

    	return Response::make($csv, '200', $headers);
	}
}
