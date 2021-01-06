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

class PlaylistController extends Controller
{
    static function getPenggunaPlaylist(Request $request){
        $playlist_id = $request->playlist_id ? $request->playlist_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;

        $fetch = DB::connection('sqlsrv_2')->table('pengguna_playlist')->where('soft_delete','=',0);
    }

    static function simpanPlaylistKuis(Request $request){
        $kuis_playlist_id = $request->kuis_playlist_id ? $request->kuis_playlist_id : RuangController::generateUUID();
        $playlist_id = $request->playlist_id ? $request->playlist_id : null;
        $sesi_kuis_id = $request->sesi_kuis_id ? $request->sesi_kuis_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $cek = DB::connection('sqlsrv_2')->table('kuis_playlist')
        ->where('sesi_kuis_id','=',$sesi_kuis_id)
        ->where('playlist_id','=',$playlist_id)
        ->get()
        ;

        if(sizeof($cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('kuis_playlist')
            ->where('sesi_kuis_id','=',$sesi_kuis_id)
            ->where('playlist_id','=',$playlist_id)
            ->update([
                'last_update' => date('Y-m-d H:i:s'),
                'soft_delete' => $soft_delete
            ]);

        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('kuis_playlist')
            ->insert([
                'kuis_playlist_id' => $kuis_playlist_id,
                'playlist_id' => $playlist_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'pengguna_id' => $pengguna_id,
                'create_date' => date('Y-m-d H:i:s'),
                'last_update' => date('Y-m-d H:i:s'),
                'soft_delete' => $soft_delete
            ]);
        }

        return response(
            [
                'sukses' => $exe ? true : false,
                'rows' => DB::connection('sqlsrv_2')->table('kuis_playlist')
                    ->where('sesi_kuis_id','=',$sesi_kuis_id)
                    ->where('playlist_id','=',$playlist_id)
                    ->get(),
                'total' => DB::connection('sqlsrv_2')->table('kuis_playlist')
                    ->where('sesi_kuis_id','=',$sesi_kuis_id)
                    ->where('playlist_id','=',$playlist_id)
                    ->count()
            ],
            200
        );
    }

    static function getPlaylistKuis(Request $request){
        $playlist_id = $request->playlist_id ? $request->playlist_id : null;

        $fetch = DB::connection('sqlsrv_2')->table('kuis_playlist')
        ->join('pengguna','pengguna.pengguna_id','=','kuis_playlist.pengguna_id')
        ->join('sesi_kuis','sesi_kuis.sesi_kuis_id','=','kuis_playlist.sesi_kuis_id')
        ->join('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
        ->where('kuis_playlist.soft_delete','=',0)
        ->where('sesi_kuis.soft_delete','=',0)
        ->where('kuis.soft_delete','=',0)
        ->select(
            'pengguna.nama as pengguna',
            'kuis_playlist.*',
            'kuis.judul as judul',
            'sesi_kuis.kode_sesi',
            'kuis.gambar_kuis',
            'sesi_kuis.keterangan'
        )
        ->orderBy('kuis.judul','ASC');
        ;

        if($playlist_id){
            $fetch->where('kuis_playlist.playlist_id','=',$playlist_id);
        }

        return response(
            [
                'rows' => $fetch->get(),
                'total' => $fetch->count()
            ],
            200
        );
    }

    static function getPlaylist(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $playlist_id = $request->playlist_id ? $request->playlist_id : null;
        $status_privasi = $request->status_privasi ? $request->status_privasi : null;
        $keyword = $request->keyword ? $request->keyword : null;

        $fetch = DB::connection('sqlsrv_2')->table('playlist')
        ->join('pengguna','pengguna.pengguna_id','=','playlist.pengguna_id')
        ->leftJoin(DB::raw("(SELECT
            playlist_id,
            SUM ( 1 ) AS total 
        FROM
            kuis_playlist 
        WHERE
            soft_delete = 0 
        GROUP BY
            playlist_id) as kuis_playlist"), 'kuis_playlist.playlist_id','=','playlist.playlist_id')
        ->where('playlist.soft_delete','=',0)
        ->select(
            'pengguna.nama as pengguna',
            'playlist.*',
            'kuis_playlist.total as jumlah_kuis'
        )
        ->orderBy('playlist.nama','ASC')
        ;

        if($pengguna_id){
            $fetch->where('playlist.pengguna_id','=',$pengguna_id);
        }
        
        if($status_privasi){
            $fetch->where('playlist.status_privasi','=',$status_privasi);
        }
        
        if($playlist_id){
            $fetch->where('playlist.playlist_id','=',$playlist_id);
        }
        
        if($keyword){
            $fetch->where('playlist.nama','ilike',DB::raw("'%".$keyword."%'"));
        }

        return response(
            [
                'rows' => $fetch->get(),
                'total' => $fetch->count()
            ],
            200
        );
    }

    static function simpanPlaylist(Request $request){
        $nama = $request->nama ? $request->nama : null;
        $kolaboratif = $request->kolaboratif ? $request->kolaboratif : "0";
        $status_privasi = $request->status_privasi ? $request->status_privasi : null;
        $playlist_id = $request->playlist_id ? $request->playlist_id : RuangController::generateUUID();
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $fetch = DB::connection('sqlsrv_2')->table('playlist')
        ->where('playlist_id','=',$playlist_id)
        ->get();

        if(sizeof($fetch) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('playlist')
            ->where('playlist_id','=',$playlist_id)
            ->update([
                'nama' => $nama,
                'kolaboratif' => $kolaboratif,
                'status_privasi' => $status_privasi,
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);

            $exe_follower = false;
        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('playlist')
            ->insert([
                'playlist_id' => $playlist_id,
                'nama' => $nama,
                'kolaboratif' => $kolaboratif,
                'status_privasi' => $status_privasi,
                'pengguna_id' => $pengguna_id,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);

            if($exe){
                //tambah pengguna playlist
                
                $exe_follower = DB::connection('sqlsrv_2')->table('pengguna_playlist')
                ->insert([
                    'pengguna_playlist_id' => RuangController::generateUUID(),
                    'playlist_id' => $playlist_id,
                    'pengguna_id' => $pengguna_id,
                    'create_date' => DB::raw('now()::timestamp(0)'),
                    'last_update' => DB::raw('now()::timestamp(0)'),
                    'soft_delete' => $soft_delete
                ]);
            }
        }

        return response(
            [
                'rows' => DB::connection('sqlsrv_2')->table('playlist')
                ->where('playlist_id','=',$playlist_id)
                ->get(),
                'sukses' => $exe ? true : false,
                'sukses_follower' => $exe_follower ? true : false
            ],
            200
        );
    }
}