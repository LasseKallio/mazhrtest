<?php
class ProfileProfessionCode extends Eloquent {

    protected $table = 'profile_professioncodes';
    protected $fillable = array('code','profile_id');

}