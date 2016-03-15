<?php
class UserWorkHistory extends Eloquent {

    protected $table = 'user_work_history';
    /**
     * Save or update work history
     *
     * @param object $user object
     * @param array | object $data to update / add
     */
    public static function saveOrUpdate($user, $data)
    {
    	// save or update single work history
    	if(is_object($data))
    	{
    		self::saveOrUpdateSingle($user, $data);
    	}
    	// save buch of work histories at once
    	if (is_array($data))
    	{
    		foreach($data as $single)
    		{
    			self::saveOrUpdateSingle($user, $single);
    		}
    	}
    }
    /**
     * Save or update work history
     *
     * @param object $user object
     * @param object $data to update / add
     */
    private static function saveOrUpdateSingle($user, $data)
    {
		if(isset($data->id)) {
			$history = self::find($data->id);
		}
		else {
			$history = new UserWorkHistory();
			$history->user_id = $user->id;
			$history->current = false;
		}
		if(!empty($data->start_year)) $history->start_year = $data->start_year;
		if(!empty($data->start_month)) $history->start_month = $data->start_month;
		if(!empty($data->end_year)) $history->end_year = $data->end_year;
		if(!empty($data->end_month)) $history->end_month = $data->end_month;
		if(!empty($data->company)) $history->company = $data->company;
		if(!empty($data->title)) $history->title = $data->title;
		if(isset($data->keywords)) $history->keywords = $data->keywords;
        if(isset($data->current)) $history->current = $data->current;
		$history->save();   	
    }

}