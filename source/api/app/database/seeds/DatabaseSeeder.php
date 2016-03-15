<?php

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Eloquent::unguard();
		$this->call('ProfileSeeder');
	}	
}

class ProfileSeeder extends Seeder {
	
	/**
	 * Seed the profiles and profile_professioncodes with example data
	 *
	 * @return void
	 */

    public function run()
    {
        DB::table('profiles')->delete();
        $exampleProfiles = array(
			'Generic Customer care agent' 			=> '12820_gen',
			'Generic Account Manager' 				=> '12821_gen',
			'Generic Brand manager' 				=> '12822_gen',
			'Generic Development director' 			=> '12823_gen',
			'Generic HR koordinaattori' 			=> '12824_gen',
			'Generic Salesman' 						=> '12825_gen',
			'Generic Senior Consultant (Technical)' => '12826_gen',
        );
        foreach ($exampleProfiles as $name => $code) {
        	Profile::create(array(
        		'name' => $name, 
        		'code' => $code
        	));
        }
/*
			{'id': 1,	'title':'Johtaja'},
			{'id': 21,	'title':'Tekniikan ja luonnontieteiden erityisasiantuntija'},
			{'id': 22,	'title':'Terveydenhuollon erityisasiantuntija'},
			{'id': 23,	'title':'Opetusalan erityisasiantuntija'},
			{'id': 24,	'title':'Liike-elämän ja hallinnon erityisasiantuntija'},
			{'id': 25,	'title':'Tieto- ja viestintäteknologian erityisasiantuntija'},
			{'id': 261,	'title':'Lainopillinen erityisasiantuntija'},
			{'id': 262,	'title':'Kirjastonhoitaja, arkistonhoitaja tai museoalan erityisasiantuntija'},
			{'id': 263,	'title':'Yhteiskunta- ja sosiaalialan tai uskonnollisen elämän erityisasiantuntija'},
			{'id': 264,	'title':'Toimittaja, kirjailija tai kielitieteilijä'},
			{'id': 265,	'title':'Taiteilija'},
			{'id': 3,	'title':'Asiantuntija'},
			{'id': 32,	'title':'Terveydenhuollon ja hoivapalvelujen asiantuntija'},
			{'id': 4,	'title':'Toimistotyöntekijä'},
			{'id': 5,	'title':'Palvelutyöntekijä'},
			{'id': 52,	'title':'Myyntityöntekijä'},
			{'id': 6,	'title':'Maanviljelijä, metsätalouden- tai luontaiselinkeinon harjoittaja'},
			{'id': 7,	'title':'Rakennus-, korjaus- tai valmistustyöntekijä'},
			{'id': 78,	'title':'Sähkö- ja elektroniikka-alan työntekijä'},
			{'id': 8,	'title':'Prosessi- ja kuljetustyöntekijä'},
			{'id': 9,	'title':'Fyysien työn tekijä'},
			{'id': 0,	'title':'Maanpuolustus-, suojelu- tai vartiontityöntekijä'}
*/
        DB::table('profile_professioncodes')->delete();

        $profileProfessionMap = array(
        	'12820_gen' => '5',
        	'12821_gen' => '1',
        	'12822_gen' => '3',
        	'12823_gen' => '1',
        	'12824_gen' => '3',
        	'12825_gen' => '52',
        	'12826_gen' => '25'
        );

        $profiles = Profile::all();

        foreach($profiles as $profile) {
        	ProfileProfessionCode::create(array( 
        		'profile_id' 	=> $profile->id,
        		'code'			=> $profileProfessionMap[$profile->code]
        	));        	
        }
    }
}

class TagSeeder extends Seeder {
	
	/**
	 * Seed the profiles and profile_professioncodes with example data
	 *
	 * @return void
	 */

    public function run()
    {
        DB::table('tags')->delete();
        $tags = array(
        	'PHP',
        	'MySql',
        	'Apache',
        	'Johtaja',
        	'Kansainväinen',
        	'JavaScript',
        	'International',
        	'Leader',
        	'Agile',
        	'Scrum',
        	'Lean',
        	'CSS',
        	'Myynti',
        	'Sales',
        	'Business',
        	'Global',
        	'CTO'
        );
        foreach ($tags as $tag) {
        	Tag::create(array('tag' => $tag));
        }
    }
}