<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Str;

class AppController extends Controller
{
    public function index($value='')
    {
    	# code...
	}

    public function getWilayah(Request $request){
        $kode_wilayah = $request->input('kode_wilayah') ? $request->input('kode_wilayah') : null;
        $id_level_wilayah = $request->input('id_level_wilayah') ? $request->input('id_level_wilayah') : null;
        $mst_kode_wilayah = $request->input('mst_kode_wilayah') ? $request->input('mst_kode_wilayah') : null;
        $skip = $request->input('skip') ? $request->input('skip') : 0;
        $take = $request->input('take') ? $request->input('take') : 50;

        $fetch = DB::connection('sqlsrv_2')->table(DB::raw("ref.mst_wilayah"))
        ->whereNull('expired_date')
        ;

        if($kode_wilayah && !$mst_kode_wilayah && !$id_level_wilayah){
            $fetch->where('kode_wilayah','=',DB::raw("'".$kode_wilayah."'"));
        }

        if($id_level_wilayah){
            switch ($id_level_wilayah) {
                case 1:
                    $fetch->where('mst_kode_wilayah','=','000000');
                    break;
                case 3:
                case 2:
                    $fetch->where('mst_kode_wilayah','=',$mst_kode_wilayah);
                    break;
                default:
                    $fetch->where('mst_kode_wilayah','=','000000');
                    break;
            }
        }else if($request->input('id_level_wilayah') == "0"){
            $fetch->where('mst_kode_wilayah','=','000000');
        }

        // return $fetch->toSql();die;

        $return = array();

        $return['total'] = $fetch->select(DB::raw("sum(1) as total"))->first()->{'total'};
        $return['rows'] = $fetch->select("*")->skip($skip)->take($take)->orderBy('kode_wilayah','ASC')->get();

        return $return;
    }

