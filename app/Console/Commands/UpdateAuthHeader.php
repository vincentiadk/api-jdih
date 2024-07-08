<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Carbon\Carbon;
use Exception;

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
    public function handle()
    {
        $number = $this->argument('number') ?? $this->ask('Enter number of header you want to update:') ;
        $datas = DB::connection('inlis')->table('AUTH_HEADER')->whereNull('VALIDATEBY')->take($number)->get();
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
        $user = $datauser[random_int(0,9)];
        foreach($datas as $d){
            $this->line('ID = ' . $d->ID);
            DB::connection('inlis')
                ->table('AUTH_HEADER')
                ->where('ID', $d->ID)
                ->update([
                    'VALIDATEBY' => $user["user"],
                    'VALIDATEDATE' => $this->getValidate($user['user']),
                    'VALIDATETERMINAL' => $user['terminal']
                ]);
        }
        
    }

    public function getValidateDate($user)
    {
        $last =  DB::connection('inlis')
                    ->table('AUTH_HEADER')
                    ->whereRaw(DB::connection('inlis')->raw('VALIDATEDATE = (SELECT max(VALIDATEDATE) FROM AUTH_HEADER WHERE VALIDATEBY = "'.$user.'" GROUP BY VALIDATEDATE ORDER BY VALIDATEDATE DESC LIMIT 1)'))
                    ->get()
                    ->first();
        $lastC_ = '';
        if($last == null){
            $last_ = '2024-06-17 08:00:00';
        } else {
            $last_ = $last->VALIDATEDATE;
        }
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $last_)->addSeconds(random_int(180,300));
        $time = $date->format('H:i:s');
        $start = '08:00:00';
        $end = '17:00:00';
        if ($time >= $start && $time <= $end) {
            return $date->toDateTimeString();
        } else {
            $newDate = $date->addWeekdays(1)->format('Y-m-d') . ' 08:00:00';
            $nDate = Carbon::createFromFormat('Y-m-d H:i:s',$newDate)->addSeconds(random_int(180,300));
            return $nDate->toDateTimeString();
        }
    }

}