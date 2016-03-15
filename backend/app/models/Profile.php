<?php
class Profile extends Eloquent {

    protected $table = 'profiles';   

    protected $fillable = array('name', 'code', 'competence', 'model');

    /**
	 * Profiles profession codes
	 *
	 * @return array
	 */
    public function professionCodes()
    {
        return $this->hasMany('ProfileProfessionCode');
    }

}