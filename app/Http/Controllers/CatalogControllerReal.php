<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

/**
  * @group Catalog INLIS
*/
class CatalogControllerReal extends Controller
{
    protected $url;
    protected $token;

    public function __construct() 
    {
        $this->url = config('tajuk.url_inlis');
        $this->token = config('tajuk.token_inlis');
    }

    public function getCatalog(Request $request)
    {
        $data = DB::connection('inlis')
            ->table('CATALOG_RUAS_FIX')
            ->select('Catalogid', 'Tag', 'Indicator1', 'Indicator2', 'Value')
            ->where('catalogid', $request->input('ID'))
            ->get();
        return response()->json(
            [
                "Data" => $data,
            ]
        );
    }

    public function searchAuthHeader(Request $request)
    {
        try{
            $filter = [
                [ "name"=>"ISTILAH_DIGUNAKAN", "Value"=> $request->input('q'), "SearchType"=>"SalahSatuIsi" ],
                [ "name"=>"ISTILAH_TDK_DIGUNAKAN", "Value"=>$request->input('q'), "SearchType"=>"SalahSatuIsi" ]
            ];
            $res = Http::get($this->url, [
                "token" => $this->token,
                "table" => "AUTH_HEADER",
                "op" => "getlist",
                "PageNumber" => 1,
                "MaxItemPerPage" => 20,
                "KriteriaFilter" => json_encode($filter)
            ]);
            $response = $res->json();
            if($response["Status"] == "Success") {
                return response()->json(
                    [
                        "Status" => "Success",
                        "Search" => $request->input('q'),
                        "Data" => $response["Data"]["Items"],
                        "Message" => $response["Message"]
                    ]
                );
            } else {
                return response()->json([
                    "Status" => "Error",
                    "Search" => $request->input('q'),
                    "Message" => $response["Message"]
                ], 500);
            }
        } catch (\Exception $e){
            return response()->json([
                'message'   => 'Failed Search Authority. Server Error',
                'err'       => $e->getMessage(),
                'status'    => 'Failed'
            ], 500);
        }
    }
    public function checkHeader($data)
    {
        $dataCheck = $data[0];
        $data_item = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h','$q', '$z','$w', '$y', '$g'], '', $dataCheck["value"]));
        $res = Http::get($this->url, [
            "token" => $this->token,
            "table" => "AUTH_DATA",
            "op" => "getlistraw",
            "sql" => "SELECT COUNT(*) JML FROM AUTH_DATA WHERE DATAITEM ='".$data_item."' AND (TAG ='100' OR TAG = '400')",
        ]);
        return intval($res["Data"]["Items"][0]["JML"]);
    }
    public function checkHeader2()
    {
        $text = request('check');
        $res = Http::get($this->url, [
            "token" => $this->token,
            "table" => "AUTH_DATA",
            "op" => "getlistraw",
            "sql" => "SELECT COUNT(*) JML FROM AUTH_DATA WHERE DATAITEM ='".$text."' AND (TAG ='100' OR TAG = '400')",
        ]);
        return intval($res["Data"]["Items"][0]["JML"]);
    }

