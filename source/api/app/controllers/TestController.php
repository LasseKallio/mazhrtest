<?php

class TestController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | CUT-E Test Controller
    |--------------------------------------------------------------------------
    |
    | Cut-e tests and result handling
    |
    */

    /**
     * Get tests
     *
     * @return json response
     */
    public function getTests()
    {
        /*
        DB::table('tests')->delete();
        Test::create(array('instrument' => 102, 'name' => 'shapes', 'price' => 11.5));
        Test::create(array('instrument' => 201, 'name' => 'views', 'price' => 5.5));
        Test::create(array('instrument' => 302, 'name' => 'scales_verbal', 'price' => 5.5));
        Test::create(array('instrument' => 301, 'name' => 'scales_numerical', 'price' => 5.5));
        Test::create(array('instrument' => 357, 'name' => 'scales_sx', 'price' => 5.5));
        Test::create(array('instrument' => 315, 'name' => 'scales_lt-e', 'price' => 5.5));
        */
        $tests = Test::assocTests();
        $user = Auth::user();
        // check if user has discounts
        foreach($tests as $instrumentId => $test)
        {

            $discountPrice = 0;
            $resetPrice = 0;

            $reseted = UserTest::where('status', '=', UserTest::TEST_RESETED)
            ->where('instrument_id', '=', $test->instrument)
            ->where('user_id', '=', $user->id)->first();

            if($reseted && ($tests[$instrumentId]->second_price > 0))
            {
                $tests[$instrumentId]->price = $tests[$instrumentId]->second_price;
            }

            $newUserTest = UserTest::where('status', '=', UserTest::TEST_NEW)
            ->where('instrument_id', '=', $test->instrument)
            ->where('user_id', '=', $user->id)
            ->whereNotNull('discount_code_id')->first();
            if($newUserTest)
            {
                $discount = DiscountCode::find($newUserTest->discount_code_id);
                if($tests[$instrumentId]->price > $discount->price)
                    $tests[$instrumentId]->price = $discount->price;
            }
        }

        // unset second price, we don't want the user to be able to see this
        unset($tests[$instrumentId]->second_price);

        $response = MzrRestResponse::get(array(), $tests);
        return Response::json($response);
    }
    /**
     * Get tests with discounts
     *
     * @return json response
     */
    public function getTestsWithDiscounts()
    {
        $response = MzrRestResponse::get(array(), Test::assocTests(true));
        return Response::json($response);
    }
    /**
     * Upadate test
     *
     * @return json response
     */
    public function updateTest($id, $node = null)
    {
        $request = Request::instance();
        $content = json_decode($request->getContent());
        $changed = array();

        try
        {
            $test = Test::find($id);
            switch($node) {
                case 'discount':
                    if(isset($content->data->id)) {
                        $discountCode = DiscountCode::findOrFail($content->data->id);
                    } else {
                        $discountCode = new DiscountCode;
                    }

                    foreach($content->data as $field => $value) {
                        if(!empty($value)) $discountCode->$field = $value;
                    }
                    $discountCode->save();
                    break;

                default:
                    foreach($content->data as $key => $value) {
                        $test->$key = $value;
                        $changed[] = $key;
                    }
                    $test->save();
            }
        }
        catch (Exception $e)
        {
            Log::warning($e->getMessage() . '/n' . $e->getTraceAsString());
            return Response::make('Tallennus epäonnistui! ' . $e->getMessage(), '400');
        }

        $response = MzrRestResponse::get(array(), Test::assocTests(true));
        return Response::json($response);
    }
    /**
     * Claim discount code
     *
     * @return json $response
     */
    public function claimDiscountCode()
    {
        // Validate input
        $rules = array(
            'test_id' => 'required|integer',
            'code'  => 'required|min:5'
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) {
            return Response::make('Bad request!', '400');
        }

        $user = Auth::user();

        try
        {
            $result = DiscountCode::claimCode(Input::get('test_id'), Input::get('code'), $user->id);
            if(!$result) {
                return Response::make('Not found', '404');
            }
            $response = MzrRestResponse::get(array(), $result->toArray());
            return Response::json($response);
        }
        catch (Exception $e)
        {
            Log::warning($e->getMessage() . '/n' . $e->getTraceAsString());
            return Response::make('System error', '500');
        }
    }
    /**
     * Redirect user to the test
     *
     * @return redirect
     */
    public function test()
    {
        // oi request
        // Required Parameters
        $config = Config::get('services.cut-e');
        $token = parseInputToken();
        $mazhrSession = MazhrSession::get($token);

        $user = User::find($mazhrSession->user_id);
        $returnUrl = Input::has('return_url') ? urldecode(Input::get('return_url')) : Request::server('HTTP_REFERER');
        $failUrl = Input::has('fail_url') ? urldecode(Input::get('fail_url')) : Request::server('HTTP_REFERER');
        Session::put('test_return_url', $returnUrl);

        //check if test is paid and everything else is ok
        try {
            $test = UserTest::findOrFail(Input::get('testid'));
            $lang = Input::get('lang', 17); //Finnish by default
            $test->language_id = (int) $lang;
            $test->save();
        }
        catch (Exception $e) {
            $mazhrSession->setValue('message', 'test_notfound');
            Log::warning($e->getMessage());
            return Redirect::to($returnUrl);
        }

        // test is not paid
        if($test->status != UserTest::TEST_PAID) {
            $mazhrSession->setValue('message', 'test_notpaid');
            Log::warning('User ' . $user->id . ' tried to do a test that was not paid ('.$test->id.').');
            return Redirect::to($returnUrl);
        }

        $params = array(
            "requesttype" => "oi",
            "clientid" => $config["clientId"],
            "projectid" => $config["projectId"],
            "candidateid" => User::candidateId($user->id),
            "firstname" => $user->first,
            "lastname" => $user->last,
            "instrumentid" => Input::get('instrument'), //config
            "languageid" => $lang,
            "returnurl" => $returnUrl,
            "securecode" => $config["secureCode"] //config
        );

        $client = new SoapClient("http://www.cut-e.net/maptq/ws/ws.asmx?WSDL"); // config
        $survey = $client->__call("runWSxml", array($params));
        $response = simplexml_load_string($survey->runWSxmlResult->any);
        if(isset($response->error))
        {
            $error = (string) $response->error;
            $errorMessage = 'generic_error';
            if(strpos($error, 'lang non-existent') || strpos($error, 'instr/lang combination does not match')) $errorMessage = 'test_language_not_found';
            $mazhrSession->setValue('message', $errorMessage);
            Log::warning($error);
            return Redirect::to($failUrl);
        }
        $surveyUrl = (string) $response->result;
        return Redirect::to($surveyUrl);
    }

    /**
     * Save test results and redirect back to test page
     * DEPRECATED
     *
     * @return redirect
     */
    public function testResult($testid, $mazhrToken = null) {

        $data = Input::get();
        $token = parseInputToken($mazhrToken);
        $mazhrSession = MazhrSession::get($token);

        $user = User::find($mazhrSession->user_id);
        $client = new SoapClient("http://www.cut-e.net/maptq/ws/ws.asmx?WSDL");
        $config = Config::get("services.cut-e");
        $returnUrl = Session::get('test_return_url');
        // Check if the user has this test
        try{
            $test = UserTest::where('id', '=', $testid)->where('user_id', '=', $user->id)->firstOrFail();
        }
        catch (Exception $e) {
            $mazhrSession->setValue('message', 'test_notfound');
            Log::warning($e->getMessage());
            return Redirect::to($returnUrl);
        }
        // no score in result -> test must be cancelled
        if(empty(Input::get('score', ''))) {
            $this->resetCuteTest($test->id);
            $mazhrSession->setValue('message', 'test_cancel');
            Log::info("Test {$test->id} cancelled!");
            return Redirect::to($returnUrl);
        }

        $getScoresXmlParams = array(
            "clientid" => $config["clientId"],
            "projectid" => $config["projectId"],
            "candidateid" => $data["cand"],
            "instrumentid" => $data["instr"],
            "normsetid" => "1000",
            "encodedscore" => urlencode(Input::get("score")),
            "securecode" => $config["secureCode"],
        );

        // shape test scores create the matches
        if($data["instr"] == '102') {
            $profiles = Profile::all();
            try
            {
                DB::beginTransaction();
                foreach($profiles as $profile)
                {

                    $getScoresXmlParams["jobid"] = $profile->code;
                    $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                    $score = simplexml_load_string($scoreCall->getScoresXmlResult->any);

                    if(!isset($score->error))
                    {
                        // uniquenes to create table would be in order
                        UserProfile::where('user_id', '=', $user->id)->where('profile', '=', $profile->code)->delete();

                        $prof = new UserProfile();
                        $prof->user_id = $user->id;
                        $prof->profile = $profile->code;
                        $prof->score = $score->result->{'risk-index'};
                        $prof->save();
                    }
                    else
                    {

                        Log::warning('User <'. $user->id .'> profile score failed! ' . $profile->code . ': ' . (string) $score->error);
                    }
                }
                DB::commit();
            }
            catch (Exception $e)
            {
                DB::rollback();
                Log::warning($e->getMessage());
                $mazhrSession->setValue('message', 'test_savefail');
                return Redirect::to($returnUrl);
            }
        }

        try {

            $scoreUrl = '';
            $score = '';
            $rep = array(
                '102' => '102009',
                '201' => '201006',
                '302' => '302002',
                '301' => '301002',
                '357' => '357002',
                '315' => '315002'
            );

            // Required parameters: RequestType, ClientId, ProjectId, CandidateId, InstrumentId, LaguageId, NormsetId, ReportId, FirstName, LastName, GenderId, SecureCode
            $runWSxmlParams = array(
                "clientid" => $config["clientId"],
                "projectid" => $config["projectId"],
                "candidateid" => $data["cand"],
                "instrumentid" => $data["instr"],
                "languageid" => $test->language_id,
                "normsetid" => "1000",
                "firstname" => $user->first,
                "lastname" => $user->last,
                "genderid" => "1",
                "securecode" => $config["secureCode"]
            );

            // if report id is mapped, get PDF score url
            if(isset($rep[$data['instr']]))
            {
                $runWSxmlParams["reportid"] = $rep[$data['instr']];
                // get url to pdf
                $runWSxmlParams['requesttype'] = 'rep';
                $pdfScoreCall = $client->__call('runWSxml', array($runWSxmlParams));
                $pdfScore = simplexml_load_string($pdfScoreCall->runWSxmlResult->any);
                $scoreUrl = (string) $pdfScore->result;
            }

            //test specific params
            switch($data["instr"])
            {
                case '102':
                case '201':
                    // get html result
                    $runWSxmlParams['requesttype'] = 'reph';
                    $htmlScoreCall = $client->__call('runWSxml', array($runWSxmlParams));
                    $htmlScore = simplexml_load_string($htmlScoreCall->runWSxmlResult->any);
                    $htmlScoreUrl = (string) $htmlScore->result;
                    Log::info('User' . $user->id . ": " . $htmlScoreUrl);
                    $html = file_get_contents($htmlScoreUrl);
                    $score = $this->parseScore($html, $data["instr"]);
                    break;
                case '315':
                    // get xml result
                    $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                    $results = simplexml_load_string($scoreCall->getScoresXmlResult->any);
                    $score = $results->result->overall_Perc;
                    break;
                default:
                    // get xml result
                    $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                    $results = simplexml_load_string($scoreCall->getScoresXmlResult->any);
                    $score = $results->result->performance_Perc;
            }

            $test->score_url = $scoreUrl;
            $test->score_key = urlencode($data["score"]);
            $test->score = $score;
            $test->save();

            $message = $data["instr"] == '102' ? 'test_done_primary' : 'test_done';
            $mazhrSession->setValue('message', $message);
        }
        catch(Exception $e)
        {
            Log::warning('Test results could not be saved for user <'. $user->id .'>. ' . $e->getMessage());
            $mazhrSession->setValue('message', 'test_savefail');
        }
        return Redirect::to($returnUrl);
    }
   /**
     * Save test results
     *
     * @return json
     */
    public function ajaxTestResult() {

        $data = Input::get();
        $client = new SoapClient("http://www.cut-e.net/maptq/ws/ws.asmx?WSDL");
        $config = Config::get("services.cut-e");

        $token = parseInputToken();
        $mazhrSession = MazhrSession::get($token);
        $user = User::find($mazhrSession->user_id);
        $message = "";

        try{
            $test = UserTest::where('instrument_id', '=', $data['instr'])->where('user_id', '=', $user->id)->where('status', '=', UserTest::TEST_PAID)->firstOrFail();
        }
        catch (Exception $e) {
            $mazhrSession->setValue('message', 'test_notfound');
            Log::warning($e->getMessage());
            return Response::make('test_notfound', '400');
        }
        // no score in result -> test must be cancelled
        if(empty(Input::get('score', ''))) {
            $mazhrSession->setValue('message', 'test_cancel');
            $this->resetCuteTest($test->id);
            Log::info("Test {$test->id} cancelled!");
            return Response::make('test_cancel', '500');
        }

        $getScoresXmlParams = array(
            "clientid" => $config["clientId"],
            "projectid" => $config["projectId"],
            "candidateid" => $data["cand"],
            "instrumentid" => $data["instr"],
            "normsetid" => "1000",
            "encodedscore" => urlencode(Input::get("score")),
            "securecode" => $config["secureCode"],
        );

        // shape test scores create the matches
        if($data["instr"] == '102') {
            $profiles = Profile::all();
            try
            {
                DB::beginTransaction();
                foreach($profiles as $profile)
                {

                    $getScoresXmlParams["jobid"] = $profile->code;
                    $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                    $score = simplexml_load_string($scoreCall->getScoresXmlResult->any);

                    if(!isset($score->error))
                    {
                        // uniquenes to create table would be in order
                        UserProfile::where('user_id', '=', $user->id)->where('profile', '=', $profile->code)->delete();

                        $prof = new UserProfile();
                        $prof->user_id = $user->id;
                        $prof->profile = $profile->code;
                        $prof->score = $score->result->{'risk-index'};
                        $prof->save();
                    }
                    else
                    {

                        Log::warning('User <'. $user->id .'> profile score failed! ' . $profile->code . ': ' . (string) $score->error);
                    }
                }
                DB::commit();
            }
            catch (Exception $e)
            {
                DB::rollback();
                Log::warning($e->getMessage());
                $mazhrSession->setValue('message', 'test_savefail');
                return Response::make('test_savefail', '500');
            }
        }

        try {

            $scoreUrl = '';
            $score = '';
            $rep = array(
                '102' => '102009',
                '201' => '201006',
                '302' => '302002',
                '301' => '301002',
                '357' => '357002',
                '315' => '315002'
            );

            // Required parameters: RequestType, ClientId, ProjectId, CandidateId, InstrumentId, LaguageId, NormsetId, ReportId, FirstName, LastName, GenderId, SecureCode
            $runWSxmlParams = array(
                "clientid" => $config["clientId"],
                "projectid" => $config["projectId"],
                "candidateid" => $data["cand"],
                "instrumentid" => $data["instr"],
                "languageid" => $test->language_id,
                "normsetid" => "1000",
                "firstname" => $user->first,
                "lastname" => $user->last,
                "genderid" => "1",
                "securecode" => $config["secureCode"]
            );

            // if report id is mapped, get PDF score url
            if(isset($rep[$data['instr']]))
            {
                $runWSxmlParams["reportid"] = $rep[$data['instr']];
                // get url to pdf
                $runWSxmlParams['requesttype'] = 'rep';
                $pdfScoreCall = $client->__call('runWSxml', array($runWSxmlParams));
                $pdfScore = simplexml_load_string($pdfScoreCall->runWSxmlResult->any);
                $scoreUrl = (string) $pdfScore->result;
            }

            //test specific params
            switch($data["instr"])
            {
                case '102':
                case '201':
                    // get html result
                    $runWSxmlParams['requesttype'] = 'reph';
                    $htmlScoreCall = $client->__call('runWSxml', array($runWSxmlParams));
                    $htmlScore = simplexml_load_string($htmlScoreCall->runWSxmlResult->any);
                    $htmlScoreUrl = (string) $htmlScore->result;
                    Log::info('User' . $user->id . ": " . $htmlScoreUrl);
                    $html = file_get_contents($htmlScoreUrl);
                    $score = $this->parseScore($html, $data["instr"]);
                    break;
                case '315':
                    // get xml result
                    $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                    $results = simplexml_load_string($scoreCall->getScoresXmlResult->any);
                    $score = $results->result->overall_Perc;
                    break;
                default:
                    // get xml result
                    $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                    $results = simplexml_load_string($scoreCall->getScoresXmlResult->any);
                    $score = $results->result->performance_Perc;
            }

            $test->score_url = $scoreUrl;
            $test->score_key = urlencode($data["score"]);
            $test->score = $score;
            $test->save();

            $message = $data["instr"] == '102' ? 'test_done_primary' : 'test_done';
            $mazhrSession->setValue('message', $message);
        }
        catch(Exception $e)
        {
            Log::warning('Test results could not be saved for user <'. $user->id .'>. ' . $e->getMessage());
            $mazhrSession->setValue('message', 'test_savefail');
            return Response::make('test_savefail', '500');
        }
        $response = MzrRestResponse::get(array(), array("status" => "ok"));
        return Response::json($response);
    }
    /**
     * Reset specific test
     *
     * @return json response
     */
    public function resetTest($userTestId)
    {
        try
        {
            $user = Auth::user();
            $test = UserTest::findOrFail($userTestId);
            if($test->user_id == $user->id)
            {
                if($test->status == UserTest::TEST_UNFINISHED_PAYMENT)
                {
                    $test->status = UserTest::TEST_PAYMENT_RESETED;
                    $test->save();
                }
                else
                {
                    $this->resetCuteTest($userTestId);
                }
            }
            $testData = Test::getNumberOfTests($user->id);
            return Response::json(MzrRestResponse::get($testData, UserTest::activeTests($test->user_id)));
        }
        catch (Exception $e)
        {
            Log::warning('Test reset failed for user ('. $user->id .'). ' . $e->getMessage());
            return Response::make('Bad request!', '400');
        }
    }

    /**
     * Parse interesting parts from CUT-E html score
     *
     * @param string $html
     * @param int $instrument
     *
     * @return string html
     */
    private function parseScore($html, $instrument)
    {
        $html = substr($html, strpos($html, 'tdRulbaseText'));
        $html = substr($html, strpos($html, '<br>'));
        $html = strip_tags(substr($html, 0, strpos($html, '<br><br>')));
        $html = trim($html);
        return $html;
    }

    /**
     * Reset test
     *
     * @param integer $userTestId
     *
     * @return object $resetCall
     */
    private function resetCuteTest($userTestId)
    {
        $client = new SoapClient("http://www.cut-e.net/maptq/ws/wsmaintenance.asmx?WSDL");
        $config = Config::get("services.cut-e");
        $test = UserTest::findOrFail($userTestId);

        // If user has completed the test and want's to do it again, they will have to pay
        // for the new test
        if(!empty($test->score)) {
            $test->status = UserTest::TEST_RESETED;
            $test->save();
        }

        // Required parameters: ClientID, ProjectID, InstrumentID, CandidateID, SecureCode
        $params = array(
            "reqobj" => array(
                "ClientId" => $config["clientId"],
                "ProjectId" => $config["projectId"],
                "CandidateId" => User::candidateId($test->user_id),
                "InstrumentId" => $test->instrument_id,
                "SecureCode" => $config["secureCode"]
            )
        );

        return $resetCall = $client->__call("ResetInstrument", array($params));
    }
}
