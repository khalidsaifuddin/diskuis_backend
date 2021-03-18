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

	static function unduhExcelPendaftarDiterima(Request $request){
		// return $request;die;

		$fetch = self::getCalonPesertaDidik($request);

		// return $fetch;die;

		return view('excel/unduhExcelPendaftarDiterima', [ 'return' => $fetch['rows'] ]);
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
		$dengan_smak = $request->dengan_smak ? $request->dengan_smak : 'Y';
		$untuk_pilihan = $request->untuk_pilihan ? $request->untuk_pilihan : 'N';
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$bentuk_pendidikan_id = $request->bentuk_pendidikan_id ? $request->bentuk_pendidikan_id : null;
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
		
		if($dengan_smak !== 'Y'){
			$fetch->whereIn('sekolah.bentuk_pendidikan_id',array(5,6));
		}

		if($dengan_tk !== 'Y' && $dengan_smak !== 'Y'){
			$fetch->whereIn('sekolah.bentuk_pendidikan_id',array(5,6));
		}

		if($bentuk_pendidikan_id){
			$fetch->whereIn('sekolah.bentuk_pendidikan_id',array($bentuk_pendidikan_id));
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
		// ->leftJoin('ppdb.calon_peserta_didik as calon','calon.calon_peserta_didik_id','=','dapo.peserta_didik.peserta_didik_id')
		->leftJoin(DB::raw("(SELECT
			calon.calon_peserta_didik_id,
			sekolah_pilihan.peserta_didik_id,
			sekolah_pilihan.status_diterima_id,
			sekolah.nama as nama_sekolah_penerima
		FROM
			ppdb.calon_peserta_didik AS calon
			JOIN ppdb.sekolah_pilihan AS sekolah_pilihan ON sekolah_pilihan.peserta_didik_id = calon.calon_peserta_didik_id 
				AND sekolah_pilihan.urut_pilihan = 1 
				AND sekolah_pilihan.soft_delete = 0
				AND (sekolah_pilihan.status_diterima_id != 3 OR sekolah_pilihan.status_diterima_id is null)
			JOIN sekolah on sekolah.sekolah_id = sekolah_pilihan.sekolah_id
		WHERE
			calon.soft_delete = 0 
			-- AND (sekolah_pilihan.soft_delete = 0 AND sekolah_pilihan.status_diterima_id != 3)
			) as calon"),'calon.calon_peserta_didik_id','=','dapo.peserta_didik.peserta_didik_id')
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
			'calon.calon_peserta_didik_id',
			'calon.status_diterima_id',
			'calon.nama_sekolah_penerima'
        )
        ->orderBy('dapo.peserta_didik.nama', 'ASC')
        // ->get()
        ;

        if($peserta_didik_id){
            $fetch->where('dapo.peserta_didik.peserta_didik_id','=',$peserta_didik_id);
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
		$status_konfirmasi_id = $request->status_konfirmasi_id ? $request->status_konfirmasi_id : null;
        $peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$urut_pilihan = $request->urut_pilihan ? $request->urut_pilihan : 99;
		$jalur_id_filter = $request->jalur_id_filter ? $request->jalur_id_filter : 99;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;
        $diterima = $request->diterima ? $request->diterima : 0;
        $filter_diterima = $request->filter_diterima ? $request->filter_diterima : 'semua';
        $publik = $request->publik ? $request->publik : 0;
        $pendaftar = $request->pendaftar ? $request->pendaftar : 0;

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
			'sekolah.nama as asal_sekolah',
			'sekolah.npsn as asal_sekolah_npsn',
			'ppdb.calon_peserta_didik.last_update as last_update'
        )
		->where('ppdb.calon_peserta_didik.soft_delete','=',0)
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
        
		if($status_konfirmasi_id){
			
			// return $status_konfirmasi_id;die;
			if($status_konfirmasi_id !== 'semua'){
				if($status_konfirmasi_id === "sudah"){
					$fetch->where('ppdb.calon_peserta_didik.status_konfirmasi_id','=',1);
				}else{
					$fetch->whereNull('ppdb.calon_peserta_didik.status_konfirmasi_id');
				}
			}

		}
		
		if($sekolah_id){
			$fetch->join('ppdb.sekolah_pilihan', function($join) use ($sekolah_id, $urut_pilihan, $jalur_id_filter, $pendaftar, $publik)
			{
				$join->on('ppdb.sekolah_pilihan.sekolah_id', '=', DB::raw("'".$sekolah_id."'"));
				$join->on('ppdb.sekolah_pilihan.peserta_didik_id', '=', 'ppdb.calon_peserta_didik.calon_peserta_didik_id');
				$join->on('ppdb.sekolah_pilihan.soft_delete', '=', DB::raw("0"));

				// if()
				if($pendaftar === 1){
					$join->on('ppdb.sekolah_pilihan.jalur_id', '=', DB::raw("'0400'"));
				}
				
				if($urut_pilihan !== 99 && $publik !== 1){
					$join->on('ppdb.sekolah_pilihan.urut_pilihan', '=', DB::raw($urut_pilihan));
				}else{
					$join->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1,2,3));
				}
				
				if($jalur_id_filter !== 99){
					$join->on('ppdb.sekolah_pilihan.jalur_id', '=', DB::raw("'".$jalur_id_filter."'"));
				}

			})
			->join('ref_ppdb.jalur as jalur','jalur.jalur_id','=','ppdb.sekolah_pilihan.jalur_id')
			->select(
				'ppdb.calon_peserta_didik.*',
				'kec.nama as kecamatan',
				'kab.nama as kabupaten',
				'prov.nama as provinsi',
				'ppdb.sekolah_pilihan.*',
				'sekolah.nama as asal_sekolah',
				'jalur.nama as jalur',
				'ppdb.calon_peserta_didik.last_update as last_update'
			);

			if($diterima == 1){
				// $fetch->where(function($query) use ($keyword){
				// 	$query->where('kuis.judul','ilike',DB::raw("'%".$keyword."%'"))
				// 		  ->orWhere('kuis.keterangan','ilike',DB::raw("'%".$keyword."%'"));
				// });

				$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1,2,3));
				$fetch->where('ppdb.calon_peserta_didik.status_konfirmasi_id','=',1);

				if($filter_diterima === 'diterima'){
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1));
				}else if($filter_diterima === 'daftar_ulang'){
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(2));
				}else if($filter_diterima === 'cabut_berkas'){
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(3));
				}else{
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1,2,3));
				}

				// return "diterima";die;
			}else{
				
				if($publik != 1){
					$fetch->whereNull('ppdb.sekolah_pilihan.status_diterima_id');
				}
			}

		}else{
			$fetch->leftJoin('ppdb.sekolah_pilihan', function($join) use ($urut_pilihan, $jalur_id_filter, $pendaftar, $publik)
			{
				// $join->on('ppdb.sekolah_pilihan.sekolah_id', '=', DB::raw("'".$sekolah_id."'"));
				$join->on('ppdb.sekolah_pilihan.peserta_didik_id', '=', 'ppdb.calon_peserta_didik.calon_peserta_didik_id');
				$join->on('ppdb.sekolah_pilihan.soft_delete', '=', DB::raw("0"));

				if($pendaftar === 1){
					$join->on('ppdb.sekolah_pilihan.jalur_id', '=', DB::raw("'0400'"));
				}
				
				if($urut_pilihan !== 99 && $publik !== 1){
					$join->on('ppdb.sekolah_pilihan.urut_pilihan', '=', DB::raw($urut_pilihan));
				}else{
					// $join->on('ppdb.sekolah_pilihan.urut_pilihan', '=', DB::raw(1));
				}
				// else{
				// 	$join->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1,2,3));
				// }

				if($jalur_id_filter !== 99){
					$join->on('ppdb.sekolah_pilihan.jalur_id', '=', DB::raw("'".$jalur_id_filter."'"));
				}

			})
			->leftJoin('ref_ppdb.jalur as jalur','jalur.jalur_id','=','ppdb.sekolah_pilihan.jalur_id')
			->select(
				'ppdb.calon_peserta_didik.*',
				'kec.nama as kecamatan',
				'kab.nama as kabupaten',
				'prov.nama as provinsi',
				'ppdb.sekolah_pilihan.*',
				'sekolah.nama as asal_sekolah',
				'jalur.nama as jalur',
				'ppdb.calon_peserta_didik.last_update as last_update'
			);

			if($diterima == 1){
				$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1,2,3));
				$fetch->where('ppdb.calon_peserta_didik.status_konfirmasi_id','=',1);

				if($filter_diterima === 'diterima'){
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1));
				}else if($filter_diterima === 'daftar_ulang'){
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(2));
				}else if($filter_diterima === 'cabut_berkas'){
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(3));
				}else{
					$fetch->whereIn('ppdb.sekolah_pilihan.status_diterima_id', array(1,2,3));
				}
			}else{

				if($publik != 1){
					$fetch->whereNull('ppdb.sekolah_pilihan.status_diterima_id');
				}

			}
		}

		if($publik == 1){
			
			$fetch->leftJoin(DB::raw("(select ppdb.sekolah_pilihan.*, sekolah.nama as nama_sekolah from ppdb.sekolah_pilihan join sekolah on sekolah.sekolah_id = ppdb.sekolah_pilihan.sekolah_id) as penerimaan"), function($join){
				$join->on('penerimaan.peserta_didik_id','=','ppdb.calon_peserta_didik.calon_peserta_didik_id')
				->where('penerimaan.soft_delete','=',0)
				// ->where('penerimaan.urut_pilihan','=',1)
				->whereIn('penerimaan.status_diterima_id', array(1,2))
				;
				// $join->on('penerimaan.soft_delete','=',0);
				// $join->on('penerimaan.urut_pilihan','=',1);
				// $join->on('penerimaan.status_diterima_id', array(1,2));
			});
			$fetch->select(
				'ppdb.calon_peserta_didik.*',
				'kec.nama as kecamatan',
				'kab.nama as kabupaten',
				'prov.nama as provinsi',
				'ppdb.sekolah_pilihan.*',
				'sekolah.nama as asal_sekolah',
				'jalur.nama as jalur',
				'ppdb.calon_peserta_didik.last_update as last_update',
				'penerimaan.sekolah_id as sekolah_id_penerima',
				'penerimaan.nama_sekolah as nama_sekolah_penerima'
			);
		}
		
		// return $fetch->toSql();die;

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
			->join('ref_ppdb.tingkat_prestasi as tingkat_prestasi','tingkat_prestasi.tingkat_prestasi_id','=','ppdb.nilai_prestasi.tingkat_prestasi_id')
			->join('ref_ppdb.jenis_prestasi as jenis_prestasi','jenis_prestasi.jenis_prestasi_id','=','ppdb.nilai_prestasi.jenis_prestasi_id')
			->where('peserta_didik_id','=',$data[$i]->calon_peserta_didik_id)
			->where('soft_delete','=',0)
			->select(
				'ppdb.nilai_prestasi.*',
				'tingkat_prestasi.skor',
				'tingkat_prestasi.nama as tingkat_prestasi',
				'jenis_prestasi.nama as jenis_prestasi'
			)
			->get();

			if(sizeof($fetch_prestasi) > 0){

				// return json_encode($fetch_prestasi[0]);die;

				// return $fetch_prestasi[0]->jenis_prestasi_id;die;

				if((int)$fetch_prestasi[0]->jenis_prestasi_id !== 3){

					// $skor = 0;

				}else{

					$skor = (
						(float)$fetch_prestasi[0]->nilai_semester_1 +
						(float)$fetch_prestasi[0]->nilai_semester_2 +
						(float)$fetch_prestasi[0]->nilai_semester_3 +
						(float)$fetch_prestasi[0]->nilai_semester_4 +
						(float)$fetch_prestasi[0]->nilai_semester_5
					) / (float)5;
	
					$fetch_prestasi[0]->skor = $skor;
				
				}

				$data[$i]->nilai_prestasi = $fetch_prestasi[0];

			}else{
				$data[$i]->nilai_prestasi = array();
			}

			//sekolah pilihannya
			$fetch_pilihan = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->join('sekolah', 'sekolah.sekolah_id','=','ppdb.sekolah_pilihan.sekolah_id')
			->where('peserta_didik_id','=',$data[$i]->calon_peserta_didik_id)
			->where('ppdb.sekolah_pilihan.soft_delete','=',0)
			->where('sekolah.soft_delete','=',0)
			->select(
				'ppdb.sekolah_pilihan.*',
				'sekolah.nama as nama_sekolah',
				'sekolah.*'
			)
			->orderBy('urut_pilihan', 'ASC')
			->get();

			$data[$i]->sekolah_pilihan = $fetch_pilihan;
		}

        $return['rows'] = $data;

        return $return;
	}

	static function getJalur(Request $request){
		$jalur_id = $request->jalur_id ? $request->jalur_id : null;
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$level_jalur = $request->level_jalur ? $request->level_jalur : 1;
		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;

		$fetch = DB::connection('sqlsrv_2')->table('ref_ppdb.jalur')
		->join('ppdb.jadwal as jadwal', 'jadwal.jalur_id', '=', 'ref_ppdb.jalur.jalur_id')
		->whereNull('ref_ppdb.jalur.expired_date')
		->where('jadwal.soft_delete','=',0)
		->where('jadwal.waktu_mulai','<=', DB::raw("now()"))
		->where('jadwal.waktu_selesai','>=', DB::raw("now()"))
		->select(
			'ref_ppdb.jalur.*',
			'jadwal.*'
		)
		;

		if($jalur_id){
			$fetch->where('ref_ppdb.jalur.jalur_id','=',$jalur_id);
		}
		
		if($level_jalur){
			$fetch->where('ref_ppdb.jalur.level_jalur','=',$level_jalur);
		}

		if($sekolah_id){
			$fetch_sekolah = DB::connection('sqlsrv_2')->table('sekolah')
			->where('sekolah_id','=',$sekolah_id)
			->first();

			if($fetch_sekolah){
				switch ($fetch_sekolah->bentuk_pendidikan_id) {
					case 5:
						$fetch->whereNotIn('ref_ppdb.jalur.jalur_id',array('0300'));
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

	static function getJenisPrestasi(Request $request){
		$jenis_prestasi_id = $request->jenis_prestasi_id ? $request->jenis_prestasi_id : null;
		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;

		$fetch = DB::connection('sqlsrv_2')->table('ref_ppdb.jenis_prestasi')
		->whereNull('expired_date')
		;

		if($jenis_prestasi_id){
			$fetch->where('jenis_prestasi_id','=',$jenis_prestasi_id);
		}

		$return = array();
        $return['total'] = $fetch->count();
        $return['rows'] = $fetch->skip($start)->take($limit)->get();

        return $return;
		
	}

	
	static function getTingkatPrestasi(Request $request){
		$tingkat_prestasi_id = $request->tingkat_prestasi_id ? $request->tingkat_prestasi_id : null;
		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;

		$fetch = DB::connection('sqlsrv_2')->table('ref_ppdb.tingkat_prestasi')
		->whereNull('expired_date')
		;

		if($tingkat_prestasi_id){
			$fetch->where('tingkat_prestasi_id','=',$tingkat_prestasi_id);
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
		->join('ref.jalur as jalur','jalur.jalur_id','=','ppdb.sekolah_pilihan.jalur_id')	
		->leftJoin('ref.bentuk_pendidikan as bp','bp.bentuk_pendidikan_id','=','sekolah.bentuk_pendidikan_id')	
		// ->where('ppdb.sekolah_pilihan.soft_delete','=',0)
		->where(function($query) use($start){
			$query->where('ppdb.sekolah_pilihan.soft_delete','=',0)
			->whereNull('ppdb.sekolah_pilihan.status_diterima_id')
			// ->whereNotIn('ppdb.sekolah_pilihan.status_diterima_id',array(1,2,3))
			;
		})
		->where('ppdb.sekolah_pilihan.peserta_didik_id','=',$peserta_didik_id)
		->select(
			'ppdb.sekolah_pilihan.*',
			'sekolah.*',
			'kec.nama as kecamatan',
			'kab.nama as kabupaten',
			'prov.nama as provinsi',
			'bp.nama as bentuk',
			'jalur.nama as jalur'
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
		->where('ppdb.sekolah_pilihan.soft_delete','=',0)
		->whereNull('ppdb.sekolah_pilihan.status_diterima_id')
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
		->whereNull('ppdb.sekolah_pilihan.status_diterima_id')
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
		$jadwal_id = $request->jadwal_id ? $request->jadwal_id : null;
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

		if($jadwal_id){
			$fetch->where('jadwal.jadwal_id','=',$jadwal_id);
		}

		if($param){
			if($param === 'hari_ini'){
				$fetch->where(DB::raw("date(now())"),'>=', DB::raw('date(jadwal.waktu_mulai)'));
				$fetch->where(DB::raw("date(now())"),'<=', DB::raw('date(jadwal.waktu_selesai)'));
			}
		}

		return response([ 
			'total' => $fetch->count(), 
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
				AND sekolah_pilihan.urut_pilihan = 1
			GROUP BY
				sekolah_pilihan.sekolah_id,
				sekolah_pilihan.jalur_id 
			) calon ON calon.jalur_id = jalur.jalur_id 
			AND calon.sekolah_id = '{$sekolah_id}' 
		WHERE
				jalur.expired_date IS NULL 
				AND jalur.level_jalur = 1";

				// return $sql;die;

		$fetch = DB::connection('sqlsrv_2')->select($sql);

		return $fetch;
	}

	public function simpanNilaiPrestasi(Request $request){
		$pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$jenis_prestasi_id = $request->jenis_prestasi_id ? $request->jenis_prestasi_id : null; 
		$tingkat_prestasi_id = $request->tingkat_prestasi_id ? $request->tingkat_prestasi_id : null;
		$nilai_semester_1 = $request->nilai_semester_1;
		$nilai_semester_2 = $request->nilai_semester_2;
		$nilai_semester_3 = $request->nilai_semester_3;
		$nilai_semester_4 = $request->nilai_semester_4;
		$nilai_semester_5 = $request->nilai_semester_5;
		
		$cek = DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
		->where('peserta_didik_id','=',$peserta_didik_id)
		->get();

		if(sizeof($cek) > 0){
			//update
			$exe = DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
			->where('peserta_didik_id','=',$peserta_didik_id)
			->update([
				'soft_delete' => 0,
				'last_update' => DB::raw("now()"),
				'pengguna_id' => $pengguna_id,
				'jenis_prestasi_id' => $jenis_prestasi_id,
				'tingkat_prestasi_id' => $tingkat_prestasi_id,
				'nilai_semester_1' => $nilai_semester_1,
				'nilai_semester_2' => $nilai_semester_2,
				'nilai_semester_3' => $nilai_semester_3,
				'nilai_semester_4' => $nilai_semester_4,
				'nilai_semester_5' => $nilai_semester_5
			]);

		}else{
			//insert
			$exe = DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
			->insert([
				'nilai_prestasi_id' => RuangController::generateUUID(),
				'soft_delete' => 0,
				'create_date' => DB::raw("now()"),
				'last_update' => DB::raw("now()"),
				'pengguna_id' => $pengguna_id,
				'peserta_didik_id' => $peserta_didik_id,
				'jenis_prestasi_id' => $jenis_prestasi_id,
				'tingkat_prestasi_id' => $tingkat_prestasi_id,
				'nilai_semester_1' => $nilai_semester_1,
				'nilai_semester_2' => $nilai_semester_2,
				'nilai_semester_3' => $nilai_semester_3,
				'nilai_semester_4' => $nilai_semester_4,
				'nilai_semester_5' => $nilai_semester_5
			]);
		}

		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
			->where('peserta_didik_id','=',$peserta_didik_id)
			->where('soft_delete','=',0)
			->get()
		], 200);
	}

	public function getNilaiPrestasi(Request $request){
		$peserta_didik_id = $request->peserta_didik_id;

		$fetch = DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
		->where('peserta_didik_id','=',$peserta_didik_id)
		->where('soft_delete','=',0)
		;
		// ->get()

		return response([ 
			'total' => $fetch->count(), 
			'rows' => $fetch->get()
		], 200);

	}

	public function print_formulir($id)
    {
    	$calon_pd = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')->where('calon_peserta_didik_id', $id)
    		->select(
    			'ppdb.calon_peserta_didik.*',
    			'sekolah.nama AS asal_sekolah',
    			'kec.nama AS kecamatan',
    			'kab.nama AS kabupaten',
    			'prop.nama AS provinsi',
    			'pddk_trkh_ayah.nama AS pendidikan_terakhir_ayah',
    			'pddk_trkh_ayah.nama AS pendidikan_terakhir_ibu',
    			'pddk_trkh_ayah.nama AS pendidikan_terakhir_wali',
    			'work_ayah.nama AS pekerjaan_ayah',
    			'work_ibu.nama AS pekerjaan_ibu',
    			'work_wali.nama AS pekerjaan_wali'
    		)
    		->leftJoin('sekolah AS sekolah', 'ppdb.calon_peserta_didik.asal_sekolah_id', '=', 'sekolah.sekolah_id')
    		->join('ref.mst_wilayah AS  kec', 'ppdb.calon_peserta_didik.kode_wilayah_kecamatan', '=', 'kec.kode_wilayah')
    		->join('ref.mst_wilayah AS  kab', 'ppdb.calon_peserta_didik.kode_wilayah_kabupaten', '=', 'kab.kode_wilayah')
    		->join('ref.mst_wilayah AS  prop', 'ppdb.calon_peserta_didik.kode_wilayah_provinsi', '=', 'prop.kode_wilayah')
    		->leftJoin('ref.pendidikan_terakhir AS pddk_trkh_ayah', 'ppdb.calon_peserta_didik.pendidikan_terakhir_id_ayah', '=', 'pddk_trkh_ayah.pendidikan_terakhir_id')
    		->leftJoin('ref.pendidikan_terakhir AS pddk_trkh_ibu', 'ppdb.calon_peserta_didik.pendidikan_terakhir_id_ibu', '=', 'pddk_trkh_ibu.pendidikan_terakhir_id')
    		->leftJoin('ref.pendidikan_terakhir AS pddk_trkh_wali', 'ppdb.calon_peserta_didik.pendidikan_terakhir_id_wali', '=', 'pddk_trkh_wali.pendidikan_terakhir_id')
    		->leftJoin('ref.pekerjaan AS work_ayah', 'ppdb.calon_peserta_didik.pekerjaan_id_ayah', '=', 'work_ayah.pekerjaan_id')
    		->leftJoin('ref.pekerjaan AS work_ibu', 'ppdb.calon_peserta_didik.pekerjaan_id_ayah', '=', 'work_ibu.pekerjaan_id')
    		->leftJoin('ref.pekerjaan AS work_wali', 'ppdb.calon_peserta_didik.pekerjaan_id_ayah', '=', 'work_wali.pekerjaan_id')
    		->first();

    	$sekolah_pilihan = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')->where('sekolah_pilihan.peserta_didik_id', $id)
			->leftJoin('sekolah AS sekolah', 'ppdb.sekolah_pilihan.sekolah_id', '=', 'sekolah.sekolah_id')
			->leftJoin('ref.jalur AS jalur', 'ppdb.sekolah_pilihan.jalur_id', '=', 'jalur.jalur_id')
			->leftJoin(
				DB::raw('(
					SELECT ROW_NUMBER
					() OVER (
						PARTITION BY sekolah_pilihan.sekolah_id, sekolah_pilihan.jalur_id
					ORDER BY
						sekolah_pilihan.urut_pilihan ASC,
						sekolah_pilihan.create_date ASC
					) AS urutan,
					urut_pilihan,
					sekolah_pilihan.create_date,
					sekolah_pilihan.jalur_id,
					calon_peserta_didik.nama,
					sekolah_pilihan.sekolah_id,
					sekolah_pilihan.peserta_didik_id
				FROM
					ppdb.sekolah_pilihan
					JOIN ppdb.calon_peserta_didik ON calon_peserta_didik.calon_peserta_didik_id = sekolah_pilihan.peserta_didik_id 
				WHERE
					sekolah_pilihan.soft_delete = 0 
					AND calon_peserta_didik.soft_delete = 0 
				ORDER BY
					sekolah_pilihan.sekolah_id,
					sekolah_pilihan.jalur_id
				) as urutan'), function ($join) {
				$join->on('urutan.sekolah_id', '=', 'ppdb.sekolah_pilihan.sekolah_id');
				$join->on('urutan.peserta_didik_id','=','ppdb.sekolah_pilihan.peserta_didik_id');
			})
			// ->leftJoin('ppdb.kuota_sekolah as kuota', function ($join) {
			// 	$join->on('kuota.sekolah_id', '=', 'ppdb.sekolah_pilihan.sekolah_id');
			// 	$join->on('kuota.jalur_id', '=', 'ppdb.sekolah_pilihan.jalur_id');
			// })
			->select(
				'sekolah_pilihan.*',
				'sekolah.npsn AS npsn',
				'sekolah.nama AS nama_sekolah',
				'jalur.nama AS nama_jalur',
				'urutan.urutan as urutan'
			)
			->where('ppdb.sekolah_pilihan.soft_delete', 0)
			->orderBy('urut_pilihan','ASC')
			->get();
		
		//poin prestasi
		$fetch_prestasi = DB::connection('sqlsrv_2')->table('ppdb.nilai_prestasi')
		->join('ref_ppdb.tingkat_prestasi as tingkat_prestasi','tingkat_prestasi.tingkat_prestasi_id','=','ppdb.nilai_prestasi.tingkat_prestasi_id')
		->join('ref_ppdb.jenis_prestasi as jenis_prestasi','jenis_prestasi.jenis_prestasi_id','=','ppdb.nilai_prestasi.jenis_prestasi_id')
		->where('peserta_didik_id','=',$id)
		->where('soft_delete','=',0)
		->select(
			'ppdb.nilai_prestasi.*',
			'tingkat_prestasi.skor',
			'tingkat_prestasi.nama as tingkat_prestasi',
			'jenis_prestasi.nama as jenis_prestasi'
		)
		->get();

		// return $fetch_prestasi;die;

		// $nilai_prestasi = [];

		if(sizeof($fetch_prestasi) > 0){
			if((int)$fetch_prestasi[0]->jenis_prestasi_id !== 3){

				// $skor = 0;

			}else{

				$skor = (
					(float)$fetch_prestasi[0]->nilai_semester_1 +
					(float)$fetch_prestasi[0]->nilai_semester_2 +
					(float)$fetch_prestasi[0]->nilai_semester_3 +
					(float)$fetch_prestasi[0]->nilai_semester_4 +
					(float)$fetch_prestasi[0]->nilai_semester_5
				) / (float)5;

				$fetch_prestasi[0]->skor = $skor;
			
			}

			$nilai_prestasi = $fetch_prestasi[0];
		}else{
			$nilai_prestasi = null;
		}
		
			// return $sekolah_pilihan;die;	

    	if(count($sekolah_pilihan) >= 1){
			$urutan = @$sekolah_pilihan[0]->urutan;

			// return $urutan;die;

			switch (strlen($urutan)) {
				case 1: $nol = "000"; break;
				case 2: $nol = "00"; break;
				case 3: $nol = "0"; break;
				case 4: $nol = ""; break;	
				default:
					$nol = "";
					break;
			}

			$urutan = $nol.$urutan;
		}else{
			$urutan = "0000";
		}

		// return $calon_pd;die;

		$arrBulan = [
			'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		];

    	$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('docs/template_formulir_pendaftaran.docx');

    	$orangtua = $calon_pd->orang_tua_utama;

		$templateProcessor->setValue('nik', $calon_pd->nik);
		$templateProcessor->setValue('no_npsn1', substr(@$sekolah_pilihan[0]->npsn,0,1));
		$templateProcessor->setValue('no_npsn2', substr(@$sekolah_pilihan[0]->npsn,1,1));
		$templateProcessor->setValue('no_npsn3', substr(@$sekolah_pilihan[0]->npsn,2,1));
		$templateProcessor->setValue('no_npsn4', substr(@$sekolah_pilihan[0]->npsn,3,1));
		$templateProcessor->setValue('no_npsn5', substr(@$sekolah_pilihan[0]->npsn,4,1));
		$templateProcessor->setValue('no_npsn6', substr(@$sekolah_pilihan[0]->npsn,5,1));
		$templateProcessor->setValue('no_npsn7', substr(@$sekolah_pilihan[0]->npsn,6,1));
		$templateProcessor->setValue('no_npsn8', substr(@$sekolah_pilihan[0]->npsn,7,1));
		$templateProcessor->setValue('no_jalur1', substr(@$sekolah_pilihan[0]->jalur_id,0,1));
		$templateProcessor->setValue('no_jalur2', substr(@$sekolah_pilihan[0]->jalur_id,1,1));
		$templateProcessor->setValue('no_jalur3', substr(@$sekolah_pilihan[0]->jalur_id,2,1));
		$templateProcessor->setValue('no_jalur4', substr(@$sekolah_pilihan[0]->jalur_id,3,1));
		$templateProcessor->setValue('no1', substr($urutan, 0, 1));
		$templateProcessor->setValue('no2', substr($urutan, 1, 1));
		$templateProcessor->setValue('no3', substr($urutan, 2, 1));
		$templateProcessor->setValue('no4', substr($urutan, 3, 1));
		$templateProcessor->setValue('nama', $calon_pd->nama);
		$templateProcessor->setValue('jenis_kelamin', $calon_pd->jenis_kelamin == 'L' ? 'Laki - laki' : 'Perempuan');
		$templateProcessor->setValue('tempat_lahir', $calon_pd->tempat_lahir);
		$templateProcessor->setValue('tgllhrd', date("d", strtotime($calon_pd->tanggal_lahir)));
		$templateProcessor->setValue('tgllhrm', date("m", strtotime($calon_pd->tanggal_lahir)));
		$templateProcessor->setValue('tgllhry', date("Y", strtotime($calon_pd->tanggal_lahir)));
		$templateProcessor->setValue('asal_sekolah', $calon_pd->asal_sekolah);
		$templateProcessor->setValue('alamat_jalan', $calon_pd->alamat_tempat_tinggal);
		$templateProcessor->setValue('rt', $calon_pd->rt);
		$templateProcessor->setValue('rw', $calon_pd->rw);
		$templateProcessor->setValue('desa', '');
		$templateProcessor->setValue('dusun', $calon_pd->dusun);
		$templateProcessor->setValue('desa', $calon_pd->desa_kelurahan);
		$templateProcessor->setValue('kecamatan', $calon_pd->kecamatan);
		$templateProcessor->setValue('kabupaten', $calon_pd->kabupaten);
		$templateProcessor->setValue('provinsi', $calon_pd->provinsi);
		$templateProcessor->setValue('lintang', $calon_pd->lintang);
		$templateProcessor->setValue('bujur', $calon_pd->bujur);
		$templateProcessor->setValue('jalur', @$sekolah_pilihan[0]->nama_jalur);
		$templateProcessor->setValue('npsn1', @$sekolah_pilihan[0]->npsn);
		$templateProcessor->setValue('sekolah1', @$sekolah_pilihan[0]->nama_sekolah);
		$templateProcessor->setValue('npsn2', @$sekolah_pilihan[1]->npsn);
		$templateProcessor->setValue('sekolah2', @$sekolah_pilihan[1]->nama_sekolah);
		$templateProcessor->setValue('npsn3', @$sekolah_pilihan[2]->npsn);
		$templateProcessor->setValue('sekolah3', @$sekolah_pilihan[2]->nama_sekolah);
		$templateProcessor->setValue('npsn4', @$sekolah_pilihan[3]->npsn);
		$templateProcessor->setValue('sekolah4', @$sekolah_pilihan[3]->nama_sekolah);
		$templateProcessor->setValue('orang_tua_utama', $calon_pd->{'nama_'.$orangtua});
		$templateProcessor->setValue('orang_tua_tempat_lahir', $calon_pd->tempat_lahir_ayah);
		$templateProcessor->setValue('orttd', date("d", strtotime( $calon_pd->{'tanggal_lahir_'.$orangtua} )));
		$templateProcessor->setValue('orttm', date("m", strtotime( $calon_pd->{'tanggal_lahir_'.$orangtua} )));
		$templateProcessor->setValue('ortty', date("Y", strtotime( $calon_pd->{'tanggal_lahir_'.$orangtua} )));
		$templateProcessor->setValue('orang_tua_pendidikan', $calon_pd->{'pendidikan_terakhir_'.$orangtua});
		$templateProcessor->setValue('orang_tua_pekerjaan', $calon_pd->{'pekerjaan_'.$orangtua});
		$templateProcessor->setValue('orang_tua_alamat_tempat_tinggal', $calon_pd->{'alamat_tempat_tinggal_'.$orangtua});
		$templateProcessor->setValue('orang_tua_no_telepon', $calon_pd->{'no_telepon_'.$orangtua});
		$templateProcessor->setValue('skor', @$sekolah_pilihan[0]->jalur_id === '0300' ? ($nilai_prestasi ? $nilai_prestasi->skor : 0) : null);
		$templateProcessor->setValue('tingkat_prestasi', @$sekolah_pilihan[0]->jalur_id === '0300' ? ($nilai_prestasi ? ($nilai_prestasi->jenis_prestasi . "" .($nilai_prestasi->jenis_prestasi_id !== 3 ? " (".$nilai_prestasi->tingkat_prestasi.")" : "")) : null) : null);
		$templateProcessor->setValue('datenow', date("d") . " " . $arrBulan[(int)date("m")-1] . " " . date("Y"));

		header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="Formulir_PPDB_'.date("Y").'-'.$calon_pd->nik.'.docx"');
        $templateProcessor->saveAs('php://output');

    }

    public function print_bukti($id)
    {
    	$calon_pd = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')->where('calon_peserta_didik_id', $id)
    		->select(
    			'calon_peserta_didik.*',
    			'sekolah.nama AS asal_sekolah'
    		)
    		->leftJoin('sekolah AS sekolah', 'ppdb.calon_peserta_didik.asal_sekolah_id', '=', 'sekolah.sekolah_id')
    		->first();

    	$sekolah_pilihan = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')->where('sekolah_pilihan.peserta_didik_id', $id)
			->leftJoin('sekolah AS sekolah', 'ppdb.sekolah_pilihan.sekolah_id', '=', 'sekolah.sekolah_id')
			->leftJoin('ref.jalur AS jalur', 'ppdb.sekolah_pilihan.jalur_id', '=', 'jalur.jalur_id')
			->leftJoin(
				DB::raw('(
					SELECT ROW_NUMBER
					() OVER (
						PARTITION BY sekolah_pilihan.sekolah_id, sekolah_pilihan.jalur_id
					ORDER BY
						sekolah_pilihan.urut_pilihan ASC,
						sekolah_pilihan.create_date ASC
					) AS urutan,
					urut_pilihan,
					sekolah_pilihan.create_date,
					sekolah_pilihan.jalur_id,
					calon_peserta_didik.nama,
					sekolah_pilihan.sekolah_id,
					sekolah_pilihan.peserta_didik_id
				FROM
					ppdb.sekolah_pilihan
					JOIN ppdb.calon_peserta_didik ON calon_peserta_didik.calon_peserta_didik_id = sekolah_pilihan.peserta_didik_id 
				WHERE
					sekolah_pilihan.soft_delete = 0 
					AND calon_peserta_didik.soft_delete = 0 
				ORDER BY
					sekolah_pilihan.sekolah_id,
					sekolah_pilihan.jalur_id
				) as urutan'), function ($join) {
				$join->on('urutan.sekolah_id', '=', 'ppdb.sekolah_pilihan.sekolah_id');
				$join->on('urutan.peserta_didik_id','=','ppdb.sekolah_pilihan.peserta_didik_id');
			})
			// ->leftJoin('ppdb.kuota_sekolah as kuota','kuota.sekolah_id','=','ppdb.sekolah_pilihan.sekolah_id')
			->select(
				'sekolah_pilihan.*',
				'sekolah.npsn AS npsn',
				'sekolah.nama AS nama_sekolah',
				'jalur.nama AS nama_jalur',
				'urutan.urutan as urutan'
			)
			->where('ppdb.sekolah_pilihan.soft_delete', 0)
			->orderBy('urut_pilihan','ASC')
			->get();

		// return $sekolah_pilihan;die;

		if(count($sekolah_pilihan) >= 1){
			$urutan = @$sekolah_pilihan[0]->urutan;

			switch (strlen($urutan)) {
				case 1: $nol = "000"; break;
				case 2: $nol = "00"; break;
				case 3: $nol = "0"; break;
				case 4: $nol = ""; break;	
				default:
					$nol = "";
					break;
			}

			$urutan = $nol.$urutan;
		}else{
			$urutan = "0000";
		}

		$arrBulan = [
			'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		];

		$berkas = DB::connection('sqlsrv_2')->table('ppdb.berkas_calon')
		->where('calon_peserta_didik_id','=',$calon_pd->calon_peserta_didik_id)
		->where('soft_delete','=',0)
		->where('jenis_berkas_id','=',8)
		->first();


		$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor('docs/template_bukti_pendaftaran.docx');

		$templateProcessor->setValue('no_npsn1', substr(@$sekolah_pilihan[0]->npsn,0,1));
		$templateProcessor->setValue('no_npsn2', substr(@$sekolah_pilihan[0]->npsn,1,1));
		$templateProcessor->setValue('no_npsn3', substr(@$sekolah_pilihan[0]->npsn,2,1));
		$templateProcessor->setValue('no_npsn4', substr(@$sekolah_pilihan[0]->npsn,3,1));
		$templateProcessor->setValue('no_npsn5', substr(@$sekolah_pilihan[0]->npsn,4,1));
		$templateProcessor->setValue('no_npsn6', substr(@$sekolah_pilihan[0]->npsn,5,1));
		$templateProcessor->setValue('no_npsn7', substr(@$sekolah_pilihan[0]->npsn,6,1));
		$templateProcessor->setValue('no_npsn8', substr(@$sekolah_pilihan[0]->npsn,7,1));
		$templateProcessor->setValue('no_jalur1', substr(@$sekolah_pilihan[0]->jalur_id,0,1));
		$templateProcessor->setValue('no_jalur2', substr(@$sekolah_pilihan[0]->jalur_id,1,1));
		$templateProcessor->setValue('no_jalur3', substr(@$sekolah_pilihan[0]->jalur_id,2,1));
		$templateProcessor->setValue('no_jalur4', substr(@$sekolah_pilihan[0]->jalur_id,3,1));
        $templateProcessor->setValue('no1', substr($urutan, 0, 1));
		$templateProcessor->setValue('no2', substr($urutan, 1, 1));
		$templateProcessor->setValue('no3', substr($urutan, 2, 1));
		$templateProcessor->setValue('no4', substr($urutan, 3, 1));
		$templateProcessor->setValue('nik', $calon_pd->nik);
		$templateProcessor->setValue('nama', $calon_pd->nama);
		$templateProcessor->setValue('nisn', $calon_pd->nisn);
		$templateProcessor->setValue('tempat_lahir', $calon_pd->tempat_lahir);
		$templateProcessor->setValue('alamat_jalan', $calon_pd->alamat_tempat_tinggal);
		$templateProcessor->setValue('tgllhr_d', date("d", strtotime($calon_pd->tanggal_lahir)));
		$templateProcessor->setValue('tgllhr_m', date("m", strtotime($calon_pd->tanggal_lahir)));
		$templateProcessor->setValue('tgllhr_y', date("Y", strtotime($calon_pd->tanggal_lahir)));
		$templateProcessor->setValue('lintang', $calon_pd->lintang);
		$templateProcessor->setValue('bujur', $calon_pd->bujur);
		$templateProcessor->setValue('asal_sekolah', $calon_pd->asal_sekolah);
		$templateProcessor->setValue('jalur', @$sekolah_pilihan[0]->nama_jalur);
		$templateProcessor->setValue('npsn1', @$sekolah_pilihan[0]->npsn);
		$templateProcessor->setValue('sekolah1', @$sekolah_pilihan[0]->nama_sekolah);
		$templateProcessor->setValue('npsn2', @$sekolah_pilihan[1]->npsn);
		$templateProcessor->setValue('sekolah2', @$sekolah_pilihan[1]->nama_sekolah);
		$templateProcessor->setValue('npsn3', @$sekolah_pilihan[2]->npsn);
		$templateProcessor->setValue('sekolah3', @$sekolah_pilihan[2]->nama_sekolah);
		$templateProcessor->setValue('npsn4', @$sekolah_pilihan[3]->npsn);
		$templateProcessor->setValue('sekolah4', @$sekolah_pilihan[3]->nama_sekolah);
		$templateProcessor->setValue('datenow', date("d") . " " . $arrBulan[(int)date("m")-1] . " " . date("Y"));
        $templateProcessor->setImageValue('codeQR', array('path' => "https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={$calon_pd->nik}", 'width' => '1in', 'height' => '1in'));
        $templateProcessor->setImageValue('pas_foto', array('path' => "https://be.diskuis.id".$berkas->nama_file, 'width' => '1in', 'height' => '2in'));


        // $templateProcessor->deleteBlock('DELETEME');
        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="Bukti_PPDB_'.date("Y").'-'.$calon_pd->nik.'.docx"');
        $templateProcessor->saveAs('php://output');
	}

	public function batalKonfirmasi(Request $request){
		$calon_peserta_didik_id = $request->calon_peserta_didik_id ? $request->calon_peserta_didik_id : null;

		$exe = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
		->where('calon_peserta_didik_id','=',$calon_peserta_didik_id)
		->update([
			'status_konfirmasi_id' => null,
			'last_update' => DB::raw("now()")
		]);

		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
			->where('calon_peserta_didik_id','=',$calon_peserta_didik_id)->get()
		], 200);
	}
	
	public function hapusCalonPesertaDidik(Request $request){
		$calon_peserta_didik_id = $request->calon_peserta_didik_id ? $request->calon_peserta_didik_id : null;

		$exe = DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
		->where('calon_peserta_didik_id','=',$calon_peserta_didik_id)
		->update([
			'soft_delete' => 1,
			'last_update' => DB::raw("now()")
		]);

		if($exe){
			$exe_pilihan = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->where('peserta_didik_id','=',$calon_peserta_didik_id)
			->update([
				'soft_delete' => 1,
				'last_update' => DB::raw("now()")
			]);
		}

		return response([ 
			'sukses' => $exe ? true : false, 
			'sukses_pilihan' => $exe_pilihan ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.calon_peserta_didik')
			->where('calon_peserta_didik_id','=',$calon_peserta_didik_id)->get()
		], 200);
	}

	public function getStatistikDinas(Request $request){
		$kode_wilayah = $request->kode_wilayah ? $request->kode_wilayah : null;

		$sql = 'SELECT
			jalur.*,
			COALESCE ( calon.total, 0 ) AS total,
			COALESCE ( calon.total_hari_ini, 0 ) as total_hari_ini
		FROM
			ref_ppdb.jalur jalur
			LEFT JOIN (
			SELECT
				jalur_id,
				SUM ( 1 ) AS total,
				SUM ( case when date(last_update) = date(now()) then 1 else 0 end) as total_hari_ini 
			FROM
				(
				SELECT
					sekolah_pilihan.sekolah_id,
					sekolah_pilihan.pengguna_id,
					sekolah_pilihan.jalur_id,
					calon_peserta_didik.last_update
				FROM
					ppdb.sekolah_pilihan AS sekolah_pilihan
					join ppdb.calon_peserta_didik as calon_peserta_didik on calon_peserta_didik.calon_peserta_didik_id = sekolah_pilihan.peserta_didik_id
				WHERE
					sekolah_pilihan.soft_delete = 0 
					AND sekolah_pilihan.urut_pilihan = 1 
					AND calon_peserta_didik.status_konfirmasi_id = 1
					AND calon_peserta_didik.soft_delete = 0
				-- GROUP BY
				-- 	sekolah_pilihan.sekolah_id,
				-- 	sekolah_pilihan.pengguna_id,
				-- 	sekolah_pilihan.jalur_id 
				) aaa 
			GROUP BY
				jalur_id 
			) calon ON calon.jalur_id = jalur.jalur_id 
		WHERE
			jalur.expired_date IS NULL 
			AND jalur.level_jalur = 1';
		
		$fetch = DB::connection('sqlsrv_2')->select($sql);

		return $fetch;
	}

	public function simpanJadwal(Request $request){
		$jadwal_id = $request->jadwal_id ? $request->jadwal_id : RuangController::generateUUID();
		$soft_delete = $request->soft_delete ? $request->soft_delete : 0;

		$cek = DB::connection('sqlsrv_2')->table('ppdb.jadwal')
		->where('jadwal_id','=',$jadwal_id)
		->get();

		if(sizeof($cek) > 0){
			//update
			$exe = DB::connection('sqlsrv_2')->table('ppdb.jadwal')
			->where('jadwal_id','=',$jadwal_id)
			->update([
				'jalur_id' => $request->jalur_id,
				'tahap' => $request->tahap,
				'waktu_mulai' => $request->waktu_mulai,
				'waktu_selesai' => $request->waktu_selesai,
				'pengguna_id' => $request->pengguna_id,
				'last_update' => DB::raw("now()"),
				'soft_delete' => $soft_delete
			]);
		}else{
			//insert
			$exe = DB::connection('sqlsrv_2')->table('ppdb.jadwal')
			->insert([
				'jadwal_id' => $jadwal_id,
				'jalur_id' => $request->jalur_id,
				'tahap' => $request->tahap,
				'waktu_mulai' => $request->waktu_mulai,
				'waktu_selesai' => $request->waktu_selesai,
				'pengguna_id' => $request->pengguna_id,
				'create_date' => DB::raw("now()"),
				'last_update' => DB::raw("now()"),
				'soft_delete' => $soft_delete,
				'kode_wilayah' => '052100',
				'periode_kegiatan_id' => '2021'
			]);
		}

		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.jadwal')
			->where('jadwal_id','=',$jadwal_id)
			->get()
		], 200);
		
	}

	public function getKuota(Request $request){
		// $sql = "SELECT
		// 	* 
		// FROM
		// 	sekolah 
		// WHERE
		// 	sekolah.soft_delete = 0 
		// 	AND sekolah.bentuk_pendidikan_id IN ( 5, 6 ) 
		// 	AND LEFT ( sekolah.kode_wilayah, 4 ) = '0521'";

		// $fetch = DB::connection('sqlsrv_2')->selec($sql);

		$start = $request->start ? $request->start : 0;
		$limit = $request->limit ? $request->limit : 20;
		$keyword = $request->keyword ? $request->keyword : null;
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

		$fetch = DB::connection('sqlsrv_2')->table('sekolah')
		->where('sekolah.soft_delete','=',0)
		->whereIn('sekolah.bentuk_pendidikan_id', array(5,6))
		->where(DB::raw('LEFT ( sekolah.kode_wilayah, 4 )'),'=',DB::raw("'0521'"))
		->leftJoin(DB::raw("(SELECT
			kuota_sekolah.sekolah_id,
			SUM ( CASE WHEN kuota_sekolah.jalur_id = '0100' THEN kuota ELSE 0 END ) AS kuota_0100,
			SUM ( CASE WHEN kuota_sekolah.jalur_id = '0200' THEN kuota ELSE 0 END ) AS kuota_0200,
			SUM ( CASE WHEN kuota_sekolah.jalur_id = '0300' THEN kuota ELSE 0 END ) AS kuota_0300,
			SUM ( CASE WHEN kuota_sekolah.jalur_id = '0400' THEN kuota ELSE 0 END ) AS kuota_0400,
			SUM ( CASE WHEN kuota_sekolah.jalur_id = '0500' THEN kuota ELSE 0 END ) AS kuota_0500 
		FROM
			ppdb.kuota_sekolah kuota_sekolah 
		WHERE
			soft_delete = 0 
		GROUP BY
			kuota_sekolah.sekolah_id) kuota"), 'kuota.sekolah_id','=','sekolah.sekolah_id')
		->join('ref.bentuk_pendidikan as bp', 'bp.bentuk_pendidikan_id','=','sekolah.bentuk_pendidikan_id')
		->join('ref.mst_wilayah as kec','kec.kode_wilayah','=',DB::raw("left(sekolah.kode_wilayah,6)"))
		->join('ref.mst_wilayah as kab','kab.kode_wilayah','=','kec.mst_kode_wilayah')
		->join('ref.mst_wilayah as prov','prov.kode_wilayah','=','kab.mst_kode_wilayah')
		->select(
			'sekolah.*',
			'kuota.*',
			DB::raw("(case when sekolah.status_sekolah = 1 then 'Negeri' else 'Swasta' end) as status"),
			'bp.nama as bentuk',
			'kec.nama as kecamatan',
			'kab.nama as kabupaten',
			'prov.nama as provinsi'
		)
		;

		if($keyword){
			$fetch->where(function($query) use($keyword){
				$query->where('sekolah.nama', 'ilike', DB::raw("'%".$keyword."%'"))
					->orWhere('sekolah.npsn','ilike', DB::raw("'%".$keyword."%'"))
					->orWhere('sekolah.alamat','ilike', DB::raw("'%".$keyword."%'"))
				;
			});
		}
		
		if($sekolah_id){
			$fetch->where(function($query) use($sekolah_id){
				$query->where('sekolah.sekolah_id', '=', DB::raw("'".$sekolah_id."'"))
				;
			});
		}

		return response([ 
			'total' => $fetch->count(), 
			'rows' => $fetch->skip($start)->take($limit)->get()
		], 200);
	}

	public function simpanKuota(Request $request){
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$jalur_id = $request->jalur_id ? $request->jalur_id : null;
		$kuota = $request->kuota ? $request->kuota : null;

		$cek = DB::connection('sqlsrv_2')->table('ppdb.kuota_sekolah')
		->where('ppdb.kuota_sekolah.sekolah_id','=',$sekolah_id)
		->where('ppdb.kuota_sekolah.jalur_id','=',$jalur_id)
		->get()
		;

		if(sizeof($cek)> 0){
			//update
			$exe = DB::connection('sqlsrv_2')->table('ppdb.kuota_sekolah')
			->where('ppdb.kuota_sekolah.sekolah_id','=',$sekolah_id)
			->where('ppdb.kuota_sekolah.jalur_id','=',$jalur_id)
			->update([
				'kuota' => $kuota,
				'last_update' => DB::raw("now()")
			])
			;

		}else{
			//insert
			$kuota_sekolah_id = RuangController::generateUUID();

			$exe = DB::connection('sqlsrv_2')->table('ppdb.kuota_sekolah')
			->insert([
				'kuota_sekolah_id' => $kuota_sekolah_id,
				'sekolah_id' => $sekolah_id,
				'jalur_id' => $jalur_id,
				'kuota' => $kuota,
				'periode_kegiatan_id' => '2021',
				'create_date' => DB::raw("now()"),
				'last_update' => DB::raw("now()"),
				'soft_delete' => 0
			])
			;

		}

		return response([ 
			'sukses' => $exe ? true : false, 
			'rows' => DB::connection('sqlsrv_2')->table('ppdb.kuota_sekolah')
			->where('ppdb.kuota_sekolah.sekolah_id','=',$sekolah_id)
			->where('ppdb.kuota_sekolah.jalur_id','=',$jalur_id)
			->get()
		], 200);
	}

	public function simpanDaftarUlang(Request $request){
		$peserta_didik_id = $request->peserta_didik_id ? $request->peserta_didik_id : null;
		$sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
		$status_diterima_id = $request->status_diterima_id ? $request->status_diterima_id : null;

		if($peserta_didik_id && $status_diterima_id && $sekolah_id){
			$cek = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
			->where('peserta_didik_id','=',$peserta_didik_id)
			->where('sekolah_id','=',$sekolah_id)
			->where('soft_delete','=',0)
			// ->where('urut_pilihan','=',1)
			->get();

			if(sizeof($cek) > 0){
				//update
				$exe = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
				->where('peserta_didik_id','=',$peserta_didik_id)
				->where('sekolah_id','=',$sekolah_id)
				->where('soft_delete','=',0)
				// ->where('urut_pilihan','=',1)
				->update([
					'status_diterima_id' => $status_diterima_id,
					'last_update' => DB::raw("now()")
				]);
			}else{
				$exe = false;
			}

			if((int)$status_diterima_id === 3){
				//lanjutan kalau cabut berkas
				$exe_tambahan = DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
				->where('peserta_didik_id','=',$peserta_didik_id)
				// ->where('sekolah_id','=',$sekolah_id)
				->whereIn('urut_pilihan', array(2,3,4))
				->where('soft_delete','=',0)
				->update([
					'soft_delete' => 1,
					'last_update' => DB::raw("now()")
				]);
			}

			return response([ 
				'sukses' => $exe ? true : false, 
				'rows' => DB::connection('sqlsrv_2')->table('ppdb.sekolah_pilihan')
				->where('peserta_didik_id','=',$peserta_didik_id)
				->where('sekolah_id','=',$sekolah_id)
				->where('soft_delete','=',0)
				// ->where('urut_pilihan','=',1)
				->get()
			], 200);

		}else{
			return response([ 
				'sukses' => false
			], 200);
			
		}

		
	}
}