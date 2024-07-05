<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
/**
  * @group Artikel Hukum
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

    public function saveTajuk(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'id_usulan' => 'required|numeric',
            'id_catalog' => 'required|numeric', 
            'istilah_digunakan' => 'required',
            'create_date' => 'required',
            'data_tag' => 'required',
            'user' => 'required'
        ], [
            'id_usulan.required' => 'ID usulan wajib diisi!',
            'id_catalog.rewqired' => 'Email tidak valid!',
            'istilah_digunakan.required' => 'Istilah digunakan wajib diisi!',
            'create_date.required' => 'Create Date wajib diisi!',
            'data_tag.required' => 'Data Tag wajib diisi!',
            'id_usulan.numeric' => 'ID usulan hanya boleh berupa angka!',
            'id_catalog.numeric' => 'ID Catalog hanya boleh berupa angka!',
            'user.required' => 'Masukan Anda wajib diisi!',
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
        $user = $datauser[random_int(0,9)];
        $auth_data = request('auth_data');
        $created_data = '';

        $auth_header_id = DB::connection('inlis')
            ->table('auth_header')
            ->insertGetId([
                'worksheet_id' => '63',
                'createdby' => $user["user"],
                'createterminal' => $user["user"],
                'create_date' => request('create_date'),
                'istilah_digunakan' => request('istilah_digunakan'),
                'istilah_tdk_digunakan' => request('istilah_tidak_digunakan'),
                ''
            ]);
        return response()->json(
            [
                "Data" => $data,
            ]
        );
    }

    public function updateAuthHeader(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'nama' => 'required|min:3',
            'surel' => 'email|required', 
            'instansi' => 'required',
            'no_hp' => 'required|numeric',
            'masukan' => 'required'
        ], [
            'nama.required' => 'Nama pemberi masukan rancangan peraturan wajib diisi!',
            'surel.email' => 'Email tidak valid!',
            'surel.required' => 'Email wajib diisi!',
            'instansi.required' => 'Instansi wajib diisi!',
            'nama.min' => 'Nama minimal terdiri dari 3 karakter!',
            'no_hp.required' => 'Nomor HP wajib diisi!',
            'no_hp.numeric' => 'Telepon hanya boleh berupa angka!',
            'masukan.required' => 'Masukan Anda wajib diisi!',
        ]);
        if($validator->fails()){
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }
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
}
