<?php

class AdController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Ad Controller
	|--------------------------------------------------------------------------
	|
	| This controller provides access to ads
	|
	*/

	/**
	 * Single ad
	 *
	 * @return json response
	 */
	public function getAd($id, $profileId = null)
	{
		try
		{
			$ad = Ad::findOrFail($id);
			$response = $ad->toJArray();
			// hack, to make the old code work
			$profileId = $profileId ? $profileId : $ad->profile_id; 
			if($profileId != null)
			{
				$profile = Profile::find($profileId);
				if(!empty($profile)) {
					$response['profile']['competences'] = explode(',', $profile->competence);
					$response['profile']['behavioral_models'] = explode(',', $profile->model);

					if(Auth::check()) {
						$score = null;
						$profileMatches = UserProfile::getMatchingProfiles(Auth::user()->id);
						foreach($profileMatches as $matchingProfile)
						{
							if($matchingProfile->code == $profile->code)
								$response['profile']['score'] = $matchingProfile->score;
						}
					}
				}
			}
			return Response::json(MzrRestResponse::get(array(), $response)); 
		}
		catch(Exception $e)
		{
			Log::warning($e->getMessage());
			return Response::make('not found', '404');
		}
	}

	/**
	 * Search for ads by profession and location and keywords
	 *
	 * @return json response
	 */
	public function getPossibilities()
	{
		$profession = '';
		$area = '';
		$keywords = '';
		if(Auth::check()) {
			$filters = Auth::user()->assocFilters();
			$profession = isset($filters['profession_code']) ? $filters['profession_code']['value'] : '';
			$area = isset($filters['area']) ? $filters['area']['value'] : '';
			$keywords = isset($filters['keywords']) ? $filters['keywords']['value'] : '';
		} else {
			$profession = Input::get('profession', '');
			$area = Input::get('area', '');
			$keywords = Input::get('keywords', '');
		}

		/* profession can be empty - returns ads from all professions
		if(empty($profession))
		{
			return Response::make('profession not found', '400');
		}
		*/

		$keywords = explode(',', $keywords);
		$area = explode(',', $area);	
		$possibilities = Ad::getByInterest($profession, $area, $keywords);
		$metadata = array('limited' => false);

		if(count($possibilities) > 200) {
			array_splice($possibilities, 200);
			$metadata['limited'] = true;
		}
		$metadata['count'] = count($possibilities);
		$metadata['ad_count'] = AdCount::getStatus();

        $response = MzrRestResponse::get($metadata, $possibilities);    
		return Response::json($response);
	}

	/**
	 * Ads by profile
	 *
	 * @return json response
	 */
	public function getAdsByProfile($profileId)
	{	
		$area = Input::get('area', '');
		$keywords = Input::get('keywords', '');
		try
		{
			$profile = Profile::findOrFail($profileId);
			$user = Auth::user();
			$filters = $user->assocFilters();
			$area = explode(',', $area);
			$keywords = explode(',', $keywords);
			$ads = Ad::getAdsByProfile($profile, $area, $keywords);
	        $response = MzrRestResponse::get(array('profile_id' => $profile->id, 'code' => $profile->code), $ads);    
			return Response::json($response);			

		}
		catch (Exception $e)
		{
			Log::warning($e->getMessage() . "\n\n" . $e->getTraceAsString());
			return Response::make('Ads by profile failed!', '500');
		}
	}		
}
