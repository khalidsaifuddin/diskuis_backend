<?php

namespace App\Http\Middleware;

use Closure;
use JWTFactory;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class CheckToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next)
    {
        // echo "<pre>";
        // var_dump(); die;
        try {
            if(!JWTAuth::getPayload(JWTAuth::getToken())->toArray()) return Response()->json(['login'=>'Unauthorized']);
        }catch(TokenExpiredException $e){
            JWTAuth::getToken();
            $token = JWTAuth::refresh();
            // return Response()->json(['login'=>'Expired', 'newToken'=>''], 200);
            $return = $next($request);
            // $return = array_merge(json_decode($return), ['login'=>'Expired', 'newToken'=>$token]);
            return Response($return); 
            // return Response()->json(['login'=>'Expired'], 400); 
        }catch(TokenInvalidException $e){
            return Response()->json(['login'=>'Invalid'], 400); 
        }catch(JWTException $e){    
            return Response()->json(['login'=>'Empty'], 400);   
        }catch(TokenBlacklistedException $e){
            return Response()->json(['login'=>'Blacklist'], 400);
        };
        return $next($request);
    }
}
