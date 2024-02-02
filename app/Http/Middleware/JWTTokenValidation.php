<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\AuthApi;

class JWTTokenValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $authorization = $request->bearerToken();
        if(!$authorization){
            return response()->json([
                'message'   => 'Token is required!',
                'status'    => 'Failed'
            ], 401);
        }
        $authapi = AuthApi::where("jwt", $authorization)->first();

        if(!$authapi){
            return response()->json([
                'message'   => 'Token is required!',
                'status'    => 'Failed'
            ], 401);
        }

        if($authorization != $authapi->jwt){
            return response()->json([
                'message'   => 'Application Key not valid.',
                'status'    => 'Failed'
            ], 401);
        }
        
        if($authapi->jwt == '123ABC-demoonly' && $request->ip() != '127.0.0.1'){
            return response()->json([
                'message'   => 'Application Key for demo only.',
                'status'    => 'Failed'
            ], 401);
        }

        if($authapi->enable == 0 && $authapi->jwt != '123ABC-demoonly'){
            return response()->json([
                'message'   => 'Application Key is disabled. Contact administrator.',
                'status'    => 'Failed'
            ], 401);
        }
        if((strtotime(date('Y-m-d H:i:s')) > strtotime($authapi->expired_at)) && $authapi->jwt != '123ABC-demoonly'){
            return response()->json([
                'message'   => 'Token expired. Please request new token',
                'status'    => 'Failed'
            ], 401);
        }

        return $next($request);
    }
}
