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
use App\Http\Controllers\LinimasaController;

use App\Http\Middleware\S3;

class LanggananController extends Controller
{
    static function getLangganan(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

        $sql = "SELECT
            pengguna.nama as nama_pengguna,
            langganan.* 
        FROM
            langganan 
            join pengguna on pengguna.pengguna_id = langganan.pengguna_id
        WHERE
            langganan.sekolah_id = '{$sekolah_id}' 
            AND langganan.soft_delete = 0 
            AND langganan.status_aktif = 1 
            AND langganan.tanggal_jatuh_tempo >= now()";

        $fetch = DB::connection('sqlsrv_2')->select($sql);

        $return = array();
        $return['rows'] = $fetch;
        $return['total'] = sizeof($fetch);

        return $return;
        
    }
}