    public function saveAuthoritySingle()
    {
        try {
            $validator = Validator::make(request()->all(), [
                'id_usulan' => 'required|numeric',
                'id_catalog' => 'required|numeric', 
                'data_tag' => 'required',
            ], [
                'id_usulan.required' => 'ID usulan wajib diisi!',
                'id_catalog.required' => 'ID catalog wajib diisi!',
                'data_tag.required' => 'Data tag wajib diisi!',
                'id_usulan.numeric' => 'ID usulan hanya boleh berupa angka!',
                'id_catalog.numeric' => 'ID catalog hanya boleh berupa angka!',
            ]);
            if($validator->fails()){
                return response()->json([
                    'status' => 'Failed',
                    'message'   => 'Failed Save Authority. Validation Error',
                    'err' => $validator->errors(),
                ], 422);
            }
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
            $date_lembur = request('date_lembur');
            $user = $datauser[random_int(0,10)];
            $data_tag = request('data_tag');
            $istilah_digunakan = ''; $istilah_tdk_digunakan = '';
            $create_date_user = $this->getCreateDate($user['user'], $date_lembur);
            if($date_lembur !=null || $date_lembur != ''){
                if($create_date_user == false){
                    $create_date_user = $this->getCreateDate($user['user'], '');
                    $date_lembur = "Melebihi Batas $date_lembur";
                    /*return response()->json(
                        [
                            'status'    => 'Failed',
                            'message'   => 'Failed Save Authority.',
                            "err" => "Batas Waktu Lembur sudah lewat",
                            "skipped" => request('id_usulan'),
                        ], 500);*/
                }
            }
            $auth_data_input = [];
            foreach($data_tag as $auth_data){
                $data_item = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h', '$q', '$z','$w', '$y', '$g'], '', $auth_data["value"]));
                array_push($auth_data_input,[ 
                                        ["name"=>'TAG', "Value" => $auth_data["tag"]],
                                        ["name"=>'INDICATOR1', "Value" => $auth_data["indikator1"]],
                                        ["name"=>'INDICATOR2',"Value" => $auth_data["indikator2"]],
                                        ["name"=>'VALUE', "Value" => trim($auth_data["value"])],
                                        ["name"=>'DATAITEM', "Value" => $data_item],
                ]);
                
                if($auth_data["tag"] == '100'){
                    $istilah_digunakan .= $data_item;
                }
                if($auth_data["tag"] == '400'){
                    if($istilah_tdk_digunakan != "") {
                        $istilah_tdk_digunakan .= " -- ";
                    }
                    $istilah_tdk_digunakan .= $data_item;
                }
            }
            $check_header = $this->checkHeader($data_tag);
            if($check_header == 0){
                $addData = [
                    [ "name"=>"WORKSHEET_ID", "Value"=> 63 ],
                    [ "name"=>"ISTILAH_DIGUNAKAN", "Value"=> $istilah_digunakan ],
                    [ "name"=>"ISTILAH_TDK_DIGUNAKAN", "Value"=> $istilah_tdk_digunakan ],
                    [ "name"=>"CREATEBY", "Value"=> $user["user"] ],
                    [ "name"=>"CREATETERMINAL", "Value"=> $user["terminal"] ],
                    [ "name"=>"CREATEDATE", "Value"=> $create_date_user ],
                    [ "name"=>"UPDATEBY", "Value"=> $user["user"] ],
                    [ "name"=>"UPDATETERMINAL", "Value"=>  $user["terminal"] ],
                    [ "name"=>"UPDATEDATE", "Value"=> $create_date_user ],
                ];
                $res = Http::get($this->url, [
                    "token" => $this->token,
                    "table" => "AUTH_HEADER",
                    "op" => "add",
                    "issavehistory"=> 1,
                    "ListAddItem" => json_encode($addData)
                ]);

                $auth_header_id = $res->json()["Data"]["ID"]; //ambil id yang diinput di auth_header

                foreach($auth_data_input as $auth_to_input){
                    array_push($auth_to_input, ["name"=>'AUTH_HEADER_ID', "Value" => $auth_header_id]);
                    $res = Http::get($this->url,[ 
                        "token" => $this->token,
                        "table" => "AUTH_DATA",
                        "op" => "add",
                        "ListAddItem" => json_encode($auth_to_input)
                    ]);
                }

                $auth_catalog = [
                    ["name"=>'CATALOG_ID', 'Value'=> request('id_catalog')],
                    ["name"=>'AUTH_HEADER_ID', 'Value'=> $auth_header_id],
                ];
                
                Http::get($this->url,[  
                    "token" => $this->token,
                    "table" => "AUTH_CATALOG",
                    "op" => "add",
                    "ListAddItem" => json_encode($auth_catalog)
                ]); //tambah data pada auth_catalog

                return response()->json(
                    [
                        'status'    => 'Success',
                        "message" => "Auth header created '" . $istilah_digunakan . "' with ID=" . $auth_header_id . " tanggal_lembur -1:" .$date_lembur,
                    ]
                );
            } else {
                return response()->json(
                    [
                        'status'    => 'Failed',
                        'message'   => 'Failed Save Authority.',
                        "err" => "Auth header failed " . $data_tag[0]["value"] . " already exists",
                        "skipped" => request('id_usulan'),
                    ], 500);
            }
        } catch (\Exception $e){
			return response()->json([
				'message'   => 'Failed Save Authority. Server Error',
				'err'       => $e->getMessage(),
				'status'    => 'Failed'
			], 500);
		}
    }

