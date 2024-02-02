<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\AuthApi;
use Str;
/**
  * @group Token
*/
class TokenController extends Controller
{
    /** 
     * GET token
     * 
     * API ini digunakan untuk mendapatkan jwt token yang dapat Anda gunakan untuk mengakses API lainnya. 
     * Anda membutuhkan header <b>x-api-key</b> agar dapat menggunakan API ini. 
     * <b>x-api-key</b> dapat Anda mintakan kepada Administrator web.
     * 
     * @header x-api-key 123ABC
    */
    public function getToken(Request $request)
    {
        $query = AuthApi::where('app_key', $request->header('x-api-key'));
        if($query->count() > 0){
            $token = Str::random(60);
            $expired_at = Date('Y-m-d H:i:s', strtotime(config('token.expires')));

            if($request->header('x-api-key') == '123ABC'){
                return response()->json([
                    'token' => $token,
                    'expired_at' => $expired_at
                ], 200);
            }

            if($query->first()->enable == 0){
                return response()->json([
                    'message' => "Application Key tidak aktif. Hubungi administrator",
                    'status' => "Failed"
                ], 401);
            }
            $query->update([
                'jwt' => $token,
                'expired_at' => $expired_at
            ]);
            return response()->json([
                'token' => $token,
                'expired_at' => $expired_at
            ], 200);
        } else {
            return response()->json([
                'message' => "Application Key tidak ditemukan",
                'status' => "Failed"
            ], 401);
        }
    }
}
