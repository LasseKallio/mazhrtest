<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

    use UserTrait, RemindableTrait;

    const USER_DEFAULT = 1;
    const USER_DELETED = 50;
    const USER_ADMIN = 100;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token', 'linkedin_id');

    /**
     * User education
     *
     * @return array
     */
    public function education()
    {
        return $this->hasMany('UserEducation')->orderBy(DB::raw('(end_year is null)'), 'desc')->orderBy('end_year', 'desc')->orderBy('end_month', 'desc')->orderBy('start_year', 'desc');
    }

    /**
     * User work history relation.
     *
     * @return array
     */
    public function workHistory()
    {
        return $this->hasMany('UserWorkHistory')->orderBy(DB::raw('(end_year is null)'), 'desc')->orderBy('end_year', 'desc')->orderBy('end_month', 'desc')->orderBy('start_year', 'desc');
    }

    /**
     * User tests realation.
     *
     * @return array
     */
    public function tests()
    {
        return $this->hasMany('UserTest');
    }

    /**
     * filters with name as index
     *
     * @return array
     */
    public function assocFilters()
    {
        $filters = $this->filters;
        $response = array();
        foreach($filters as $filter) {
            $response[$filter->type] = $filter;
        }
        return $response;
    }

    /**
     * Users privacy settings
     *
     * @return array
     */
    public function privacy()
    {
        $privacy = UserPrivacy::findOrCreateByUserId($this->id);
        return $privacy;
    }

    /**
     * skills with name key or category as index
     *
     * @return array
     */
    public function assocSkills()
    {
        $skills = $this->skills;
        $response = array();
        foreach($skills as $skill)
        {
            if(isset($skill->category))
            {
                if(!isset($response[$skill->category])) $response[$skill->category] = array();
                $response[$skill->category][] = $skill;
            }
            else
            {
                $response[$skill->key] = $skill;
            }
        }
        return $response;
    }

    /**
     * extras with name key or category as index
     *
     * @return array
     */
    public function assocExtras()
    {
        $extras = $this->extras;
        $response = array();
        foreach($extras as $extra)
        {
            if(isset($extra->category))
            {
                if(!isset($response[$extra->category])) $response[$extra->category] = array();
                $response[$extra->category][] = $extra;
            }
            else
            {
                $response[$extra->key] = $extra;
            }
        }
        return $response;
    }

    /**
     * Create candidate id for cut-e test
     *
     * @param integer $userId Id of the user the candidate id is made for
     *
     * @return string $candidateId
     */
    public static function candidateId($userId)
    {
        // it might be a good idea not to bind the candidate id to the user id in the future
        // but create uuid of some sort and store it to database
        $config = Config::get('services.cut-e');
        return $candidateId = $config['candidatePrefix'] . $userId;
    }

    /**
     * User filters realation.
     *
     * @return array
     */
    public function filters()
    {
        return $this->hasMany('UserFilter');
    }

    /**
     * User skills realation.
     *
     * @return array
     */
    public function skills()
    {
        return $this->hasMany('UserSkill');
    }

    /**
     * User extras realation.
     *
     * @return array
     */
    public function extras()
    {
        return $this->hasMany('UserExtra');
    }

    /**
     * Convert json fields to arrays
     *
     * @return array
     */
    public function toJArray()
    {
        $array = $this->toArray();
        $linkedinData = json_decode($this->linkedin_data);
        $array["linkedin_data"] = $linkedinData;
        return $array;
    }
    /**
     * All user related data in array form
     *
     * @return array
     */
    public function getFullProfileAsArray()
    {
        $response = $this->toJArray();
        $response['competence_points'] = is_null($this->competence_points) ? [] : json_decode($this->competence_points);
        $response['behaviour_points'] = is_null($this->behaviour_points) ? [] : json_decode($this->behaviour_points);
        $response['motivation_points'] = is_null($this->motivation_points) ? [] : json_decode($this->motivation_points);
        $response['workhistory'] = $this->workHistory;
        $response['education'] = $this->education;
        $response['tests'] = UserTest::activeTests($this->id);
        $response['filters'] = $this->assocFilters();
        $response['skills'] = $this->assocSkills();
        $response['extras'] = $this->assocExtras();
        $response['privacy'] = $this->privacy()->toArray();
        return $response;
    }

    /**
     * Create image name
     *
     * @param string $extension
     *
     * @return string $image
     */
    public static function imageName($extension)
    {
        $prefix = date('Ymdhis');
        $image = $prefix . '_' . md5(time() . $prefix . rand(1000, 9999)) . $extension;
        return $image;
    }
}
