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
     * This API is used to get a JSON WEB TOKEN (JWT) that you can use to access other APIs. 
     * You need the <b>x-api-key</b> header in order to use this API. 
     * Kindly ask the web Administrator for JWT requests.
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
