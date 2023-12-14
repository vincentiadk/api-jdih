<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use App\Models\Survey;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{

    public function saveSurvey()
    {
        $validator = Validator::make(request()->all(), [
            'idsessionuser' => 'required', 
            'pilihan' => 'required|integer|max:5|min:1',
            'email' => 'email', 
            'telephone' => 'numeric'
        ], [
            'idsessionuser.required' => 'ID Session User wajib diisi!',
            'pilihan.required' => 'Pilihan hasil survey wajib diisi!',
            'pilihan.integer' => 'Pilihan hasil survey hanya berupa angka 1 sd 5!',
            'pilihan.max' => 'Pilihan hasil survey hanya berupa angka 1 sd 5!',
            'pilihan.min' => 'Pilihan hasil survey hanya berupa angka 1 sd 5!',
            'email.email' => 'Email tidak valid!',
            'telephone.numeric' => 'Telepon hanya boleh berupa angka!',
        ]);
        if($validator->fails()){
            return response()->json([
                'status' =>422,
                'error' => $validator->errors(),
            ]);
        } else {
            $survey = Survey::create([
                'idsessionuser' => request('idsessionuser') . '-mobile', 
                'pilihan' => request('pilihan') ,
                'tanggal' => Carbon::now(),
                'nama' =>request('nama'),
                'masukan'=> request('masukan'),
                'instansi' => request('instansi'),
                'email' => request('email'),
                'telephone' => request('telephone')
            ]);
            if($survey) {
                return response()->json([
                    'status' => 200,
                    'message' => 'success',
                ]);
            } else {
                return response()->json([
                    'status' => 503,
                    'message' => 'Eeror save!',
                ]);
            }
        }
    }

}