    static function getGeoJsonBasic(Request $request){

		$kode_wilayah = $request->input('kode_wilayah');
		
		$str = '[';

		//baru
		switch ($request->input('id_level_wilayah')) {
			case 0:
				$col_wilayah = 's.propinsi';
				$group_wilayah_1 = 's.kode_wilayah_propinsi';
				$group_wilayah_2 = 's.id_level_wilayah_propinsi';
				$group_wilayah_3 = 's.mst_kode_wilayah_propinsi';
				$group_wilayah_4 = '';
				$group_wilayah_4_group = '';
				$params_wilayah ='';
				break;
			case 1:
				$col_wilayah = 's.kabupaten';
				$group_wilayah_1 = 's.kode_wilayah_kabupaten';
				$group_wilayah_2 = 's.id_level_wilayah_kabupaten';
				$group_wilayah_3 = 's.mst_kode_wilayah_kabupaten';
				$group_wilayah_4 = 's.mst_kode_wilayah_propinsi AS mst_kode_wilayah_induk,';
				$group_wilayah_4_group = 's.mst_kode_wilayah_propinsi,';
				$params_wilayah = " AND s.kode_wilayah_propinsi = '".$kode_wilayah."'";
				break;
			case 2:
				$col_wilayah = 's.kecamatan';
				$group_wilayah_1 = 's.kode_wilayah_kecamatan';
				$group_wilayah_2 = 's.id_level_wilayah_kecamatan';
				$group_wilayah_3 = 's.mst_kode_wilayah_kecamatan';
				$group_wilayah_4 = 's.mst_kode_wilayah_kabupaten AS mst_kode_wilayah_induk,';
				$group_wilayah_4_group = 's.mst_kode_wilayah_kabupaten,';
				$params_wilayah = " AND s.kode_wilayah_kabupaten = '".$kode_wilayah."'";
				break;
			default:
				$col_wilayah = 's.propinsi';
				$group_wilayah_1 = 's.kode_wilayah_propinsi';
				$group_wilayah_2 = 's.id_level_wilayah_propinsi';
				$group_wilayah_3 = 's.mst_kode_wilayah_propinsi';
				$group_wilayah_4 = '';
				$group_wilayah_4_group = '';
				$params_wilayah ='';
				break;
		}

        if($request->input('bentuk_pendidikan_id')){
            $arrBentuk = explode("-", $request->input('bentuk_pendidikan_id'));
            $strBentuk = "(";

            for ($iBentuk=0; $iBentuk < sizeof($arrBentuk); $iBentuk++) { 
                if($arrBentuk[$iBentuk] == '13'){
                    $strBentuk .= "13,55,";
                }else if($arrBentuk[$iBentuk] == '5'){
                    $strBentuk .= "5,53,";
                }else if($arrBentuk[$iBentuk] == '6'){
                    $strBentuk .= "6,54,";
                }else{
                    $strBentuk .= $arrBentuk[$iBentuk].",";
                }
            }

            $strBentuk = substr($strBentuk, 0, (strlen($strBentuk)-1));
            $strBentuk .= ")";

            // return $strBentuk;
            $param_bentuk = "AND s.bentuk_pendidikan_id IN ".$strBentuk;

            // return $param_bentuk;die;
        }else{
            $param_bentuk = "";
        }

		$sql = "SELECT
				{$col_wilayah} AS nama,
				{$group_wilayah_1} AS kode_wilayah,
				{$group_wilayah_2} AS id_level_wilayah,
				{$group_wilayah_3} AS mst_kode_wilayah,
				{$group_wilayah_4}
				sum(s.pd) as pd,
				sum(s.guru) as ptk,
				sum(s.guru + s.pegawai) as ptk_total,
				sum(s.pegawai) as pegawai,
				sum(s.rombel) as rombel,
				sum(1) as sekolah
			FROM
				rekap_sekolah s
			where 
				s.semester_id = ".($request->input('semester_id') ? $request->input('semester_id') : '20191')."
				{$param_bentuk}
				{$params_wilayah}
				AND s.soft_delete = 0
			GROUP BY
				{$group_wilayah_1},
				{$group_wilayah_2},
				{$group_wilayah_3},
				{$group_wilayah_4_group}
				{$col_wilayah}";

		// return $sql;die;
        $fetch = DB::connection('sqlsrv_2')
        ->select(DB::raw($sql));

        // return $fetch;die;
		// return json_encode($return);die;
        // $host = '223.27.152.200:640';
        
		$host = '118.98.166.44';
        
        // $host = 'validasi.dikdasmen.kemdikbud.go.id';

		foreach ($fetch as $rw) {

            $rw = (array)$rw;

			$geometry = @file_get_contents('http://'.$host.'/geoNew/'.substr($rw['kode_wilayah'],0,6).'.txt', true);

			if(substr($geometry, 0, 4) == '[[[['){
				$geometry = substr($geometry, 1, strlen($geometry)-2);
			}

			if(!array_key_exists('mst_kode_wilayah_induk', $rw) ){
				$induk = null;
			}else{
				$induk = $rw['mst_kode_wilayah_induk'];
			}

			$str .= '{
			    "type": "Feature",
			    "geometry": {
			        "type": "MultiPolygon",
			        "coordinates": ['.$geometry.']
			    },
			    "properties": {
			        "kode_wilayah": "'.substr($rw['kode_wilayah'],0,6).'",
			        "id_level_wilayah": "'.$rw['id_level_wilayah'].'",
			        "mst_kode_wilayah": "'.$rw['mst_kode_wilayah'].'",
			        "mst_kode_wilayah_induk": "'.$induk.'",
			        "name": "'.$rw['nama'].'",
			        "pd": '.$rw['pd'].',
			        "guru": '.$rw['ptk'].',
			        "pegawai": '.$rw['pegawai'].',
			        "rombel": '.$rw['rombel'].',
			        "sekolah": '.$rw['sekolah'].'
			    }
			},';

		}

		$str = substr($str,0,(strlen($str)-1));

		$str .= ']';
		
		return $str;
		
	}

}