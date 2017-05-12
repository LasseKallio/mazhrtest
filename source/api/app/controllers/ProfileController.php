<?php

class ProfileController extends BaseController {

  /*
  |--------------------------------------------------------------------------
  | Profile Controller
  |--------------------------------------------------------------------------
  |
  */

  /**
   * Search for MATCHES (tm)
   *
   * @return json response
   */
  public function getMatchingProfiles()
  {
    try
    {
      $user = Auth::user();
      $matchingProfiles = UserProfile::getMatchingProfiles($user->id);
          $response = MzrRestResponse::get(array(), $matchingProfiles);
      return Response::json($response);

    }
    catch (Exception $e)
    {
      Log::warning($e->getMessage() . "\n\n" . $e->getTraceAsString());
      return Response::make('Matching profile fail', '500');
    }
  }
  /**
   * Get all profiles for admin
   *
   * @return json
   */
  public function getAllProfiles()
  {
    return Response::json(MzrRestResponse::get(array(), Profile::with('professionCodes')->get()));
  }

  /**
   * Get all profiles
   *
   * @return json
   */
  public function getProfiles()
  {
    return Response::json(MzrRestResponse::get(array(), Profile::with('professionCodes')->get()));
  }
  /**
   * Get all profiles
   *
   * @return json
   */
  public function saveProfile()
  {
    if(!Input::has('id')) Response::make('Bad request!', '400');
    $id = (int) Input::get('id');
    $competence = Input::get('competence', '');
    $model = Input::get('model', '');

    try
    {
      $profile = Profile::findOrFail($id);
      $profile->competence = $competence;
      $profile->model = $model;
      $profile->save();
      return Response::json(MzrRestResponse::get(array(), Profile::with('professionCodes')->get()));
    }
    catch (Exception $e)
    {
      Log::warning($e->getMessage());
      return Response::make('System Error', '500');
    }
  }
  /**
   * Add profession code
   *
   * @return json
   */
  public function addProfessionCode($profileId = null, $code = null)
  {

    $validator = Validator::make(
        array(
          'profileId' => $profileId,
          'code' => $code
        ),
        array(
          'profileId' => 'required|integer',
          'profileId' => 'required|integer'
        )
    );

    if ($validator->fails()) return Response::make('Bad request!', '400');

    try
    {
      $professionCode = New ProfileProfessionCode();
      $professionCode->profile_id = $profileId;
      $professionCode->code = $code;
      $professionCode->save();
      return Response::json(MzrRestResponse::get(array(), Profile::with('professionCodes')->get()));
    }
    catch (Exception $e)
    {
      Log::warning('Removing profession code failed:' . $e->getMessage());
    }
  }
  /**
   * Remove profession code
   *
   * @return json
   */
  public function removeProfessionCode($id = null)
  {
    $validator = Validator::make(
        array('id' => $id),
        array('id' => 'required|integer')
    );

    if ($validator->fails()) return Response::make('Bad request!', '400');

    try
    {
      ProfileProfessionCode::destroy($id);
      return Response::make('Done', '200');
    }
    catch (Exception $e)
    {
      Log::warning('Removing profession code failed:' . $e->getMessage());
      return Response::make($e->getMessage(), '404');
    }
  }
}
