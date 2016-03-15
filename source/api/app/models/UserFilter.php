<?php
class UserFilter extends Eloquent {

    protected $table = 'user_filters';
    /**
     * Save or update filters
     *
     * @param user user object
     * @param data data to update / add
     * @return array of changed fields and values
     */
    public static function saveOrUpdate($user, $data)
    {
		$changed = array();
		$filters = $user->assocFilters();
		foreach($data as $key => $value) {
			if(isset($filters[$key])) 
			{
				$filter = $filters[$key];
				if($filter->value != $value) {
					$filter->value = $value;
					$filter->save();
					$changed[$key] = $value;
				}
			}
			else
			{
				$filter = new UserFilter();
				$filter->user_id = $user->id;
				$filter->type = $key;
				$filter->value = $value;
				$filter->save();
				$changed[$key] = $value;
			}
		}
		return $changed;    	
    }

}