<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class PointsUpdate extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:pointsupdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update behaviour/competence/motivational points for users';

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
     // 102 - behaviour
     // 102 + mapping_id = 3 - competence
     // 201 - motivational
    public function fire()
    {
        $instrument_id = $this->argument('instrument');
        $mapping_id = $this->argument('mapping'); // optional, 3 for competence points
        $client = new SoapClient("http://www.cut-e.net/maptq/ws/ws.asmx?WSDL");
        $config = Config::get("services.cut-e");
        $tests = UserTest::where("status", "=", UserTest::TEST_PAID)->whereNotNull("score_key")->where("instrument_id", "=", 102)->get();
        $getScoresXmlParams = array(
            "clientid" => $config["clientId"],
            "projectid" => $config["projectId"],
            "instrumentid" => $instrument_id,
            "languageid" => "17",
            "normsetid" => "1000",
            "mappingid" => $mapping_id,
            "securecode" => $config["secureCode"],
        );


        foreach($tests as $test)
        {

            $getScoresXmlParams["candidateid"] = User::candidateId($test->user_id);
            $getScoresXmlParams["encodedscore"] = $test->score_key;
            $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));

            //var_dump($scoreCall);
            $score = simplexml_load_string($scoreCall->getScoresXmlResult->any);

            if(!isset($score->error))
            {
                $details = array();

                foreach ($score->names->children() as $name) {
                  $scale = $name->getName();
                  $details[] = array('key' => $name->__toString(), 'value' => $score->result->{$scale}->__toString());
                }

                $user = User::where('id', '=', $test->user_id)->first();

                $points_group = 'behaviour';

                if (isset($getScoresXmlParams['mappingid']) && $getScoresXmlParams['mappingid'] == '3' && $getScoresXmlParams['instrumentid'] == "102") {
                  $points_group = 'competence';
                } else if ($getScoresXmlParams['instrumentid'] == "201") {
                  $points_group = 'motivation';
                }


                var_dump(json_encode($details));

                $user->{$points_group . '_points'} = json_encode($details);
                $user->save();

                echo "\n";
                echo ".";
            }
            else
            {
                echo "Error\n";
                echo $score->error;
                echo "\n";
                Log::warning('Script pointsupdate: User <'. $test->user_id .'> failed: ' . (string) $score->error);
            }
        }
        echo "\n\n";
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {

        return array(
          ['instrument'],
          ['mapping']
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
        );

    }

}