    public function saveAuthorityMultiple()
    {
        try {
            $datas = request('data');
            
            if(request('data') == null){
                return response()->json([
                    'status'    => 'Failed',
                    'message'   => 'Failed Save Authority. Validation Error',
                    'err' => 'Parameter "data" wajib diisi dalam bentuk array',
                ], 422);
            }
            $auth_created = []; 
            $auth_skipped = [];
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
            foreach($datas as $data) {
                $validator = Validator::make($data, [
                    'id_usulan' => 'required|numeric',
                    'id_catalog' => 'required|numeric', 
                    'data_tag' => 'required',
                ], [
                    'id_usulan.required' => 'ID usulan wajib diisi!',
                    'id_catalog.required' => 'ID catalog wajib diisi!',
                    'data_tag.required' => 'Data Tag wajib diisi!',
                    'id_usulan.numeric' => 'ID usulan hanya boleh berupa angka!',
                    'id_catalog.numeric' => 'ID catalog hanya boleh berupa angka!',
                ]);
                if($validator->fails()){
                    return response()->json([
                        'status' => 'Failed',
                        'error' => $validator->errors(),
                        'message'   => 'Failed Save Authority. Validation Error',
                    ], 422);
                }
                $date_lembur = request('date_lembur');
                    $user = $datauser[random_int(0,10)];
                    $data_tag = $data['data_tag'];
                    $istilah_digunakan = ''; $istilah_tdk_digunakan = '';
                    $create_date_user = $this->getCreateDate($user['user'], $date_lembur);
                    if($date_lembur !=null || $date_lembur != ''){
                        if($create_date_user == false){
                            $create_date_user = $this->getCreateDate($user['user'], '');
                            $date_lembur = "Melebihi Batas $date_lembur";
                            /*return response()->json(
                                [
                                    'status'    => 'Failed',
                                    'message'   => 'Failed Save Authority.',
                                    "err" => "Batas Waktu Lembur sudah lewat",
                                    "skipped" => request('id_usulan'),
                                ], 500);*/
                        }
                    }
                    $auth_data_input = [];
                    
                    foreach($data_tag as $auth_data){
                        $data_item = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h', '$q','$z','$w', '$y', '$g'], '', $auth_data["value"]));
                        array_push($auth_data_input,[
                                ["name"=>'TAG', "Value" => $auth_data["tag"]],
                                ["name"=>'INDICATOR1', "Value" => $auth_data["indikator1"]],
                                ["name"=>'INDICATOR2',"Value" => $auth_data["indikator2"]],
                                ["name"=>'VALUE', "Value" => trim($auth_data["value"])],
                                ["name"=>'DATAITEM', "Value" => $data_item],
                            ]);
                        if($auth_data["tag"] == '100'){
                            $istilah_digunakan .= $data_item;
                        }
                        if($auth_data["tag"] == '400'){
                            if($istilah_tdk_digunakan != "") {
                                $istilah_tdk_digunakan .= " -- ";
                            }
                            $istilah_tdk_digunakan .= $data_item;
                        }
                    
                    }
                    $check_header = $this->checkHeader($data_tag);
                    if($check_header == 0){
                        $addData = [
                            [ "name"=>"WORKSHEET_ID", "Value"=> 63 ],
                            [ "name"=>"ISTILAH_DIGUNAKAN", "Value"=> $istilah_digunakan ],
                            [ "name"=>"ISTILAH_TDK_DIGUNAKAN", "Value"=> $istilah_tdk_digunakan ],
                            [ "name"=>"CREATEBY", "Value"=> $user["user"] ],
                            [ "name"=>"CREATETERMINAL", "Value"=> $user["terminal"] ],
                            [ "name"=>"CREATEDATE", "Value"=> $create_date_user ],
                            [ "name"=>"UPDATEBY", "Value"=> $user["user"] ],
                            [ "name"=>"UPDATETERMINAL", "Value"=>  $user["terminal"] ],
                            [ "name"=>"UPDATEDATE", "Value"=> $create_date_user ],
                        ];
                        $res = Http::get($this->url, [
                            "token" => $this->token,
                            "table" => "AUTH_HEADER",
                            "op" => "add",
                            "issavehistory"=> 1,
                            "ListAddItem" => json_encode($addData)
                        ]);

                        $auth_header_id = $res->json()["Data"]["ID"]; //ambil id yang diinput di auth_header
                        foreach($auth_data_input as $auth_to_input){
                            unset($auth_to_input[5]);
                            array_push($auth_to_input, ["name"=>'AUTH_HEADER_ID', "Value" => $auth_header_id]);
                            $res = Http::get($this->url,[ 
                                "token" => $this->token,
                                "table" => "AUTH_DATA",
                                "op" => "add",
                                "ListAddItem" => json_encode($auth_to_input)
                            ]);
                        }
                        $auth_catalog = [
                            ["name"=>'CATALOG_ID', 'Value'=> $data['id_catalog']],
                            ["name"=>'AUTH_HEADER_ID', 'Value'=> $auth_header_id],
                        ];
                        Http::get($this->url,[  
                            "token" => $this->token,
                            "table" => "AUTH_CATALOG",
                            "op" => "add",
                            "ListAddItem" => json_encode($auth_catalog)
                        ]); //tambah data pada auth_catalog
        
                        array_push($auth_created,[
                            ["auth_header_id" => $auth_header_id], 
                            ['istilah_digunakan' => $istilah_digunakan]
                        ]);
                    } else {
                        array_push($auth_skipped, [
                            [ "id_usulan" => $data['id_usulan']], 
                            [ "istilah_digunakan" => $istilah_digunakan ]
                        ]);
                    }
                
                $msg = "Created: " . count($auth_created) . "\nSkipped: " . count($auth_skipped);
            }
            return response()->json(
                [
                    "message" => $msg,
                    "skipped" => $auth_skipped,
                    "created" => $auth_created,
                ]
            );
        } catch (\Exception $e){
			return response()->json([
				'message'   => 'Failed Save Authority. Server Error',
				'err'       => $e->getMessage(),
				'status'    => 'Failed'
			], 500);
		}
    }

