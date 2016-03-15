<?php
class AdCount extends Eloquent {

    protected $table = 'ad_count';
    protected $fillable = array('count');

	/**
	 * current ad count staus
	 *
	 * @return object
	 */
	public static function getStatus() {
		$status = new StdClass();
		$now = DB::table('ads')->count();
		$yesterdaysAds = self::where(DB::raw('date(created_at)'), '=', DB::raw('subdate(current_date, 1)'))->first();
		$yesterday = !empty($yesterdaysAds) ? $yesterdaysAds->count : 0;
		$status->now = $now;
		$status->yesterday = $yesterday;
		$status->difference = $now - $yesterday;
		return $status;
	}
}