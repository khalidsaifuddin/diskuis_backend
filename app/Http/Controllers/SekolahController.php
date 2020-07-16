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

class SekolahController extends Controller
{   
    static function simpanSekolah(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;
        $nama = $request->nama;
        $npsn = $request->npsn;
        $keterangan = $request->keterangan;
        $alamat = $request->alamat;
        $tipe_sekolah_id = $request->tipe_sekolah_id;
        $gambar_latar = $request->gambar_latar ? $request->gambar_latar : '/assets/berkas/2.jpg';
        $gambar_logo = $request->gambar_logo ? $request->gambar_logo : '/assets/berkas/ava-sekolah.jpg';
        $soft_delete = $request->soft_delete ? $request->soft_delete : '0';

        $fetch_cek = DB::connection('sqlsrv_2')->table('sekolah')->where('sekolah_id','=',$sekolah_id)->get();

        if(sizeof($fetch_cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('sekolah')
            ->where('sekolah_id','=',$sekolah_id)
            ->update([
                'nama' => $nama,
                'npsn' => $npsn,
                'keterangan' => $keterangan,
                'alamat' => $alamat,
                'gambar_latar' => $gambar_latar,
                'gambar_logo' => $gambar_logo,
                'tipe_sekolah_id' => $tipe_sekolah_id,
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('sekolah')->insert([
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'nama' => $nama,
                'npsn' => $npsn,
                'keterangan' => $keterangan,
                'alamat' => $alamat,
                'gambar_latar' => $gambar_latar,
                'gambar_logo' => $gambar_logo,
                'tipe_sekolah_id' => $tipe_sekolah_id,
                'kode_sekolah' => strtoupper(RuangController::generateRandomString(10)),
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }

        return response(
            [
                'success' => ($exe ? true: false),
                'rows' => DB::connection('sqlsrv_2')->table('sekolah')->where('sekolah_id','=',$sekolah_id)->get()
            ],
            200
        );
    }
}