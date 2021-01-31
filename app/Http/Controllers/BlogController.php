<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;
use App\Http\Controllers\RuangController;

class BlogController extends Controller
{

    static function getArtikel(Request $request){
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;
        $artikel_id = $request->artikel_id ? $request->artikel_id : null;
        $publikasi = $request->publikasi ? $request->publikasi : 99;
        $url_ramah = $request->url_ramah ? $request->url_ramah : null;

        $cek = DB::connection('sqlsrv_2')->table('blog.artikel')
        ->join('pengguna', 'pengguna.pengguna_id','=','blog.artikel.pengguna_id')
        ->where('blog.artikel.soft_delete','=',0)
        ->orderBy('blog.artikel.create_date', 'DESC')
        ->select(
            'blog.artikel.*',
            'pengguna.nama as nama_pengguna'
        )
        ;

        if($artikel_id){
            $cek->where('artikel_id','=',$artikel_id);
        }
       
        if($publikasi && $publikasi !== 99 && !$artikel_id){
            $cek->where('publikasi','=',$publikasi);
        }

        if($url_ramah){
            $cek->where('url_ramah','=',$url_ramah);
        }

        return response(
            [
                'total' => $cek->count(),
                'rows' => $cek->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanArtikel(Request $request){
        $artikel_id = $request->artikel_id ? $request->artikel_id : RuangController::generateUUID();
        $judul = $request->judul ? $request->judul : null;
        $konten = $request->konten ? $request->konten : null;
        $publikasi = $request->publikasi ? $request->publikasi : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;
        $gambar = $request->gambar ? $request->gambar : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;

        $cek = DB::connection('sqlsrv_2')->table('blog.artikel')
        ->where('artikel_id','=',$artikel_id)
        ->get();

        if(sizeof($cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('blog.artikel')
                ->where('artikel_id','=',$artikel_id)
                ->update([
                    'judul' => $judul,
                    'konten' => $konten,
                    'last_update' => DB::raw("now()"),
                    'soft_delete' => $soft_delete,
                    'publikasi' => $publikasi,
                    'gambar' => $gambar,
                    'url_ramah' => preg_replace('/[^a-zA-Z0-9\']/', '_', str_replace("?","",str_replace("/","_",str_replace(" ","_",$judul))))
                ]);
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('blog.artikel')
                ->insert([
                    'artikel_id' => $artikel_id,
                    'judul' => $judul,
                    'konten' => $konten,
                    'create_date' => DB::raw("now()"),
                    'last_update' => DB::raw("now()"),
                    'tanggal' => DB::raw("now()"),
                    'soft_delete' => $soft_delete,
                    'publikasi' => $publikasi,
                    'pengguna_id' => $pengguna_id,
                    'gambar' => $gambar,
                    'url_ramah' => preg_replace('/[^a-zA-Z0-9\']/', '_', str_replace("?","",str_replace("/","_",str_replace(" ","_",$judul))))
                ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('blog.artikel')
                ->where('artikel_id','=',$artikel_id)
                ->get()
            ],
            200
        );
    }

}