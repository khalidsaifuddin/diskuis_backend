<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        $whitelist = [
            'http://localhost:3000',
            'http://localhost:3001',
            'http://localhost:8081',
            'http://localhost:8082',
            'http://localhost:8000',
            'http://localhost',
            'http://validasi.dikdasmen.kemdikbud.go.id',
            "http://192.168.0.104:3000",
            "http://test.one",
            "http://app.diskuis.id",
            "https://app.diskuis.id",
            "https://mtsmiftahululum.diskuis.id",
            "http://ict.mtsmubakid.sch.id",
            "https://ict.mtsmubakid.sch.id",
            "http://empu.diskuis.id",
            "http://diskuisbuild:8888",
            "https://dev.diskuis.id",
            "http://localhost:3000",
            "https://diskuis.id",
            "http://117.53.47.43:8060",
            "http://ppdblumajang.diskuis.id",
            "https://ppdblumajang.diskuis.id",
            "file://",
            // "http://gamemaster.diskuis.id/",
            null
        ];
        
        $origin = $request->header('Origin');

        if(in_array($origin, $whitelist))
        return $next($request)->withHeaders(['Access-Control-Allow-Origin'=>$origin, 'Access-Control-Allow-Methods'=>'GET, POST, PATCH, PUT, DELETE, OPTIONS', 'Access-Control-Allow-Headers'=>'Content-Type, Accept, Authorization, X-Requested-With, Application', 'Access-Control-Max-Age'=>86400]);
        return Response()->json(['status'=>'Domain Tidak terdaftar']);

    }
}
