<?php
class UserProfile extends Eloquent {

    protected $table = 'user_profiles';

	/**
	 * Matching user profiles
	 *
	 * @param integer user id
	 * @return array
	 */
	//public static function getMatches($userId, $area) {	
	public static function getMatchingProfiles($userId) {
		$results = DB::table('user_profiles')
			->join('profiles', 'user_profiles.profile', '=', 'profiles.code')
			//->join('profile_professioncodes', 'profile_professioncodes.profile_id', '=', 'profiles.id')
			//->join('ad_profession_codes', 'ad_profession_codes.code', 'like', DB::raw("concat(profile_professioncodes.code, '%')"))
			//->join('ads', 'ad_profession_codes.ad_id', '=', 'ads.id')
			//->select('name', 'score', 'profile_professioncodes.code', 'ad_profession_codes.ad_id', 'title', 'area')
			->select('name', 'code', 'score', 'profiles.id')
			->where('user_id', '=', $userId)
			//->where('area', '=', $area)
			->orderBy('score', 'desc')
			//->orderBy('name')
			//->orderBy('published', 'desc')
			->get();
			
		return $results;		
	}    

}