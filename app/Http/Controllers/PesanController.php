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

class PesanController extends Controller
{
    static function getPesan(Request $request){
        $kelompok_pesan_id = $request->kelompok_pesan_id ? $request->kelompok_pesan_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 30;

        $fetch = DB::connection('sqlsrv_2')->table('pesan')
        ->where('kelompok_pesan_id','=',$kelompok_pesan_id)
        ->where('soft_delete','=',0)
        ;

        return response(
            [
                'total' => $fetch->count(),
                'start' => ($fetch->count() <= $limit ? $start : ($fetch->count() - $limit)),
                'limit' => $limit,
                'rows' => $fetch
                        ->skip(($fetch->count() <= $limit ? $start : ($fetch->count() - $limit)))
                        ->take($limit)
                        ->orderBy('create_date','ASC')
                        ->get()
            ],
            200
        );

    }

    static function simpanPesan(Request $request){
        $kelompok_pesan_id = $request->kelompok_pesan_id ? $request->kelompok_pesan_id : null;
        $pengguna_id_pengirim = $request->pengguna_id_pengirim ? $request->pengguna_id_pengirim : null;
        $konten = $request->konten ? $request->konten : null;
        $pesan_id = RuangController::generateUUID();

        $exe = DB::connection('sqlsrv_2')->table('pesan')
        ->insert([
            'pesan_id' => $pesan_id,
            'pengguna_id_pengirim' => $pengguna_id_pengirim,
            'konten' => $konten,
            'kelompok_pesan_id' => $kelompok_pesan_id,
            'create_date' => DB::raw("now()"),
            'last_update' => DB::raw("now()"),
            'soft_delete' => 0,
            'sudah_dibaca' => 0
        ]);

        return response(
            [
                'sukses' => $exe ? true : false,
                'rows' => DB::connection('sqlsrv_2')->table('pesan')
                ->where('pesan_id','=',$pesan_id)
                ->get(),
                'pesan_id' => $pesan_id
            ],
            200
        );
    }

    static function getKelompokPesan(Request $request){
        $pengguna_id_1 = $request->pengguna_id_1 ? $request->pengguna_id_1 : null;
        $pengguna_id_2 = $request->pengguna_id_2 ? $request->pengguna_id_2 : null;
        $kelompok_pesan_id = $request->kelompok_pesan_id ? $request->kelompok_pesan_id : null;

        $fetch = DB::connection('sqlsrv_2')->table('kelompok_pesan')
        ->join('pengguna as pengguna_1', 'pengguna_1.pengguna_id','=','kelompok_pesan.pengguna_id_1')
        ->join('pengguna as pengguna_2', 'pengguna_2.pengguna_id','=','kelompok_pesan.pengguna_id_2')
        ->where('kelompok_pesan.soft_delete','=', 0)
        ->select(
            'kelompok_pesan.*',
            'pengguna_2.nama as nama_2',
            'pengguna_1.nama as nama_1'
        )
        ;

        if($kelompok_pesan_id){
            $fetch->where('kelompok_pesan_id','=',$kelompok_pesan_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->get()
            ],
            200
        );
    }
    
    static function simpanKelompokPesan(Request $request){
        // return "oke";
        $pengguna_id_1 = $request->pengguna_id_1 ? $request->pengguna_id_1 : null;
        $pengguna_id_2 = $request->pengguna_id_2 ? $request->pengguna_id_2 : null;

        $cek = DB::connection('sqlsrv_2')->table('kelompok_pesan')
        ->where('pengguna_id_1','=',$pengguna_id_1)
        ->where('pengguna_id_2','=',$pengguna_id_2)
        ->where('soft_delete','=', 0)
        ->get();

        if(sizeof($cek) > 0){
            //ada
            $kelompok_pesan_id = $cek[0]->kelompok_pesan_id;
            $exe = true;
        }else{
            // belum ada. cek kebalikannya
            $cek2 = DB::connection('sqlsrv_2')->table('kelompok_pesan')
            ->where('pengguna_id_1','=',$pengguna_id_2)
            ->where('pengguna_id_2','=',$pengguna_id_1)
            ->where('soft_delete','=', 0)
            ->get();

            if(sizeof($cek2) > 0){
                //ternyata ada
                $kelompok_pesan_id = $cek2[0]->kelompok_pesan_id;
                $exe = true;
            }else{
                //tetep nggak ada
                $kelompok_pesan_id = RuangController::generateUUID();
    
                $exe = DB::connection('sqlsrv_2')->table('kelompok_pesan')
                ->insert([
                    'kelompok_pesan_id' => $kelompok_pesan_id,
                    'pengguna_id_1' => $pengguna_id_1,
                    'pengguna_id_2' => $pengguna_id_2,
                    'jenis_pesan_id' => 1,
                    'create_date' => DB::raw("now()"),
                    'last_update' => DB::raw("now()"),
                    'soft_delete' => 0
                ]);
            }


            // if($exe){

            // }
        }

        return response(
            [
                'sukses' => $exe ? true : false,
                'rows' => DB::connection('sqlsrv_2')->table('kelompok_pesan')
                ->where('pengguna_id_1','=',$pengguna_id_1)
                ->where('pengguna_id_2','=',$pengguna_id_2)
                ->get(),
                'kelompok_pesan_id' => $kelompok_pesan_id
            ],
            200
        );
    }

