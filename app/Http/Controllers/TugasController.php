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

class TugasController extends Controller
{
    static function getTugas(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $sesi_kuis_id = $request->sesi_kuis_id ? $request->sesi_kuis_id : null;
        $tugas_id = $request->tugas_id ? $request->tugas_id : null;

        $fetch = DB::connection('sqlsrv_2')->table('tugas')
        // ->where('sesi_kuis_id','=',$sesi_kuis_id)
        // ->where('pengguna_id','=',$pengguna_id)
        // ->where('sekolah_id','=',$sekolah_id)
        ->where('soft_delete','=',0)
        ->orderBy('tugas_id','ASC')
        ;

        if($pengguna_id){
            $fetch->where('pengguna_id','=',$pengguna_id);
        }
        
        if($sesi_kuis_id){
            $fetch->where('sesi_kuis_id','=',$sesi_kuis_id);
        }
        
        if($sekolah_id){
            $fetch->where('sekolah_id','=',$sekolah_id);
        }
        
        if($tugas_id){
            $fetch->where('tugas_id','=',$tugas_id);
        }
        
        // ->get()
        ;

        return response(
            [
                'rows' => $fetch->get(),
                'total' => $fetch->count()
            ],
            200
        );
    }

    static function simpanTugas(Request $request){
        // return "oke";
        $durasi = $request->durasi ? $request->durasi : null;
        $isi = $request->isi ? $request->isi : null;
        $judul = $request->judul ? $request->judul : null;
        // $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $sesi_kuis_id = $request->sesi_kuis_id ? $request->sesi_kuis_id : null;
        $tugas_id = $request->tugas_id ? $request->tugas_id : RuangController::generateUUID();
        $tindak_lanjut = $request->tindak_lanjut ? $request->tindak_lanjut : null;
        $status_tindak_lanjut_id = $request->status_tindak_lanjut_id ? $request->status_tindak_lanjut_id : null;
        $catatan_tindak_lanjut = $request->catatan_tindak_lanjut ? $request->catatan_tindak_lanjut : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $fetch = DB::connection('sqlsrv_2')->table('tugas')
        // ->where('sesi_kuis_id','=',$sesi_kuis_id)
        // ->where('pengguna_id','=',$pengguna_id)
        // ->where('sekolah_id','=',$sekolah_id)
        ->where('tugas_id','=',$tugas_id)
        ->get();

        if(sizeof($fetch) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('tugas')
            // ->where('sesi_kuis_id','=',$sesi_kuis_id)
            // ->where('pengguna_id','=',$pengguna_id)
            // ->where('sekolah_id','=',$sekolah_id)
            ->where('tugas_id','=',$tugas_id)
            ->update([
                'durasi' => $durasi,
                'isi' => $isi,
                'judul' => $judul,
                'pengguna_id' => $pengguna_id,
                'sekolah_id' => $sekolah_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'tindak_lanjut' => $tindak_lanjut,
                'status_tindak_lanjut_id' => $status_tindak_lanjut_id,
                'catatan_tindak_lanjut' => $catatan_tindak_lanjut,
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }else{
            //bekum ada
            $exe = DB::connection('sqlsrv_2')->table('tugas')
            ->insert([
                'tugas_id' => $tugas_id,
                'durasi' => $durasi,
                'isi' => $isi,
                'judul' => $judul,
                'pengguna_id' => $pengguna_id,
                'sekolah_id' => $sekolah_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'tindak_lanjut' => $tindak_lanjut,
                'status_tindak_lanjut_id' => $status_tindak_lanjut_id,
                'catatan_tindak_lanjut' => $catatan_tindak_lanjut,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }

        return response(
            [
                'rows' => DB::connection('sqlsrv_2')->table('tugas')
                // ->where('sesi_kuis_id','=',$sesi_kuis_id)
                // ->where('pengguna_id','=',$pengguna_id)
                // ->where('sekolah_id','=',$sekolah_id)
                ->where('tugas_id','=',$tugas_id)
                ->get(),
                'sukses' => $exe ? true : false
            ],
            200
        );
    }
}