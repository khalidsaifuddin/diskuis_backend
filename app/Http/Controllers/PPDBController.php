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

class PPDBController extends Controller
{
	static function distance($lat1, $lon1, $lat2, $lon2) { 
        $pi80 = M_PI / 180; 
        $lat1 *= $pi80; 
        $lon1 *= $pi80; 
        $lat2 *= $pi80; 
        $lon2 *= $pi80; 
        $r = 6372.797; // mean radius of Earth in km 
        $dlat = $lat2 - $lat1; 
        $dlon = $lon2 - $lon1; 
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2); 
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a)); 
        $km = $r * $c; 
        //echo ' '.$km; 
        return $km; 
	}

	static function getSekolahPPDB(Request $request){
		$keyword = $request->keyword ? $request->keyword : null;
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;
		$kode_wilayah = $request->kode_wilayah ? $request->kode_wilayah : '052100';
		$bentuk_pendidikan_id = $request->bentuk_pendidikan_id ? $request->bentuk_pendidikan_id : null;
		$status_sekolah = $request->status_sekolah ? $request->status_sekolah : null;
		$keyword = $request->keyword ? $request->keyword : null;
		$lintang = $request->lintang ? $request->lintang : null;
		$bujur = $request->bujur ? $request->bujur : null;
		$dengan_tk = $request->dengan_tk ? $request->dengan_tk : 'Y';
		$untuk_pilihan = $request->untuk_pilihan ? $request->untuk_pilihan : 'N';
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$urutkan = $request->urutkan ? $request->urutkan : 'az';

		$fetch = DB::connection('sqlsrv_2')->table('sekolah')
		->join('ref.mst_wilayah as kec','kec.kode_wilayah','=',DB::raw("left(sekolah.kode_wilayah,6)"))
		->join('ref.mst_wilayah as kab','kab.kode_wilayah','=','kec.mst_kode_wilayah')
		->join('ref.mst_wilayah as prov','prov.kode_wilayah','=','kab.mst_kode_wilayah')	
		->leftJoin('ref.bentuk_pendidikan as bp','bp.bentuk_pendidikan_id','=','sekolah.bentuk_pendidikan_id')	
		->select(
			'sekolah.*',
			'kec.nama as kecamatan',
			'kab.nama as kabupaten',
			'prov.nama as provinsi',
			'bp.nama as bentuk'
		)
		;

		if($sekolah_id){
			$fetch->where('sekolah.sekolah_id','=',$sekolah_id);
		}
		
		if($kode_wilayah){
			$fetch->where('kab.kode_wilayah','=',$kode_wilayah);
		}
		
		if($bentuk_pendidikan_id && $bentuk_pendidikan_id !== 99){
			$fetch->where('sekolah.bentuk_pendidikan_id','=',$bentuk_pendidikan_id);
		}
		
		if($status_sekolah && $status_sekolah !== 99){
			$fetch->where('sekolah.status_sekolah','=',$status_sekolah);
		}

		if($dengan_tk !== 'Y'){
			$fetch->whereIn('sekolah.bentuk_pendidikan_id',array(5,6));
		}

		if($keyword){
			$fetch->where(function($query) use($keyword){
				$query->where('sekolah.nama', 'ilike', DB::raw("'%".$keyword."%'"))
					->orWhere('sekolah.npsn','ilike', DB::raw("'%".$keyword."%'"))
					->orWhere('sekolah.alamat','ilike', DB::raw("'%".$keyword."%'"))
				;
			});
		}

		if($untuk_pilihan === 'Y'){
			$fetch->leftJoin('ppdb.sekolah_pilihan', function($join) use ($peserta_didik_id)
			{
				$join->on('ppdb.sekolah_pilihan.sekolah_id', '=', 'sekolah.sekolah_id');
				$join->on('ppdb.sekolah_pilihan.peserta_didik_id', '=', DB::raw("'".$peserta_didik_id."'"));
				$join->on('ppdb.sekolah_pilihan.soft_delete', '=', DB::raw("0"));
			});
			
			$fetch->select(
				'sekolah.*',
				'kec.nama as kecamatan',
				'kab.nama as kabupaten',
				'prov.nama as provinsi',
				'bp.nama as bentuk',
				'ppdb.sekolah_pilihan.sekolah_pilihan_id'
			);
		}

		if($urutkan){
			if($urutkan === 'az'){
				$fetch->orderBy('sekolah.nama','ASC');
			}else if($urutkan === 'za'){
				$fetch->orderBy('sekolah.nama','DESC');
			}
		}

		$return = array();
		$return['total'] = $fetch->count();
		
		$data = $fetch->skip($start)->take($limit)->get();

		for ($i=0; $i < sizeof($data); $i++) { 
			if($lintang && $bujur){
				$data[$i]->jarak = self::distance($lintang, $bujur, $data[$i]->lintang,$data[$i]->bujur);
			}
		}

        $return['rows'] = $data;

        return $return;
	}

    static function getPesertaDidikDapodik(Request $request){
        $keyword = $request->keyword ? $request->keyword : null;
        $peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $fetch = DB::connection('sqlsrv_2')->table('dapo.peserta_didik')
		->join('dapo.sekolah','dapo.sekolah.sekolah_id','=','dapo.peserta_didik.sekolah_id')
		->leftJoin('ppdb.calon_peserta_didik as calon','calon.calon_peserta_didik_id','=','dapo.peserta_didik.peserta_didik_id')
        ->where(function($query) use($keyword){
            $query->where('dapo.peserta_didik.nama', 'ilike', DB::raw("'%".$keyword."%'"))
            ->orWhere('dapo.peserta_didik.nik','ilike', DB::raw("'%".$keyword."%'"))
            ->orWhere('dapo.peserta_didik.nisn','ilike', DB::raw("'%".$keyword."%'"))
            ;
        })
        ->select(
            'dapo.peserta_didik.*',
            'dapo.sekolah.nama as nama_sekolah',
			'dapo.sekolah.npsn as npsn',
			'calon.calon_peserta_didik_id'
        )
        ->orderBy('dapo.peserta_didik.nama', 'ASC')
        // ->get()
        ;

        if($peserta_didik_id){
            $fetch->where('peserta_didik_id','=',$peserta_didik_id);
		}
		
		if($sekolah_id){
			$fetch_sekolah = DB::connection('sqlsrv_2')->table('sekolah')
			->where('sekolah_id','=',$sekolah_id)
			->first();

			if($fetch_sekolah){
				switch ($fetch_sekolah->bentuk_pendidikan_id) {
					case 5:
						$fetch->whereNotIn('tingkat_pendidikan_id',array(1,2,3,4,5,6,7,8,9,10,11,12,13,14));
						break;
					case 6:
						$fetch->whereIn('tingkat_pendidikan_id',array(4,5,6));
						break;
					default:
						# code...
						break;
				}
			}
		}

        $return = array();
        $return['total'] = $fetch->count();
        $return['rows'] = $fetch->skip($start)->take($limit)->get();

        return $return;
    }

    public function cekNik(Request $request){
		$nik = $request->input('nik') ? $request->input('nik') : null;
		$calon_peserta_didik_id = $request->input('calon_peserta_didik_id') ? $request->input('calon_peserta_didik_id') : null;

		if($nik){
			$fetch = DB::connection('sqlsrv_2')
			->table('ppdb.calon_peserta_didik')
			->where('ppdb.calon_peserta_didik.nik','=',$nik)
			->where('ppdb.calon_peserta_didik.soft_delete','=',0);

			if($calon_peserta_didik_id){
				$fetch->whereNotIn('ppdb.calon_peserta_didik.calon_peserta_didik_id',array($calon_peserta_didik_id));
			}
			
			$fetch = $fetch->get();

			return response([ 'rows' => $fetch, 'count' => sizeof($fetch) ], 201);
			
		}else{
			return response([ 'rows' => [], 'count' => 1 ], 201);
		}
	}

	public function cekNISN(Request $request){
		$nisn = $request->input('nisn') ? $request->input('nisn') : null;
		$calon_peserta_didik_id = $request->input('calon_peserta_didik_id') ? $request->input('calon_peserta_didik_id') : null;

		if($nisn){
			$fetch = DB::connection('sqlsrv_2')
			->table('ppdb.calon_peserta_didik')
			->where('nisn','=',$nisn)
			->where('soft_delete','=',0);

			if($calon_peserta_didik_id){
				$fetch->whereNotIn('calon_peserta_didik_id',array($calon_peserta_didik_id));
			}
			
			$fetch = $fetch->get();

			return response([ 'rows' => $fetch, 'count' => sizeof($fetch) ], 201);
			
		}else{
			return response([ 'rows' => [], 'count' => 1 ], 201);
		}
	}

	public function simpanCalonPesertaDidik(Request $request){

		// return $request->input('')
		$peserta_didik_id = $request->input('peserta_didik_id') ? $request->input('peserta_didik_id') : null;

		$fetch_cek = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')->where('calon_peserta_didik_id','=',$peserta_didik_id)->get();

		if(sizeof($fetch_cek) > 0){
			//update
			$label = 'update';

			$arrValue = [
				"last_update" => DB::raw('now()::timestamp(0)'), 
				"soft_delete" => 0, 
				"nik" => $request->input('nik'), 
				"jenis_kelamin" => $request->input('jenis_kelamin'), 
				"tempat_lahir" => $request->input('tempat_lahir'), 
				"tanggal_lahir" => $request->input('tanggal_lahir'), 
				"asal_sekolah_id" => $request->input('asal_sekolah_id'), 
				"alamat_tempat_tinggal" => $request->input('alamat_tempat_tinggal'), 
				"kode_wilayah_kecamatan" => $request->input('kode_wilayah_kecamatan'), 
				"kode_pos" => $request->input('kode_pos'), 
				"lintang" => str_replace(",",".",$request->input('lintang')), 
				"bujur" => str_replace(",",".",$request->input('bujur')), 
				"nama_ayah" => $request->input('nama_ayah'), 
				"tempat_lahir_ayah" => $request->input('tempat_lahir_ayah'), 
				"tanggal_lahir_ayah" => $request->input('tanggal_lahir_ayah'), 
				"pendidikan_terakhir_id_ayah" => $request->input('pendidikan_terakhir_id_ayah'), 
				"pekerjaan_id_ayah" => $request->input('pekerjaan_id_ayah'), 
				"alamat_tempat_tinggal_ayah" => $request->input('alamat_tempat_tinggal_ayah'), 
				"no_telepon_ayah" => $request->input('no_telepon_ayah'), 
				"nama_ibu" => $request->input('nama_ibu'), 
				"tempat_lahir_ibu" => $request->input('tempat_lahir_ibu'), 
				"pendidikan_terakhir_id_ibu" => $request->input('pendidikan_terakhir_id_ibu'), 
				"pekerjaan_id_ibu" => $request->input('pekerjaan_id_ibu'), 
				"alamat_tempat_tinggal_ibu" => $request->input('alamat_tempat_tinggal_ibu'), 
				"no_telepon_ibu" => $request->input('no_telepon_ibu'), 
				"nama_wali" => $request->input('nama_wali'), 
				"tempat_lahir_wali" => $request->input('tempat_lahir_wali'), 
				"tanggal_lahir_wali" => $request->input('tanggal_lahir_wali'), 
				"pekerjaan_id_wali" => $request->input('pekerjaan_id_wali'), 
				"tanggal_lahir_ibu" => $request->input('tanggal_lahir_ibu'), 
				"alamat_tempat_tinggal_wali" => $request->input('alamat_tempat_tinggal_wali'), 
				"no_telepon_wali" => $request->input('no_telepon_wali'), 
				"orang_tua_utama" => $request->input('orang_tua_utama'), 
				"rt" => $request->input('rt'), 
				"rw" => $request->input('rw'), 
				"pengguna_id" => $request->input('pengguna_id'), 
				"periode_kegiatan_id" => '2021', 
				"kode_wilayah_kabupaten" => $request->input('kode_wilayah_kabupaten'), 
				"kode_wilayah_provinsi" => $request->input('kode_wilayah_provinsi'), 
				"dusun" => $request->input('dusun'), 
				"desa_kelurahan" => $request->input('desa_kelurahan'), 
				"nama" => $request->input('nama'), 
				"nisn" => $request->input('nisn'), 
				"pendidikan_terakhir_id_wali" => $request->input('pendidikan_terakhir_id_wali')
			];

			$exe = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
			->where('calon_peserta_didik_id','=',$peserta_didik_id)
			->update($arrValue);
		}else{
			//insert
			// $pd_id = Str::uuid();

			$arrValue = [
				"calon_peserta_didik_id" => $peserta_didik_id,
				"create_date" => DB::raw('now()::timestamp(0)'), 
				"last_update" => DB::raw('now()::timestamp(0)'), 
				"soft_delete" => 0, 
				"nik" => $request->input('nik'), 
				"jenis_kelamin" => $request->input('jenis_kelamin'), 
				"tempat_lahir" => $request->input('tempat_lahir'), 
				"tanggal_lahir" => $request->input('tanggal_lahir'), 
				"asal_sekolah_id" => $request->input('asal_sekolah_id'), 
				"alamat_tempat_tinggal" => $request->input('alamat_tempat_tinggal'), 
				"kode_wilayah_kecamatan" => $request->input('kode_wilayah_kecamatan'), 
				"kode_pos" => $request->input('kode_pos'), 
				"lintang" => str_replace(",",".",$request->input('lintang')), 
				"bujur" => str_replace(",",".",$request->input('bujur')), 
				"nama_ayah" => $request->input('nama_ayah'), 
				"tempat_lahir_ayah" => $request->input('tempat_lahir_ayah'), 
				"tanggal_lahir_ayah" => $request->input('tanggal_lahir_ayah'), 
				"pendidikan_terakhir_id_ayah" => $request->input('pendidikan_terakhir_id_ayah'), 
				"pekerjaan_id_ayah" => $request->input('pekerjaan_id_ayah'), 
				"alamat_tempat_tinggal_ayah" => $request->input('alamat_tempat_tinggal_ayah'), 
				"no_telepon_ayah" => $request->input('no_telepon_ayah'), 
				"nama_ibu" => $request->input('nama_ibu'), 
				"tempat_lahir_ibu" => $request->input('tempat_lahir_ibu'), 
				"pendidikan_terakhir_id_ibu" => $request->input('pendidikan_terakhir_id_ibu'), 
				"pekerjaan_id_ibu" => $request->input('pekerjaan_id_ibu'), 
				"alamat_tempat_tinggal_ibu" => $request->input('alamat_tempat_tinggal_ibu'), 
				"no_telepon_ibu" => $request->input('no_telepon_ibu'), 
				"nama_wali" => $request->input('nama_wali'), 
				"tempat_lahir_wali" => $request->input('tempat_lahir_wali'), 
				"tanggal_lahir_wali" => $request->input('tanggal_lahir_wali'), 
				"pekerjaan_id_wali" => $request->input('pekerjaan_id_wali'), 
				"tanggal_lahir_ibu" => $request->input('tanggal_lahir_ibu'), 
				"alamat_tempat_tinggal_wali" => $request->input('alamat_tempat_tinggal_wali'), 
				"no_telepon_wali" => $request->input('no_telepon_wali'), 
				"orang_tua_utama" => $request->input('orang_tua_utama'), 
				"rt" => $request->input('rt'), 
				"rw" => $request->input('rw'), 
				"pengguna_id" => $request->input('pengguna_id'), 
				"periode_kegiatan_id" => '2021', 
				"kode_wilayah_kabupaten" => $request->input('kode_wilayah_kabupaten'), 
				"kode_wilayah_provinsi" => $request->input('kode_wilayah_provinsi'), 
				"dusun" => $request->input('dusun'), 
				"desa_kelurahan" => $request->input('desa_kelurahan'), 
				"nama" => $request->input('nama'), 
				"nisn" => $request->input('nisn'), 
				"pendidikan_terakhir_id_wali" => $request->input('pendidikan_terakhir_id_wali')
			];

			$label = 'insert';
			$exe = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')->insert($arrValue);
		}

		if($exe){
			return response([ 'success' => true, 'peserta_didik_id' => ($peserta_didik_id ? $peserta_didik_id : $pd_id),'rows' => DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')->where('calon_peserta_didik_id','=', ($peserta_didik_id ? $peserta_didik_id : $pd_id))->get() ], 201);
		}else{
			return response([ 'success' => false, 'peserta_didik_id' => null ], 201);
		}

		// return $label;
	}

	public function simpanLintangBujur(Request $request){
		$peserta_didik_id = $request->input('peserta_didik_id') ? $request->input('peserta_didik_id') : null;
		$lintang = $request->input('lintang') ? $request->input('lintang') : null;
		$bujur = $request->input('bujur') ? $request->input('bujur') : null;

		$exe = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
			->where('calon_peserta_didik_id','=',$peserta_didik_id)
			->update([
				'lintang' => $lintang,
				'bujur' => $bujur,
				'last_update' => DB::raw('now()::timestamp(0)')
			]);
		
			return response([ 'success' => true, 'peserta_didik_id' => ($peserta_didik_id),'rows' => DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')->where('calon_peserta_didik_id','=', ($peserta_didik_id))->get() ], 201);
	}

	static function getCalonPesertaDidik(Request $request){
		$keyword = $request->keyword ? $request->keyword : null;
        $peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

		$fetch = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
		->join('ref.mst_wilayah as kec','kec.kode_wilayah','=',DB::raw("LEFT(ppdb.calon_peserta_didik.kode_wilayah_kecamatan,6)"))
		->join('ref.mst_wilayah as kab','kab.kode_wilayah','=','kec.mst_kode_wilayah')
		->join('ref.mst_wilayah as prov','prov.kode_wilayah','=','kab.mst_kode_wilayah')
		->leftJoin('sekolah','sekolah.sekolah_id','=','ppdb.calon_peserta_didik.asal_sekolah_id')
        ->select(
			'ppdb.calon_peserta_didik.*',
			'kec.nama as kecamatan',
			'kab.nama as kabupaten',
			'prov.nama as provinsi',
			'sekolah.nama as asal_sekolah'
        )
        ->orderBy('ppdb.calon_peserta_didik.create_date', 'DESC')
        // ->orderBy('ppdb.calon_peserta_didik.nama', 'ASC')
		;
		
		if($keyword){
			$fetch->where(function($query) use($keyword){
				$query->where('ppdb.calon_peserta_didik.nama', 'ilike', DB::raw("'%".$keyword."%'"))
				->orWhere('ppdb.calon_peserta_didik.nik','ilike', DB::raw("'%".$keyword."%'"))
				->orWhere('ppdb.calon_peserta_didik.nisn','ilike', DB::raw("'%".$keyword."%'"))
				;
			});
		}

        if($peserta_didik_id){
            $fetch->where('ppdb.calon_peserta_didik.calon_peserta_didik_id','=',$peserta_didik_id);
		}
		
		if($sekolah_id){
			$fetch->join('ppdb.sekolah_pilihan', function($join) use ($sekolah_id)
			{
				$join->on('ppdb.sekolah_pilihan.sekolah_id', '=', DB::raw("'".$sekolah_id."'"));
				$join->on('ppdb.sekolah_pilihan.peserta_didik_id', '=', 'ppdb.calon_peserta_didik.calon_peserta_didik_id');
				$join->on('ppdb.sekolah_pilihan.soft_delete', '=', DB::raw("0"));
			})
			->join('ref_ppdb.jalur as jalur','jalur.jalur_id','=','ppdb.sekolah_pilihan.jalur_id')
			->select(
				'ppdb.calon_peserta_didik.*',
				'kec.nama as kecamatan',
				'kab.nama as kabupaten',
				'prov.nama as provinsi',
				'ppdb.sekolah_pilihan.*',
				'sekolah.nama as asal_sekolah',
				'jalur.nama as jalur'
			);
		}
		
		$return = array();
        $return['total'] = $fetch->count();
		
		$data = $fetch->skip($start)->take($limit)->get();

		for ($i=0; $i < sizeof($data); $i++) { 
			$fetch_berkas = DB::connection('sqlsrv_2')->table('ppdb.berkas_calon')
			->where('ppdb.berkas_calon.soft_delete','=',0)
			->where('ppdb.berkas_calon.calon_peserta_didik_id','=',$data[$i]->calon_peserta_didik_id)
			->get();

			$data[$i]->berkas_calon = $fetch_berkas;

			//poin prestasi
			$fetch_prestasi = DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
			->where('peserta_didik_id','=',$data[$i]->calon_peserta_didik_id)
			->get();

			if(sizeof($fetch_prestasi) > 0){

				$skor = (
					(float)$fetch_prestasi[0]->nilai_semester_1 +
					(float)$fetch_prestasi[0]->nilai_semester_2 +
					(float)$fetch_prestasi[0]->nilai_semester_3 +
					(float)$fetch_prestasi[0]->nilai_semester_4 +
					(float)$fetch_prestasi[0]->nilai_semester_5
				) / (float)5;

				$fetch_prestasi[0]->skor = $skor;

				$data[$i]->nilai_prestasi = $fetch_prestasi[0];
			}else{
				$data[$i]->nilai_prestasi = array();
			}
		}

        $return['rows'] = $data;

        return $return;
	}

	static function getJalur(Request $request){
		$jalur_id = $request->jalur_id ? $request->jalur_id : null;
		$level_jalur = $request->level_jalur ? $request->level_jalur : 1;
		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;

		$fetch = DB::connection('sqlsrv_2')->table('ref_ppdb.jalur')
		->whereNull('expired_date')
		;

		if($jalur_id){
			$fetch->where('jalur_id','=',$jalur_id);
		}
		
		if($level_jalur){
			$fetch->where('level_jalur','=',$level_jalur);
		}

		if($sekolah_id){
			$fetch_sekolah = DB::connection('sqlsrv_2')->table('sekolah')
			->where('sekolah_id','=',$sekolah_id)
			->first();

			if($fetch_sekolah){
				switch ($fetch_sekolah->bentuk_pendidikan_id) {
					case 5:
						$fetch->whereNotIn('jalur_id',array('3'));
						break;
					case 6:
						# do nothing
						break;
					default:
						# code...
						break;
				}
			}
		}

		$return = array();
        $return['total'] = $fetch->count();
        $return['rows'] = $fetch->skip($start)->take($limit)->get();

        return $return;
		
	}

	static function cekSekolahPilihan(Request $request){
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;

		$fetch = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan as sekolah_pilihan')
		->where('sekolah_pilihan.soft_delete','=',0)
		;

		if($sekolah_id){
			$fetch->where('sekolah_pilihan.sekolah_id','=',$sekolah_id);
		}
	}

	public function getSekolahPilihan(Request $request){
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;
		$lintang = $request->lintang ? $request->lintang : null;
		$bujur = $request->bujur ? $request->bujur : null;

		$fetch = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
		->join('sekolah','sekolah.sekolah_id','=','ppdb.sekolah_pilihan.sekolah_id')
		->join('ref.mst_wilayah as kec','kec.kode_wilayah','=',DB::raw("left(sekolah.kode_wilayah,6)"))
		->join('ref.mst_wilayah as kab','kab.kode_wilayah','=','kec.mst_kode_wilayah')
		->join('ref.mst_wilayah as prov','prov.kode_wilayah','=','kab.mst_kode_wilayah')	
		->leftJoin('ref.bentuk_pendidikan as bp','bp.bentuk_pendidikan_id','=','sekolah.bentuk_pendidikan_id')	
		->where('ppdb.sekolah_pilihan.soft_delete','=',0)
		->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
		->select(
			'ppdb.sekolah_pilihan.*',
			'sekolah.*',
			'kec.nama as kecamatan',
			'kab.nama as kabupaten',
			'prov.nama as provinsi',
			'bp.nama as bentuk'
		)
		->orderBy('urut_pilihan','ASC')
		;
		
		$return = array();
		$return['total'] = $fetch->count();

		$data = $fetch->skip($start)->take($limit)->get();

		for ($i=0; $i < sizeof($data); $i++) { 
			if($lintang && $bujur){
				$data[$i]->jarak = self::distance($lintang, $bujur, $data[$i]->lintang,$data[$i]->bujur);
			}
		}

        $return['rows'] = $data;

        return $return;
	}

	public function simpanSekolahPilihan(Request $request){
		$jalur_id = $request->jalur_id ? $request->jalur_id : null;
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$urut_pilihan = $request->urut_pilihan ? $request->urut_pilihan : null;
		$periode_kegiatan_id = $request->periode_kegiatan_id ? $request->periode_kegiatan_id : 2021;
		$pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
		$soft_delete = $request->soft_delete ? $request->soft_delete : 0;
		$sekolah_pilihan_id = $request->sekolah_pilihan_id ? $request->sekolah_pilihan_id : RuangController::generateUUID();
	
		$cek = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
		->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
		// ->where('ppdb.sekolah_pilihan.sekolah_id','=',$sekolah_id)
		// ->get()
		;

		if($sekolah_id){
			$cek->where('ppdb.sekolah_pilihan.sekolah_id','=',$sekolah_id);
		}

		$cek = $cek->get();

		$urut = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
		->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
		->where('ppdb.sekolah_pilihan.soft_delete','=',0)
		->count();
		
		if(sizeof($cek) > 0){
			//update
			
			$exe = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id);

			if($sekolah_id){
				$exe->where('ppdb.sekolah_pilihan.sekolah_id','=',$sekolah_id);
				$exe = $exe->update([
					'soft_delete' => $soft_delete,
					'last_update' => DB::raw("now()"),
					'jalur_id' => $jalur_id,
					'urut_pilihan' => ($urut+1)
				]);
			}else{
				$exe = $exe
				->where('ppdb.sekolah_pilihan.soft_delete','=',0)
				->update([
					'soft_delete' => $soft_delete,
					'last_update' => DB::raw("now()"),
					'jalur_id' => $jalur_id
				]);
			}

			if($soft_delete > 0){
				//hapus nih
				$fetch = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
				->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
				->where('ppdb.sekolah_pilihan.soft_delete','=',0)
				->orderBy('urut_pilihan', 'ASC')
				->get();

				for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
					$exeNormalisasi = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
					->where('ppdb.sekolah_pilihan.sekolah_pilihan_id','=',$fetch[$iFetch]->sekolah_pilihan_id)
					->update([
						'urut_pilihan' => ((int)$iFetch+1)
					]);
				}

			}

			// ->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)

		}else{
			//insert
			$exe = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->insert([
				'sekolah_pilihan_id' => $sekolah_pilihan_id,
				'sekolah_id' => $sekolah_id,
				'peserta_didik_id' => $peserta_didik_id,
				'pengguna_id' => $pengguna_id,
				'jalur_id' => $jalur_id,
				'urut_pilihan' => ($urut+1),
				'periode_kegiatan_id' => $periode_kegiatan_id,
				'soft_delete' => $soft_delete,
				'create_date' => DB::raw("now()"),
				'last_update' => DB::raw("now()"),
			]);
		}

		if($sekolah_id){
			$data = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->where('ppdb.sekolah_pilihan.sekolah_id','=',$sekolah_id)
			->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
			->get(); 
		}else{
			$data = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
			->get();
		}

		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => $data
		], 200);
	}

	public function getJalurBerkas(Request $request){
		$jalur_id = $request->jalur_id ? $request->jalur_id : '0100';
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;

		$fetch = DB::connection('sqlsrv_2')->table('ref_ppdb.jalur_berkas')
		->join('ref_ppdb.jenis_berkas as jenis_berkas', 'jenis_berkas.jenis_berkas_id','=','ref_ppdb.jalur_berkas.jenis_berkas_id')
		->leftJoin(DB::raw("(SELECT
			* 
		FROM
			ppdb.berkas_calon 
		WHERE
			soft_delete = 0
			and calon_peserta_didik_id = '".$peserta_didik_id."') as berkas_calon"), 'berkas_calon.jenis_berkas_id','=','ref_ppdb.jalur_berkas.jenis_berkas_id')
		->whereNull('jenis_berkas.expired_date')
		->whereNull('ref_ppdb.jalur_berkas.expired_date')
		->where('jalur_id','=',$jalur_id)
		->select(
			'berkas_calon.*',
			'ref_ppdb.jalur_berkas.*',
			'jenis_berkas.nama'
		)
		->get();

		return response([ 
			'total' => sizeof($fetch), 
			'rows' => $fetch
		], 200);
		
	}

	public function simpanBerkasCalon(Request $request){
		$pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$jenis_berkas_id = $request->jenis_berkas_id ? $request->jenis_berkas_id : null;
		$nama_file = $request->nama_file ? $request->nama_file : null;
		$keterangan = $request->keterangan ? $request->keterangan : null;
		$periode_kegiatan_id = $request->periode_kegiatan_id ? $request->periode_kegiatan_id : 2021;
		$soft_delete = $request->soft_delete ? $request->soft_delete : 0;
		$berkas_calon_id = $request->berkas_calon_id ? $request->berkas_calon_id : RuangController::generateUUID();

		$cek = DB::connection('sqlsrv_2')->table('ppdb.berkas_calon')
		->where('calon_peserta_didik_id','=',$peserta_didik_id)
		->where('jenis_berkas_id','=',$jenis_berkas_id)
		->get();

		if(sizeof($cek) > 0){
			//update
			$exe = DB::connection('sqlsrv_2')->table('ppdb.berkas_calon')
			->where('calon_peserta_didik_id','=',$peserta_didik_id)
			->where('jenis_berkas_id','=',$jenis_berkas_id)
			->update([
				'nama_file' => $nama_file,
				'keterangan' => $keterangan,
				'last_update' => DB::raw("now()"),
				'soft_delete' => $soft_delete
			]);
		}else{
			//insert
			$exe = DB::connection('sqlsrv_2')->table('ppdb.berkas_calon')
			->insert([
				'berkas_calon_id' => $berkas_calon_id,
				'calon_peserta_didik_id' => $peserta_didik_id,
				'jenis_berkas_id' => $jenis_berkas_id,
				'nama_file' => $nama_file,
				'keterangan' => $keterangan,
				'create_date' => DB::raw("now()"),
				'last_update' => DB::raw("now()"),
				'soft_delete' => $soft_delete,
				'periode_kegiatan_id' => $periode_kegiatan_id,
				'pengguna_id' => $pengguna_id
			]);
		}

		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.berkas_calon')
			->where('calon_peserta_didik_id','=',$peserta_didik_id)
			->where('jenis_berkas_id','=',$jenis_berkas_id)
			->get()
		], 200);
		
	}

	public function simpanKonfirmasi(Request $request){
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$status_konfirmasi_id = $request->status_konfirmasi_id ? $request->status_konfirmasi_id : 1;

		$exe = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
		->where('calon_peserta_didik_id','=',$peserta_didik_id)
		->update([
			'status_konfirmasi_id' => $status_konfirmasi_id,
			'last_update' => DB::raw("now()")
		]);
		
		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
			->where('calon_peserta_didik_id','=',$peserta_didik_id)
			->get()
		], 200);
		
	}

	public function getJadwal(Request $request){
		$kode_wilayah = $request->kode_wilayah ? $request->kode_wilayah : null;
		$param = $request->param ? $request->param : null;

		$fetch = DB::connection('sqlsrv_2')->table('ppdb.jadwal as jadwal')
		->join('ref.jalur as jalur','jalur.jalur_id','=','jadwal.jalur_id')
		->where('jadwal.soft_delete','=',0)
		->select(
			'jadwal.*',
			'jalur.nama as jalur'
		)
		;

		if($kode_wilayah){
			$fetch->where('jadwal.kode_wilayah','=',$kode_wilayah);
		}

		if($param){
			if($param === 'hari_ini'){
				$fetch->where(DB::raw("date(now())"),'>=', DB::raw('date(jadwal.waktu_mulai)'));
				$fetch->where(DB::raw("date(now())"),'<=', DB::raw('date(jadwal.waktu_selesai)'));
			}
		}

		return response([ 
			'sukses' => $fetch->count(), 
			'rows' => $fetch->orderBy('waktu_mulai','ASC')->get()
		], 200);
	}

	public function getStatistikSekolah(Request $request){
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

		$sql = "SELECT
			jalur.*,
			COALESCE(calon.total,0) as total,
			COALESCE(calon.total_hari_ini,0) as total_hari_ini
		FROM
			ref_ppdb.jalur jalur
			LEFT JOIN (
			SELECT
				sekolah_pilihan.sekolah_id,
				sekolah_pilihan.jalur_id,
				SUM ( 1 ) AS total,
				SUM ( case when date(calon_peserta_didik.last_update) = date(now()) then 1 else 0 end) as total_hari_ini 
			FROM
				ppdb.calon_peserta_didik calon_peserta_didik
				JOIN ppdb.sekolah_pilihan sekolah_pilihan ON sekolah_pilihan.peserta_didik_id = calon_peserta_didik.calon_peserta_didik_id 
			WHERE
				calon_peserta_didik.soft_delete = 0 
				AND sekolah_pilihan.soft_delete = 0 
				AND calon_peserta_didik.status_konfirmasi_id != 99 
			GROUP BY
				sekolah_pilihan.sekolah_id,
				sekolah_pilihan.jalur_id 
			) calon ON calon.jalur_id = jalur.jalur_id 
			AND calon.sekolah_id = '{$sekolah_id}' 
		WHERE
			jalur.expired_date IS NULL 
			AND jalur.level_jalur = 1";

		$fetch = DB::connection('sqlsrv_2')->select($sql);

		return $fetch;
	}

}