<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class Check670 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tajuk:check670 {file?} {type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check tag 670 from data upload';
    protected $url;
    protected $token;
    protected $url_tajuk;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = config('tajuk.url_inlis');
        $this->token = config('tajuk.token_inlis');
        $this->url_tajuk = config('tajuk.url');
        $this->token_tajuk = config('tajuk.token');
    }

    public function handle()
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $file = $this->argument('file') ?? $this->ask('Enter file name to check tag 670');
        $type = $this->argument('type') ?? $this->ask('Enter type file tajuk to check tag 670');
        
        if($file == "database"){
            $sql = urlencode("SELECT AUTH_DATA.*, TRIM(SUBSTR(AUTH_DATA.VALUE, INSTR(AUTH_DATA.VALUE, '\$w') + 2)) BIBID  from AUTH_HEADER JOIN AUTH_DATA ON AUTH_DATA.AUTH_HEADER_ID = AUTH_HEADER.ID WHERE  AUTH_HEADER.createby like 'entryautho%' AND AUTH_HEADER.AUTH_ID is null AND rownum <=10000");
            $getData = Http::get($this->url, [
                "token" => $this->token,
                "op" => "getlistraw",
                "sql" => "SELECT AUTH_DATA.*, TRIM(SUBSTR(AUTH_DATA.VALUE, INSTR(AUTH_DATA.VALUE, '\$w') + 2)) BIBID  from AUTH_HEADER JOIN AUTH_DATA ON AUTH_DATA.AUTH_HEADER_ID = AUTH_HEADER.ID WHERE  AUTH_HEADER.createby like 'entryautho%' AND AUTH_HEADER.AUTH_ID is null AND rownum <=10000"
                ])["Data"]["Items"];
            $lines = $this->_group_by($getData, 'AUTH_HEADER_ID');
            $j = 0;
            foreach($lines as $line){
                $id_catalogs =  $getData = Http::get($this->url, [
                    "token" => $this->token,
                    "op" => "getlistraw",
                    "sql" => "SELECT * FROM AUTH_CATALOG WHERE AUTH_HEADER_ID = $line[0]['AUTH_HEADER_ID']"
                    ])["Data"]["Items"];
                if(count($id_catalogs) > 0){
                    foreach($id_catalogs as $id_catalog) {
                        $this->addTag670Database($line, $id_catalog,$j); 
                    }
                }
                $j++;
            }
        } else {
            $lines = File::lines(storage_path("app/$file"));
            $id_katalog = ""; $id_usulan = "";
            $j = 1;
            foreach($lines as $line){
                try{
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
                                    break;
                                }
                            }
                            $i += 1;
                        }
                        if($to_update && count($auth_data) > 1){
                            $this->addTag670($auth_data_to_update, $id_katalog, $j); //add tag 670
                            $j++;
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
                        if($to_update && count($auth_data) > 1){ //kalau cuma spasi tidak ada isinya
                            $authors = $this->_group_by($auth_data_to_update, 'orang_ke');
                            $all = $authors[""];
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
                                    $this->addTag670($author, $id_katalog,$j); //add tag 670
                                    $j++;
                                }
                                    
                            }
                        }  
                    } 
                } catch (\Exception $e){
                    $out->writeln($e->getMessage());
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

    public function addTag670($author, $id_katalog, $i)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
       
        $key_670 = array_search('670', array_column($author, 'tag')); //cari letak tag 670
        $key_100 = array_search('100', array_column($author, 'tag')); //cari letak tag 100
        if(is_numeric($key_100)){
            $tag100 = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h','$z','$w', '$y', '$g'], '',$author[$key_100]["value"])); 
        } 
        if(is_numeric($key_670)){
            $tag670 = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h', '$z','$w', '$y', '$g'], '',$author[$key_670]["value"])); 
        }
        $getid = $this->getId($author[$key_100]["value"]);
        if(isset($getid[0]['ID'])) {
            $id= $getid[0]['ID'];
            $sql = "SELECT AUTH_HEADER_ID, AUTH_HEADER.ISTILAH_DIGUNAKAN, AUTH_DATA.VALUE, TRIM(SUBSTR(AUTH_DATA.VALUE, INSTR(AUTH_DATA.VALUE, '\$w') + 2)) BIBID FROM AUTH_HEADER ";
            $sql .= "LEFT JOIN AUTH_DATA ON AUTH_DATA.AUTH_HEADER_ID = AUTH_HEADER.ID WHERE AUTH_DATA.TAG = '670' AND AUTH_DATA.AUTH_HEADER_ID = '$id'";
            $auth_headers = Http::get($this->url ."?token=$this->token&op=getlistraw&sql=".urlencode($sql))["Data"]["Items"];
            if(count($auth_headers) == 0){
                $res = Http::get($this->url,[ 
                    "token" => $this->token,
                    "table" => "AUTH_DATA",
                    "op" => "add",
                    "ListAddItem" => json_encode([ 
                            ["name"=>'TAG', "Value" => "670"],
                            ["name"=>'INDICATOR1', "Value" =>$author[$key_670]["indikator1"]],
                            ["name"=>'INDICATOR2',"Value" => $author[$key_670]["indikator2"]],
                            ["name"=>'VALUE', "Value" => $author[$key_670]["value"]],
                            ["name"=>'DATAITEM', "Value" => $tag670],
                            ["name"=>'AUTH_HEADER_ID', "Value" => $id]
                        ])
                ]);
                $out->writeln($i . " Menambahkan tag 670 pada AUTH_DATA, AUTH_HEADER_ID = $id, ID = " . $res["Data"]["ID"] );
            } else {
                $bibid_catalog =  Http::get($this->url, [
                    "token" => $this->token,
                    "op" => "getlistraw",
                    "sql" => "SELECT BIBID FROM CATALOGS WHERE ID = '$id_katalog'"
                    ])["Data"]["Items"][0]['BIBID'];
                $bib_id_exists = array_search($bibid_catalog, array_column($auth_headers, 'BIBID'));
                if($bib_id_exists === false) { //check apakah ada tag 670 dengan bib id tersebut di auth_data, jika ada bib_id_exists is_numeric, jika tidak ada false
                    $res3 = Http::get($this->url,[ 
                    "token" => $this->token,
                    "table" => "AUTH_DATA",
                    "op" => "add",
                    "ListAddItem" => json_encode([ 
                                    ["name"=>'TAG', "Value" => "670"],
                                    ["name"=>'INDICATOR1', "Value" => $author[$key_670]["indikator1"]],
                                    ["name"=>'INDICATOR2',"Value" => $author[$key_670]["indikator2"]],
                                    ["name"=>'VALUE', "Value" => $author[$key_670]["value"]],
                                    ["name"=>'DATAITEM', "Value" => $tag670],
                                    ["name"=>'AUTH_HEADER_ID', "Value" => $id]
                                ])
                    ]);
                    $out->writeln($i . " Menambahkan tag 670 pada AUTH_DATA, AUTH_HEADER_ID = $id, ID = " . $res3["Data"]["ID"] );
                } else {
                    $out->writeln($i . " $bibid_catalog pada AUTH_DATA sudah ada, AUTH_HEADER_ID = $id, tajuk = " . $tag100 );
                }
            }
            $sql2 = "SELECT COUNT(*) JUMLAH FROM AUTH_CATALOG WHERE AUTH_HEADER_ID = '$id' AND CATALOG_ID = '$id_katalog'";
            $countCatID = Http::get($this->url ."?token=$this->token&op=getlistraw&sql=".$sql2)["Data"]["Items"][0]["JUMLAH"];
            //\Log::info($countCatID);
            if(intval($countCatID) == 0){
               // \Log::info("countCat kurang dari 1 ".$countCatID);
                $res2 = Http::get($this->url,[ 
                        "token" => $this->token,
                        "table" => "AUTH_CATALOG",
                        "op" => "add",
                        "ListAddItem" => json_encode([ 
                                ["name"=>'CATALOG_ID', "Value" => $id_katalog],
                                ["name"=>'AUTH_HEADER_ID', "Value" =>$id],
                        ])
                ]);
                //\Log::info($res2);
                $out->writeln($i . " Menambahkan CATALOG_ID=$id_katalog AUTH_HEADER_ID =$id pada AUTH_CATALOG, ID = " . $res2["Data"]["ID"]);
            }
        } else {
            $response = Http::withToken($this->token_tajuk)
                        ->post($this->url_tajuk . "/authority/save/single", [
                            'id_catalog' => intval($id_katalog),
                            'id_usulan' => 0,
                            'data_tag' => $author,
                            'date_lembur' => ''
                    ]);
            if($response["status"] == "Failed"){
                $out->writeln($response["err"]);
            } else {
                $out->writeln($response["message"]);
            }
        }
    }

    public function addTag670Database($author, $id_katalog, $i)
    {
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        
        $bibids = array_search('BIBID', array_column($author, 'BIBID')); //dapatkan semua bib id yang sudah ada di auth_data
        $id = $author[0]['AUTH_HEADER_ID'];
        $sql = "SELECT BIBID, SUBSTR(CATALOGS.TITLE, 1, Instr(CATALOGS.TITLE, '/', -1, 1) -1) TITLE FROM CATALOGS WHERE ID=$id_katalog";
        $catalogs = Http::get($this->url ."?token=$this->token&op=getlistraw&sql=".urlencode($sql))["Data"]["Items"][0];
        $bibid_catalog =  $catalogs['BIBID'];
        $is_exists = false;
        foreach($bibids as $bibid_auth_data){
            if($bibid_auth_data == $bibid_catalog){
                $is_exists = true;
                break;
            }
        } 
        if($is_exists == false){
            
            $res3 = Http::get($this->url,[ 
                "token" => $this->token,
                "table" => "AUTH_DATA",
                "op" => "add",
                "ListAddItem" => json_encode([ 
                                    ["name"=>'TAG', "Value" => "670"],
                                    ["name"=>'INDICATOR1', "Value" => '#'],
                                    ["name"=>'INDICATOR2',"Value" => '#'],
                                    ["name"=>'VALUE', "Value" => '$a '. $catalogs['TITLE']. ' $w' . $catalogs['BIBID']],
                                    ["name"=>'DATAITEM', "Value" => $catalogs['TITLE'] ." ". $catalogs['BIBID'] ],
                                    ["name"=>'AUTH_HEADER_ID', "Value" => $id]
                    ])
                ]);
            $out->writeln($i . " Menambahkan tag 670 pada AUTH_DATA, AUTH_HEADER_ID = $id, ID = " . $res3["Data"]["ID"] );
        } else {
            $out->writeln($i . " $bibid_catalog pada AUTH_DATA sudah ada, AUTH_HEADER_ID = $id, tajuk = " . $tag100 );
        }
    }
    public function getID($data)
    {
        $dataCheck = $data[0];
        $data_item = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h','$q', '$z','$w', '$y', '$g'], '', $data));
        
        $res = Http::get($this->url, [
            "token" => $this->token,
            "table" => "AUTH_DATA",
            "op" => "getlistraw",
            "sql" => "SELECT AUTH_HEADER_ID ID FROM AUTH_DATA WHERE DATAITEM ='".$data_item."' AND (TAG ='100' OR TAG = '400')",
        ]);
        return $res["Data"]["Items"];//[0]["AUTH_HEADER_ID"]);
    }
}