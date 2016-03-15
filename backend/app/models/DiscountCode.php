<?php
class DiscountCode extends Eloquent {

	protected $table = 'discount_codes';

	const DISCOUNT_ACTIVE = 1;
	const DISCOUNT_DELETED = 2;
	const DISCOUNT_LIMIT_REACHED = 3;
	const DISCOUNT_FUTURE = 4;
	const DISCOUNT_OUTDATED = 5;

	/**
	 * claim discount code
	 *
	 * @param integer $testId
	 * @param string $discountCode
	 * @return boolean
	 */
	public static function claimCode($testId, $discountCode, $userId)
	{
		$code = self::where('code', '=', $discountCode)->first();

		if(empty($code)) return false;

		if($code->test_id != $testId) throw new Exception('Code test id and given test id does not match!');
		
		if($code->isValid()) {
			$test = Test::find($testId);	
			$userTest = UserTest::firstOrCreate(array(
				'user_id' => $userId,
				'instrument_id' => $test->instrument,
				'status' => UserTest::TEST_NEW
			));

			$userTest->discount_code_id = $code->id;
			$userTest->save();
		}

		return $code;
	}
	/**
	 * check code validity
	 *
	 * @return boolean
	 */
	public function isValid()
	{

		// check limit
		if(!empty($this->usage_limit))
		{
			$usedCodes = UserTest::where('discount_code_id', '=', $this->id)->where('status', '!=', UserTest::TEST_PAYMENT_RESETED)->count();
			if($usedCodes >= $this->usage_limit)
			{
				$this->status = self::DISCOUNT_LIMIT_REACHED;
				$this->save();
				return false;
			}
		}
		// check start
		if(!empty($this->start))
		{
			$start = strtotime($this->start);
			if(time() < $start)
			{
				$this->status = self::DISCOUNT_FUTURE;
				$this->save();
				return false;
			}
		}
		// check end
		if(!empty($this->end))
		{
			$end = strtotime($this->end);
			if(time() > $end)
			{
				$this->status = self::DISCOUNT_OUTDATED;
				$this->save();
				return false;	
			}
		}

		return true;

	}	
}

