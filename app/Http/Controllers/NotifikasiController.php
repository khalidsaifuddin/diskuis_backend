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

// use Illuminate\Support\Facades\Redis;

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

    static function getNotifikasiRedis(Request $request){
        $pengguna_id = $request->pengguna_id;
        $tipe = $request->tipe ? $request->tipe : 'semua';

        switch ($tipe) {
            case 'semua':
                $listNotifikasi = Redis::zRange('notifikasi_semua:'.$pengguna_id, 0, -1);
                break;
            case 'belum_dibaca':
                $listNotifikasi = Redis::zRange('notifikasi_belum_dibaca:'.$pengguna_id, 0, -1);
                break;
            case 'sudah_dibaca':
                $listNotifikasi = Redis::zRange('notifikasi_sudah_dibaca:'.$pengguna_id, 0, -1);
                break;
            default:
                $listNotifikasi = Redis::zRange('notifikasi_semua:'.$pengguna_id, 0, -1);
                break;
        }

        $arrNotifikasi = array();

        for ($i=0; $i < sizeof($listNotifikasi); $i++) { 
            array_push($arrNotifikasi, json_decode(Redis::get($listNotifikasi[$i])));
        }

        return response(
            [
                'rows' => $arrNotifikasi,
                'total' => sizeof($arrNotifikasi),
                'tipe' => $tipe
            ],
            200
        );

        // return $arrNotifikasi;
    }

    static function bacaNotifikasi(Request $request){
        $notifikasi_id = $request->notifikasi_id ? $request->notifikasi_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;

        try {
            Redis::zRem('notifikasi_belum_dibaca:'.$pengguna_id, 'notifikasi:'.$notifikasi_id);
            Redis::zAdd('notifikasi_sudah_dibaca:'.$pengguna_id, sizeof(Redis::zRange('notifikasi_sudah_dibaca:'.$pengguna_id, 0, -1)), 'notifikasi:'.$notifikasi_id);

            $exe = true;

        } catch (\Throwable $th) {
            //throw $th;
            $exe = false;
        }

        return response(
            [
                'sukses' => $exe ? true : false
            ],
            200
        );
    }

    static function simpanNotifikasiSekolah(Request $request){
        $pengguna_id = $request->pengguna_id;
        $pertanyaan_id = $request->pertanyaan_id;
        $sekolah_id = $request->sekolah_id;

        $arrPengguna = array();

        $arrNotifikasi = array();
        $arrNotifikasi['notifikasi_tipe'] = 'aktivitas_sekolah';
        $arrNotifikasi['pelaku_pengguna_id'] = $pengguna_id;
        $arrNotifikasi['create_date'] = date('Y-m-d H:i:s');

        try {
            //pengguna pelakunya
            $data_pengguna = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();
        } catch (\Throwable $th) {
            //throw $th;
            $data_pengguna = null;
        }

        try {
            $data_sekolah = DB::connection('sqlsrv_2')->table('sekolah')->where('sekolah_id','=',$sekolah_id)->first();
            // array_push($arrPengguna, $data1->pengguna_id);

            $arrNotifikasi['sekolah_sekolah_id'] = $data_sekolah->sekolah_id;
            $arrNotifikasi['sekolah_nama'] = $data_sekolah->nama;

        } catch (\Throwable $th) {
            //handle error
            $data1 = null;
        }

        try {
            $data1 = DB::connection('sqlsrv_2')->table('pertanyaan')->where('pertanyaan_id','=',$pertanyaan_id)->first();

        } catch (\Throwable $th) {
            //handle error
            $data1 = null;
        }

        $arrNotifikasi['pelaku_nama'] = $data_pengguna->nama;
        $arrNotifikasi['pelaku_username'] = $data_pengguna->username;
        $arrNotifikasi['aktivitas_teks'] = substr(str_replace("&nbsp;"," ",strip_tags($data1->konten)),0,100)."...";
        $arrNotifikasi['aktivitas_pertanyaan_id'] = $pertanyaan_id;

        try {
            $data2 = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
            ->where('sekolah_id', '=', $sekolah_id)
            ->where('soft_delete', '=', 0)
            ->get();
            // array_push($arrPengguna, $data1->pengguna_id);

            for ($i=0; $i < sizeof($data2); $i++) { 

                if(in_array($data2[$i]->pengguna_id, $arrPengguna)){
                    //do nothing
                }else{

                    if($data2[$i]->pengguna_id !== $pengguna_id){
                        array_push($arrPengguna, $data2[$i]->pengguna_id);
                    }

                }

            }
            
        } catch (\Throwable $th) {
            //handle error
        }

        for ($j=0; $j < sizeof($arrPengguna); $j++) {   
            $notifikasi_id = self::generateUUID();

            // return Redis::zRange('notifikasi_semua:'.$arrPengguna[$j], 0, -1);die;

            $arrNotifikasi['notifikasi_id'] = $notifikasi_id;
            $arrNotifikasi['penerima_pengguna_id'] = $arrPengguna[$j];

            Redis::zAdd('notifikasi_semua:'.$arrPengguna[$j], sizeof(Redis::zRange('notifikasi_semua:'.$arrPengguna[$j], 0, -1)),'notifikasi:'.$notifikasi_id);
            Redis::zAdd('notifikasi_belum_dibaca:'.$arrPengguna[$j], sizeof(Redis::zRange('notifikasi_belum_dibaca:'.$arrPengguna[$j], 0, -1)), 'notifikasi:'.$notifikasi_id);
            

            Redis::set('notifikasi:'.$notifikasi_id, json_encode($arrNotifikasi));
        }

        // Redis::set('notifikasi:'.$pertanyaan_id.':'.$pengguna_id, 'notifikasi baru' );

        return $arrPengguna;
    }

    static function simpanNotifikasiRuang(Request $request){
        $pengguna_id = $request->pengguna_id;
        $pertanyaan_id = $request->pertanyaan_id;
        $ruang_id = $request->ruang_id;

        $arrPengguna = array();

        $arrNotifikasi = array();
        $arrNotifikasi['notifikasi_tipe'] = 'aktivitas_ruang';
        $arrNotifikasi['pelaku_pengguna_id'] = $pengguna_id;
        $arrNotifikasi['create_date'] = date('Y-m-d H:i:s');

        try {
            //pengguna pelakunya
            $data_pengguna = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();
        } catch (\Throwable $th) {
            //throw $th;
            $data_pengguna = null;
        }

        try {
            $data_ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$ruang_id)->first();
            // array_push($arrPengguna, $data1->pengguna_id);

            $arrNotifikasi['ruang_ruang_id'] = $data_ruang->ruang_id;
            $arrNotifikasi['ruang_nama'] = $data_ruang->nama;

        } catch (\Throwable $th) {
            //handle error
            $data1 = null;
        }

        try {
            $data1 = DB::connection('sqlsrv_2')->table('pertanyaan')->where('pertanyaan_id','=',$pertanyaan_id)->first();

        } catch (\Throwable $th) {
            //handle error
            $data1 = null;
        }

        $arrNotifikasi['pelaku_nama'] = $data_pengguna->nama;
        $arrNotifikasi['pelaku_username'] = $data_pengguna->username;
        $arrNotifikasi['aktivitas_teks'] = substr(str_replace("&nbsp;"," ",strip_tags($data1->konten)),0,100)."...";
        $arrNotifikasi['aktivitas_pertanyaan_id'] = $pertanyaan_id;

        try {
            $data2 = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->where('ruang_id', '=', $ruang_id)
            ->where('soft_delete', '=', 0)
            ->get();
            // array_push($arrPengguna, $data1->pengguna_id);

            for ($i=0; $i < sizeof($data2); $i++) { 

                if(in_array($data2[$i]->pengguna_id, $arrPengguna)){
                    //do nothing
                }else{

                    if($data2[$i]->pengguna_id !== $pengguna_id){
                        array_push($arrPengguna, $data2[$i]->pengguna_id);
                    }

                }

            }
            
        } catch (\Throwable $th) {
            //handle error
        }

        for ($j=0; $j < sizeof($arrPengguna); $j++) {   
            $notifikasi_id = self::generateUUID();

            // return Redis::zRange('notifikasi_semua:'.$arrPengguna[$j], 0, -1);die;

            $arrNotifikasi['notifikasi_id'] = $notifikasi_id;
            $arrNotifikasi['penerima_pengguna_id'] = $arrPengguna[$j];

            Redis::zAdd('notifikasi_semua:'.$arrPengguna[$j], sizeof(Redis::zRange('notifikasi_semua:'.$arrPengguna[$j], 0, -1)),'notifikasi:'.$notifikasi_id);
            Redis::zAdd('notifikasi_belum_dibaca:'.$arrPengguna[$j], sizeof(Redis::zRange('notifikasi_belum_dibaca:'.$arrPengguna[$j], 0, -1)), 'notifikasi:'.$notifikasi_id);
            

            Redis::set('notifikasi:'.$notifikasi_id, json_encode($arrNotifikasi));
        }

        // Redis::set('notifikasi:'.$pertanyaan_id.':'.$pengguna_id, 'notifikasi baru' );

        return $arrPengguna;
    }

    static function simpanNotifikasiKomentar(Request $request){
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_id = $request->input('pertanyaan_id');

        $arrPengguna = array();

        $arrNotifikasi = array();
        $arrNotifikasi['notifikasi_tipe'] = 'komentar_aktivitas';
        $arrNotifikasi['pelaku_pengguna_id'] = $pengguna_id;
        $arrNotifikasi['create_date'] = date('Y-m-d H:i:s');
        
        //get pertanyaannya
        // $fetch_pertanyaan = DB::connection('sqlsrv_2')->table('pertanyaan')

        try {
            //pengguna pelakunya
            $data_pengguna = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();
        } catch (\Throwable $th) {
            //throw $th;
            $data_pengguna = null;
        }

        try {
            $data1 = DB::connection('sqlsrv_2')->table('pertanyaan')->where('pertanyaan_id','=',$pertanyaan_id)->first();
            
            $data_pengguna_pemilik = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$data1->pengguna_id)->first();
            
            array_push($arrPengguna, $data1->pengguna_id);

            $arrNotifikasi['pemilik_pengguna_id'] = $data_pengguna_pemilik->pengguna_id;
            $arrNotifikasi['pemilik_nama'] = $data_pengguna_pemilik->nama;

        } catch (\Throwable $th) {
            //handle error
            $data1 = null;
        }

        // return $data_pengguna->nama;die;
        // return substr(str_replace("&nbsp;"," ",strip_tags($data1->konten)),0,100)."...";die;
        $arrNotifikasi['pelaku_nama'] = $data_pengguna->nama;
        $arrNotifikasi['pelaku_username'] = $data_pengguna->username;
        $arrNotifikasi['aktivitas_teks'] = substr(str_replace("&nbsp;"," ",strip_tags($data1->konten)),0,100)."...";
        $arrNotifikasi['aktivitas_pertanyaan_id'] = $pertanyaan_id;

        // return "<b>".$data_pengguna->nama."</b> memberikan komentar pada aktivitas <b></b>"
        
        try {
            $data2 = DB::connection('sqlsrv_2')->table('jawaban')
            ->where('soft_delete','=',0)
            ->where('pertanyaan_id','=',$pertanyaan_id)
            ->get();
            // array_push($arrPengguna, $data1->pengguna_id);

            for ($i=0; $i < sizeof($data2); $i++) { 

                if(in_array($data2[$i]->pengguna_id, $arrPengguna)){
                    //do nothing
                }else{

                    if($data2[$i]->pengguna_id !== $pengguna_id){
                        array_push($arrPengguna, $data2[$i]->pengguna_id);
                    }

                }

            }
            
        } catch (\Throwable $th) {
            //handle error
        }

        for ($j=0; $j < sizeof($arrPengguna); $j++) {   
            $notifikasi_id = self::generateUUID();

            // return Redis::zRange('notifikasi_semua:'.$arrPengguna[$j], 0, -1);die;

            $arrNotifikasi['notifikasi_id'] = $notifikasi_id;
            $arrNotifikasi['penerima_pengguna_id'] = $arrPengguna[$j];

            Redis::zAdd('notifikasi_semua:'.$arrPengguna[$j], sizeof(Redis::zRange('notifikasi_semua:'.$arrPengguna[$j], 0, -1)),'notifikasi:'.$notifikasi_id);
            Redis::zAdd('notifikasi_belum_dibaca:'.$arrPengguna[$j], sizeof(Redis::zRange('notifikasi_belum_dibaca:'.$arrPengguna[$j], 0, -1)), 'notifikasi:'.$notifikasi_id);
            

            Redis::set('notifikasi:'.$notifikasi_id, json_encode($arrNotifikasi));
        }

        // Redis::set('notifikasi:'.$pertanyaan_id.':'.$pengguna_id, 'notifikasi baru' );

        return $arrPengguna;
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