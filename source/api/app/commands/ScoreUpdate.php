<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ScoreUpdate extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:scoreupdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update test scores';

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
        $client = new SoapClient("http://www.cut-e.net/maptq/ws/ws.asmx?WSDL");
        $config = Config::get("services.cut-e");
        $tests = UserTest::where("status", "=", UserTest::TEST_PAID)->whereNotNull("score_key")->where("instrument_id", "=", 102)->get();
        $getScoresXmlParams = array(
            "clientid" => $config["clientId"],
            "projectid" => $config["projectId"],
            "instrumentid" => "102",
            "normsetid" => "1000",
            "securecode" => $config["secureCode"],
        );

        $profiles = Profile::all();

        foreach($tests as $test)
        {
            $getScoresXmlParams["candidateid"] = User::candidateId($test->user_id);
            $getScoresXmlParams["encodedscore"] = $test->score_key;
            foreach($profiles as $profile)
            {
                $getScoresXmlParams["jobid"] = $profile->code;
                $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                $score = simplexml_load_string($scoreCall->getScoresXmlResult->any);

                if(!isset($score->error))
                {
                    UserProfile::where('user_id', '=', $test->user_id)->where('profile', '=', $profile->code)->delete();

                    $prof = new UserProfile();
                    $prof->user_id = $test->user_id;
                    $prof->profile = $profile->code;
                    $prof->score = $score->result->{'risk-index'};
                    $prof->save();
                    echo ".";
                }
                else
                {
                    Log::warning('Script scoreupdate: User <'. $test->user_id .'> profile score failed! ' . $profile->code . ': ' . (string) $score->error);
                }
            }
        }
        echo "\n\n";
        /*
        $runWSxmlParams = array(
            "reportid" => "102009",
            "clientid" => "3389",
            "projectid" => "57862",
            "candidateid" => "mazhr-1",
            "instrumentid" => "102",
            "languageid" => "17",
            "normsetid" => "1000",
            "firstname" => "Testi",
            "lastname" => "Testi",
            "genderid" => "1",
            "securecode" => "6a1eee870cdb32ebf0550a32da6fe01b",
            "requesttype" => "rep"
        );

        $pdfScoreCall = $client->__call('runWSxml', array($runWSxmlParams));
        $pdfScore = simplexml_load_string($pdfScoreCall->runWSxmlResult->any);
        $scoreUrl = (string) $pdfScore->result;
        echo $scoreUrl . "\n\n";

        $getScoresXmlParams = array(
            "clientid" => "3389",
            "projectid" => "57862",
            "candidateid" => "mazhr-1",
            "instrumentid" => "102",
            "normsetid" => "1000",
            "encodedscore" => urlencode("z8Y/Q+9e6eyYF2I5q8jDwZwpsTBk96QbAuhoWbo3SIdUnKHtrxUYre71D1n/SJIIgOVHyLU/SMieHwZdLeQL48jK0TOSUFGwNvlsk68zwx+b1yIqV26MaAHulShz1O/jmLy8rDMI87+eaG3BSX8Z9w=="),
            "securecode" => "6a1eee870cdb32ebf0550a32da6fe01b",
        );

        // shape test scores create the matches

        $profiles = array(
            "16584",
            "geti_15476",
            "geti_12701",
            "16580",
            "geti_16635",
            "geti_15842",
            "geti_15841",
            "geti_15853",
            "geti_15843",
            "geti_12455",
            "16781",
            "geti_15474",
            "geti_15479",
            "geti_15847",
            "geti_15444",
            "geti_15831",
            "geti_14716",
            "geti_15832",
            "16354",
            "geti_12700",
            "geti_12453",
            "geti_15851",
            "16353",
            "geti_15445",
            "geti_15844",
            "geti_15449",
            "geti_15447",
            "geti_15453",
            "geti_12451",
            "geti_15477",
            "geti_15840",
            "geti_13366",
            "16588",
            "geti_15828",
            "geti_15478",
            "geti_12698",
            "geti_14714",
            "geti_15443",
            "geti_12699",
            "16680",
            "16583",
            "16582",
            "geti_15850",
            "geti_15839",
            "18722",
            "18723",
            "18724",
            "18726",
            "18730",
            "18732",
            "18733",
            "18734",
            "18742",
            "18757",
            "18758",
            "18771"
        );

        try
        {
            foreach($profiles as $profile)
            {

                $getScoresXmlParams["jobid"] = $profile;
                $scoreCall = $client->__call("getScoresXml", array($getScoresXmlParams));
                $score = simplexml_load_string($scoreCall->getScoresXmlResult->any);
                $error = "";
                if(isset($score->error)) $error =  (string) $score->error;
                echo $profile .":" . (string) $score->result->{'risk-index'} . $error . "\n";;

            }
        }
        catch (Exception $e)
        {
            echo $e->getMessage();            
        }
        */
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        
        return array(
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
