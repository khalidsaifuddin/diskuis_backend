<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTFactory;

use App\Http\Controllers\RuangController;

class SiswaController extends Controller
{   
    static function getSiswa(Request $request){
        // return "oke";
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('siswa')
        ->where('soft_delete','=',0);

        if($sekolah_id){
            $fetch->where('siswa.sekolah_id','=',$sekolah_id);
        }

        if($pengguna_id){
            $fetch->where('siswa.pengguna_id','=',$pengguna_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }
    
    static function getOrangtua(Request $request){
        // return "oke";
        $orangtua_id = $request->orangtua_id ? $request->orangtua_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('orangtua')
        ->where('soft_delete','=',0);

        if($pengguna_id){
            $fetch->where('orangtua.pengguna_id','=',$pengguna_id);
        }
        
        if($orangtua_id){
            $fetch->where('orangtua.orangtua_id','=',$orangtua_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanSiswa(Request $request){
        return "oke";
    }
    
    static function simpanOrangtua(Request $request){
        return "oke";
    }
}