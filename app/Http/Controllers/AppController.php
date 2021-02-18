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

	public function getVersi(Request $request){
		try {
			$fetch = DB::connection('sqlsrv_2')->table('versi')->where('jenis_versi','=','versi_app')->first()->versi_int;
		} catch (\Throwable $th) {
			//throw $th;
			$fetch = 30303;
		}

		return $fetch;
	}

	static function getRekapKumulatif(Request $request){
		// return "oke";
		$interval = $request->interval ? $request->interval : 10;
		$jenis = $request->jenis ? $request->jenis : 'kuis';

		$str_interval = "";

		for ($i=0; $i < ($interval-1); $i++) { 
			$str_interval .= "SELECT now() - INTERVAL '".($i+1)."' DAY AS tanggal UNION ";
		}

		$sql = "
		SELECT
			base_tanggal.tanggal,
			COALESCE ( (select sum(1) from {$jenis} where soft_delete = 0 and pengguna_id is not null and create_date <= cast(base_tanggal.tanggal as timestamp)), 0 ) AS total 
		FROM
			(
			SELECT SUBSTRING
				( CAST ( tanggal AS VARCHAR ( 100 )), 0, 11 ) AS tanggal 
			FROM
				(
				SELECT now() AS tanggal UNION
				{$str_interval}
				SELECT NULL
				) kumpulan_tanggal 
			WHERE
				tanggal IS NOT NULL 
			ORDER BY
				tanggal DESC 
			) base_tanggal
			LEFT JOIN (
			SELECT
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 11 ) AS tanggal,
				SUM ( 1 ) AS total 
			FROM
				{$jenis} 
			WHERE
				soft_delete = 0 
				AND pengguna_id IS NOT NULL 
			GROUP BY
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 11 ) 
			) rekap ON rekap.tanggal = base_tanggal.tanggal 
		ORDER BY
			base_tanggal.tanggal ASC
		";

		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

		return $fetch;
	}

	static function getRekapBulananKumulatif(Request $request){
		// return "oke";
		$interval = $request->interval ? $request->interval : 3;
		$jenis = $request->jenis ? $request->jenis : 'kuis';

		$str_interval = "";

		for ($i=0; $i < ($interval-1); $i++) { 
			$str_interval .= "SELECT now() - INTERVAL '".($i+1)."' MONTH AS tanggal UNION ";
		}

		$sql = "
		SELECT
			base_tanggal.tanggal,
			COALESCE (
				(
				SELECT SUM
					( 1 ) 
				FROM
					{$jenis} 
				WHERE
					soft_delete = 0 
					AND pengguna_id IS NOT NULL 
				AND create_date <= CAST ( concat ( base_tanggal.tanggal, ( CASE WHEN substr( base_tanggal.tanggal, 6 ) != '02' THEN '-30' ELSE'-28' END ) ) AS TIMESTAMP )),
				0 
			) AS total 
		FROM
			(
			SELECT SUBSTRING
				( CAST ( tanggal AS VARCHAR ( 100 )), 0, 8 ) AS tanggal 
			FROM
				(
				SELECT now() AS tanggal UNION
				{$str_interval}
				SELECT NULL
				) kumpulan_tanggal 
			WHERE
				tanggal IS NOT NULL 
			ORDER BY
				tanggal DESC 
			) base_tanggal
			LEFT JOIN (
			SELECT
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 8 ) AS tanggal,
				SUM ( 1 ) AS total 
			FROM
				{$jenis} 
			WHERE
				soft_delete = 0 
				AND pengguna_id IS NOT NULL 
			GROUP BY
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 8 ) 
			) rekap ON rekap.tanggal = base_tanggal.tanggal 
		ORDER BY
			base_tanggal.tanggal ASC
		";

		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

		return $fetch;
	}

	static function getRekap(Request $request){
		// return "oke";
		$interval = $request->interval ? $request->interval : 10;
		$jenis = $request->jenis ? $request->jenis : 'kuis';

		$str_interval = "";

		for ($i=0; $i < ($interval-1); $i++) { 
			$str_interval .= "SELECT now() - INTERVAL '".($i+1)."' DAY AS tanggal UNION ";
		}

		$sql = "
		SELECT
			base_tanggal.tanggal,
			COALESCE ( rekap.total, 0 ) AS total 
		FROM
			(
			SELECT SUBSTRING
				( CAST ( tanggal AS VARCHAR ( 100 )), 0, 11 ) AS tanggal 
			FROM
				(
				SELECT now() AS tanggal UNION
				{$str_interval}
				SELECT NULL
				) kumpulan_tanggal 
			WHERE
				tanggal IS NOT NULL 
			ORDER BY
				tanggal DESC 
			) base_tanggal
			LEFT JOIN (
			SELECT
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 11 ) AS tanggal,
				SUM ( 1 ) AS total 
			FROM
				{$jenis} 
			WHERE
				soft_delete = 0 
				AND pengguna_id IS NOT NULL 
			GROUP BY
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 11 ) 
			) rekap ON rekap.tanggal = base_tanggal.tanggal 
		ORDER BY
			base_tanggal.tanggal ASC
		";

		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

		return $fetch;
	}

	static function getRekapBulanan(Request $request){
		// return "oke";
		$interval = $request->interval ? $request->interval : 3;
		$jenis = $request->jenis ? $request->jenis : 'kuis';

		$str_interval = "";

		for ($i=0; $i < ($interval-1); $i++) { 
			$str_interval .= "SELECT now() - INTERVAL '".($i+1)."' MONTH AS tanggal UNION ";
		}

		$sql = "
		SELECT
			base_tanggal.tanggal,
			COALESCE ( rekap.total, 0 ) AS total 
		FROM
			(
			SELECT SUBSTRING
				( CAST ( tanggal AS VARCHAR ( 100 )), 0, 8 ) AS tanggal 
			FROM
				(
				SELECT now() AS tanggal UNION
				{$str_interval}
				SELECT NULL
				) kumpulan_tanggal 
			WHERE
				tanggal IS NOT NULL 
			ORDER BY
				tanggal DESC 
			) base_tanggal
			LEFT JOIN (
			SELECT
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 8 ) AS tanggal,
				SUM ( 1 ) AS total 
			FROM
				{$jenis} 
			WHERE
				soft_delete = 0 
				AND pengguna_id IS NOT NULL 
			GROUP BY
				substr( CAST ( create_date AS VARCHAR ( 100 )), 0, 8 ) 
			) rekap ON rekap.tanggal = base_tanggal.tanggal 
		ORDER BY
			base_tanggal.tanggal ASC
		";

		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

		return $fetch;
	}

	static function getJenisBerkas(Request $request){
		$fetch = DB::connection('sqlsrv_2')->table('ref.jenis_berkas')->whereNull('expired_date')->orderBy('jenis_berkas_id','ASC')->get();

		return $fetch;
	}

	static function getRataKuis(Request $request){
		$sql = "SELECT
					* 
				FROM
					(
					SELECT
						pengguna_id,
						AVG ( skor ) AS rata,
						SUM ( 1 ) AS total,
						SUM ( CASE WHEN status_mengerjakan_id = 1 THEN 1 ELSE 0 END ) AS belum_tuntas,
						SUM ( CASE WHEN status_mengerjakan_id = 2 THEN 1 ELSE 0 END ) AS sudah_tuntas
					FROM
						pengguna_kuis 
					WHERE
						soft_delete = 0 
					GROUP BY
						pengguna_id 
					) rata_kuis 
				WHERE
					rata_kuis.pengguna_id = '".$request->pengguna_id."'";
		
		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

		return $fetch;
		
	}

	static function getStatEmpu(Request $request){
		$sql = "SELECT * FROM (
				SELECT
					base_tanggal.tanggal,
					COALESCE ( total_pengguna_baru, 0 ) AS total_pengguna_baru,
					COALESCE ( kuis_baru_total, 0 ) AS kuis_baru_total,
					COALESCE ( kuis_baru_rilis, 0 ) AS kuis_baru_rilis,
					COALESCE ( kuis_baru_draft, 0 ) AS kuis_baru_draft,
					COALESCE ( peserta_kuis_total, 0 ) AS peserta_kuis_total,
					COALESCE ( ruang_baru_total, 0 ) AS ruang_baru_total 
				FROM
					(
					SELECT SUBSTRING
						( CAST ( tanggal AS VARCHAR ( 100 )), 1, 10 ) AS tanggal 
					FROM
						(
						SELECT
							now() AS tanggal UNION
						SELECT
							now() - INTERVAL '1' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '2' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '3' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '4' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '5' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '6' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '7' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '8' DAY AS tanggal UNION
						SELECT
							now() - INTERVAL '9' DAY AS tanggal 
						) kumpulan_tanggal 
					ORDER BY
						tanggal DESC 
					) base_tanggal
					LEFT JOIN (
					SELECT
						tanggal,
						SUM ( 1 ) AS total_pengguna_baru 
					FROM
						( SELECT SUBSTRING ( CAST ( create_date AS VARCHAR ( 100 )), 1, 10 ) AS tanggal,* FROM pengguna WHERE soft_delete = 0 ) penggunas 
					GROUP BY
						tanggal 
					) penggunass ON penggunass.tanggal = base_tanggal.tanggal
					LEFT JOIN (
					SELECT
						tanggal,
						SUM ( 1 ) AS kuis_baru_total,
						SUM ( CASE WHEN kuiss.publikasi = 1 THEN 1 ELSE 0 END ) AS kuis_baru_rilis,
						SUM ( CASE WHEN kuiss.publikasi = 0 THEN 1 ELSE 0 END ) AS kuis_baru_draft 
					FROM
						( SELECT SUBSTRING ( CAST ( create_date AS VARCHAR ( 100 )), 1, 10 ) AS tanggal,* FROM kuis WHERE soft_delete = 0 ) kuiss 
					GROUP BY
						tanggal 
					) kuisss ON base_tanggal.tanggal = kuisss.tanggal
					LEFT JOIN (
					SELECT
						tanggal,
						SUM ( 1 ) AS peserta_kuis_total 
					FROM
						(
						SELECT SUBSTRING
							( CAST ( pengguna_kuis.create_date AS VARCHAR ( 100 )), 1, 10 ) AS tanggal,* 
						FROM
							pengguna_kuis
							JOIN kuis ON kuis.kuis_id = pengguna_kuis.kuis_id 
						WHERE
							kuis.soft_delete = 0 
							AND pengguna_kuis.soft_delete = 0 
							AND kuis.publikasi = 1 
						) kuiss 
					GROUP BY
						tanggal 
					) pengguna_kuisss ON base_tanggal.tanggal = pengguna_kuisss.tanggal
					LEFT JOIN (
					SELECT
						tanggal,
						SUM ( 1 ) AS ruang_baru_total 
					FROM
						( SELECT SUBSTRING ( CAST ( create_date AS VARCHAR ( 100 )), 1, 10 ) AS tanggal,* FROM ruang WHERE soft_delete = 0 ) ruangs 
					GROUP BY
						tanggal 
					) ruangss ON base_tanggal.tanggal = ruangss.tanggal 
				ORDER BY
					base_tanggal.tanggal DESC 
					LIMIT 10
				) abc ORDER BY tanggal ASC";
		
		$sql2 = "
				SELECT * FROM (
				SELECT SUBSTRING
					( CAST ( tanggal AS VARCHAR ( 100 )), 1, 10 ) AS tanggal,
					(select sum(1) as total_pengguna from pengguna where soft_delete = 0 and create_date <= tanggal),
					(select sum(1) as total_kuis from kuis where soft_delete = 0 and create_date <= tanggal),
					(select sum(1) as total_ruang from ruang where soft_delete = 0 and create_date <= tanggal),
					(select sum(1) as total_peserta_kuis from pengguna_kuis peserta_kuis join kuis on kuis.kuis_id = peserta_kuis.kuis_id where peserta_kuis.soft_delete = 0 and kuis.soft_delete = 0 and kuis.publikasi = 1 and peserta_kuis.create_date <= tanggal)
				FROM
					(
					SELECT
						now() AS tanggal UNION
					SELECT
						now() - INTERVAL '1' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '2' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '3' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '4' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '5' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '6' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '7' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '8' DAY AS tanggal UNION
					SELECT
						now() - INTERVAL '9' DAY AS tanggal 
					) kumpulan_tanggal 
				ORDER BY
					tanggal DESC
				) acdc ORDER BY tanggal ASC";

		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));
		$fetch2 = DB::connection('sqlsrv_2')->select(DB::raw($sql2));

		$return = array();
		$return['diskrit'] = $fetch;
		$return['kumulatif'] = $fetch2;

		return $return;
	}

	static function getStatistik(Request $request){
		$pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
		$sql = "SELECT * FROM (SELECT
					1 as urut,
					'kuis' AS label,
					COALESCE(SUM ( 1 ),0) as jumlah
				FROM
					kuis 
				WHERE
					pengguna_id = '{$pengguna_id}' 
					AND soft_delete = 0
				UNION
				SELECT
					2 as urut,
					'ruang' AS label,
					COALESCE(SUM ( 1 ),0) as jumlah
				FROM
					ruang 
				WHERE
					pengguna_id = '{$pengguna_id}' 
					AND soft_delete = 0
				UNION
				SELECT
					3 as urut,
					'kuis_diikuti' AS label,
					COALESCE(SUM ( 1 ),0) as jumlah
				FROM
					pengguna_kuis
				join kuis on kuis.kuis_id = pengguna_kuis.kuis_id	
				WHERE
					kuis.pengguna_id = '{$pengguna_id}' 
					AND kuis.soft_delete = 0
					AND pengguna_kuis.soft_delete = 0
				UNION
				SELECT
					4 as urut,
					'ruang_diikuti' AS label,
					COALESCE(SUM ( 1 ),0) as jumlah
				FROM
					pengguna_ruang
				join ruang on ruang.ruang_id = pengguna_ruang.ruang_id
				WHERE
					ruang.pengguna_id = '{$pengguna_id}' 
					AND ruang.soft_delete = 0
					AND pengguna_ruang.soft_delete = 0
				UNION	
				SELECT
					5 as urut,
					'kuis_diikuti_hari_ini' AS label,
					COALESCE(SUM ( 1 ),0) as jumlah
				FROM
					pengguna_kuis
				join kuis on kuis.kuis_id = pengguna_kuis.kuis_id	
				WHERE
					kuis.pengguna_id = '{$pengguna_id}' 
					AND kuis.soft_delete = 0
					AND pengguna_kuis.soft_delete = 0
					AND pengguna_kuis.create_date > '".date('Y-m-d')." 00:00:00'
				UNION
				SELECT
					6 as urut,
					'ruang_diikuti_hari_ini' AS label,
					COALESCE(SUM ( 1 ),0) as jumlah
				FROM
					pengguna_ruang
				join ruang on ruang.ruang_id = pengguna_ruang.ruang_id
				WHERE
					ruang.pengguna_id = '{$pengguna_id}' 
					AND ruang.soft_delete = 0
					AND pengguna_ruang.soft_delete = 0
					AND pengguna_ruang.create_date > '".date('Y-m-d')." 00:00:00'
				) stat_all order by stat_all.urut asc";
		$fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

		return $fetch;
	}

	static function getMapel(Request $request){
		$mata_pelajaran_id = $request->mata_pelajaran_id ? $request->mata_pelajaran_id : null;
		$limit = $request->limit ? $request->limit : null;
		$trending = $request->trending ? $request->trending : null;
		
		$fetch = DB::connection('sqlsrv_2')
		->table('ref.mata_pelajaran')
		->leftJoin(DB::raw("(select mata_pelajaran_id, COALESCE(total,0) as total from (select mata_pelajaran_id, sum(1) as total from kuis where soft_delete = 0 and publikasi = 1 and status_privasi = 1 and pengguna_id is not null group by mata_pelajaran_id) as kuisnya) as kuisnya"), 'kuisnya.mata_pelajaran_id','=','ref.mata_pelajaran.mata_pelajaran_id')
		->whereNull('expired_date')
		->select(
			'ref.mata_pelajaran.*',
			'kuisnya.total'
			// DB::raw('COALESCE(kuisnya.total,0) as total')
		)
		;

		if($mata_pelajaran_id){
			$fetch->where('ref.mata_pelajaran.mata_pelajaran_id','=',$mata_pelajaran_id);
		}
		
		if($limit){
			$fetch->take($limit);
		}
		
		if($trending){
			$fetch->orderBy(DB::raw('COALESCE(kuisnya.total,0)'),'DESC');
		}else{
			$fetch->orderBy('ref.mata_pelajaran.nama', 'ASC');
		}

		// return $fetch->toSql();die;

		return $fetch->get();
	}

	public function getWilayahHirarki(Request $request){
		$kode_wilayah = $request->input('kode_wilayah') ? $request->input('kode_wilayah') : null;
		$id_level_wilayah = $request->input('id_level_wilayah') ? $request->input('id_level_wilayah') : null;

		switch ($id_level_wilayah) {
			case 3:
				$fetch = DB::connection('sqlsrv_2')->table('ref.mst_wilayah as kec')->where('kec.kode_wilayah','=',$kode_wilayah)
				->join('ref.mst_wilayah as kab','kab.kode_wilayah','=','kec.mst_kode_wilayah')
				->join('ref.mst_wilayah as prov','prov.kode_wilayah','=','kab.mst_kode_wilayah')
				->join('ref.mst_wilayah as nasional','nasional.kode_wilayah','=','prov.mst_kode_wilayah')
				->select(
					'kec.nama as kecamatan',
					'kab.nama as kabupaten',
					'prov.nama as provinsi',
					'nasional.nama as negara'
				)
				->get();
				break;
			case 2:
				$fetch = DB::connection('sqlsrv_2')->table('ref.mst_wilayah as kab')->where('kab.kode_wilayah','=',$kode_wilayah)
				->join('ref.mst_wilayah as prov','prov.kode_wilayah','=','kab.mst_kode_wilayah')
				->join('ref.mst_wilayah as nasional','nasional.kode_wilayah','=','prov.mst_kode_wilayah')
				->select(
					'kab.nama as kabupaten',
					'prov.nama as provinsi',
					'nasional.nama as negara'
				)
				->get();
				break;
			case 1:
				$fetch = DB::connection('sqlsrv_2')->table('ref.mst_wilayah as prov')->where('prov.kode_wilayah','=',$kode_wilayah)
				->join('ref.mst_wilayah as nasional','nasional.kode_wilayah','=','prov.mst_kode_wilayah')
				->select(
					'prov.nama as provinsi',
					'nasional.nama as negara'
				)
				>get();
				break;
			default:
				$fetch = DB::connection('sqlsrv_2')->table('ref.mst_wilayah as nasional')->where('nasional.kode_wilayah','=',$kode_wilayah)
				->select(
					'nasional.nama as negara'
				)
				->get();
				break;
		}

		return $fetch;
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