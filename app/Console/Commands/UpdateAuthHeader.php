<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class UpdateAuthHeader extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:auth-header {number?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Auth Header tabel';
    protected $url;
    protected $token;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = "http://192.168.7.170/isbn_api/Restful.aspx";
        //$this->url = "http://demo321.online/ISBN_API/Restful.aspx";
        $this->token = "WWQG9BP0JBCL3QSAW9K75G";
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        try{
            $number = $this->argument('number') ?? $this->ask('Enter max number of header you want to update');
            $sql ="SELECT AD.AUTH_HEADER_ID FROM AUTH_DATA AD JOIN AUTH_HEADER AH ON AD.AUTH_HEADER_ID = AH.ID WHERE AD.tag='100' ";
            $sql .=" AND rownum <= $number AND to_char(AH.CREATEDATE, 'YYYY-MM-DD') < '2024-01-01' ";
            $sql .=" GROUP BY AD.AUTH_HEADER_ID ";
            $response = Http::get($this->url, [
                        "token" => $this->token,
                        "op" => "getlistraw",
                        "sql" => $sql,
                    ])->json();
            $datas = $response["Data"]["Items"];
            $datauser = [
                [
                    "user" => "entryauthority2024_1", 
                    "terminal" => "192.168.1.77"
                ],
                [
                    "user" => "entryauthority2024_2", 
                    "terminal" => "192.168.1.86"
                ],
                [
                    "user" => "entryauthority2024_3", 
                    "terminal" => "192.168.1.83"
                ],
                [
                    "user" => "entryauthority2024_4", 
                    "terminal" => "192.168.1.46"
                ],
                [
                    "user" => "entryauthority2024_5", 
                    "terminal" => "192.168.1.59"
                ],
                [
                    "user" => "entryauthority2024_6", 
                    "terminal" => "192.168.1.109"
                ],
                [
                    "user" => "entryauthority2024_7", 
                    "terminal" => "192.168.1.146"
                ],
                [
                    "user" => "entryauthority2024_8", 
                    "terminal" => "192.168.1.187"
                ],
                [
                    "user" => "entryauthority2024_9", 
                    "terminal" => "192.168.1.180"
                ],
                [
                    "user" => "entryauthority2024_10", 
                    "terminal" => "192.168.1.209"
                ],
                [
                    "user" => "entryauthority2024_11", 
                    "terminal" => "192.168.1.152"
                ],
                [
                    "user" => "entryauthority2024_12", 
                    "terminal" => "192.168.1.155"
                ],
                [
                    "user" => "entryauthority2024_13", 
                    "terminal" => "192.168.1.172"
                ],
                [
                    "user" => "entryauthority2024_14", 
                    "terminal" => "192.168.1.188"
                ],
                [
                    "user" => "entryauthority2024_15", 
                    "terminal" => "192.168.1.202"
                ],
            ];
            $i = 1;
            foreach($datas as $d){
                $user = $datauser[random_int(0,10)];
                $cDate = $this->getValidateDate($user['user']);
                $items =  [ ["name" => 'CREATEBY', "Value"=> $user["user"]],
                            ["name" => 'CREATEDATE', "Value"=> $cDate],
                            ["name" => 'CREATETERMINAL', "Value"=> $user['terminal']],
                            ["name" => 'UPDATEBY', "Value"=> $user["user"]],
                            ["name" => 'UPDATEDATE', "Value"=> $cDate],
                            ["name" => 'UPDATETERMINAL', "Value"=> $user['terminal']]];
                $response = Http::get($this->url, [
                    "token" => $this->token,
                    "op" => "update",
                    "table" => "AUTH_HEADER",
                    "id" => $d["AUTH_HEADER_ID"],
                    "ListUpdateItem"=> json_encode($items)
                ]);

                $out->writeln($i . " " . $response['Message'] . " ID => " . $d['AUTH_HEADER_ID'] . " User => " . $user['user'] . " Date => " . $cDate);
                $i++;
            }
        } catch (\Exception $e){
            $out->writeln(" Error => " . $e->getMessage());
        }
        
    }

    public function getValidateDate($user)
    {
        $lastCreateDate = Http::get($this->url, [
            "token" => $this->token,
            "table" => "AUTH_HEADER",
            "op" => "getlistraw",
            "sql" => "SELECT max(CREATEDATE) CREATEDATE FROM AUTH_HEADER WHERE CREATEBY = '".$user."' GROUP BY CREATEDATE"
        ])->json()["Data"]["Items"];
        //\Log::info($lastCreateDate);
        $lastCreateDate_ = '';
        if(count($lastCreateDate) == 0){
            $lastCreateDate_ = '7/01/2024 08:00:00 AM';       
        } else {
            $lastCreateDate_ = $lastCreateDate[0]["CREATEDATE"];
        }
        $dateCreated = Carbon::createFromFormat('m/d/Y h:i:s A', $lastCreateDate_)->addSeconds(random_int(300,400));
        
        $day =  $dateCreated->format('m/d/Y');

        $start = Carbon::createFromFormat('m/d/Y h:i:s A', $day . ' 08:00:00 AM');
        $end = Carbon::createFromFormat('m/d/Y h:i:s A', $day . ' 04:30:00 PM');
        if ($dateCreated >= $start && $dateCreated <= $end) {
            //\Log::info("time >=start and time <= end === true, time = " . $dateCreated);
            $return = $dateCreated->format('Y-m-d H:i:s');
            return $return;
        } else {
            //\Log::info("time < start and time > end ==== false, time = " . $dateCreated);
            $newDate = $dateCreated->addWeekdays(1)->format('m/d/Y') . ' 08:00:00 AM';
            $nDate = Carbon::createFromFormat('m/d/Y h:i:s A',$newDate)->addSeconds(random_int(300,400));
            $return = $nDate->format('Y-m-d H:i:s');
            return $return;
        }
    }


}