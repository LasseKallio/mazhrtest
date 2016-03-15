<?php
class PaymentLog extends Eloquent {

	const PAYMENT_CREATED = 0;
	const PAYMENT_STARTED = 1;
	const PAYMENT_CANCELLED = 2;
	const PAYMENT_ERROR = 3;
	const PAYMENT_SYSTEM_ERROR = 4;
	const PAYMENT_SUCCESSFUL = 5;

    protected $table = 'payment_log';
    protected $guarded = array('id');

	/**
	 * New Payment transaction
	 * 
	 * @param string $orderNumber
	 * @param string $productName 
	 * @param integer $productId Id of the product
	 * @param integer $userId current users id
	 * @param decimal $sum	 
	 *
	 * @return object $paymentLog
	 */
	public static function newTransaction($orderNumber, $productName, $productId, $userId, $sum) {

		// Find or Create log
		$paymentLog = self::firstOrCreate(array(
			'order_number' => $orderNumber,
			'product_name' => $productName,
			'product_id' => $productId,
			'user_id' => $userId,
		));

		$paymentLog->sum = $sum; //Sum can change, even if the payment stays the same (discount code is used)
		$paymentLog->save();

		return $paymentLog;
	}

	/**
	 * Check if user has unfinished payments
	 * 
	 * @param string $orderNumber 
	 *
	 * @return boolean $response
	 */
	public static function canProceed($orderNumber) {

		// Find or Create log
		$paymentLog = self::where('order_number', '=', $orderNumber)->first();

		if($paymentLog)
		{
			$response = $paymentLog->status != self::PAYMENT_STARTED;
		}
		else
		{
			$response = true;
		}

		return $response;
	}	
}