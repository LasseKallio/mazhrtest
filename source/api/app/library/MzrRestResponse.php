<?php
/**
 * Libraty test
 *
 */
class MzrRestResponse {
	public static function get($metadata, $data) {
		$responseObject = new StdClass;
		$metaObject = new StdClass;

		foreach($metadata as $key => $value){
			$metaObject->$key = $value;
		}

		$responseObject->data = $data;
		$responseObject->metadata = $metaObject;

		return $responseObject;
	}
}
