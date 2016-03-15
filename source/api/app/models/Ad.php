<?php
class Ad extends Eloquent {

    protected $table = 'ads';

    /**
     * Convert json fields objects in order to convert everything to json
     *
     * @return array
     */
    public function toJArray() {
        $array = $this->toArray();  
        $jsonAd = json_decode($this->json_ad);
        $jsonAd->kuvausteksti = nl2br($jsonAd->kuvausteksti);
        $array["json_ad"] = $jsonAd;
        return $array;
    }

    /**
     * Get ads by user interest
     *
     * @param integer profession (ILO)
     * @param array area
     * @param array keywords
     * @return array
     */
    public static function getByInterest($profession, $area, $keywords) {
        
        // Beginning of the magical query
        $results = DB::table('ads')
        ->join('ad_profession_codes', 'ad_profession_codes.ad_id', '=', 'ads.id')
        ->select('ads.id', 'ads.title', 'ads.area', 'ads.json_ad');
        
        // add area if necessary
        //if(!empty($area)) $results->where('area', '=', $area);

        if(count($area)) {
            $results->where(function($query) use ($area){
                $first = true;
                foreach($area as $key => $value){
                    if($value == "") continue;
                    $value = trim($value);
                    if ($first == true)
                    {
                        $query->where('area', '=', $value);
                        $first = false;
                    }
                    else
                    {
                        $query->orWhere('area', '=', $value);
                    }   
                }
            });
        }

        // profession codes
        if(!empty($profession))
        {
            $professionCodes = explode('-', $profession);
            if(count($professionCodes) > 1)
            {
                $results->where(function($query) use ($professionCodes){
                    foreach($professionCodes as $key => $value){
                        if($key == 0)
                        {
                            $query->where('code', 'LIKE', $value . '%');
                        }
                        else
                        {
                            $query->orWhere('code', 'LIKE', $value . '%');
                        }   
                    }
                });
            }
            else
            {
                $results->where('code', 'LIKE', $profession . '%');
            }
        }
        // keywords

        // check what kind of keywords we are talking about
        $normalKeywords = array();
        $cityKeywords = array();
        foreach($keywords as $keyword) {
            if(City::isCity($keyword))
            {
                $cityKeywords[] = $keyword;
            }
            else
            {
                $normalKeywords[] = $keyword;
            }

        }

        // add normal keywords to the query
        if(count($normalKeywords)) {
            $results->where(function($query) use ($normalKeywords){
                foreach($normalKeywords as $key => $value){
                    $value = trim($value);
                    if ($key == 0)
                    {
                        $query->where('title', 'LIKE', '%' . $value . '%');
                    }
                    else
                    {
                        $query->orWhere('title', 'LIKE', '%' . $value . '%');
                    }   
                }
            });
        }

        if(count($cityKeywords)) {
            $results->where(function($query) use ($cityKeywords){
                foreach($cityKeywords as $key => $value){
                    $value = trim($value);
                    if ($key == 0)
                    {
                        $query->where('city', '=', $value);
                    }
                    else
                    {
                        $query->orWhere('city', '=', $value);
                    }   
                }
            });
        }

        // grouping and ordering etc.
        $searchResult = $results->groupBy('ads.id')->orderBy('published', 'desc')->take(201)->get();
 
        foreach($searchResult as $key => $result) {
            $result->json_ad = json_decode($result->json_ad);
            $searchResult[$key] = $result;
        }

        return $searchResult;       
    }

