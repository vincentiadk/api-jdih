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
        $this->url = "http://demo321.online/ISBN_API/Restful.aspx";
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
        $number = $this->argument('number') ?? $this->ask('Enter max number of header you want to update');
        $sql = "SELECT AD.AUTH_HEADER_ID FROM AUTH_DATA AD JOIN AUTH_HEADER AH ON AD.AUTH_HEADER_ID = AH.ID WHERE AD.tag='100' ";
        $sql .="AND AH.CREATEDATE <= to_date('06-17-2024 08:00:00', 'mm-dd-yyyy hh24:mi:ss') AND rownum <= $number";
        $sql .=" GROUP BY AD.AUTH_HEADER_ID";
        $datas = Http::get($this->url, [
                    "token" => $this->token,
                    "op" => "getlistraw",
                    "sql" => $sql,
                ])->json()["Data"]["Items"];
    
        $datauser = [
            [
                "user" => "magangauthority1", 
                "terminal" => "192.168.1.77"
            ],
            [
                "user" => "magangauthority2", 
                "terminal" => "192.168.1.86"
            ],
            [
                "user" => "magangauthority3", 
                "terminal" => "192.168.1.83"
            ],
            [
                "user" => "magangauthority4", 
                "terminal" => "192.168.1.46"
            ],
            [
                "user" => "magangauthority5", 
                "terminal" => "192.168.1.59"
            ],
            [
                "user" => "magangauthority6", 
                "terminal" => "192.168.1.109"
            ],
            [
                "user" => "magangauthority7", 
                "terminal" => "192.168.1.146"
            ],
            [
                "user" => "magangauthority8", 
                "terminal" => "192.168.1.187"
            ],
            [
                "user" => "magangauthority9", 
                "terminal" => "192.168.1.180"
            ],
            [
                "user" => "magangauthority10", 
                "terminal" => "192.168.1.209"
            ],
        ];
        $i = 1;
        foreach($datas as $d){
            $user = $datauser[random_int(0,9)];
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
            $out->writeln($i . " " . $response['Message'] . " ID => " . $d['AUTH_HEADER_ID'] . " User => " . $user['user']);
            $i++;
        }
        
    }

    public function getValidateDate($user)
    {
        $lastCreateDate = Http::get($this->url, [
            "token" => $this->token,
            "table" => "AUTH_HEADER",
            "op" => "getlistraw",
            "sql" => "SELECT max(CREATEDATE) CREATEDATE FROM AUTH_HEADER WHERE CREATEBY = '".$user."' AND rownum=1 GROUP BY CREATEDATE ORDER BY CREATEDATE DESC"
        ])->json()["Data"]["Items"];
        $lastCreateDate_ = '';
        if(count($lastCreateDate) == 0){
            $lastCreateDate_ = '6/17/2024 08:00:00 AM';
        } else {
            $lastCreateDate_ = $lastCreateDate[0]["CREATEDATE"];
        }
        $dateCreated = Carbon::createFromFormat('m/d/Y h:i:s A', $lastCreateDate_)->addSeconds(random_int(180,300));
        $time = $dateCreated->format('h:i:s A');
        $start = '08:00:00 AM';
        $end = '05:00:00 PM';
        if ($time >= $start && $time <= $end) {
            $return = $dateCreated->format('Y-m-d H:i:s');
            return $return;
        } else {
            $newDate = $dateCreated->addWeekdays(1)->format('m/d/Y') . ' 08:00:00 AM';
            $nDate = Carbon::createFromFormat('m/d/Y h:i:s A',$newDate)->addSeconds(random_int(180,300));
            $return = $nDate->format('Y-m-d H:i:s');
            return $return;
        }
    }

}