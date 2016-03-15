<?php
class UserPrivacy extends Eloquent {

    protected $table = 'user_privacy';

    /**
     * create user privacy row
     *
     * @param id $userId
     * @return object
     */
    public static function findOrCreateByUserId($userId) {
        $existingPrivacy = self::where('user_id', '=', $userId)->first();
        if($existingPrivacy) {;
            $privacy = $existingPrivacy;
        }   
        else
        {
            $privacy = new UserPrivacy();
            $privacy->user_id = $userId;
            $privacy->profile_token = generateUuid();
            $privacy->save();
        }
        return $privacy;
    }
}