    static function getDaftarPesan(Request $request){
        $pengguna_id = $request->pengguna_id;

        $sql = "SELECT
            kelompok.total,
            kelompok.belum_dibaca,
            pengguna_1.nama AS nama_1,
            pengguna_2.nama AS nama_2,
            pengguna_1.gambar AS gambar_1,
	        pengguna_2.gambar AS gambar_2,
            pesan_terakhir.konten,
            pesan_terakhir.create_date as waktu_pesan_terakhir,
            pesan_terakhir.pengguna_id_pengirim,
            pesan_terakhir.sudah_dibaca,
            kelompok_pesan.* 
        FROM
            kelompok_pesan
            LEFT JOIN ( 
            SELECT 
                kelompok_pesan_id, 
                SUM ( 1 ) AS total,
                SUM (case when pengguna_id_pengirim = '".$pengguna_id."' then 1 else 0 end) as total_terkirim,
                SUM (case when pengguna_id_pengirim != '".$pengguna_id."' then 1 else 0 end) as total_diterima, 
                SUM (case when pengguna_id_pengirim != '".$pengguna_id."' AND sudah_dibaca = 0 then 1 else 0 end) as belum_dibaca
            FROM 
                pesan 
            WHERE 
                soft_delete = 0 
            GROUP BY 
                kelompok_pesan_id 
            ) kelompok ON kelompok.kelompok_pesan_id = kelompok_pesan.kelompok_pesan_id
            LEFT JOIN (
            SELECT
                * 
            FROM
                ( SELECT ROW_NUMBER () OVER ( PARTITION BY kelompok_pesan_id ORDER BY create_date DESC ) AS urutan,
                * FROM pesan WHERE soft_delete = 0 ) aaa 
            WHERE
                urutan = 1 
            ) pesan_terakhir ON pesan_terakhir.kelompok_pesan_id = kelompok_pesan.kelompok_pesan_id
            JOIN pengguna AS pengguna_1 ON pengguna_1.pengguna_id = kelompok_pesan.pengguna_id_1
            JOIN pengguna AS pengguna_2 ON pengguna_2.pengguna_id = kelompok_pesan.pengguna_id_2 
        WHERE
            ( pengguna_id_1 = '".$pengguna_id."' OR pengguna_id_2 = '".$pengguna_id."' ) 
            AND kelompok_pesan.soft_delete = 0 
            AND pengguna_1.soft_delete = 0 
            AND pengguna_2.soft_delete = 0 
            AND kelompok.total > 0
        ORDER BY pesan_terakhir.create_date DESC";
        
        $fetch = DB::connection('sqlsrv_2')->select($sql);

        $belum_dibaca = 0;

        for ($i=0; $i < sizeof($fetch); $i++) { 
            if($fetch[$i]->belum_dibaca > 0){
                $belum_dibaca++;
            }
        }

        return response(
            [
                'total' => sizeof($fetch),
                'rows' => $fetch,
                'belum_dibaca' => $belum_dibaca
            ],
            200
        );
    }

    static function simpanPesanDibaca(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $kelompok_pesan_id = $request->kelompok_pesan_id ? $request->kelompok_pesan_id : null;

        $exe = DB::connection('sqlsrv_2')->table('pesan')
        ->where('pesan.pengguna_id_pengirim','!=',$pengguna_id)
        ->where('kelompok_pesan_id','=',$kelompok_pesan_id)
        ->update([
            'sudah_dibaca' => 1,
            'last_update' => DB::raw("now()")
        ]);

        return response(
            [
                'sukses' => $exe ? true : false
            ],
            200
        );
    }
}