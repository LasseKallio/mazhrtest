<?php
class ExampleAd extends Eloquent {

    protected $table = 'example_ads';

	/**
	 * Convert json fields objects in order to convert everything to json
	 *
	 * @return array
	 */
	public function toJArray() {
		$array = $this->toArray();	
		$jsonAd = json_decode($this->json_ad);
		$jsonAd->kuvausteksti = nl2br($jsonAd->kuvausteksti);
		$array["json_ad"] = $jsonAd;
		return $array;
	}
}
