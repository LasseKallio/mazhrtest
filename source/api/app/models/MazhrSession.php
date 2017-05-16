<?php
class MazhrSession extends Eloquent {

    protected $table = 'mazhr_sessions';
    protected $guarded = array('id');

    /**
     * Get Mazhr Session
     *
     * @param string $key
     * @return object
     */
    public static function get($key)
    {
        $user = Auth::user();
        $mazhrSessionQuery = self::where('key', '=', $key)->where('updated_at', '>=', DB::raw('date_sub(NOW(), interval ' . Config::get('auth.tmpTokenLifetime') . ' minute)')); 
        $session = $mazhrSessionQuery->first();
	Log::info($session);
	
        // Someone is trying to use somebody elses tmp token
        if(isset($user->id) && !empty($session))
        {
            if($session->user_id != null && $user->id != $session->user_id)
                return null;
        }

        return $session;
    }

    /**
     * Set Mazhr Session
     *
     * @param string $key
     * @param json $value
     * @return object
     */
    public static function set($key, $value = '{}')
    {

        $user = Auth::user();
        $newSession = new MazhrSession();
        $newSession->key = $key;
        $newSession->value = $value;
        if(isset($user->id))
        {
            $newSession->user_id = $user->id;
        }
        $newSession->save();
        return $newSession;

    }

    /**
     * Check if user is authenticated and update if needed. Touch timestamps.
     */
    public function ping()
    {
        $this->touch();
        $user = Auth::user();
        if(isset($user->id) && $this->user_id == null)
        {
            $this->user_id = $user->id;
            $this->save();
        }
    }

//
//  Value object thingy feels little bit fishy and is badly named. Refactoring would be great.
//

    /**
     * Get value
     *
     * @return object
     */
    public function valueObject()
    {
        if(empty($this->value))
        {
            return new stdClass();
        }
        else
        {
            return json_decode($this->value);
        }
    } 

    /**
     * save value object
     *
     * @return object
     */
    public function saveValueObject($value)
    {
        $this->value = json_encode($value);
        $this->save();
    }

    /**
     * Set value to value object
     *
     * @param string $key
     * @param string $value
     */
    public function setValue($key, $value)
    {
        $values = $this->valueObject();
        $values->$key = $value;
        $this->saveValueObject($values);
    }

    /**
     * Get value from value object
     * @param string $key
     *
     * @return string 
     */
    public function getValue($key)
    {
        $values = $this->valueObject();
        return isset($values->$key) ? $values->$key : null;
    }       
}
