<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use App\Models\MasukanPeraturan;
use Illuminate\Support\Facades\Validator;
/**
  * @group Peraturan Perundangan
*/
class MasukanPeraturanController extends Controller
{
     /**
     * @urlParam id integer required Example: 4
     * @bodyParam nama string required Example: Andika Biangkara
     * @bodyParam surel email required Example: emailAnda@mail.com
     * @bodyParam instansi string required Example: Lembaga Bimbingan Hukum
     * @bodyParam no_hp numeric required Example: 081234567890
     * @bodyParam masukan string required Example: Bagus dan menyenangkan!
     */
    public function save($id)
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
        } else {
            $d = MasukanPeraturan::create([
                'id_rancangan_peraturan' => $id, 
                'nama' => request('nama') ,
                'created_at' => Carbon::now(),
                'masukan'=> request('masukan'),
                'instansi' => request('instansi'),
                'surel' => request('email'),
                'no_hp' => request('no_hp')
            ]);
            if($d) {
                return response()->json([
                    'message' => 'Success',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Errors saving data!',
                ], 503);
            }
        }
    }
}
