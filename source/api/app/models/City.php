<?php
class City extends Eloquent {

    protected $table = 'cities';

	/**
	 * Check if given string is a name of a city
	 *
	 * @param string
	 * @return boolean
	 */
	public static function isCity($string) {
		return self::where('city', '=', $string)->count() ? true : false;
	}    
}
