<?php
class UserExtra extends Eloquent {

    const EXTRA_TYPE_FREETEXT = 1;
    const EXTRA_TYPE_YEARS = 2;
    const EXTRA_TYPE_INT = 3;

    protected $table = 'user_extras';
    protected $guarded = array('id');

    /**
     * Save or update extras
     * - pretty much a direct copy from user skills
     * - refactoring might be in order
     * - something mystical is ging on with the user object. Decided to make a new one to make the code work.
     *
     * @param user user object
     * @param data data to update / add
     * @return array of changed fields and values
     */
    public static function saveOrUpdate($user, $data)
    {
        $changed = array();
        $newUserObject = User::find($user->id);
        $oldExtras = $newUserObject->assocExtras();
        foreach($data as $key => $value)
        {
            // editing non categorized extras
            if(isset($oldExtras[$key])) 
            {
                $extra = $oldExtras[$key];
                $extra->value = $value->value;
                $extra->keywords = $value->keywords;
                $extra->save();
                $changed[$key] = $value;
            }
            // categorized extra
            else if(isset($value->category))
            {   
                if(isset($oldExtras[$value->category]))
                {
                    $foundIt = false;
                    foreach($oldExtras[$value->category] as $categoryExtra)
                    {
                        // editing extra with category
                        if($categoryExtra->key == $value->key)
                        {
                            $categoryExtra->value = $value->value;
                            $categoryExtra->keywords = $value->keywords;
                            $categoryExtra->save();
                            $foundIt = true;                
                        }
                    }
                    // new extra to existing category
                    if($foundIt == false) $changed[$key] = self::addNew($newUserObject, $value);
                }
                // new extra to new category
                else
                {
                    $changed[$key] = self::addNew($newUserObject, $value);
                }
            }
            // creating new extra (without a category)
            else
            {
                $changed[$key] = self::addNew($newUserObject, $value);
            }
        }
        return $changed;  
    }
    
    private static function addNew($user, $data)
    {
        $extra = new UserExtra();
        $extra->user_id = $user->id;
        $extra->key = $data->key;
        $extra->value = $data->value;
        $extra->type = $data->type;
        $extra->keywords = $data->keywords;
        if(isset($data->category)) $extra->category = $data->category;
        $extra->save();
        Log::info('New Extra '. $data->key .' created for user ' . $user->email); 
        return $extra->value;
    }
}

