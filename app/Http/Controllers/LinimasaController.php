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

class LinimasaController extends Controller
{
    static function getLinimasa(Request $request){
        $pengguna_id = $request->pengguna_id;
        $ruang_id = $request->ruang_id;
        $start = $request->start ? $request->start : 0;

        $sql = "SELECT
                    pengguna_ruang.*,
                    pengguna.gambar,
                    pengguna.nama as nama_pengguna,
                    linimasa.*	
                FROM
                    pengguna_ruang 
                    join linimasa on linimasa.ruang_id = pengguna_ruang.ruang_id ".($ruang_id ? "and linimasa.pengguna_id_pelaku = pengguna_ruang.pengguna_id" : "")."
                    join pengguna on pengguna.pengguna_id = linimasa.pengguna_id_pelaku
                WHERE
                    pengguna_ruang.soft_delete = 0
                    ".($pengguna_id ? "AND pengguna_ruang.pengguna_id = '".$pengguna_id."'" : ""). 
                    "AND linimasa.Soft_delete = 0
                    ".($ruang_id ? "AND pengguna_ruang.ruang_id = '".$ruang_id."'" : ""). 
                "ORDER BY linimasa.create_date DESC
                OFFSET ".$start." LIMIT 20";
    
        // return $sql;die;

        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        return response(
            [
                'rows' => $fetch,
                'total' => sizeof($fetch)
            ],
            200
        );

    }

    static function simpanLinimasa($linimasa_id, $pengguna_id, $jenis_linimasa_id, $keterangan, $tautan, $ruang_id=null, $sesi_kuis_id=null){
        $fetch_cek =  DB::connection('sqlsrv_2')->table('linimasa')
        ->where('linimasa_id','=', $linimasa_id)
        ->get();

        switch ($jenis_linimasa_id) {
            case 1:
                $pengguna = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();
                $ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$ruang_id)->first();
                
                $keterangan_teks = "<b>".$pengguna->nama . "</b> bergabung dengan ruang <b>".$ruang->nama."</b>";
                $tautan_teks = '/tampilRuang/'.$ruang->ruang_id;
                break;
            case 2:
                $pengguna = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();
                $sesi_kuis = DB::connection('sqlsrv_2')->table('sesi_kuis')->where('sesi_kuis_id','=',$sesi_kuis_id)->first();
                $kuis = DB::connection('sqlsrv_2')->table('kuis')->where('kuis_id','=',$sesi_kuis->kuis_id)->first();
                $ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$sesi_kuis->ruang_id)->first();
                
                $keterangan_teks = "<b>".$pengguna->nama . "</b> menambahkan kuis <b>".$kuis->judul."</b> pada ruang <b>".$ruang->nama."</b>";
                $tautan_teks = '/tampilRuang/'.$ruang->ruang_id;
                break;
            case 3:
                $pengguna = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();
                $sesi_kuis = DB::connection('sqlsrv_2')->table('sesi_kuis')->where('sesi_kuis_id','=',$sesi_kuis_id)->first();
                $kuis = DB::connection('sqlsrv_2')->table('kuis')->where('kuis_id','=',$sesi_kuis->kuis_id)->first();
                $ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$sesi_kuis->ruang_id)->first();
                
                $keterangan_teks = "<b>".$pengguna->nama . "</b> mengikuti kuis <b>".$kuis->judul."</b> di ruang <b>".$ruang->nama."</b>";
                $tautan_teks = '/peringkatKuis/'.$sesi_kuis_id;
                break;
            default:
                # code...
                $keterangan_teks = '';
                break;
        }


        if(sizeof($fetch_cek) > 0){
            //sudah ada (update)

            $exe = DB::connection('sqlsrv_2')->table('linimasa')
            ->where('linimasa_id','=',$linimasa_id)
            ->update([
                'pengguna_id_pelaku' => $pengguna_id,
                'jenis_linimasa_id' => $jenis_linimasa_id,
                'keterangan' => $keterangan_teks,
                'tautan' => $tautan_teks,
                'ruang_id' => $ruang->ruang_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'last_update'=> DB::raw('now()::timestamp(0)')
            ]);
            $label='UPDATE';

        }else{
            //belum ada (insert)
            $exe = DB::connection('sqlsrv_2')->table('linimasa')
            ->insert([
                'linimasa_id' => $linimasa_id,
                'pengguna_id_pelaku' => $pengguna_id,
                'jenis_linimasa_id' => $jenis_linimasa_id,
                'keterangan' => $keterangan_teks,
                'tautan' => $tautan_teks,
                'ruang_id' => $ruang->ruang_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'last_update'=> DB::raw('now()::timestamp(0)'),
                'create_date'=> DB::raw('now()::timestamp(0)'),
                'soft_delete'=> 0
            ]);
            $label='INSERT';
        }

        return response(
            [
                'rows' => $exe = DB::connection('sqlsrv_2')->table('linimasa')->where('linimasa_id','=',$linimasa_id)->get(),
                'success' => ($exe ? true : false),
                'label' => $label
            ],
            200
        );
    }

    static function simpanAktivitas($aktivitas_id, $pengguna_id, $jenis_aktivitas_id, $keterangan, $related_id){
        $fetch_cek =  DB::connection('sqlsrv_2')->table('aktivitas')
        ->where('aktivitas_id','=', $aktivitas_id)
        ->get();

        switch ($jenis_aktivitas_id) {
            case 1:
                
                break;
            case 2:
                # code...
                break;
            case 3:
                # code...
                break;
            default:
                # code...
                break;
        }

        if(sizeof($fetch_cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('aktivitas')
            ->where('aktivitas_id','=',$aktivitas_id)
            ->update([
                'pengguna_id' => $pengguna_id,
                'jenis_aktivitas_id' => $jenis_aktivitas_id,
                'keterangan' => $keterangan,
                'related_id' => $related_id,
                'last_update'=> DB::raw('now()::timestamp(0)')
            ]);
            $label='UPDATE';
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('aktivitas')
            ->insert([
                'aktivitas_id' => $aktivitas_id,
                'pengguna_id' => $pengguna_id,
                'jenis_aktivitas_id' => $jenis_aktivitas_id,
                'keterangan' => $keterangan,
                'related_id' => $related_id,
                'last_update'=> DB::raw('now()::timestamp(0)'),
                'create_date'=> DB::raw('now()::timestamp(0)'),
                'soft_delete'=> 0
            ]);
            $label='INSERT';
        }

        return response(
            [
                'rows' => $exe = DB::connection('sqlsrv_2')->table('aktivitas')->where('aktivitas_id','=',$aktivitas_id)->get(),
                'success' => ($exe ? true : false),
                'label' => $label
            ],
            200
        );
    }
}

