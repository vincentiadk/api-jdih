<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
/**
  * @group Catalog INLIS
*/
class CatalogController extends Controller
{

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
        $data = DB::connection('inlis')
            ->table('AUTH_HEADER')
            ->select('ID','ISTILAH_DIGUNAKAN', 'ISTILAH_TDK_DIGUNAKAN', 'ISTILAH_TERKAIT')
            ->where('ISTILAH_DIGUNAKAN','LIKE', '%'.$request->input('q').'%')
            ->orWhere('ISTILAH_TDK_DIGUNAKAN','LIKE', '%'.$request->input('q').'%')
            ->orWhere('ISTILAH_TERKAIT','LIKE', '%'.$request->input('q').'%')
            ->get();
        return response()->json(
            [
                "Search" => $request->input('q'),
                "Data" => $data,
            ]
        );
    }

    public function saveAuthoritySingle()
    {
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
                'error' => $validator->errors(),
            ], 422);
        }
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
        $check_catalog  = DB::connection('inlis')->table('AUTH_CATALOG')->where('CATALOG_ID', request('id_catalog'))->count();
        $check_usulan  = DB::connection('inlis')->table('AUTH_USULAN_UPDATE')->where('AUTH_USULAN_ID', request('id_usulan'))->count();
        if($check_catalog == 0 && $check_usulan == 0){
            $user = $datauser[random_int(0,9)];
            $data_tag = request('data_tag');
            $istilah_digunakan = ''; $istilah_tdk_digunakan = '';
            $create_date_user = $this->getCreateDate($user['user']);
            $auth_header_found = false;
            $auth_data_input = [];
            foreach($data_tag as $auth_data){
                $data_item = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h', '$z','$w', '$y', '$g'], '', $auth_data["value"]));
                $check_header = DB::connection('inlis')->table('AUTH_DATA')->where('DATAITEM',$data_item)->count();
                if($check_header == 1){
                    $auth_header_found = true;
                    break;
                }
                array_push($auth_data_input,[
                        'TAG' => $auth_data["tag"],
                        'INDICATOR1' => $auth_data["indikator1"],
                        'INDICATOR2' => $auth_data["indikator2"],
                        'VALUE' => trim($auth_data["value"]),
                        'DATAITEM' => trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h', '$z'], '', $auth_data["value"])),
                        'AUTH_HEADER_ID' => ''
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
            if($auth_header_found == false){
                $auth_header_id = DB::connection('inlis') 
                    ->table('AUTH_HEADER')
                    ->insertGetId([
                        'WORKSHEET_ID' => '63',
                        'ISTILAH_DIGUNAKAN' => $istilah_digunakan,
                        'ISTILAH_TDK_DIGUNAKAN' => $istilah_tdk_digunakan,
                        'CREATEBY' => $user["user"],
                        'CREATETERMINAL' => $user["terminal"],
                        'CREATEDATE' => $create_date_user,
                        'UPDATEBY' => $user["user"],
                        'UPDATETERMINAL' => $user["terminal"],
                        'UPDATEDATE' => $create_date_user,
                        'AUTH_ID' => $this->getAuthId(),
                    ]);
                foreach($auth_data_input as $auth_to_input){
                    $auth_to_input['AUTH_HEADER_ID'] = $auth_header_id;
                    DB::connection('inlis')
                        ->table('AUTH_DATA')
                        ->insert($auth_to_input);
                }
                DB::connection('inlis')
                    ->table('AUTH_CATALOG')
                    ->insert([
                        'CATALOG_ID' => request('id_catalog'),
                        'AUTH_HEADER_ID' => $auth_header_id,
                    ]);
                DB::connection('inlis')
                    ->table('AUTH_USULAN_UPDATE')
                    ->insert([
                        'AUTH_USULAN_ID' => request('id_usulan'),
                    ]);
                return response()->json(
                    [
                        "message" => "Auth header created '" . $istilah_digunakan . "' with ID=" . $auth_header_id,
                    ]
                );
            } else {
                DB::connection('inlis')
                    ->table('AUTH_HEADER')
                    ->where('ID',$auth_header_id)
                    ->delete();
                return response()->json(
                    [
                        "message" => "Auth header failed already exists",
                        "skipped" => request('id_usulan')
                    ]);
            }
        } else {
            return response()->json(
                [
                    "message" => "Auth header failed to created, usulan already processed or catalog is already exists in auth catalog.",
                    "skipped" => request('id_usulan')
                ]);
        }
    }

    public function saveAuthorityMultiple()
    {
        $datas = request('data');
        $auth_created = []; 
        $auth_skipped = [];
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
                    'error' => $validator->errors(),
                ], 422);
            }
            $check_catalog  = DB::connection('inlis')->table('AUTH_CATALOG')->where('CATALOG_ID', $data['id_catalog'])->count();
            $check_usulan  = DB::connection('inlis')->table('AUTH_USULAN_UPDATE')->where('AUTH_USULAN_ID', $data['id_usulan'])->count();
            if($check_catalog == 0 && $check_usulan == 0){
                $user = $datauser[random_int(0,9)];
                $data_tag = $data['data_tag'];
                $istilah_digunakan = ''; $istilah_tdk_digunakan = '';
                $create_date_user = $this->getCreateDate($user['user']);
                $auth_header_found = false;
                $auth_data_input = [];
                
                foreach($data_tag as $auth_data){
                    $data_item = trim(str_replace(['$a','$b', '$c', '$d', '$e', '$h', '$z','$w', '$y', '$g'], '', $auth_data["value"]));
                    $check_header = DB::connection('inlis')->table('AUTH_DATA')->where('DATAITEM',$data_item)->count();
                    if($check_header == 1){
                        $auth_header_found = true;
                        break;
                    }
                    array_push($auth_data_input,[
                            'TAG' => $auth_data["tag"],
                            'INDICATOR1' => $auth_data["indikator1"],
                            'INDICATOR2' => $auth_data["indikator2"],
                            'VALUE' => trim($auth_data["value"]),
                            'DATAITEM' => $data_item,
                            'AUTH_HEADER_ID' => ''
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
                if($auth_header_found == false){
                    $auth_header_id = DB::connection('inlis') 
                        ->table('AUTH_HEADER')
                        ->insertGetId([
                            'WORKSHEET_ID' => '63',
                            'ISTILAH_DIGUNAKAN' => $istilah_digunakan,
                            'ISTILAH_TDK_DIGUNAKAN' => $istilah_tdk_digunakan,
                            'CREATEBY' => $user["user"],
                            'CREATETERMINAL' => $user["terminal"],
                            'CREATEDATE' => $create_date_user,
                            'UPDATEBY' => $user["user"],
                            'UPDATETERMINAL' => $user["terminal"],
                            'UPDATEDATE' => $create_date_user,
                            'AUTH_ID' => $this->getAuthId(),
                        ]);
                    foreach($auth_data_input as $auth_to_input){
                        $auth_to_input['AUTH_HEADER_ID'] = $auth_header_id;
                        DB::connection('inlis')
                            ->table('AUTH_DATA')
                            ->insert($auth_to_input);
                    }
                    DB::connection('inlis')
                        ->table('AUTH_CATALOG')
                        ->insert([
                            'CATALOG_ID' => $data['id_catalog'],
                            'AUTH_HEADER_ID' => $auth_header_id,
                        ]);
                    DB::connection('inlis')
                        ->table('AUTH_USULAN_UPDATE')
                        ->insert([
                            'AUTH_USULAN_ID' => $data['id_usulan'],
                        ]);
                    array_push($auth_created,[$istilah_digunakan, $auth_header_id]);
                } else {
                    array_push($auth_skipped, $data['id_usulan']);
                }
            } else {
                array_push($auth_skipped, $data['id_usulan']);
            }
            $msg = 'Auth header created: ';
            foreach($auth_created as $authCreated){
                $msg .= "ID = " . $authCreated[1] . " --> " . $authCreated[0] . "\n";
            }
            $msg .= "Total : " . count($auth_created) . " Authority";
        }
        return response()->json(
            [
                "message" => $msg,
                "skipped" => $auth_skipped
            ]
        );
    }

    public function getCreateDate($user)
    {
        $lastCreateDate =  DB::connection('inlis')
                    ->table('AUTH_HEADER')
                    ->whereRaw(DB::connection('inlis')->raw('CREATEDATE = (SELECT max(CREATEDATE) FROM AUTH_HEADER WHERE CREATEBY = "'.$user.'" GROUP BY CREATEDATE ORDER BY CREATEDATE DESC LIMIT 1)'))
                    ->get()
                    ->first();
        $lastCreateDate_ = '';
        if($lastCreateDate == null){
            $lastCreateDate_ = '2024-06-17 08:00:00';
        } else {
            $lastCreateDate_ = $lastCreateDate->CREATEDATE;
        }
        $dateCreated = Carbon::createFromFormat('Y-m-d H:i:s', $lastCreateDate_)->addSeconds(random_int(180,300));
        $time = $dateCreated->format('H:i:s');
        $start = '08:00:00';
        $end = '17:00:00';
        if ($time >= $start && $time <= $end) {
            return $dateCreated->toDateTimeString();
        } else {
            $newDate = $dateCreated->addWeekdays(1)->format('Y-m-d') . ' 08:00:00';
            $nDate = Carbon::createFromFormat('Y-m-d H:i:s',$newDate)->addSeconds(random_int(180,300));
            return $nDate->toDateTimeString();
        }
    }

    public function getAuthId()
    {
        $lastAuthId =  DB::connection('inlis')
                    ->table('AUTH_HEADER')
                    ->select(DB::connection('inlis')->raw('(SELECT max(AUTH_ID) FROM AUTH_HEADER WHERE AUTH_ID LIKE "AUTH%") as AUTH_ID'))
                    ->get()
                    ->first();
        $id = intval(substr($lastAuthId->AUTH_ID,5,11)) + 1;
        $str_length = 11;
        return "AUTH-" . substr("000000{$id}", -$str_length);
    }
}