<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GetAds extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'command:getads';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get ads';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */	
	public function fire()
	{

		ini_set('memory_limit', '1000M');
		$fieldsArray = array(
			"ammattikoodi",
			"kuvausteksti",
			"ilmoitusnumero",
			"tyokokemusammattikoodi",
			"ammattiLevel3",
			"tehtavanimi",
			"tyokokemusammatti",
			"tyonantajanNimi",
			"kunta",
			"ilmoituspaivamaara",
			"hakuPaattyy",
			"tyoaikatekstiYhdistetty",
			"tyonKestoKoodi",
			"tyonKesto",
			"tyonKestoTekstiYhdistetty",
			"hakemusOsoitetaan",
			"maakunta",
			"maa",
			"hakuTyosuhdetyyppikoodi",
			"hakuTyoaikakoodi",
			"hakuTyonKestoKoodi",
			"tyonantajanWwwOsoite",
			"lahiosoite",
			"wwwTyonhakulomake",
			"wwwTyonhakulomakeKevytHTML",
			"tyonantajanWwwOsoiteKevytHTML",
			"yhteystiedotKevytHTML",
			"yhteystiedotPuhelin",
			"tyokokemus",
			"tyoAlkaaPaivamaara",
			"tyoAlkaaTekstiYhdistetty",
			"palkkausteksti"
		);
		$fields = "";
		for($i = 0; $i < count($fieldsArray); $i++  )
		{
			$fields .= $i == 0 ? $fieldsArray[$i] : "%2C" . $fieldsArray[$i];
		}
		$json = file_get_contents("http://www.mol.fi/tyopaikat/tyopaikkatiedotus/ws/tyopaikat?lang=fi&hakusana=%20&hakusanakentta=sanahaku&alueet=&ilmoitettuPvm=1&vuokrapaikka=---&start=0&kentat={$fields}&sort=mainAmmattiRivino+asc%2C+tehtavanimi+asc%2C+tyonantajanNimi+asc%2C+viimeinenHakupaivamaara+asc");
		$jobs = json_decode($json);

		// Truncate tables before getting the new ones
		DB::statement('SET FOREIGN_KEY_CHECKS = 0');
		DB::table('ad_profession_codes')->truncate();
		DB::table('ads')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS = 1');
		$adCount = 0;
		foreach($jobs->response->docs as $job) {
			$ad = new Ad;
			$ad->uuid = generateUuid();
			$ad->source = 'MOL';
			$ad->title = $job->tehtavanimi;
			$ad->area = isset($job->maakunta) ? $job->maakunta : "";
			if(isset($job->kunta)) $ad->city = $job->kunta;
			$ad->json_ad = json_encode($job);
			$ad->published = date('Y-m-d H:i:s', strtotime($job->ilmoituspaivamaara));
			$ad->save();
			$adCount++;
			echo ".";
			foreach($job->ammattikoodi as $code) {
				$proCode = new AdProfessionCode;
				$proCode->ad_id = $ad->id;
				$proCode->code = $code;
				$proCode->save(); $ad->save();
			}
		}
		AdCount::create(array('count' => $adCount));
		Ad::profileUpdate();
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}
}
