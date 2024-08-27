<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class UploadTajuk2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:tajuk2 {file?} {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload data tajuk ke demo';
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
        $type = $this->argument('type') ?? $this->ask('Enter type file tajuk to upload:');
        $lines = File::lines(storage_path("app/$file"));
        $id_katalog = ""; $id_usulan = "";

        foreach($lines as $line){
            switch($type){
                case 1: $auth_data = explode("^", $line);
                        break;
                case 2: $auth_data = explode("+", $line);
                    break;
                case 3: $auth_data = explode("^", $line);
                    break;
            }
            
            $auth_data_to_update = [];
            $i = 0;
            $to_update = true;
            if($type == 1 || $type == 2) {
                foreach($auth_data as $auth_data_detail){
                    if($i == 0){
                        $istilah_digunakan = explode("|", $auth_data_detail);
                        $id_katalog = $istilah_digunakan[1];
                        $id_usulan = $istilah_digunakan[0];
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
                    $out->writeln($response["message"]);
                }
            } else {
                foreach($auth_data as $auth_data_detail){
                    if($i == 0){
                        $istilah_digunakan = explode("|", $auth_data_detail);
                        $id_katalog = $istilah_digunakan[1];
                        $id_usulan = $istilah_digunakan[2];
                    }
                    if($i > 0) {
                        $istilah_digunakan = explode("|", $auth_data_detail);
                        if($istilah_digunakan[0] != "000"){ // kalau tidak skip
                            array_push($auth_data_to_update, [
                                'tag' => $istilah_digunakan[0],
                                'indikator1' => $istilah_digunakan[1],
                                'indikator2' => $istilah_digunakan[2],
                                'value' => $istilah_digunakan[3],
                                'orang_ke' => $istilah_digunakan[4]? $istilah_digunakan[4] : ''
                            ]);
                        } else {
                            $to_update = false;
                        }
                    }
                    $i += 1;
                }
                if($to_update){
                    $authors = $this->_group_by($auth_data_to_update, 'orang_ke');
                    $all = $authors[""];
                    $authors_update = [];
                    foreach($authors as $author){
                        if($author[0]['orang_ke'] != ''){
                            foreach($all as $d){
                                    array_push($author, [
                                                'tag' => $d["tag"],
                                                'indikator1' => $d["indikator1"],
                                                'indikator2' => $d["indikator2"],
                                                'value' => $d["value"],
                                                'orang_ke' => ''
                                ]);
                            }
                            $response = Http::withToken($this->token)
                                            ->post($this->url . "/authority/save/single", [
                                                'id_catalog' => intval($id_katalog),
                                                'id_usulan' => intval($id_usulan),
                                                'data_tag' => $author
                                        ]);
                            $out->writeln($response["message"]);
                            
                            \Log::info($author);
                        }
                     }  
                }
            }
        }
    }

    public function _group_by($array, $key)
    {
        $return = array();
        foreach($array as $val) {
            $return[$val[$key]][] = $val;
        }
        return $return;
    }
}