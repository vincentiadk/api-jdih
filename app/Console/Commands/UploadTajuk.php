<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class UploadTajuk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:tajuk {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload data tajuk';
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
        $this->url = config('tajuk.url');
        $this->token = config('tajuk.token');
    }

    public function handle()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $file = $this->argument('file') ?? $this->ask('Enter file name to upload:');
        $lines = File::lines(storage_path("app/$file"));
        $id_katalog = ""; $id_usulan = "";

        foreach($lines as $line){
            $auth_data = explode("^", $line);
            if(count($auth_data) == 1) {
                $auth_data = explode("+", $line);
            }
            $auth_data_to_update = [];
            $i = 0;
            $to_update = true;
            foreach($auth_data as $auth_data_detail){
                if($i == 0){
                    $istilah_digunakan = explode("|", $auth_data_detail);
                    $id_katalog = $istilah_digunakan[0];
                    $id_usulan = $istilah_digunakan[1];
                }
                if($i > 0) {
                    $istilah_digunakan = explode("|", $auth_data_detail);
                    if($istilah_digunakan[0] != "000"){
                        array_push($auth_data_to_update, [
                            'tag' => $istilah_digunakan[0],
                            'indikator1' => $istilah_digunakan[1],
                            'indikator2' => $istilah_digunakan[2],
                            'value' => $istilah_digunakan[3],
                        ]);
                    } else {
                        $to_update = false;
                    }
                }
                $i += 1;
            }
            if($to_update){
                $response = Http::withToken($this->token)
                    ->post($this->url . "/authority/save/single", [
                        'id_catalog' => intval($id_katalog),
                        'id_usulan' => intval($id_usulan),
                        'data_tag' => $auth_data_to_update
                    ]);
                $out->writeln($response);
            }
        }
    }
}