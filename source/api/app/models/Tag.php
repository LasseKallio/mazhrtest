<?php
class Tag extends Eloquent {

    protected $table = 'tags';
    protected $guarded = array('id');
    public $timestamps = false;
}