<?php

/**
 * Generate uuid
 *
 * @return string
 */
function generateUuid() {
	return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	// 32 bits for "time_low"
	mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

	// 16 bits for "time_mid"
	mt_rand( 0, 0xffff ),

	// 16 bits for "time_hi_and_version",
	// four most significant bits holds version number 4
	mt_rand( 0, 0x0fff ) | 0x4000,

	// 16 bits, 8 bits for "clk_seq_hi_res",
	// 8 bits for "clk_seq_low",
	// two most significant bits holds zero and one for variant DCE1.1
	mt_rand( 0, 0x3fff ) | 0x8000,

	// 48 bits for "node"
	mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	);
}

/**
 * get right temp token
 *
 * @param array $headers
 *
 * @return string $tmpToken
 */
function getTmpToken() {

	$headers = getallheaders();
	$tmpToken = generateUuid();

	if(isset($headers['MazhrSession']))
	{
		$tmpSession = MazhrSession::get($headers['MazhrSession']);
		if(empty($tmpSession))
		{
			$tmpSession = MazhrSession::set($tmpToken);
		}
		else
		{
			$tmpSession->ping();
		}
		return $tmpSession->key;
	}

	MazhrSession::set($tmpToken);
	return $tmpToken;
}

/**
 * parse temp token from input
 *
 * @param array $headers
 *
 * @return string $token
 */
function parseInputToken($inputToken = null) {

    $token = null;

    if($inputToken) {
    	$token = $inputToken;
    }
    else if(Input::has('mazhr_token') )
    {
        $token = Input::get('mazhr_token');
    }

    return $token;
}
/**
 * parse temp token from input
 *
 * @param array || object $input
 * @param string $delimiter defaut ";"
 *
 * @return string cvs
 */
function toCsv($input, $delimiter = ";")
{
    $tempMemory = fopen('php://memory', 'w');
    foreach ($input as $line) {
        fputcsv($tempMemory, $line->toArray(), $delimiter);
    }

    fseek($tempMemory, 0);
    ob_start(); // fishy business
    fpassthru($tempMemory);
	return ob_get_clean();
}
