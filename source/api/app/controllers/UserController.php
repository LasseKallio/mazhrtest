<?php

class UserController extends BaseController {

    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | User controller for user related activities
    |
    */

    /**
     * Data of the currently logged in user
     *
     * @return json response
     */
    public function me()
    {
        $tmpToken = getTmpToken();
        $userData = Auth::user()->getFullProfileAsArray();

        // unset values we do not want the API to send to client
        unset($userData['linkedin_data']);

        $testData = Test::getNumberOfTests(Auth::user()->id);
        $metadata = array_merge(array('tmpToken' => $tmpToken), $testData);
        $response = MzrRestResponse::get($metadata, $userData);
        return Response::json($response);
    }

    /**
     * Data of the user by given id
     *
     * @param int $id User id
     * @return json response
     */
    public function user($id)
    {
        try
        {
            $user = User::findOrFail($id);
            $userData = $user->getFullProfileAsArray();
            $userData['payment_history'] = PaymentLog::where('user_id', '=', $user->id)->get();
            $response = MzrRestResponse::get(array(), $userData);
            return Response::json($response);
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage);
            return Response::make('Bad request!', '400');
        }
    }

    /**
     * Data of the user by given id
     *
     * @param string $profileToken
     * @return json response
     */
    public function userProfile($profileToken)
    {
        try
        {
            $privacy = UserPrivacy::where('profile_token', '=', $profileToken)->firstOrFail();
            if($privacy->public_profile == false)
                return Response::make('Bad request!', '400');
            $user = User::find($privacy->user_id);
            $userData = $user->getFullProfileAsArray();
            $publicData = array();
            $defaultMap = array('first', 'last');
            $privacyMap = explode(',', $privacy->show);
            $map = array_merge($defaultMap, $privacyMap);
            foreach($map as $itemName) {
                if(isset($userData[$itemName])) $publicData[$itemName] = $userData[$itemName];
                if($itemName == 'extras') $publicData['education_level'] = $userData['education_level'];
            }
            $response = MzrRestResponse::get(array(), $publicData);
            return Response::json($response);
        }
        catch (Exception $e)
        {
            Log::info($e->getMessage());
            return Response::make('Bad request!', '400');
        }
    }

    /**
     * Login with username and password
     *
     * @return json response
     */
    public function login()
    {
        $email = Input::get('email', '');
        $password = Input::get('password', '');
        $remember_me = Input::get('remember_me', false);

        if (Auth::attempt(array('email' => $email, 'password' => $password), $remember_me)) {
            $user = Auth::user();
            $token = JWTAuth::fromUser($user);
            $tmpToken = getTmpToken();
            $testData = Test::getNumberOfTests($user->id);
            $metadata = array_merge(array('status' => 'ok', 'mazhr_token' => $token, 'tmpToken' => $tmpToken), $testData);
            $response = MzrRestResponse::get($metadata, Auth::user()->getFullProfileAsArray());
            Log::info('User <' . $email . '> has logged in successfully.');
            return Response::json($response);
        } else {
            Log::warning('Authentication failed for user <' . $email . '>.');
            return Response::make('Bad request!', '400');
        }
    }

    /**
     * Logout
     *
     * @return json response
     */
    public function logout()
    {
        if(Auth::check()) {
            $user = Auth::user();
            Auth::logout();
            Log::info('User <' . $user->email . '> has logged out.');
        }
        Session::forget('linkedIn');
        return Response::json(array('status' => 'ok'));
    }

    /**
     * Send new password
     *
     * @return Response
     */
    public function newPassword()
    {
        if(!Input::has('email'))
            return Response::make('Bad request!', '400');

        try
        {
            DB::beginTransaction();
            $user = User::where('email', '=', Input::get('email'))->first();
            if(empty($user)) {
               return Response::json(array('status' => 'ok'));
            }
            $newPass = str_random(8);
            $user->password = Hash::make($newPass);
            $user->save();

            $mailData = array('user' => $user, 'password' => $newPass);
            Mail::send('password', $mailData, function($message) use ($mailData){
                $message->from('info@mazhr.com', 'Mazhr');
                $message->to($mailData['user']->email, $mailData['user']->first . ' ' . $mailData['user']->last)->subject('Uusi salasanasi Mazhr.com -palveluun.');
            });

            DB::commit();
            return Response::json(array('status' => 'ok'));
        }
        catch (Exception $e)
        {
            DB::rollback();
            Log::info($e->getMessage());
            return Response::make('Bad request!', '400');
        }
    }

    /**
     * Change the password
     *
     * @return Response
     */
    public function changePassword()
    {
        if(!Input::has('password'))
            return Response::make('Bad request!', '400');

        try
        {
            $user = Auth::user();
            $user->password = Hash::make(Input::get('password'));
            $user->save();
            return Response::json(array('status' => 'ok'));
        }
        catch (Exception $e)
        {

            Log::info($e->getMessage());
            return Response::make('Bad request!', '400');
        }
    }

    /**
     * Change user profile image
     *
     * @return Response
     */
    public function changeImage()
    {

        $request = Request::instance();
        $content = $request->getContent();
        dd($content);

        $rules = array('image' => 'mimes:jpeg,png');

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails())
        {
            Log::info("Image failed validation.");
            return Response::make('Bad request!', '400');
        }

        try
        {
            $user = Auth::user();
            $user->password = Hash::make(Input::get('password'));
            $user->save();
            return Response::make('Ok',  '200');
        }
        catch (Exception $e)
        {

            Log::info($e->getMessage());
            return Response::make('Bad request!', '400');
        }
    }


    /**
     * Register user to the service and login if everything went well
     *
     * @return json
     */
    public function register() {

  Log::info(Input::all());

        $headers = getallheaders();
  Log::info($headers);

        if(!isset($headers['MazhrSession']))
            return Response::make('Unauthorized', '400');

        $tkey = $headers['MazhrSession'];
        $session = MazhrSession::get($tkey);
        $sessionValues = $session->valueObject();

        if(empty($session))
            return Response::make('Unauthorized', '400');

        // logging in with linkedin
        if(isset($session->user_id) && isset($sessionValues->linkedinId))
        {
            $user = User::find($session->user_id);
            if($user->linkedin_id == $sessionValues->linkedinId)
            {
                $session->delete();
                $token = JWTAuth::fromUser($user);
                return Response::json(MzrRestResponse::get(array('mazhr_token' => $token), $user->getFullProfileAsArray()));
            }
        }

        if(empty(Input::all())) {
            $response = array();
            if(isset($sessionValues->linkedin)) {
                $response['session'] = 'true';
                $response['linkedinData'] = $sessionValues->linkedin;
            }
            return Response::json(MzrRestResponse::get($response, array()));

        }
        else
        {

            $rules = array(
                'first'             => 'required|min:1',
                'last'              => 'required|min:2',
                'email'             => 'required|email',
                'education_level'   => 'required|integer',
            );

            $hasSession = isset($sessionValues->linkedin);
            $sessionData = null;

            if($hasSession)
            {
                $sessionData = $sessionValues->linkedin;
            }
            else
            {
                $rules['pass1'] = 'required|min:6';
                $rules['pass2'] = 'required|min:6';
            }

            $validator = Validator::make(Input::all(), $rules);

            if( (Input::get('pass1') != Input::get('pass2')) && !$hasSession ) {
                return Response::make('passwords don´t match', '400');
            } else if ($validator->fails()) {
                return Response::make($validator->messages(), '400');
            } else {

                try{
                    DB::beginTransaction();

                    // save basic user data
                    $user = new User();
                    $user->user_status = User::USER_DEFAULT;
                    $user->first = $hasSession ? $sessionData->firstName : Input::get('first');
                    $user->last = $hasSession ? $sessionData->lastName : Input::get('last');
                    $user->email = $hasSession ? $sessionData->emailAddress : Input::get('email');
                    if($hasSession) $user->linkedin_id = $sessionData->id;
                    $user->linkedin_data = json_encode($sessionData);
                    if(!$hasSession) $user->password = Hash::make(Input::get('pass1'));
                    $user->activity_status = 1;
                    $user->education_level = Input::get('education_level');

                    if(User::where('email', '=', $user->email)->count())
                    {
                        DB::rollback();
                        Log::warning('Error in registration: existing email' . $user->email);
                        return Response::json(array('error' => 'email_in_use'), 500);
                    }

                    $user->save();

                    // User exra
                    $data = new stdClass();
                    if(!empty($user->email))
                    {
                        $extraEmail = new UserExtra(array("user_id" => $user->id, "type" => "1", "key" => "contact_email", "value" => $user->email, "keywords" => ""));
                        $extraEmail->save();
                    }
                    if($hasSession && isset($sessionData->phoneNumbers->values[0]))
                    {
                        $extraPhone = new UserExtra(array("user_id" => $user->id, "type" => "1", "key" => "contact_phone", "value" => $sessionData->phoneNumbers->values[0]->phoneNumber, "keywords" => ""));
                        $extraPhone->save();
                    }

                    // save work history from Linkedid
                    if($hasSession){
                        if($sessionData->positions->_total != 0) {
                            $this->populateLnWorkHistory($user->id, $sessionData->positions->values);
                        }
                        //user image
                        if(isset($sessionData->pictureUrls->values[0])) {
                            $url = $sessionData->pictureUrls->values[0];
                            $imgName = User::imageName('.jpg');
                            $img = public_path() . '/uploads/' . $imgName;
                            file_put_contents($img, file_get_contents($url));
                            $user->image = $imgName;
                            $user->save();
                        }
                    }


                    // Send email to user
                    $mailData = array('user' => $user);
                    Mail::send('hello', $mailData, function($message) use ($mailData){
                        $message->from('info@mazhr.com', 'Mazhr');
                        $message->to($mailData['user']->email, $mailData['user']->first . ' ' . $mailData['user']->last)->subject('Uusi palvelu työnhakuun ja urasuunnitteluun!');
                    });

                   DB::commit();

                    Log::info('User <'. $user->email .'> registered');
                    $session->delete();
                    $token = JWTAuth::fromUser($user);
                    return Response::json(MzrRestResponse::get(array('mazhr_token' => $token), $user->getFullProfileAsArray()));

                } catch (Exception $e) {
                    DB::rollback();
                    Log::warning('User <'. Input::get('email') .'> registration failed: ' . $e->getMessage() . '\n\n' . $e->getTraceAsString());
                    return Response::make('register failed', '500');
                }
            }
        }
    }

    /**
     * Linkedin
     *
     * @return json response
     */
    public function linkedin()
    {
        $returnUrl = '';

        if(!Input::has('mazhr_token')) return Response::make('Bad request', '400');
        $tkey = Input::get('mazhr_token');
        $session = MazhrSession::get($tkey);
        if(empty($session)) return Response::make('Unauthorized', '401');
        $sessionValues = $session->valueObject();

        $lnConfig = Config::get('social.linkedin');
        $lnConfig['redirectUri'] .= '?mazhr_token=' . $tkey;
        $provider = new LinkedIn($lnConfig);

        if (!Input::has('code'))
        {

            // If we don't have an authorization code, get one
            $url = $provider->getAuthorizationUrl();
            $newValue = $session->valueObject();
            // check for return urls and save them to session if found. Else use referer
            $newValue->ok_url = Input::has('ok_url') ? urldecode(Input::get('ok_url')) : Request::server('HTTP_REFERER');
            $newValue->fail_url = Input::has('fail_url') ? urldecode(Input::get('fail_url')) : Request::server('HTTP_REFERER');
            $session->saveValueObject($newValue);
            return Redirect::to($url);
        }
        else
        {
            try
            {
                // Try to get an access token (using the authorization code grant)
                $t = $provider->getAccessToken('authorizationCode', array('code' => Input::get('code')));

                try
                {
                    // We got an access token, let's now get the user's details
                    $userDetails = $provider->getUserDetails($t);
                    $resource = '/v1/people/~:(id,first-name,last-name,email-address,industry,positions,picture-urls::(original),languages,skills,following,date-of-birth,phone-numbers)';
                    $params = array('oauth2_access_token' => $t->accessToken, 'format' => 'json');
                    $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params);
                    $context = stream_context_create(array('http' => array('method' => 'GET')));
                    $response = file_get_contents($url, false, $context);
                    $linkedIn = json_decode($response);
                    $returnUrl = $sessionValues->ok_url;

                    // User is logged in
                    if(!empty($session->user_id)){
                        $user = User::find($session->user_id);
                        $user->linkedin_data = $response;
                        if(empty($user->linkedin_id))
                        {
                            if(User::where('linkedin_id', '=', $linkedIn->id)->count())
                            {
                                $session->setValue('message', 'linkedin_already_linked');
                                return Redirect::to($sessionValues->fail_url);
                            }
                            $user->linkedin_id = $linkedIn->id;
                        }
                        $user->save();

                        if($linkedIn->positions->_total != 0) {
                            $this->populateLnWorkHistory($user->id, $linkedIn->positions->values);
                        }


                        //user image
                        if(empty($user->image) && isset($linkedIn->pictureUrls->values[0])) {
                            $url = $linkedIn->pictureUrls->values[0];
                            $imgName = User::imageName('.jpg');
                            $img = public_path() . '/uploads/' . $imgName;
                            file_put_contents($img, file_get_contents($url));
                            $user->image = $imgName;
                            $user->save();
                        }


                    } else {

                        // check if has registered with linkedin already
                        $registeredWithLinkedIn = DB::table('users')->where('linkedin_id', '=', $linkedIn->id)->first();
                        if($registeredWithLinkedIn) {
                            $session->user_id = $registeredWithLinkedIn->id;
                            $session->save();
                            $session->setValue('linkedinId', $linkedIn->id);
                        }
                        else
                        {

                            $sameMail = DB::table('users')->where('email', '=', $linkedIn->emailAddress)->get();
                            // New user -> back to register form with linkedin data
                            if(count($sameMail) == 0)
                            {
                                $session->setValue('linkedin', $linkedIn);
                            }
                            // Registered with same email address -> back with message
                            else if(count($sameMail) == 1)
                            {
                                $session->setValue('message', 'linkedin_samemail');
                                $returnUrl = $sessionValues->fail_url;
                            }
                        }
                    }

                } catch (Exception $e) {
                    Log::warning('Unable to get user details from LinkedIn: ' . $e->getMessage() . '\n\n'. $e->getTraceAsString());
                    $session->setValue('message', 'linkedin_detailsfail');
                }

            } catch (Exception $e) {
                Log::warning('Unable to get access token: ' . $e->getMessage());
                $session->setValue('message', 'linkedin_accesstokenfail');
            }
        }
        return Redirect::to($returnUrl);
    }

    /**
     * Get all users
     *
     * @return json
     */
    public function getUsers()
    {
        $users = User::all();
        $usersWithExtra = array();
        foreach($users as $user)
        {
            $user->candidate_id = User::candidateId($user->id);
            $usersWithExtra[] = $user;
        }
        return Response::json(MzrRestResponse::get(array(), $usersWithExtra));
    }

    /**
     * Get all users for admin with full details
     *
     * @return json
     */
    public function getAllUsers()
    {
        $users = User::all();
        $usersWithExtra = array();
        foreach($users as $user)
        {
            $user->candidate_id = User::candidateId($user->id);
            $userData = $user->getFullProfileAsArray();
            $userData['payment_history'] = PaymentLog::where('user_id', '=', $user->id)->get();
            $usersWithExtra[] = $userData;
        }
        return Response::json(MzrRestResponse::get(array(), $usersWithExtra));
    }

    /**
     * Update users data
     *
     * @return json
     */
    public function updateUser($node = null)
    {

        $request = Request::instance();
        $content = json_decode($request->getContent());
        $notAllowed = array('id', 'password', 'remember_token', 'linkedin_id', 'user_staus');
        $changed = array();

        try
        {
            // who are we updating here
            $user = Auth::user();
            if(isset($content->data->user_id) && $user->user_status == User::USER_ADMIN)
            {
                $user = User::find($content->data->user_id);
            }


            // update user table
            if(empty($node))
            {
                // user image
                if($request->files->has('image'))
                {
                    $rules = array('image' => 'mimes:jpeg,png');
                    $image = $request->files->get('image');

                    $validator = Validator::make(array("image" => $image), $rules);

                    if ($validator->fails())
                    {
                        Log::info("Image failed validation.");
                        return Response::make('Bad request!', '400');
                    }

                    $originalName = $image->getClientOriginalName();
                    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                    $name = User::imageName($ext);
                    $image->move('uploads', $name);
                    $newImage = Image::make('uploads/' . $name)->fit(512)->save();
                    $user->image = $name;

                }
                // basic user data
                else
                {
                    foreach($content->data as $key => $value) {
                        if(!in_array($key, $notAllowed) && isset($user->$key)) {
                            if(isset($user->$key))
                            {
                                $user->$key = $value;
                                $changed[] = $key;
                            }
                        }
                    }
                }

                $user->save();
                $response = MzrRestResponse::get(array('changed' => $changed), $user->getFullProfileAsArray());
                return Response::json($response);
            }
            // update nodes
            else
            {
                switch($node) {
                    case 'filters':
                        $changed = UserFilter::saveOrUpdate($user, $content->data);
                        $response = MzrRestResponse::get(array('changed' => $changed), $user->assocFilters());
                        return Response::json($response);

                    case 'workhistory':
                        UserWorkHistory::saveOrUpdate($user, $content->data);
                        $response = MzrRestResponse::get(array(), $user->workhistory);
                        return Response::json($response);

                    case 'skills':
                        $changed = UserSkill::saveOrUpdate($user, $content->data);
                        $response = MzrRestResponse::get(array('changed' => $changed), $user->assocSkills());
                        return Response::json($response);

                    case 'education':
                        UserEducation::saveOrUpdate($user, $content->data);
                        $response = MzrRestResponse::get(array(), $user->education);
                        return Response::json($response);

                    case 'extras':
                        $changed = UserExtra::saveOrUpdate($user, $content->data);
                        $response = MzrRestResponse::get(array('changed' => $changed), $user->assocExtras());
                        return Response::json($response);

                    case 'remove':
                        $time = time();
                        $user->email = $time .'_'. $user->email;
                        if($user->linkedin_id != null)
                        {
                            $user->linkedin_id = $time .'_'. $user->linkedin_id;
                        }
                        $user->user_status = User::USER_DELETED;
                        $user->save();
                        return Response::json(array('status' => 'ok'));

                    case 'privacy':
                        $privacy = UserPrivacy::Where('user_id', '=', $user->id)->first();
                        if(isset($content->data->public_profile)) $privacy->public_profile = $content->data->public_profile;
                        if(isset($content->data->show)) $privacy->show = $content->data->show;
                        $privacy->save();
                        $response = MzrRestResponse::get(array(), $privacy->toArray());
                        return Response::json($response);

                    default:
                        return Response::make('Bad request!', '400');
                }
            }
        }
        catch (Exception $e)
        {
            Log::warning($e->getTraceAsString());
            $message = App::environment() == 'live' ? 'Bad request!' : $e->getMessage();
            return Response::make($message, '400');
        }
    }

    /**
     * Delete data from node
     *
     * @return json
     */
    public function deleteFromNode($node = null, $id = null)
    {
        if(empty($node) || empty($id)) return Response::make('Missing input!', '400');

        try
        {
            $user = Auth::user();
            switch($node) {
                case 'workhistory':
                    if(UserWorkHistory::where('user_id', '=', $user->id)->where('id', '=', $id)->count())
                    {
                        UserWorkHistory::destroy($id);
                    }
                    $response = MzrRestResponse::get(array(), $user->workhistory);
                    return Response::json($response);

                case 'education':
                    if(UserEducation::where('user_id', '=', $user->id)->where('id', '=', $id)->count())
                    {
                        UserEducation::destroy($id);
                    }
                    $response = MzrRestResponse::get(array(), $user->education);
                    return Response::json($response);

                case 'skills':
                    if(UserSkill::where('user_id', '=', $user->id)->where('id', '=', $id)->count())
                    {
                        UserSkill::destroy($id);
                    }
                    $response = MzrRestResponse::get(array(), $user->assocSkills());
                    return Response::json($response);

                case 'extras':
                    if(UserExtra::where('user_id', '=', $user->id)->where('id', '=', $id)->count())
                    {
                        UserExtra::destroy($id);
                    }
                    $response = MzrRestResponse::get(array(), $user->assocExtras());
                    return Response::json($response);

                default:
                    return Response::make('Wrong node!', '400');
            }
        }
        catch (Exception $e)
        {
            Log::warning($e->getTraceAsString());
            $message = App::environment() == 'live' ? 'Bad request!' : $e->getMessage();
            return Response::make($message, '400');
        }
    }

    /**
     * Add / Update positions from LinkedIn
     *
     * @param int $userId
     * @param array $positions
     *
     */
    private function populateLnWorkHistory($userId, $positions)
    {
        $current = DB::table('user_work_history')->where('user_id', '=', $userId)->whereNotNull('linkedin_id')->get();
        $oldieIds = array();

        foreach($current as $old)
        {
            $oldieIds[] = $old->linkedin_id;
        }

        foreach($positions as $position)
        {
            if(in_array($position->id, $oldieIds)) {
                $workHistory = UserWorkHistory::where('linkedin_id', '=', $position->id)->firstOrFail();
            }
            else
            {
                $workHistory = New UserWorkHistory();
                $workHistory->user_id = $userId;
                $workHistory->linkedin_id = $position->id;
            }
            if(isset($position->startDate->month)) $workHistory->start_month = $position->startDate->month;
            if(isset($position->startDate->year)) $workHistory->start_year = $position->startDate->year;
            if(isset($position->endDate->month)) $workHistory->end_month = $position->endDate->month;
            if(isset($position->endDate->month)) $workHistory->end_year = $position->endDate->year;

            $workHistory->title = $position->title;
            $workHistory->company = $position->company->name;
            $workHistory->current = $position->isCurrent;
            $workHistory->save();
        }
    }
}
