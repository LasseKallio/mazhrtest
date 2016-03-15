<?php
class UserEducation extends Eloquent {

    protected $table = 'user_education';
    /**
     * Save or update education
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
     * Save or update single education object
     *
     * @param object $user object
     * @param object $data to update / add
     */
    private static function saveOrUpdateSingle($user, $data)
    {
        if(isset($data->id)) {
            $education = self::find($data->id);
        }
        else {
            $education = new UserEducation();
            $education->user_id = $user->id;
            $education->current = false;
        }
        if(!empty($data->start_year)) $education->start_year = $data->start_year;
        if(!empty($data->start_month)) $education->start_month = $data->start_month;
        if(!empty($data->end_year)) $education->end_year = $data->end_year;
        if(!empty($data->end_month)) $education->end_month = $data->end_month;
        if(!empty($data->level)) $education->level = $data->level;
        if(!empty($data->degree)) $education->degree = $data->degree;
        if(isset($data->keywords)) $education->keywords = $data->keywords;
        if(!empty($data->school)) $education->school = $data->school;
        if(isset($data->current)) $education->current = $data->current;
        $education->save();     
    }

}