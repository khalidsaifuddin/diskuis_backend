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

class PengikutController extends Controller
{
    static function cekMengikuti(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $pengguna_id_pengikut = $request->pengguna_id_pengikut ? $request->pengguna_id_pengikut : null;

        $fetch_cek =  DB::connection('sqlsrv_2')->table('pengikut_pengguna')
        ->where('pengguna_id','=', $pengguna_id)
        ->where('pengguna_id_pengikut','=', $pengguna_id_pengikut)
        ->where('soft_delete','=',0)
        ->get();

        // return $fetch_cek;die;

        return response(
            [
                'status' => (sizeof($fetch_cek) > 0 ? 'Y' : 'N')
            ],
            200
        );
    }

    static function getPengikut(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;

        $sql = "SELECT
                    * 
                FROM
                    (
                    SELECT
                        pengguna_id,
                        'pengikut' AS label,
                        SUM ( 1 ) AS jumlah 
                    FROM
                        pengikut_pengguna 
                    WHERE
                        pengguna_id = '".$pengguna_id."' 
                    AND soft_delete = 0
                    GROUP BY
                        pengguna_id UNION
                    SELECT
                        pengguna_id_pengikut,
                        'mengikuti' AS label,
                        SUM ( 1 ) AS jumlah 
                    FROM
                        pengikut_pengguna 
                    WHERE
                        pengguna_id_pengikut = '".$pengguna_id."' 
                    AND soft_delete = 0
                    GROUP BY
                    pengguna_id_pengikut 
                    ) pengikut_pengguna";
        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        return $fetch;
    }

    static function simpanPengikut(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $pengguna_id_pengikut = $request->pengguna_id_pengikut ? $request->pengguna_id_pengikut : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : '0';
        $pengikut_pengguna_id = $request->pengikut_pengguna_id ? $request->pengikut_pengguna_id : RuangController::generateUUID();

        $fetch_cek =  DB::connection('sqlsrv_2')->table('pengikut_pengguna')
        ->where('pengguna_id','=', $pengguna_id)
        ->where('pengguna_id_pengikut','=', $pengguna_id_pengikut)
        ->get();

        if(sizeof($fetch_cek) > 0){
            //sudah ada, tinggal diaktifkan lagi
            $exe = DB::connection('sqlsrv_2')->table('pengikut_pengguna')
            ->where('pengguna_id','=', $pengguna_id)
            ->where('pengguna_id_pengikut','=', $pengguna_id_pengikut)
            ->update([
                'create_date'=> DB::raw('now()::timestamp(0)'),
                'soft_delete'=> $soft_delete
            ]);
            $label='UPDATE';

        }else{
            //belum ada insert baru
            $exe = DB::connection('sqlsrv_2')->table('pengikut_pengguna')
            ->insert([
                'pengikut_pengguna_id' => $pengikut_pengguna_id,
                'pengguna_id' => $pengguna_id,
                'pengguna_id_pengikut' => $pengguna_id_pengikut,
                'last_update'=> DB::raw('now()::timestamp(0)'),
                'create_date'=> DB::raw('now()::timestamp(0)'),
                'soft_delete'=> $soft_delete
            ]);
            $label='INSERT';
        }

        if($exe){
            try {
                //code...
                $aktivitas_id = RuangController::generateUUID();
                $aktivitas = LinimasaController::simpanAktivitas($aktivitas_id, $pengguna_id_pengikut, 4, 'mengikuti-pengguna', $pengguna_id);
    
                if($aktivitas){
                    $sukses_aktivitas_mengikuti = true;
                }else{
                    $sukses_aktivitas_mengikuti = false;
                }
            } catch (\Throwable $th) {
                $sukses_aktivitas_mengikuti = false;
            }
            
            try {
                //code...
                $aktivitas_id = RuangController::generateUUID();
                $aktivitas = LinimasaController::simpanAktivitas($aktivitas_id, $pengguna_id, 4, 'diikuti-pengguna', $pengguna_id_pengikut);
    
                if($aktivitas){
                    $sukses_aktivitas_diikuti = true;
                }else{
                    $sukses_aktivitas_diikuti = false;
                }
            } catch (\Throwable $th) {
                $sukses_aktivitas_diikuti = false;
            }
        }

        return response(
            [
                'rows' => DB::connection('sqlsrv_2')->table('pengikut_pengguna')
                        ->where('pengguna_id','=', $pengguna_id)
                        ->where('pengguna_id_pengikut','=', $pengguna_id_pengikut)
                        ->get(),
                'success' => ($exe ? true : false),
                'sukses_aktivitas_mengikuti' => $sukses_aktivitas_mengikuti,
                'sukses_aktivitas_diikuti' => $sukses_aktivitas_diikuti,
                'label' => $label
            ],
            200
        );

    }
}