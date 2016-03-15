<?php
class Test extends Eloquent {

    protected $table = 'tests';
    protected $fillable = array('instrument', 'name', 'price');

    /**
     * test with instrument as index
     *
     * @return array
     */
    public static function assocTests($withDiscounts = false)
    {
        $tests = self::all();
        $discounts = $withDiscounts ? DiscountCode::whereIn('status', array(DiscountCode::DISCOUNT_ACTIVE, DiscountCode::DISCOUNT_FUTURE))->get() : null;
        $response = array(); 
        foreach($tests as $test)
        {
            $response[$test->instrument] = $test;
            
            //discount codes
            if($discounts)
            {
                $discountCodes = array();
                foreach($discounts as $discount)
                {
                    if($discount->test_id == $test->id)
                    {
                        $discountCodes[] = $discount;
                    }
                }
                $response[$test->instrument]['discount_codes'] = $discountCodes;
            } 
        }
        return $response;
    }
    /**
     * Number of tests
     *
     * @return array done
     */
    public static function getNumberOfTests($userId, $tests = null)
    {
        $userTests = $tests == null ? UserTest::activeTests($userId) : $tests;
        $testsTotal = self::all()->count();
        $testsDone = 0;
        
        foreach($userTests as $test)
        {
            if(($test->status == 1) && (!empty($test->score))) $testsDone++;
        }

        return array("tests" => $testsTotal, "testsDone" => $testsDone);
    }
}
