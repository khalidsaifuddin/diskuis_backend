<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;

class RefController extends Controller
{
    public function getRef(Request $request){
        $nama_tabel = $request->nama_tabel ? $request->nama_tabel : null;

        $return = array();

        $fetch =DB::connection('sqlsrv_2')->table('ref.'.$nama_tabel)->whereNull('expired_date')->get();

        $return['total'] = sizeof($fetch);
        $return['rows'] = $fetch;

        return $return;
    }

    public function getJenjang(Request $request){
        $return = array();

        $fetch =DB::connection('sqlsrv_2')->table('ref.jenjang')->whereNull('expired_date')->get();

        $return['total'] = sizeof($fetch);
        $return['rows'] = $fetch;

        return $return;
    }
    
    public function getMataPelajaran(Request $request){
        $return = array();

        $fetch =DB::connection('sqlsrv_2')->table('ref.mata_pelajaran')->whereNull('expired_date')->get();

        $return['total'] = sizeof($fetch);
        $return['rows'] = $fetch;

        return $return;
    }
    
    public function getTingkatPendidikan(Request $request){
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('ref.tingkat_pendidikan')->whereNull('expired_date');

        if($request->input('jenjang_id')){
            switch ((int)$request->input('jenjang_id')) {
                case 1:
                    $fetch->whereIn('tingkat_pendidikan_id', array(1,2,3,4,5,6));
                    break;
                case 2:
                    $fetch->whereIn('tingkat_pendidikan_id', array(7,8,9));
                    break;
                case 3:
                    $fetch->whereIn('tingkat_pendidikan_id', array(10,11,12));
                    break;
                case 4:
                    $fetch->whereIn('tingkat_pendidikan_id', array(10,11,12,13));
                    break;
                default:
                    $fetch->whereIn('tingkat_pendidikan_id', array(1,2,3,4,5,6));
                    break;
            }
        }
        
        $fetch = $fetch->get();

        $return['total'] = sizeof($fetch);
        $return['rows'] = $fetch;

        return $return;
    }
}