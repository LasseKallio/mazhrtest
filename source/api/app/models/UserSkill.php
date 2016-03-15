<?php
class UserSkill extends Eloquent {

    const SKILL_TYPE_FREETEXT = 1;
    const SKILL_TYPE_YEARS = 2;
    const SKILL_TYPE_INT = 3;


    protected $table = 'user_skills';
    protected $guarded = array('id');


    /**
     * Save or update skills
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
        $oldSkills = $newUserObject->assocSkills();
        foreach($data as $key => $value)
        {
            // editing non categorized skill
            if(isset($oldSkills[$key])) 
            {
                $skill = $oldSkills[$key];
                $skill->value = $value->value;
                $skill->keywords = $value->keywords;
                $skill->save();
                $changed[$key] = $value;
            }
            // categorized skill
            else if(isset($value->category))
            {   
                if(isset($oldSkills[$value->category]))
                {
                    $foundIt = false;
                    foreach($oldSkills[$value->category] as $categorySkill)
                    {
                        // editing skill with category
                        if($categorySkill->key == $value->key)
                        {
                            $categorySkill->value = $value->value;
                            $categorySkill->keywords = $value->keywords;
                            $categorySkill->save();
                            $foundIt = true;                
                        }
                    }
                    // new skill to existing category
                    if($foundIt == false) $changed[$key] = self::addNew($newUserObject, $value);
                }
                // new skill to new category
                else
                {
                    $changed[$key] = self::addNew($newUserObject, $value);
                }
            }
            // creating new skill (without a category)
            else
            {
                $changed[$key] = self::addNew($newUserObject, $value);
            }
        }
        return $changed;  
    }
    
    private static function addNew($user, $data)
    {
        $skill = new UserSkill();
        $skill->user_id = $user->id;
        $skill->key = $data->key;
        $skill->value = $data->value;
        $skill->type = $data->type;
        $skill->keywords = $data->keywords;
        if(isset($data->category)) $skill->category = $data->category;
        $skill->save();
        Log::info('New skill '. $data->key .' created for user ' . $user->email); 
        return $skill->value;
    }
}

