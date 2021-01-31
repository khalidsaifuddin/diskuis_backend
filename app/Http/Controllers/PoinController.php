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

class PoinController extends Controller
{
    static function getPoinDiri(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
    }

    static function getLeaderboardGlobal(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 10;

        $sql = "SELECT
            pengguna.username,
            aaa.* 
        FROM
            (
            SELECT
                rekap_poin.* 
            FROM
                rekap.poin_pengguna_harian rekap_poin
                JOIN pengguna ON pengguna.pengguna_id = rekap_poin.pengguna_id                
            WHERE
                pengguna.soft_delete = 0 
            ) aaa
            JOIN pengguna ON pengguna.pengguna_id = aaa.pengguna_id 
        ORDER BY
            poin DESC 
        OFFSET {$start} LIMIT {$limit}";

        $fetch = DB::connection('sqlsrv_2')->select($sql);

        $return = array();
        $return['rows'] = $fetch;
        $return['total'] = sizeof($fetch);

        return $return;
    }

    static function getLeaderboardPengguna(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 10;

        $sql = "SELECT
            pengguna.username,
            aaa.* 
        FROM
            (
            SELECT
                rekap_poin.* 
            FROM
                rekap.poin_pengguna_harian rekap_poin
                JOIN pengguna ON pengguna.pengguna_id = rekap_poin.pengguna_id
                JOIN pengikut_pengguna ON pengikut_pengguna.pengguna_id = pengguna.pengguna_id 
            WHERE
                pengguna.soft_delete = 0 
                AND pengikut_pengguna.soft_delete = 0 
                AND pengikut_pengguna.pengguna_id_pengikut = '".$pengguna_id."' UNION
            SELECT
                * 
            FROM
                rekap.poin_pengguna_harian 
            WHERE
                pengguna_id = '".$pengguna_id."' 
            ) aaa
            JOIN pengguna ON pengguna.pengguna_id = aaa.pengguna_id 
        ORDER BY
            poin DESC 
        OFFSET {$start} LIMIT {$limit}";

        $fetch = DB::connection('sqlsrv_2')->select($sql);

        $return = array();
        $return['rows'] = $fetch;
        $return['total'] = sizeof($fetch);

        return $return;
    }

    static function simpanPoin($pengguna_id, $create_date, $jenis_poin_id, $kuis_id, $sesi_kuis_id, $skor){
        switch ($jenis_poin_id) {
            case 1:
                # poin dari ngerjain kuis
                $kuis = DB::connection('sqlsrv_2')->table('sesi_kuis')
                ->join('kuis','sesi_kuis.kuis_id','=','kuis.kuis_id')
                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                ->select(
                    'sesi_kuis.*',
                    'kuis.judul as judul'
                )
                ->first()
                ;

                $exe = DB::connection('sqlsrv_2')->table('poin_pengguna')
                ->insert([
                    'poin_pengguna_id' => DB::raw("uuid_generate_v4 ()"),
                    'create_date' => $create_date,
                    'last_update' => DB::raw("now()"),
                    'soft_delete' => 0,
                    'jenis_poin_id' => $jenis_poin_id,
                    'nilai_poin' => DB::raw("COALESCE ( CEIL ( ".$skor." ), 0 )"),
                    'keterangan' => 'Poin dari pengerjaan kuis '.$kuis->judul.' sesi '.$kuis->keterangan,
                    'pengguna_id' => $pengguna_id
                ]);
                break;
            case 2:
                # poin dari buat kuis
                $kuis = DB::connection('sqlsrv_2')->table('kuis')
                ->where('kuis_id','=',$kuis_id)
                ->select(
                    'kuis.*'
                )
                ->first()
                ;

                $exe = DB::connection('sqlsrv_2')->table('poin_pengguna')
                ->insert([
                    'poin_pengguna_id' => DB::raw("uuid_generate_v4 ()"),
                    'create_date' => $create_date,
                    'last_update' => DB::raw("now()"),
                    'soft_delete' => 0,
                    'jenis_poin_id' => $jenis_poin_id,
                    'nilai_poin' => 150,
                    'keterangan' => 'Poin dari pembuatan kuis '.$kuis->judul,
                    'pengguna_id' => $pengguna_id
                ]);
                break;
            case 3:
                # poin jariyah kuis
                $kuis = DB::connection('sqlsrv_2')->table('sesi_kuis')
                ->join('kuis','sesi_kuis.kuis_id','=','kuis.kuis_id')
                ->join('pengguna','kuis.pengguna_id','=','pengguna.pengguna_id')
                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                ->select(
                    'sesi_kuis.*',
                    'kuis.judul as judul',
                    'pengguna.nama as nama_pengguna',
                    'pengguna.pengguna_id as pengguna_id'
                )
                ->first()
                ;

                $exe = DB::connection('sqlsrv_2')->table('poin_pengguna')
                ->insert([
                    'poin_pengguna_id' => DB::raw("uuid_generate_v4 ()"),
                    'create_date' => $create_date,
                    'last_update' => DB::raw("now()"),
                    'soft_delete' => 0,
                    'jenis_poin_id' => $jenis_poin_id,
                    'nilai_poin' => 10,
                    'keterangan' => 'Poin dari pengerjaan kuis ' . $kuis->judul . ' oleh ' . $kuis->nama_pengguna,
                    'pengguna_id' => $kuis->pengguna_id
                ]);
                break;
            default:
                # code...
                break;
        }

        return $exe ? true : false;

    }
}