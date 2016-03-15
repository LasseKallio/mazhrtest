<?php
class UserTest extends Eloquent {

	const TEST_NEW = 0;
	const TEST_PAID = 1;
	const TEST_RESETED = 2;
	const TEST_UNFINISHED_PAYMENT = 3;
	const TEST_PAYMENT_RESETED = 4;

    protected $table = 'user_tests';
    protected $guarded = array('id');

	/**
	 * Active (paid) tests
	 *
	 * @param integer $userId
	 *
	 * @return array $response
	 */
    public static function activeTests($userId) {
    	$tests = DB::table('user_tests')->whereIn('status', array(self::TEST_PAID, self::TEST_UNFINISHED_PAYMENT))->where('user_id', '=', $userId)->get();
    	$response = array();
    	foreach($tests as $test)
    	{
    		if(strlen($test->score) < 4 && !empty($test->score))
    		{
    			$test->score = self::getScoreFromPercentile((float) $test->score);
    		}
    		$response[] = $test;
    	}
    	return $response;
    }

	/**
	 * Get star score from percentile
	 *
	 * @param integer $percentile
	 *
	 * @return integer
	 */
	private static function getScoreFromPercentile($percentile){
		if ($percentile <= 2.4){
			return 1;
		} else if ($percentile <= 16){
			return 2;
		} else if ($percentile <= 50){
			return 3;
		} else if ($percentile <= 84){
			return 4;
		} else if ($percentile <= 97.6){
			return 5;
		} else if ($percentile > 97.6){
			return 6;
		}
			return 0;
	}

}