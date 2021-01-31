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

class UnitUsahaController extends Controller
{
    static function getUnitUsaha(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $unit_usaha_id = $request->unit_usaha_id ? $request->unit_usaha_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;
        
        $fetch = DB::connection('sqlsrv_2')->table('unit_usaha')
        ->where('soft_delete','=',0)
        ->orderBy('create_date','DESC')
        ;

        if($sekolah_id){
            $fetch->where('sekolah_id','=',$sekolah_id);
        }
        
        if($unit_usaha_id){
            $fetch->where('unit_usaha_id','=',$unit_usaha_id);
        }
        

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanUnitUsaha(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $unit_usaha_id = $request->unit_usaha_id ? $request->unit_usaha_id : RuangController::generateUUID();
        $nama = $request->nama ? $request->nama : null;
        $keterangan = $request->keterangan ? $request->keterangan : null;
        $gambar_logo = $request->gambar_logo ? $request->gambar_logo : null;
        $jenis_unit_usaha_id = $request->jenis_unit_usaha_id ? $request->jenis_unit_usaha_id : null;
        $aktif = $request->aktif ? $request->aktif : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $fetch = DB::connection('sqlsrv_2')->table('unit_usaha')
        ->where('unit_usaha_id','=',$unit_usaha_id)
        ->get();

        if(sizeof($fetch) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('unit_usaha')
            ->where('unit_usaha_id','=',$unit_usaha_id)
            ->update([
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }else{
            //bekum ada
            $exe = DB::connection('sqlsrv_2')->table('unit_usaha')
            ->insert([
                'unit_usaha_id' => $unit_usaha_id,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }

        return response(
            [
                'rows' => DB::connection('sqlsrv_2')->table('unit_usaha')
                ->where('unit_usaha_id','=',$unit_usaha_id)
                ->get(),
                'sukses' => $exe ? true : false
            ],
            200
        );
    }
}