    /**
     * Get ads for the user by the profile
     *
     * @param object $profile
     * @param string area
     * @return array
     */ 
    public static function getAdsByProfile($profile, $area, $keywords) {

        $professioncodes = $profile->professionCodes;
        if(count($professioncodes) == 0) return;

        $codes = array();
        $mappedKeywords = array();
        foreach($professioncodes as $mapping){
            $first = substr($mapping->code, 0, 1);
            if(is_numeric($first) || $first == "x") {
                $codes[] = $mapping->code;
            } else {
                $mappedKeywords[] = $mapping->code;
            }
        } 

        $results = DB::table('ads')
        ->join('ad_profession_codes', 'ad_profession_codes.ad_id', '=', 'ads.id')
        ->select('ads.id', 'ads.title', 'ads.area', 'ads.json_ad');

        //if(!empty($area)) $results->where('area', '=', $area);

        if(count($area)) {
            $results->where(function($query) use ($area){
                $first = true;
                foreach($area as $key => $value){
                    if($value == "") continue;
                    $value = trim($value);
                    if ($first == true)
                    {
                        $query->where('area', '=', $value);
                        $first = false;
                    }
                    else
                    {
                        $query->orWhere('area', '=', $value);
                    }   
                }
            });
        }

        if(count($codes)) {
            $results->where(function($query) use ($codes){
                foreach($codes as $key => $value){
                    $value = trim($value);
                    if ($key == 0)
                    {
                        $query->where('ad_profession_codes.code', 'LIKE', $value . '%');
                    }
                    else
                    {
                        $query->orWhere('ad_profession_codes.code', 'LIKE', $value . '%');
                    }   
                }
            });
        }

        // check what kind of keywords we are talking about
        $normalKeywords = array();
        $cityKeywords = array();
        foreach($keywords as $keyword) {
            if($keyword == "") continue;
            if(City::isCity($keyword))
            {
                $cityKeywords[] = $keyword;
            }
            else
            {
                $normalKeywords[] = $keyword;
            }
        }

        // add mapped keywords to the query
        if(count($mappedKeywords)) {
            $results->where(function($query) use ($mappedKeywords){
                foreach($mappedKeywords as $key => $value){
                    $value = trim($value);
                    if ($key == 0)
                    {
                        $query->where('title', 'LIKE', '%' . $value . '%');
                    }
                    else
                    {
                        $query->orWhere('title', 'LIKE', '%' . $value . '%');
                    }   
                }
            });
        }

        // add normal keywords to the query
        if(count($normalKeywords)) {
            $results->where(function($query) use ($normalKeywords){
                foreach($normalKeywords as $key => $value){
                    $value = trim($value);
                    if ($key == 0)
                    {
                        $query->where('title', 'LIKE', '%' . $value . '%');
                    }
                    else
                    {
                        $query->orWhere('title', 'LIKE', '%' . $value . '%');
                    }   
                }
            });
        }

        if(count($cityKeywords)) {
            $results->where(function($query) use ($cityKeywords){
                foreach($cityKeywords as $key => $value){
                    $value = trim($value);
                    if ($key == 0)
                    {
                        $query->where('city', '=', $value);
                    }
                    else
                    {
                        $query->orWhere('city', '=', $value);
                    }   
                }
            });
        }

        $searchResult = $results->groupBy('ads.id')->orderBy('published', 'desc')->get();
 
        foreach($searchResult as $key => $result) {
            $result->json_ad = json_decode($result->json_ad);
            $searchResult[$key] = $result;
        }

        return $searchResult;       
    }

    /**
     * get a map of ad id's and profile id's
     *
     * @param integer $profileId Optional profile id to get map for single profile
     * @return array $profileMap
     */
    public static function getProfileMap($profileId = null)
    {
        $profiles = array();
        $profileMap = array();

        if($profileId)
        {
            if($profile = Profile::find($profileId))
            {
                $profiles[] = $profile;
            }
        }
        else
        {
            $profiles = Profile::all();
        }

        foreach($profiles as $profile)
        {
            $ads = self::getAdsByProfile($profile, array(), array());
            if(!$ads) continue;
            foreach($ads as $ad)
            {
                $profileMap[$ad->id][] = $profile->id;
            }
        }

        return $profileMap;
    }

    /**
     * Set / update ad profiles
     *
     * @param integer $profileId Optional profile id to update single profile ads
     */
    public static function profileUpdate($profileId = null)
    {
        $profileMap = self::getProfileMap($profileId);
        $allAds = self::all();

        foreach($allAds as $singleAd)
        {
            $singleAd->profile_id = null;
            if(isset($profileMap[$singleAd->id]))
            {
                // collect all profile codes to these arrays
                $profileCodes = array(); // numeric codes
                $profileKeywords = array(); //keywords

                // got trough all ads profiles
                foreach($profileMap[$singleAd->id] as $profileId)
                {
                    $profile = Profile::find($profileId);
                    $codes = $profile->professionCodes;
                    if(!empty($codes))
                    {
                        // collect profile codes
                        foreach($codes as $code)
                        {
                            if(is_numeric($code->code))
                            {
                                $profileCodes[$code->code] = $code->profile_id;
                            }
                            else
                            {
                                $profileKeywords[$code->code] = $code->profile_id;  
                            }
                        }
                    }
                }
                // if keywords (search phrases) are found, first will be used to determine the best matchng profile for the ad
                if(count($profileKeywords))
                {   
                    $singleAd->profile_id = reset($profileKeywords); //first profile
                }
                else if(count($profileCodes))
                {
                    arsort($profileCodes); // sort array "high to low"
                    $singleAd->profile_id = reset($profileCodes); //first (and the most precize) code dermines the profile 
                }
            }

            $singleAd->save();
        }
    }
}