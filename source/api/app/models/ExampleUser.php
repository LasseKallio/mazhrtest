<?php
class ExampleUser extends Eloquent {
    protected $table = 'example_users';
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('authtoken', 'remember_token');
    
    public function toJArray() {
    	$array = $this->toArray();
    	$array['progress'] = json_decode($this->progress);
    	$array['skills'] = json_decode($this->skills);
    	$array['experience'] = json_decode($this->experience);
    	$array['education'] = json_decode($this->education);
    	$array['filters'] = json_decode($this->filters);
    	return $array;
    }

    public static function last() {
    	return ExampleUser::orderBy('created_at', 'desc')->firstOrFail();
    }
}