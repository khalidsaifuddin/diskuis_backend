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

class NotifikasiController extends Controller
{
    static public function generateUUID()
    {
        $uuid = DB::connection('sqlsrv_2')
        ->table(DB::raw('pengguna'))
        ->select(DB::raw('uuid_generate_v4() as uuid'))
        ->first();

        return $uuid->{'uuid'};
    }

    static public function getNotifikasi(Request $request){

        // return "oke";die;
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $notifikasi_id = $request->input('notifikasi_id') ? $request->input('notifikasi_id') : null;
        $dibaca = $request->input('dibaca');
        $return = array();

        // return $dibaca;die;

        $fetch = DB::connection('sqlsrv_2')->table('notifikasi')
        ->join('pengguna','pengguna.pengguna_id','=','notifikasi.pengguna_id')
        ->where('notifikasi.soft_delete','=',0)
        ->take(20)
        ->select(
            'notifikasi.*',
            'pengguna.nama as pengguna'
        )
        ->orderBy('notifikasi.create_date','DESC');

        if($pengguna_id){
            $fetch->where('notifikasi.pengguna_id','=',$pengguna_id);
        }
        
        if($dibaca){
            $fetch->where('notifikasi.dibaca','=',$dibaca);
        }

        if($notifikasi_id){
            $fetch->where('notifikasi.notifikasi_id','=',$pertanyaan_id);
        }

        // return $fetch->toSql();die;

        $fetch = $fetch->get();

        $return['result_dibaca'] = 0;
        $return['result_belum_dibaca'] = 0;

        for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
            if((int)$fetch[$iFetch]->dibaca == 1){
                $return['result_belum_dibaca'] = $return['result_belum_dibaca']+1;
            }else{
                $return['result_dibaca'] = $return['result_dibaca']+1;
            }
        }

        $return['rows'] = $fetch;
        $return['result'] = sizeof($fetch);

        return $return;
    }

    static public function simpanNotifikasi(Request $request){
        // return "oke";
        $judul = $request->input('judul');
        $konten = $request->input('konten');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_id = $request->input('pertanyaan_id');
        $notifikasi_id = $request->input('notifikasi_id') ? $request->input('notifikasi_id') : self::generateUUID();
        $jenis_notifikasi_id = $request->input('jenis_notifikasi_id');
        $tautan = $request->input('tautan');
        $dibaca = $request->input('dibaca');
        $pengguna_id_pengirim = $request->input('pengguna_id_pengirim');

        $return = array();

        if($dibaca == 2){

            $insert = DB::connection('sqlsrv_2')->table('notifikasi')
            ->where('notifikasi_id','=',$notifikasi_id)
            ->update([
                'dibaca' => $dibaca,
                'last_update' => DB::raw("now()")
            ]);

        }else{

            $insert = DB::connection('sqlsrv_2')->table('notifikasi')->insert([
                'judul' => $judul,
                'konten' => $konten,
                'pengguna_id' => $pengguna_id,
                'pertanyaan_id' => $pertanyaan_id,
                'notifikasi_id' => $notifikasi_id,
                'jenis_notifikasi_id' => $jenis_notifikasi_id,
                'tautan' => $tautan,
                'pengguna_id_pengirim' => $pengguna_id_pengirim 
            ]);

        }


        if($insert){
            $return['sukses'] = true;
            $return['rows'] = DB::connection('sqlsrv_2')->table('pertanyaan')->where('pertanyaan_id','=',$pertanyaan_id)->first();
        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
    }
}