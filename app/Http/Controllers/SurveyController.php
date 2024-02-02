<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use GuzzleHttp\Client as GuzzleClient;
use Carbon\Carbon;
use App\Models\Survey;
use Illuminate\Support\Facades\Validator;
/**
  * @group Survey Kepuasan
*/
class SurveyController extends Controller
{
    /**
     * @header Authorization Bearer 123ABC-demoonly
     * @bodyParam idsessionuser string required Example: asiacmpdxdfiofasga123as
     * @bodyParam pilihan integer required Pilihan 1 sd 5. 
     * Very poor,
     * Poor,
     * Average,
     * Good,
     * Excelent Example: 3
     * @bodyParam email email required Example: emailAnda@mail.com
     * @bodyParam telephone numeric required Example: 081234567890
     * @bodyParam masukan string Example: Bagus dan menyenangkan! Saya suka dengan aplikasi JDIH mobile :)
     */
    public function save()
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
                'error' => $validator->errors(),
            ], 422);
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
                    'message' => 'Success',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Errors saving data!',
                ], 503);
            }
        }
    }

    /**
     * @header Authorization Bearer 123ABC-demoonly
     */
    public function getHasilSurvey()
    {
        $p = Survey::select(\DB::raw('COUNT(pilihan) as jumlah'), 'pilihan')
            ->whereIn('pilihan', ['1','2','3','4','5'])
            ->groupBy('pilihan')
            ->get();
        if($p){
            return $p;
        } else {
            return response()->json([
                'message' => 'Error fetching data!',
            ], 503);
        }
    }
}