    public function getCreateDate($user, $date_lembur)
    {
        if($date_lembur != null || $date_lembur != ''){ //ada lembur
            $lastCreateDate = Http::get($this->url, [
                "token" => $this->token,
                "table" => "AUTH_HEADER",
                "op" => "getlistraw",
                "sql" => "SELECT max(CREATEDATE) CREATEDATE FROM AUTH_HEADER WHERE CREATEBY = '".$user."' AND CREATEDATE <= TO_DATE('$date_lembur','YYYY-MM-DD')"
            ])->json()["Data"]["Items"][0]["CREATEDATE"];
            $lastCreateDate_ = '';
            if(($lastCreateDate) == ""){
                $lastCreateDate_ =  Carbon::createFromFormat('Y-m-d h:i:s A', $date_lembur . ' 4:30:00 PM');    
            } else {
                $lastCreateDate_ = $lastCreateDate;
            }
           
        } else {
            $lastCreateDate = Http::get($this->url, [
                "token" => $this->token,
                "table" => "AUTH_HEADER",
                "op" => "getlistraw",
                "sql" => "SELECT max(CREATEDATE) CREATEDATE FROM AUTH_HEADER WHERE CREATEBY = '".$user."' "
            ])->json()["Data"]["Items"][0]["CREATEDATE"];
            $lastCreateDate_ = '';
            if(($lastCreateDate) == ""){
                $lastCreateDate_ =  $lastCreateDate_ = '7/1/2024 4:30:00 AM';        
            } else {
                $lastCreateDate_ = $lastCreateDate;
            } 
        }
       
        $dateCreated = Carbon::createFromFormat('m/d/Y h:i:s A', $lastCreateDate_)->addSeconds(random_int(150,300));
        
        $day =  $dateCreated->format('m/d/Y');
        if($date_lembur != null || $date_lembur != ''){
            $start = Carbon::createFromFormat('m/d/Y h:i:s A', $day . ' 4:30:00 PM');
            $end = Carbon::createFromFormat('m/d/Y h:i:s A', $day . ' 7:00:00 PM');
            if ($dateCreated >= $start && $dateCreated <= $end) {
                $return = $dateCreated->format('Y-m-d H:i:s');
                return $return;
            } else {
                return false;
                /*$newDate = $dateCreated->addWeekdays(1)->format('m/d/Y') . ' 4:30:00 PM';
                $nDate = Carbon::createFromFormat('m/d/Y h:i:s A',$newDate)->addSeconds(random_int(200,500));
                $return = $nDate->format('Y-m-d H:i:s');
                return $return;*/
            }
        } else {
            $start = Carbon::createFromFormat('m/d/Y h:i:s A', $day . ' 8:00:00 AM');
            $end = Carbon::createFromFormat('m/d/Y h:i:s A', $day . ' 4:30:00 PM');
            if ($dateCreated >= $start && $dateCreated <= $end) {
                $return = $dateCreated->format('Y-m-d H:i:s');
                return $return;
            } else {
                $newDate = $dateCreated->addWeekdays(1)->format('m/d/Y') . ' 8:00:00 AM';
                $nDate = Carbon::createFromFormat('m/d/Y h:i:s A',$newDate)->addSeconds(random_int(150,300));
                $return = $nDate->format('Y-m-d H:i:s');
                return $return;
            }
        }
        
    }

}