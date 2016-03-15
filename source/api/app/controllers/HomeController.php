<?php

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	*/

	/**
	 * Pull message(s) from session to json
	 * ..still work in progress..
	 *
	 * @return json response
	 */
	public function message()
	{

		$message = "";
		$headers = getallheaders();

		if(isset($headers['MazhrSession']))
		{
			$tmpSession = MazhrSession::get($headers['MazhrSession']);
			if(!empty($tmpSession))
			{
				$message = $tmpSession->getValue('message');
				$message = $message ? $message : ""; // dude
				$tmpSession->setValue('message', null);
			}
		}

		$response = new stdClass;
		$response->message = $message;
		
		return Response::json($response);
		
	}


	/**
	 * Tags
	 *
	 * @return json response
	 */
	public function getTags()
	{
		$tags = Tag::all();
		$response = MzrRestResponse::get(array('count' => count($tags)), $tags);
		return Response::json($response);
	}

	/**
	 * Translation JSON
	 *
	 * @return json response
	 */
	public function getTranslations($language = 'fi')
	{
		$languages = array('fi' => 'finnish', 'en' => 'english', 'sv' => 'swedish');
		if(isset($languages[$language]))
		{
			$json = file_get_contents(Config::get('app.language_url') . '/'. $languages[$language] .'.json');
			$response = Response::make($json, 200);
			$response->header('Content-Type', ' application/json');
			return $response;
		}
		else
		{
			return Response::make('Not found', '404');
		}

	}

}
