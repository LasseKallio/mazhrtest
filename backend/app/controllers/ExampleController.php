<?php
class ExampleController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Example Controller
	|--------------------------------------------------------------------------
	|
	| Example controller for mobile demo. Will get replaced with Jobs And User controllers eventually
	|
	*/


	public function copyAds()
	{
		$ads = DB::table('ads')->take(10)->get();
		foreach($ads as $ad) {
			$newAd = new ExampleAd();
			$newAd->uuid = $ad->uuid;
			$newAd->source = $ad->source;
			$newAd->title = $ad->title;
			$newAd->area = $ad->area;
			$newAd->json_ad = $ad->json_ad;
			$newAd->published = $ad->published;
			//$newAd->save();
		}
	}

	/**
	 * Me
	 *
	 * @return json response
	 */
	public function createMe()
	{
        $request = Request::instance();
        $content = json_decode($request->getContent());

        try {
			$user = new ExampleUser();
			$user->authtoken = $this->genUuid();
			$user->filters = json_encode($content->data->filters);
			$user->firstname = "";
			$user->lastname = "";
			$user->current = "";
			$user->avatar = "http://testipenkki.esmes.fi/mzr/avatar.png";


	        $progress = '
			    {
			        "progress": 0.03,
			        "title": "Profiili 3% valmis",
			        "action": {
			            "id": 1,
			            "type": "mobile",
			            "description": "Tarkenna suosituksia täyttämällä perustiedot"
			        }
			    }';
			$user->progress = $progress;
			$user->save();
			return Response::json(MzrRestResponse::get(array("authtoken" => $user->authtoken), $user->toJArray()));

		} catch(Exception $e) {
			$message = App::environment() == 'live' ? 'Bad request!' : $e->getMessage();
			return Response::make($e->getMessage(), '400');
		}
	}

	/**
	 * Me
	 *
	 * @return json response
	 */
	public function populateMe()
	{
		$user = ExampleUser::last();
		$exampleJson = file_get_contents('http://api.randomuser.me/');
		$exampleData = json_decode($exampleJson);
		$user->firstname = $exampleData->results[0]->user->name->first;
		$user->lastname = $exampleData->results[0]->user->name->last;
		$user->avatar = $exampleData->results[0]->user->picture->medium;

		$progress = rand(10, 100);
		$progressJson = '
		{
            "progress": 0.4,
            "title": "Profiili 40% valmis",
            "action": {
                "id": 12333,
                "type": "test",
                "description": "Tarkenna suosituksia tekemällä persoonallisuustesti."
            }
        }';

        $currents = array(
			"System Administrator",
			"Human Resource Manager",
			"CEO",
			"Sales Manager"
		);
		$current = $currents[rand(0, (count($currents) - 1))];

		$experience = '
        [
            {
                "title": "Palmu inc.",
                "start": {
                    "month": 1,
                    "year": 2009
                },
                "end": null
            }, {
                "title": "Ego Beta Oy",
                "start": {
                    "month": 5,
                    "year": 2007
                },
                "end": {
                    "month": 1,
                    "year": 2009
                }
            }
        ]';

		$education = '
        [
            {
                "title": "Teknillinen korkeakoulu",
                "end": {
                    "month": 12,
                    "year": 2005
                }
            }
        ]
		';

		$user->progress = $progressJson;
		$user->current = $current;
		$user->experience = $experience;
		$user->education = $education;
		$user->save();
		return View::make('examples/linkedin_success'); 
		//return Response::json(MzrRestResponse::get(array(), $user->toJArray()));
	}

	/**
	 * Me
	 *
	 * @return json response
	 */
	public function getMe($node = null)
	{

		if(!Input::has('debug')) {
			$user = ExampleUser::last();
			return Response::json(MzrRestResponse::get(array("authtoken" => $user->authtoken), $user->toJArray()));
		}

		$progress = rand(10, 100);
		$exampleJson = file_get_contents('http://api.randomuser.me/');
		$exampleData = json_decode($exampleJson);

		$example = '
		{
			"metadata": {
		    },
		    "data": {
		        "id": 123,
		        "avatar": "'. $exampleData->results[0]->user->picture->medium .'",
		        "firstname": "' . $exampleData->results[0]->user->name->first . '",
		        "lastname": "' . $exampleData->results[0]->user->name->last . '",
		        "current": "Backup Admin",
		        "progress": {
		            "progress": 0.' . $progress . ',
		            "title": "Profiili '. $progress .'% valmis",
		            "action": {
		                "id": 12333,
		                "type": "test",
		                "description": "Tarkenna suosituksia tekemällä persoonallisuustesti"
		            }
		        },     

		        "skills": [
		            {
		                "id": 1,
		                "title": "Kielitaito",
		                "type": "array",
		                "value": [
		                    {
		                        "id": 2,
		                        "title": "englanti",
		                        "type": "percentage",
		                        "value": "' . rand(10, 100) . '%"
		                    },
		                    {
		                        "id": 244,
		                        "title": "ruotsi",
		                        "type": "percentage",
		                        "value": "' . rand(10, 100) . '%"
		                    }
		                ]               
		            }, {
		                "id": 5,
		                "title": "Persoonallisuustesti",
		                "type": "action",
		                "value": {
		                    "id": 12333,
		                    "type": "raport",
		                    "title": "Lue raportti"
		                }
		            }, {
		                "id": 7,
		                "title": "Ulkomaantyökokemus",
		                "type": "text",
		                "value": "ei"
		            }, {
		                "id": 17,
		                "title": "Ohjelmointitaidot",
		                "type": "tags",
		                "value": ["Java", ".NET", "C#"]
		            }
		        ],

		        "experience": [
		            {
		                "title": "Palmu inc.",
		                "start": {
		                    "month": 1,
		                    "year": 2009
		                },
		                "end": null
		            }, {
		                "title": "Ego Beta Oy",
		                "start": {
		                    "month": 5,
		                    "year": 2007
		                },
		                "end": {
		                    "month": 1,
		                    "year": 2009
		                }
		            }
		        ],

		        "education": [
		            {
		                "title": "Teknillinen korkeakoulu",
		                "end": {
		                    "month": 12,
		                    "year": 2005
		                }
		            }
		        ],

		        "filters": [
		            {
		                "id": 1,
		                "type": "regions",
		                "title": "Alue",
		                "values": [
		                    {
		                        "id": 1,
		                        "title": "Uusimaa"
		                    }
		                ]
		            }, {
		                "id": 2,
		                "type": "interests",
		                "title": "Kiinnostus",
		                "values": [
		                    {
		                        "id": 1,
		                        "title": "Tieto- ja viestintätekniikka"
		                    }
		                ]
		            }
		        ]
		    }
		}';

		$user = json_decode($example);

		if(!empty($node) && isset($user->data->$node)) {
			$response = $user->data->$node;
		} else {
			$response = $user;
		}

		return Response::json($response);
	}

	/**
	 * Jobs
	 *
	 * @return json response
	 */
	public function getJobs($param = null)
	{

		$response = array();
		$example =
		'{
		    "metadata": {
		        "total": '.rand(10, 100).',
		        "offset": 0,
		        "limit": 10
		    },
		    "data": [
		    ]
		}';
		
		$single = 
		'{
	        "id": 123,
	        "type": "job",
	        "updated": 123123123123,
	        "stars": 3,
	        "expired": "false",
	        "expiresStatus": "18 pvä jäljellä",
	        "starred": "true",
	        "match": {
	            "percentage": 0.79,
	            "set": [
	                {
	                    "title": "Numeraalinen hahmottamiskyky",
	                    "value": {
	                        "type": "stars",
	                        "value": 4
	                    }
	                },
	                {
	                    "title": "Sopivuus persoonaan",
	                    "value": {
	                        "type": "action",
	                        "value": {
	                            "id": 12333,
	                            "type": "test",
	                            "title": "Tee testi"
	                        }
	                    }
	                },
	                {
	                    "title": "Vakinaisessa työssä",
	                    "description": "5 vuotta",
	                    "value": {
	                        "type": "status",
	                        "value": 1
	                    }
	                },
	                {
	                    "title": "Korkeakoulututkinto",
	                    "value": {
	                        "type": "status",
	                        "value": 1
	                    }
	                },
	                {
	                    "title": "Kielitaito",
	                    "description": "Suomi, Englanti, Ruotsi",
	                    "value": {
	                        "type": "status",
	                        "value": 1
	                    }
	                },
	                {
	                    "title": "Työskentely ulkomailla",
	                    "value": {
	                        "type": "status",
	                        "value": 0
	                    }
	                }
	            ]
	        },      
	        "jobcard": {
	            "id": 1234,
	            "title": "Karttatuotepäällikkö",
	            "summary": "Tekninen ja ympäristötoimi luo päivittäisellä työllään lorem ipsum dolor sit amet",
	            "description": "Tekninen ja ympäristötoimi luo päivittäisellä työllään lorem ipsum dolor sit amet asu luo päivittäisellä työllään lorem ipsum dolor sit amet asu luo päivittäisellä työllään lorem ipsum dolor sit amet asu" 
	        },

	        "companycard": {
	            "id": 12355,
	            "title": "Espoon kaupunki",
	            "logo": "http://testipenkki.esmes.fi/mzr/espoo.png",
	            "logoShape": "",
	            "color": "#2a344d",
	            "highlightColor": "#ffffff",
	            "backgroundColor": "#06b6f1",
	            "promotionScore": 0.89		          
	        },

	        "progress": {
	            "progress": 0.7,
	            "title": "Sinut on valittu jatkoon!",
	            "action": {
	                "id": 12333,
	                "type": "test",
	                "title": "Tee testi",
	                "description": "Sinulta puuttuu vaadittu kielitaitotesti"
	            }
	        }
	    }';


		$jobs = json_decode($example);

		$jobTitles = array(
			"Karttatuotepäällikkö",
			"Johtaja",
			"Palvelupäällikkö",
			"Myyntijohtaja",
			"Toimitusjohtaja",
			"Asiakaspalvelupäällikkö",
			"Johtaja, merikeskuksen päällikkö",
			"Johtaja, kalustealan tki-yksikkö",
			"Chef, informations- och kommunikationsteknik (IKT)",
			"Elokuvateatterin Päällikkö, Finnkino Oulun Plaza"
		);

		$colors = array(
			"#06b6f1",
			"#1abc9c",
			"#2ecc71",
			"#9b59b6",
			"#e67e22",
			"#e74c3c",
			"#95a5a6"
		);

		$realAds = ExampleAd::all();

		foreach($realAds as $ad) {
			$jsonAd = json_decode($ad->json_ad);
			$exampleJob = json_decode($single);
			$exampleJob->id = $ad->id;
			$exampleJob->jobcard->title = $ad->title;
			$exampleJob->jobcard->description = nl2br($jsonAd->kuvausteksti);
			$exampleJob->match->percentage = (rand(20, 99) / 100);
			$exampleJob->companycard->backgroundColor = $colors[rand(0, (count($colors) - 1))];
			$jobs->data[] = $exampleJob;
		}

		if(!empty($param)) {
			if(is_numeric($param) && isset($jobs->data[$param - 1])) {
				$response = $jobs->data[$param - 1];
			}
		} else {
			$response = $jobs;
		}
		
		return Response::json($response);
	}

	/**
	 * Generate uuid
	 *
	 * This should be moved to more generic place, if uuid will be videly used.
	 *
	 * @return string
	 */	
	private function genUuid() {
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
}