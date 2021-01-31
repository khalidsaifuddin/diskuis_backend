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
use App\Http\Controllers\PoinController;

use App\Http\Middleware\S3;

class KuisController extends Controller
{
    static public function getSkema(){
        $return = array();
        $return['dbo'] = 'mb';
        $return['ref'] = 'mb_ref';

        return $return;
    }

    static function getHasilKuisPenggunaExcel(Request $request){
        $sesi_kuis_id = $request->sesi_kuis_id;
        $pengguna_id = $request->pengguna_id;
        $kuis_id = $request->kuis_id;

        $sql = "SELECT
            pertanyaan_kuis.kode_pertanyaan,
            pertanyaan_kuis.teks as pertanyaan,
            pilihan_pertanyaan_kuis.teks as pilihan_jawaban,
            jawaban_kuis.* 
        FROM
            jawaban_kuis
            JOIN pertanyaan_kuis ON pertanyaan_kuis.pertanyaan_kuis_id = jawaban_kuis.pertanyaan_kuis_id
            LEFT JOIN pilihan_pertanyaan_kuis ON pilihan_pertanyaan_kuis.pilihan_pertanyaan_kuis_id = jawaban_kuis.pilihan_pertanyaan_kuis_id 
            AND jawaban_kuis.soft_delete = 0 
        WHERE
            jawaban_kuis.pengguna_id = '".$pengguna_id."' 
            AND jawaban_kuis.kuis_id = '".$kuis_id."' 
            AND jawaban_kuis.sesi_kuis_id = '".$sesi_kuis_id."' 
            AND jawaban_kuis.soft_delete = 0";

        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql))
        ;

        $fetch2 = DB::connection('sqlsrv_2')->table('sesi_kuis')
        ->join('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
        ->where('sesi_kuis.sesi_kuis_id','=',$sesi_kuis_id)
        ->select(
            'kuis.*',
            'sesi_kuis.keterangan as keterangan'
        )
        ->first();

        // return $fetch2;

        return view('excel/HasilKuisPengguna', ['return' => $fetch, 'judul_kuis' => $fetch2->judul, 'keterangan' => $fetch2->keterangan]);
    }

    static function getJawabanPenggunaKuis(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $sesi_kuis_id = $request->sesi_kuis_id ? $request->sesi_kuis_id : null;

        $fetch_pertanyaan = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
        ->where('pertanyaan_kuis.soft_delete','=',0)
        ->where('pertanyaan_kuis.kuis_id','=',$kuis_id)
        // ->get()
        ;

        $rows = $fetch_pertanyaan->get();

        for ($i=0; $i < sizeof($rows); $i++) { 
            $fetch_jawaban = DB::connection('sqlsrv_2')->table('jawaban_kuis')
            ->leftJoin('pilihan_pertanyaan_kuis','pilihan_pertanyaan_kuis.pilihan_pertanyaan_kuis_id','=','jawaban_kuis.pilihan_pertanyaan_kuis_id')
            ->where('jawaban_kuis.soft_delete','=',0)
            ->where('jawaban_kuis.pertanyaan_kuis_id','=', $rows[$i]->pertanyaan_kuis_id)
            ->where('jawaban_kuis.pengguna_id','=',$pengguna_id)
            ->where('jawaban_kuis.sesi_kuis_id','=',$sesi_kuis_id)
            ->select(
                'jawaban_kuis.*',
                'pilihan_pertanyaan_kuis.teks as pilihan_pertanyaan_kuis'
            )
            ->get()
            ;

            $rows[$i]->jawaban = array(
                'rows' => $fetch_jawaban, 
                'total' => sizeof($fetch_jawaban)
            );
        }

        return response([
            'rows' => $rows, 
            'total' => $fetch_pertanyaan->count()
        ]);
    }
    
    static function getCountKuisUmum(Request $request){
        $fetch = DB::connection('sqlsrv_2')->select(DB::raw("SELECT
            jenjang_id,
            SUM ( 1 ) AS total 
        FROM
            kuis 
        WHERE
            soft_delete = 0 
            AND status_privasi = 1 
            AND jenjang_id = 98
            AND publikasi = 1
            AND pengguna_id is not null
        GROUP BY
            jenjang_id"));

        return $fetch;
    }

    static function getLaporanSesiKuis(Request $request){
        $sesi_kuis_id = $request->sesi_kuis_id;

        $fetch = DB::connection('sqlsrv_2')->table('pengguna_kuis')
        ->join('pengguna','pengguna.pengguna_id','=','pengguna_kuis.pengguna_id')
        ->where('pengguna_kuis.soft_delete','=',0)
        ->where('pengguna.soft_delete','=',0)
        ->where('pengguna_kuis.sesi_kuis_id','=',$sesi_kuis_id)
        ->select(
            'pengguna_kuis.*',
            'pengguna.nama as nama_pengguna',
            'pengguna.username as username_pengguna'
        )
        ->get();

        $fetch2 = DB::connection('sqlsrv_2')->table('sesi_kuis')
        ->join('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
        ->where('sesi_kuis.sesi_kuis_id','=',$sesi_kuis_id)
        ->select(
            'kuis.*',
            'sesi_kuis.keterangan as keterangan'
        )
        ->first();

        // return $fetch2;

        return view('excel/LaporanSesiKuisExcel', ['return' => $fetch, 'judul_kuis' => $fetch2->judul, 'keterangan' => $fetch2->keterangan]);
    }

    public function uploadAudio(Request $request)
    {
        $data = $request->all();
        $file = $data['file_audio'];
        $guid = $data['guid'];

        if(($file == 'undefined') OR ($file == '')){
            return response()->json(['msg' => 'tidak_ada_file']);
        }

        $ext = $file->getClientOriginalExtension();
        $name = $file->getClientOriginalName();

        $destinationPath = base_path('/public/assets/audio');
        $upload = $file->move($destinationPath, $guid.".".$ext);

        $msg = $upload ? 'sukses' : 'gagal';

        if($upload){
            return response(['msg' => $msg, 'filename' => "/assets/audio/".$guid.".".$ext]);
        }

        // try {
        //     $s3 = new S3( '174ad63d885761e63d0b', 'vPsIF6wJrQ/vgsYxz6HKZMjE303FatMvkGLfQHre' );
    
        //     $s3->putBucket('audio-diskuis', S3::ACL_PUBLIC_READ);
    
        //     $s3->putObjectFile($guid.".".$ext, 'audio-diskuis', basename($guid.".".$ext), S3::ACL_PUBLIC_READ);

        //     // return true;
        //     return response(['msg' => 'sukses', 'filename' => $guid.".".$ext]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     return response(['msg' => 'gagal', 'filename' => $guid.".".$ext]);
        //     // return false;
        // }


    }

    static function getStatKuis(Request $request){
        $sesi_kuis_id = $request->input('sesi_kuis_id');

        $sql = "SELECT
            rerata.rata,
            rerata.maksimal,
            rerata.minimal,
            rerata.total_peserta,
            tertinggi.pengguna_id AS peserta_tertinggi,
            tertinggi.nama_pengguna as peserta_tertinggi_nama,
            terendah.pengguna_id AS peserta_terendah,
            terendah.nama_pengguna as peserta_terendah_nama,
            kuis.* 
        FROM
            kuis
            JOIN sesi_kuis ON sesi_kuis.kuis_id = kuis.kuis_id
            LEFT JOIN (
            SELECT
                sesi_kuis_id,
                kuis_id,
                AVG ( skor ) AS rata,
                MAX ( skor ) AS maksimal,
                MIN ( skor ) AS minimal,
                sum(1) as total_peserta
            FROM
                pengguna_kuis 
            WHERE
                sesi_kuis_id = '{$sesi_kuis_id}' 
                AND soft_delete = 0 
            GROUP BY
                sesi_kuis_id,
                kuis_id 
            ) rerata ON rerata.kuis_id = kuis.kuis_id
            LEFT JOIN (
            SELECT
                pengguna_kuis.sesi_kuis_id,
                pengguna_kuis.kuis_id,
                pengguna_kuis.pengguna_id,
                pengguna_kuis.skor,
                pengguna_kuis.create_date,
                pengguna_kuis.last_update,
                pengguna_kuis.durasi ,
                pengguna.nama as nama_pengguna
            FROM
                pengguna_kuis 
                join pengguna on pengguna.pengguna_id = pengguna_kuis.pengguna_id and pengguna.soft_delete = 0
            WHERE
                pengguna_kuis.soft_delete = 0 
                AND pengguna_kuis.sesi_kuis_id = '{$sesi_kuis_id}' 
            ORDER BY
                pengguna_kuis.skor DESC,
                pengguna_kuis.durasi DESC 
                LIMIT 1 
            ) tertinggi ON tertinggi.kuis_id = kuis.kuis_id
            LEFT JOIN (
            SELECT
                pengguna_kuis.sesi_kuis_id,
                pengguna_kuis.kuis_id,
                pengguna_kuis.pengguna_id,
                pengguna_kuis.skor,
                pengguna_kuis.create_date,
                pengguna_kuis.last_update,
                pengguna_kuis.durasi ,
                pengguna.nama as nama_pengguna
            FROM
                pengguna_kuis 
                join pengguna on pengguna.pengguna_id = pengguna_kuis.pengguna_id and pengguna.soft_delete = 0
            WHERE
                pengguna_kuis.soft_delete = 0 
                AND pengguna_kuis.sesi_kuis_id = '{$sesi_kuis_id}' 
            ORDER BY
                pengguna_kuis.skor ASC,
                pengguna_kuis.durasi ASC 
                LIMIT 1 
            ) terendah ON terendah.kuis_id = kuis.kuis_id 
        WHERE
            kuis.soft_delete = 0 
            AND sesi_kuis.soft_delete = 0 
            AND sesi_kuis.sesi_kuis_id = '{$sesi_kuis_id}'";

        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        return $fetch;
    }

    static function aktivitasKuis(Request $request){
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $fetch = DB::connection('sqlsrv_2')
        ->table('pengguna_kuis')
        ->join('kuis','kuis.kuis_id','=','pengguna_kuis.kuis_id')
        ->join('pengguna as peserta_kuis','peserta_kuis.pengguna_id','=','pengguna_kuis.pengguna_id')
        ->where('pengguna_kuis.soft_delete','=',0)
        ->orderBy('pengguna_kuis.create_date','DESC')
        ->select(
            'pengguna_kuis.*',
            'peserta_kuis.nama as peserta',
            'peserta_kuis.gambar as gambar_peserta',
            'kuis.judul as nama_kuis'
        )
        ;

        if($kuis_id){
            $fetch->where('pengguna_kuis.kuis_id','=',$kuis_id);
        }

        if($pengguna_id){
            $fetch->where('kuis.pengguna_id','=',$pengguna_id);
        }

        return response(
            [
                'rows' => $fetch->skip($start)->take($limit)->get(),
                'total' =>$fetch->count()
            ],
            200
        );
    }
    
    static public function generateUUID()
    {
        // return self::getSkema()['dbo'];

        $uuid = DB::connection('sqlsrv_2')
        ->table(DB::raw('pengguna'))
        ->select(DB::raw('uuid_generate_v4() as uuid'))
        ->first();

        return $uuid->{'uuid'};
    }

    static public function hapusKuis(Request $request){
        $kuis_id = $request->kuis_id;

        $exe1 = DB::connection('sqlsrv_2')->table('kuis')
        ->where('kuis_id','=', $kuis_id)
        ->update([
            'soft_delete' => 1,
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        if($exe1){

            return response(
                [
                    '1' => ($exe1 ? true : false),
					'success' => true
				],
				200
			);

        }else{
            return response(
                [
					'success' => false
				],
				200
			);
        }
    }

    static public function hapusSesiKuis(Request $request){
        $sesi_kuis_id = $request->sesi_kuis_id;

        $exe1 = DB::connection('sqlsrv_2')->table('sesi_kuis')
        ->where('sesi_kuis_id','=', $sesi_kuis_id)
        ->update([
            'soft_delete' => 1,
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        if($exe1){

            return response(
                [
                    '1' => ($exe1 ? true : false),
					'success' => true
				],
				200
			);

        }else{
            return response(
                [
					'success' => false
				],
				200
			);
        }
    }

    public function getLaporanHasilKuis_excel(Request $request)
	{
        $data = $this->getLaporanHasilKuis($request);
        $nama_ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$request->ruang_id)->first()->nama;

		return view('excel/LaporanHasilKuisExcel', ['return' => $data, 'nama_ruang' => $nama_ruang]);
	}

    static function getLaporanHasilKuis(Request $request){
        $sql = "SELECT
                    pengguna.pengguna_id,
                    pengguna.nama,
                    pengguna_ruang.ruang_id
                FROM
                    pengguna_ruang
                join pengguna on pengguna.pengguna_id = pengguna_ruang.pengguna_id	
                WHERE
                    pengguna_ruang.ruang_id = '".$request->ruang_id."'
                    and room_master != 1";
        
        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
            // $sql_kuis = "SELECT
            //             kuis.judul, 
	        //             pengguna_kuis.skor 
            //         FROM
            //                 pengguna_kuis
            //                 LEFT JOIN sesi_kuis ON sesi_kuis.sesi_kuis_id = pengguna_kuis.sesi_kuis_id
            //                 JOIN kuis on kuis.kuis_id = sesi_kuis.kuis_id 
            //         WHERE
            //             pengguna_kuis.pengguna_id = '".$fetch[$iFetch]->pengguna_id."'
            //             AND sesi_kuis.ruang_id = '".$fetch[$iFetch]->ruang_id."'
            //         order by sesi_kuis.create_date asc";
            $sql_kuis = "SELECT
                            kuis.judul, 
                            sesi_kuis.keterangan,
                            COALESCE(pengguna_kuis.skor,0) as skor
                        FROM
                            sesi_kuis
                            JOIN kuis on kuis.kuis_id = sesi_kuis.kuis_id and kuis.soft_delete = 0
                            LEFT JOIN pengguna_kuis ON sesi_kuis.sesi_kuis_id = pengguna_kuis.sesi_kuis_id AND pengguna_kuis.pengguna_id = '".$fetch[$iFetch]->pengguna_id."'
                        WHERE
                            sesi_kuis.ruang_id = '".$fetch[$iFetch]->ruang_id."'
                        order by sesi_kuis.create_date asc";
            $fetch_kuis = DB::connection('sqlsrv_2')->select(DB::raw($sql_kuis));
            
            $arr = array();

            for ($iFetchKuis=0; $iFetchKuis < sizeof($fetch_kuis); $iFetchKuis++) { 
                // foreach ($fetch_kuis[$iFetchKuis] as $key => $value) {
                //     $fetch[$iFetch]->{$fetch_kuis[$iFetchKuis]->{$key}} = $value;
                // }
                $arrTmp = array();

                $arrTmp['judul'] = $fetch_kuis[$iFetchKuis]->{'judul'};
                $arrTmp['keterangan'] = $fetch_kuis[$iFetchKuis]->{'keterangan'};
                $arrTmp['skor'] = round($fetch_kuis[$iFetchKuis]->{'skor'},2);

                // $fetch[$iFetch]->{$fetch_kuis[$iFetchKuis]->{'judul'}} = $fetch_kuis[$iFetchKuis]->{'skor'};

                array_push($arr, $arrTmp);
            }
            
            $fetch[$iFetch]->{'kuis'} = $arr;
        }

        if($request->output == 'xlsx'){
            // return $request->ruang_id;die;
            // return DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$request->ruang_id)->get();die;
            $nama_ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$request->ruang_id)->first()->nama;
            return view('excel/LaporanHasilKuisExcel', ['return' => $fetch, 'nama_ruang' => $nama_ruang]);
        }else{
            
            return response(
                [
                    'rows' => $fetch,
                    'total' => sizeof($fetch)
                ],
                200
            );

        }

    }

    static function getKuisTrending(Request $request){
        $sql = "SELECT
                    pengguna.nama as pengguna,
                    * 
                FROM (
                    SELECT 
                        sesi_default.kode_sesi as kode_sesi,
                        COALESCE( peserta_total.total, 0 ) AS jumlah_peserta,
                        COALESCE ( sesi_total.total, 0 ) AS jumlah_sesi,
                        COALESCE ( SUBSTRING ( CAST ( peserta_total.terakhir_diakses AS VARCHAR ( 20 )), 1, 10 ), '2000-01-01' ) AS akses_terakhir,
                        kuis.*,
                        sesi_default.sesi_kuis_id 
                    FROM
                        kuis
                        LEFT JOIN ( SELECT kuis_id, SUM ( 1 ) AS total, MAX ( last_update ) AS terakhir_diakses FROM pengguna_kuis WHERE soft_delete = 0 GROUP BY kuis_id ) peserta_total ON peserta_total.kuis_id = kuis.kuis_id
                        LEFT JOIN ( SELECT kuis_id, SUM ( 1 ) AS total FROM sesi_kuis WHERE Soft_delete = 0 GROUP BY kuis_id ) sesi_total ON sesi_total.kuis_id = kuis.kuis_id 
                        LEFT JOIN (SELECT
                            ROW_NUMBER () OVER ( 
                                PARTITION BY 
                                    kuis_id
                                ORDER BY 
                                    create_date ASC 
                            ) AS urutan,
                            * 
                        FROM
                            sesi_kuis 
                        WHERE
                            \"default\" = 1
                        AND soft_delete = 0
                        AND ruang_id is null) sesi_default on sesi_default.kuis_id = kuis.kuis_id AND sesi_default.urutan = 1
                    WHERE
                        kuis.soft_delete = 0 
                        and kuis.publikasi = 1
                        and kuis.status_privasi = 1
                ) kuis_trending
                JOIN pengguna on pengguna.pengguna_id = kuis_trending.pengguna_id
                ORDER BY
                    akses_terakhir DESC,
                    jumlah_peserta DESC,
                    jumlah_sesi DESC
                LIMIT 8";

        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        return response(
            [
                'rows' => $fetch,
                'total' => sizeof($fetch)
            ],
            200
        );
    }

    static function getSesiKuis(Request $request){
        $kuis_id = $request->input('kuis_id');
        $ruang_id = $request->input('ruang_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        $kode_sesi = $request->input('kode_sesi');
        $pengguna_id = $request->input('pengguna_id');
        $tampil_jumlah_peserta = $request->input('tampil_jumlah_peserta');
        
        $fetch = DB::connection('sqlsrv_2')->table('sesi_kuis')
        ->join('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
        ->join('pengguna','pengguna.pengguna_id','=','kuis.pengguna_id')
        ->where('sesi_kuis.soft_delete','=',0)
        ->where('kuis.soft_delete','=',0)
        ->where('pengguna.soft_delete','=',0)
        ->select(
            'kuis.*',
            'sesi_kuis.*',
            'pengguna.nama as pengguna'
            // 'kuis.judul as judul'
        );

        if($sesi_kuis_id){
            $fetch->where('sesi_kuis.sesi_kuis_id','=',$sesi_kuis_id);
        }

        if($kuis_id){
            $fetch->where('sesi_kuis.kuis_id','=',$kuis_id);
        }
        
        if($kode_sesi){
            $fetch->where('sesi_kuis.kode_sesi','=',$kode_sesi);
        }
        
        if($ruang_id){
            $fetch->where('sesi_kuis.ruang_id','=',$ruang_id);
        }

        if($tampil_jumlah_peserta ==  'Y'){
            $fetch->leftJoin(DB::raw("(select kuis_id, sesi_kuis_id, sum(1) as total from pengguna_kuis where soft_delete = 0 group by kuis_id, sesi_kuis_id) as jumlah_peserta"),function($join)
            {
                $join->on('jumlah_peserta.kuis_id','=', 'sesi_kuis.kuis_id');
                $join->on('jumlah_peserta.sesi_kuis_id','=', 'sesi_kuis.sesi_kuis_id');
            });
            $fetch->select(
                'kuis.*',
                'sesi_kuis.*',
                'pengguna.nama as pengguna',
                'jumlah_peserta.total as jumlah_peserta'
            );
        }

        $fetch = $fetch->get();

        $return = array();
        $return['total'] = sizeof($fetch);
        $return['rows'] = $fetch;

        return $return;
    }

    public function setSesiKuis(Request $request){
        $kuis_id = $request->input('kuis_id');
        $pengguna_id = $request->input('pengguna_id');
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : null;
        $jawaban_diacak = $request->input('jawaban_diacak');
        $tampilkan_jawaban_benar = $request->input('tampilkan_jawaban_benar');
        $waktu_mulai = $request->input('waktu_mulai');
        $waktu_selesai = $request->input('waktu_selesai');
        $keterangan = $request->input('keterangan');
        $jumlah_percobaan = $request->input('jumlah_percobaan');
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : null;
        $sesi_kuis_id = self::generateUUID();

        $exe = DB::connection('sqlsrv_2')->table('sesi_kuis')->insert([
            'sesi_kuis_id' => $sesi_kuis_id,
            'pengguna_id' =>  $pengguna_id,
            'kuis_id' => $kuis_id,  
            'ruang_id' => $ruang_id,  
            'tanggal' => DB::raw('now()::timestamp(0)'),
            'jawaban_diacak' => $jawaban_diacak,  
            'tampilkan_jawaban_benar' => $tampilkan_jawaban_benar,  
            'waktu_mulai' => $waktu_mulai,  
            'waktu_selesai' => $waktu_selesai,  
            'keterangan' => $keterangan,  
            'jumlah_percobaan' => $jumlah_percobaan,  
            'ruang_id' => ((int)$ruang_id != 99 ? $ruang_id : null),  
            'create_date' => DB::raw('now()::timestamp(0)'),
            'last_update' => DB::raw('now()::timestamp(0)'),
            'soft_delete' => 0,
            'kode_sesi' => strtoupper(RuangController::generateRandomString(10))
        ]);
        $label = 'INSERT';  

        if($exe){

            if($label == 'INSERT' && $ruang_id){
                try {
                    //code...
                    $linimasa_id = RuangController::generateUUID();
                    $linimasa = LinimasaController::simpanLinimasa($linimasa_id, $pengguna_id,2,'','',$ruang_id,$sesi_kuis_id);
        
                    if($linimasa){
                        $sukses_linimasa = true;
                    }else{
                        $sukses_linimasa = false;
                    }
                } catch (\Throwable $th) {
                    $sukses_linimasa = false;
                }

                try {
                    //code...
                    $aktivitas_id = RuangController::generateUUID();
                    $aktivitas = LinimasaController::simpanAktivitas($aktivitas_id, $pengguna_id, 2, 'tambah kuis ke ruang', $sesi_kuis_id);
        
                    if($aktivitas){
                        $sukses_aktivitas = true;
                    }else{
                        $sukses_aktivitas = false;
                    }
                } catch (\Throwable $th) {
                    $sukses_aktivitas = false;
                }
            }else{
                $sukses_linimasa = false;
                $sukses_aktivitas = false;
            }

            return response(
                [
					'rows' => DB::connection('sqlsrv_2')->table('sesi_kuis')->where('sesi_kuis_id','=',$sesi_kuis_id)->get(),
					'success' => true,
					'sukses_linimasa' => ($sukses_linimasa ? true : false),
					'sukses_aktivitas' => ($sukses_aktivitas ? true : false)
				],
				200
			);
        }else{
            return response(
				[
					'rows' => [],
					'success' => false
				],
				200
			);
        }
    }

    public function simpanJawabanKuisCheckbox(Request $request){
        $kuis_id = $request->input('kuis_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_kuis_id = $request->input('pertanyaan_kuis_id');
        $pilihan_pertanyaan_kuis_id = $request->input('pilihan_pertanyaan_kuis_id');
        $nilai = $request->input('nilai');
        $pilihan_pertanyaan_kuis_id = $request->input('pilihan_pertanyaan_kuis_id');
        
        //delete everything else
        $exe_delete = DB::connection('sqlsrv_2')
        ->table('jawaban_kuis')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
        // ->where('pilihan_pertanyaan_kuis_id','=',$pilihan_pertanyaan_kuis_id)
        ->where('sesi_kuis_id','=',$sesi_kuis_id)
        ->where('soft_delete','=',0)
        ->update([
            'soft_delete' => 1,
            'last_update' => date('Y-m-d H:i:s')
        ]);
            
            // return $pilihan_pertanyaan_kuis_id;
        $str_pilihan_pertanyaan_kuis_id = "";
            
        for ($i=0; $i < sizeof($pilihan_pertanyaan_kuis_id); $i++) { 
            
            $str_pilihan_pertanyaan_kuis_id .= ",'".$pilihan_pertanyaan_kuis_id[$i]."'";
            $jawaban_kuis_id = self::generateUUID();

            $cek = DB::connection('sqlsrv_2')
                ->table('jawaban_kuis')
                ->where('pengguna_id','=',$pengguna_id)
                ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
                ->where('pilihan_pertanyaan_kuis_id','=',$pilihan_pertanyaan_kuis_id[$i])
                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                ->where('soft_delete','=',0)
                ->get();
            
            // return $cek;
            if(sizeof($cek) > 0){
                //update - sudah ada
                $exe = DB::connection('sqlsrv_2')->table('jawaban_kuis')
                ->where('pengguna_id','=',$pengguna_id)
                ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
                ->where('pilihan_pertanyaan_kuis_id','=',$pilihan_pertanyaan_kuis_id[$i])
                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                ->update([
                    'pilihan_pertanyaan_kuis_id' => $pilihan_pertanyaan_kuis_id[$i],
                    'nilai' => 1,
                    'isian' => '',
                    'benar' => DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')->where('pilihan_pertanyaan_kuis_id','=',$pilihan_pertanyaan_kuis_id[$i])->first()->jawaban_benar,
                    'last_update' => date('Y-m-d H:i:s')
                ]);
                $label = 'UPDATE';

            }else{
                //insert - belum ada
                $exe = DB::connection('sqlsrv_2')->table('jawaban_kuis')->insert([
                    'jawaban_kuis_id' => $jawaban_kuis_id,
                    'pengguna_id' =>  $pengguna_id,
                    'kuis_id' => $kuis_id,
                    'pertanyaan_kuis_id' => $pertanyaan_kuis_id,
                    'pilihan_pertanyaan_kuis_id' => $pilihan_pertanyaan_kuis_id[$i],
                    'sesi_kuis_id' => $sesi_kuis_id,
                    'nilai' => 1,
                    'isian' => '',
                    'benar' => DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')->where('pilihan_pertanyaan_kuis_id','=',$pilihan_pertanyaan_kuis_id[$i])->first()->jawaban_benar
                ]);
                $label = 'INSERT';  

            }

        }

        $str_pilihan_pertanyaan_kuis_id = substr($str_pilihan_pertanyaan_kuis_id, 1, 1000000);
        
        $sql_benar = "SELECT
            cek_benar.jawaban_benar,
            cek_benar.total_jawaban_benar,
            (
                case when cek_benar.total_jawaban_benar > 0 then
                ROUND( CAST ( ( cek_benar.jawaban_benar / CAST ( ( case when cek_benar.total_jawaban_benar > 0 then cek_benar.total_jawaban_benar else 1 end ) AS FLOAT )) AS NUMERIC ), 2 )
                ELSE
                0
                END
            )  AS benar 
        FROM
        ( SELECT SUM
            ( jawaban_benar ) AS jawaban_benar,
            ( 
            SELECT 
                SUM ( jawaban_benar ) 
            FROM 
                pilihan_pertanyaan_kuis 
            WHERE 
                pertanyaan_kuis_id = '".$pertanyaan_kuis_id."' 
            ) AS total_jawaban_benar 
        FROM
            pilihan_pertanyaan_kuis 
        WHERE
            pilihan_pertanyaan_kuis_id IN ( ".$str_pilihan_pertanyaan_kuis_id." ) 
        AND soft_delete = 0 
        ) cek_benar";

        // return $sql_benar;
        $fetch_benar = DB::connection('sqlsrv_2')->select(DB::raw($sql_benar));

        $return = array();
        $return['rows'] = $fetch_benar;
        $return['total'] =  sizeof($return['rows']);

        return $return;

    }

    public function simpanJawabanKuisIsian(Request $request){
        // return "oke";
        $kuis_id = $request->input('kuis_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_kuis_id = $request->input('pertanyaan_kuis_id');
        $pilihan_pertanyaan_kuis_id = $request->input('pilihan_pertanyaan_kuis_id');
        $nilai = $request->input('nilai');
        $isian_jawaban = $request->input('isian_jawaban');
        $jawaban_kuis_id = self::generateUUID();

        $fetch_pertanyaan_kuis = DB::connection('sqlsrv_2')
        ->table('pilihan_pertanyaan_kuis')
        ->where('pilihan_pertanyaan_kuis.pertanyaan_kuis_id', '=', DB::raw("'".$pertanyaan_kuis_id."'"))
        ->where('pilihan_pertanyaan_kuis.soft_delete', '=', DB::raw('0'))
        ->where(DB::raw("'".$isian_jawaban."'"), 'ilike', DB::raw("( '%' || teks || '%' )"))
        ->get();

        $cek = DB::connection('sqlsrv_2')
                ->table('jawaban_kuis')
                ->where('pengguna_id','=',$pengguna_id)
                ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                ->where('soft_delete','=',0)
                ->get();

        if(sizeof($fetch_pertanyaan_kuis) > 0){
            //jawaban benar
            $benar = 1;
        }else{
            //jawaban salah
            $benar = 0;
        }

        if(sizeof($cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('jawaban_kuis')
            ->where('pengguna_id','=',$pengguna_id)
            ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
            ->where('sesi_kuis_id','=',$sesi_kuis_id)
            ->update([
                'pilihan_pertanyaan_kuis_id' => $pilihan_pertanyaan_kuis_id,
                'nilai' => $nilai,
                'isian' => $isian_jawaban,
                'benar' => $benar,
                'last_update' => date('Y-m-d H:i:s')
            ]);

            $label = 'UPDATE';
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('jawaban_kuis')->insert([
                'jawaban_kuis_id' => $jawaban_kuis_id,
                'pengguna_id' =>  $pengguna_id,
                'kuis_id' => $kuis_id,
                'pertanyaan_kuis_id' => $pertanyaan_kuis_id,
                'pilihan_pertanyaan_kuis_id' => $pilihan_pertanyaan_kuis_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'nilai' => $nilai,
                'isian' => $isian_jawaban,
                'benar' => $benar
            ]);
            $label = 'INSERT';  
        }

        $return = array();
        $return['rows'] = DB::connection('sqlsrv_2')
        ->table('jawaban_kuis')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
        ->where('sesi_kuis_id','=',$sesi_kuis_id)
        ->where('soft_delete','=',0)
        ->get();
        $return['total'] =  sizeof($return['rows']);

        return $return;

        // return $fetch_pertanyaan_kuis;
    }

    public function simpanJawabanKuis(Request $request){
        $kuis_id = $request->input('kuis_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_kuis_id = $request->input('pertanyaan_kuis_id');
        $pilihan_pertanyaan_kuis_id = $request->input('pilihan_pertanyaan_kuis_id');
        $nilai = $request->input('nilai');
        $isian = $request->input('isian');
        $jawaban_kuis_id = self::generateUUID();

        $cek = DB::connection('sqlsrv_2')
                ->table('jawaban_kuis')
                ->where('pengguna_id','=',$pengguna_id)
                ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                ->where('soft_delete','=',0)
                ->get();

        $cek_benar = DB::connection('sqlsrv_2')
                ->table('pilihan_pertanyaan_kuis')
                ->where('pilihan_pertanyaan_kuis_id','=', $pilihan_pertanyaan_kuis_id)
                ->where('soft_delete','=',0)
                ->get();

        if(sizeof($cek_benar) > 0){

            if($cek_benar[0]->jawaban_benar == 1){
                $benar = 1;
            }else{
                $benar = 0;
            }

        }else{
            //harusnya sih ada. error ini
            $benar = 0;
        }

        if(sizeof($cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('jawaban_kuis')
            ->where('pengguna_id','=',$pengguna_id)
            ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
            ->where('sesi_kuis_id','=',$sesi_kuis_id)
            ->update([
                'pilihan_pertanyaan_kuis_id' => $pilihan_pertanyaan_kuis_id,
                'nilai' => $nilai,
                'isian' => $isian,
                'benar' => $benar,
                'last_update' => date('Y-m-d H:i:s')
            ]);

            $label = 'INSERT';
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('jawaban_kuis')->insert([
                'jawaban_kuis_id' => $jawaban_kuis_id,
                'pengguna_id' =>  $pengguna_id,
                'kuis_id' => $kuis_id,
                'pertanyaan_kuis_id' => $pertanyaan_kuis_id,
                'pilihan_pertanyaan_kuis_id' => $pilihan_pertanyaan_kuis_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'nilai' => $nilai,
                'isian' => $isian,
                'benar' => $benar
            ]);
            $label = 'INSERT';  
        }

        $return = array();
        $return['rows'] = DB::connection('sqlsrv_2')
        ->table('jawaban_kuis')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
        ->where('sesi_kuis_id','=',$sesi_kuis_id)
        ->where('soft_delete','=',0)
        ->get();
        $return['total'] =  sizeof($return['rows']);

        return $return;

    }

    public function getPertanyaanKuis(Request $request){
        $pertanyaan_kuis_id = $request->input('pertanyaan_kuis_id');
        $kuis_id = $request->input('kuis_id');

        $fetch = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
        ->leftJoin('ref.tipe_pertanyaan as tipe_pertanyaan','tipe_pertanyaan.tipe_pertanyaan_id','=','pertanyaan_kuis.tipe_pertanyaan_id')
        ->where('pertanyaan_kuis.soft_delete','=',0)
        ->select(
            'pertanyaan_kuis.*',
            'tipe_pertanyaan.nama as tipe_pertanyaan'
        )
        ;

        if($pertanyaan_kuis_id){
            $fetch->where('pertanyaan_kuis.pertanyaan_kuis_id','=',$pertanyaan_kuis_id);
        }
        
        if($kuis_id){
            $fetch->where('pertanyaan_kuis.kuis_id','=',$kuis_id);
        }

        $fetch =  $fetch->orderBy('create_date','ASC')->get();

        for ($iData=0; $iData < sizeof($fetch); $iData++) { 
            $fetch[$iData]->{'pilihan_pertanyaan_kuis'} = (object)array();
            
            $fetch_pertanyaan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')
            ->where('pertanyaan_kuis_id','=',$fetch[$iData]->{'pertanyaan_kuis_id'})
            ->where('pilihan_pertanyaan_kuis.soft_delete','=',0)
            ->get();
            
            for ($iPertanyaan=0; $iPertanyaan < sizeof($fetch_pertanyaan); $iPertanyaan++) { 
                
                $fetch[$iData]->{'pilihan_pertanyaan_kuis'}->{$fetch_pertanyaan[$iPertanyaan]->{'pilihan_pertanyaan_kuis_id'}} = $fetch_pertanyaan[$iPertanyaan];
                
            }

        }

        $return = array();
        $return['total'] = sizeof($fetch);
        $return['rows'] = $fetch;

        return $return;
    }

    // public function getPeringkatPenggunaKuis(Request $request){

    // }

    public function getKuisDiikuti(Request $request){
        $pengguna_id = $request->input('pengguna_id');
        $hanya_publik = $request->input('hanya_publik') ? $request->input('hanya_publik') : 'N';
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 15;

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna_kuis')
        ->join('pengguna','pengguna.pengguna_id','=','pengguna_kuis.pengguna_id')
        ->join('kuis','kuis.kuis_id','=','pengguna_kuis.kuis_id')
        ->join('sesi_kuis','sesi_kuis.sesi_kuis_id','=','pengguna_kuis.sesi_kuis_id')
        ->join(DB::raw("(select kuis_id, sesi_kuis_id, sum(1) as total from pengguna_kuis where soft_delete = 0 group by kuis_id, sesi_kuis_id) as jumtot"), function($join)
        {
            $join->on('jumtot.kuis_id','=', 'pengguna_kuis.kuis_id');
            $join->on('jumtot.sesi_kuis_id','=', 'pengguna_kuis.sesi_kuis_id');
        })
        // ->join(, 'peringkat.pengguna_id','=', 'pengguna_kuis.pengguna_id')
        ->join(DB::raw("(SELECT ROW_NUMBER
            () OVER ( PARTITION BY kuis_id, sesi_kuis_id ORDER BY status_mengerjakan_id DESC, COALESCE(skor,0) DESC ) AS peringkat ,
            *
        FROM
            pengguna_kuis 
        WHERE
            soft_delete = 0) as peringkat"), function($join)
        {
            $join->on('peringkat.pengguna_id','=', 'pengguna_kuis.pengguna_id');
            $join->on('peringkat.kuis_id','=', 'pengguna_kuis.kuis_id');
            $join->on('peringkat.sesi_kuis_id','=', 'pengguna_kuis.sesi_kuis_id');
        })
        ->where('pengguna_kuis.pengguna_id','=',DB::raw("'".$pengguna_id."'"))
        // ->where('pengguna_kuis.kuis_id','=',$kuis_id)
        ->where('pengguna_kuis.soft_delete','=',DB::raw('0'))
        ->where('kuis.soft_delete','=',DB::raw('0'))
        ->whereIn('kuis.status_privasi',($hanya_publik == 'Y' ? array('1') : array('1','2')))
        ->select(
            'pengguna_kuis.*',
            'kuis.*',
            'sesi_kuis.keterangan as keterangan_sesi_kuis',
            'sesi_kuis.kode_sesi',
            'pengguna.nama as nama_pengguna',
            'peringkat.peringkat',
            'jumtot.total as total_peserta',
            'pengguna_kuis.create_date as tanggal_mengerjakan'
        )
        // ->toSql();
        ->orderBy('pengguna_kuis.create_date', 'DESC');
        // ->get();

        // return $fetch_cek;die;

        $return = array();
        $return['total'] = $fetch_cek->count();
        $return['rows'] = $fetch_cek->skip($start)->take($limit)->get();

        return $return;
    }

    public function getPenggunaKuis(Request $request){
        $pengguna_id = $request->input('pengguna_id');
        $order_by_peringkat = $request->input('order_by_peringkat');
        $kuis_id = $request->input('kuis_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        // $kode_kuis = $request->input('kode_kuis');

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna_kuis')
        ->join('pengguna','pengguna.pengguna_id','=','pengguna_kuis.pengguna_id')
        ->join(DB::raw("(select kuis_id, sesi_kuis_id, sum(1) as total from pengguna_kuis where kuis_id = '".$kuis_id."' and sesi_kuis_id = '".$sesi_kuis_id."' group by kuis_id, sesi_kuis_id) as jumtot"), function($join)
        {
            $join->on('jumtot.kuis_id','=', 'pengguna_kuis.kuis_id');
            $join->on('jumtot.sesi_kuis_id','=', 'pengguna_kuis.sesi_kuis_id');
        })
        ->join(DB::raw("(SELECT ROW_NUMBER
            () OVER ( ORDER BY COALESCE(skor,0) DESC, COALESCE(pengguna_kuis.durasi,0) ASC ) AS peringkat ,
            *
        FROM
            pengguna_kuis 
        WHERE
            soft_delete = 0 
            AND kuis_id = '".$kuis_id."'
            AND sesi_kuis_id = '".$sesi_kuis_id."') as peringkat"), 'peringkat.pengguna_id','=', 'pengguna_kuis.pengguna_id')
        ->where('pengguna_kuis.kuis_id','=',DB::raw("'".$kuis_id."'"))
        ->where('pengguna_kuis.soft_delete','=',DB::raw('0'))
        ->where('pengguna_kuis.sesi_kuis_id','=', $sesi_kuis_id)
        ->select(
            'pengguna_kuis.*',
            'pengguna.nama as nama_pengguna',
            'peringkat.peringkat',
            'jumtot.total as total_peserta'
        );

        if($pengguna_id){
            $fetch_cek->where('pengguna_kuis.pengguna_id','=',DB::raw("'".$pengguna_id."'"));
        }

        if($order_by_peringkat == 'Y'){
            $fetch_cek->orderBy('peringkat.peringkat','ASC')
            ;
        }

        $fetch_cek = $fetch_cek->get();

        // return $fetch_cek;die;

        $return = array();
        $return['rows'] = $fetch_cek;
        $return['total'] = sizeof($fetch_cek);

        return $return;
    }
    
    public function getKuisRuang(Request $request){
        $ruang_id = $request->input('ruang_id');
        // $kode_kuis = $request->input('kode_kuis');

        $fetch_cek = DB::connection('sqlsrv_2')->table('kuis_ruang')
        ->join('kuis','kuis.kuis_id','=','kuis_ruang.kuis_id')
        ->join('pengguna','pengguna.pengguna_id','=','kuis.pengguna_id')
        ->where('kuis_ruang.ruang_id','=',DB::raw("'".$ruang_id."'"))
        ->where('kuis_ruang.soft_delete','=',DB::raw('0'))
        ->select(
            'kuis.*',
            'pengguna.nama as pengguna'
        )
        ;

        $fetch_cek = $fetch_cek->get();

        // return $fetch_cek;die;

        $return = array();
        $return['rows'] = $fetch_cek;
        $return['total'] = sizeof($fetch_cek);

        return $return;
    }

    public function simpanPertanyaanKuis(Request $request){
        $kuis_id = $request->input('kuis_id');
        $pertanyaan_kuis_id = $request->input('pertanyaan_kuis_id');
        $teks = $request->input('teks');
        $tipe_pertanyaan_id = $request->input('tipe_pertanyaan_id');
        $file_audio = $request->input('file_audio');
        $file_video = $request->input('file_video');
        $pengguna_id = $request->input('pengguna_id');
        $bagian_kuis_id = $request->input('bagian_kuis_id');
        $soft_delete = $request->input('soft_delete') ? $request->input('soft_delete') : '0';
        $pilihan_pertanyaan_kuis = $request->input('pilihan_pertanyaan_kuis');
        $kode_pertanyaan = $request->input('kode_pertanyaan');
        

        //simpan pertanyaan kuisnya
        $fetch_cek = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)->get();

        if(sizeof($fetch_cek) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
            ->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)
            ->update([
                'teks' => $teks,
                'pengguna_id' => $pengguna_id,
                'tipe_pertanyaan_id' => $tipe_pertanyaan_id,
                'file_audio' => $file_audio,
                'file_video' => $file_video,
                'bagian_kuis_id' => $bagian_kuis_id,
                'kode_pertanyaan' => $kode_pertanyaan,
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
            $label = 'UPDATE';
        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
            ->insert([
                'pertanyaan_kuis_id' => $pertanyaan_kuis_id,
                'teks' => $teks,
                'tipe_pertanyaan_id' => $tipe_pertanyaan_id,
                'pengguna_id' => $pengguna_id,
                'file_audio' => $file_audio,
                'file_video' => $file_video,
                'bagian_kuis_id' => $bagian_kuis_id,
                'kode_pertanyaan' => $kode_pertanyaan,
                'tanggal' => DB::raw('now()::timestamp(0)'),
                'kuis_id' => $kuis_id,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
            $label = 'INSERT';
        }

        if($exe){

            //simpan pilihannya
            $pilihan_sukses = 0;
            $pilihan_total = 0;

            if($pilihan_pertanyaan_kuis){

                foreach ($pilihan_pertanyaan_kuis as $key => $obj) {
                    // return $obj;die;
    
                    $fetch_cek_pilihan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')->where('pilihan_pertanyaan_kuis_id','=',$obj['pilihan_pertanyaan_kuis_id'])->get();
    
                    if(sizeof($fetch_cek_pilihan) > 0){
                        //sudah ada
                        $exe_pilihan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')
                        ->where('pilihan_pertanyaan_kuis_id','=',$obj['pilihan_pertanyaan_kuis_id'])
                        ->update([
                            'jawaban_benar' => $obj['jawaban_benar'],
                            'teks' => $obj['teks'],
                            'soft_delete' => $obj['soft_delete'],
                            'last_update' => DB::raw('now()::timestamp(0)')
                        ]);
                        $label = 'UPDATE';
                    }else{
                        //belum ada
                        if(array_key_exists('teks', $obj)){

                            $exe_pilihan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')
                            ->insert([
                                'pilihan_pertanyaan_kuis_id' => $obj['pilihan_pertanyaan_kuis_id'],
                                'jawaban_benar' => $obj['jawaban_benar'],
                                'teks' => $obj['teks'],
                                'soft_delete' => $obj['soft_delete'],
                                'pertanyaan_kuis_id' => $pertanyaan_kuis_id,
                                'pengguna_id' => $pengguna_id,
                                'create_date' => DB::raw('now()::timestamp(0)'),
                                'last_update' => DB::raw('now()::timestamp(0)')
                            ]);
                            $label = 'INSERT';
                            
                        }

                    }
    
                    if($exe_pilihan){
                        $pilihan_sukses++;
                    }
    
                    $pilihan_total++;
    
                }
                
            }


        }

        return response(
            [
                'success' => ($exe ? true: false),
                'pilihan_total' => $pilihan_total,
                'kuis_id' => $kuis_id,
                'pengguna_id' => $pengguna_id,
                'rows' => DB::connection('sqlsrv_2')->table('pertanyaan_kuis')->where('pertanyaan_kuis_id','=',$pertanyaan_kuis_id)->get(),
                'rows_kuis' => DB::connection('sqlsrv_2')->table('kuis')->where('kuis_id', '=', $kuis_id)->get(),
                // 'pengguna_id_asli' => $pengguna_id,
                'pilihan_sukses' => $pilihan_sukses,
                'pertanyaan_kuis_id' => $pertanyaan_kuis_id
            ],
            200
        );
        // return $pilihan_pertanyaan_kuis;
    }

    public function simpanPenggunaKuis(Request $request){
        $pengguna_id = $request->input('pengguna_id');
        $kuis_id = $request->input('kuis_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        $durasi = $request->input('durasi');
        $status_mengerjakan_id = $request->input('status_mengerjakan_id');
        $skor = $request->input('skor');
        $total = $request->input('total');
        $benar = $request->input('benar');
        $salah = $request->input('salah');
        $pertanyaan_kuis_id_terakhir = $request->input('pertanyaan_kuis_id_terakhir');

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna_kuis')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('kuis_id','=',$kuis_id)
        ->where('sesi_kuis_id','=',$sesi_kuis_id)
        ->where('soft_delete','=',DB::raw('0'))
        ->get();

        if(sizeof($fetch_cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')
            ->table('pengguna_kuis')
            ->where('pengguna_id','=',$pengguna_id)
            ->where('kuis_id','=',$kuis_id)
            ->where('sesi_kuis_id','=',$sesi_kuis_id)
            ->update([
                'status_mengerjakan_id' => ($status_mengerjakan_id ? $status_mengerjakan_id : 1),
                'pertanyaan_kuis_id_terakhir' => $pertanyaan_kuis_id_terakhir,
                'skor' => (int)$skor <= 100 ? $skor : 100,
                'total' => $total,
                'benar' => (int)$benar <= (int)$total ? $benar : $total,
                'salah' => (int)$salah <= $total ? $salah : $total,
                'durasi' => $durasi,
                'last_update' => date('Y-m-d H:i:s')
            ]);

            $label = 'UPDATE';
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')
            ->table('pengguna_kuis')
            ->insert([
                'pengguna_id' => $pengguna_id,
                'kuis_id' => $kuis_id,
                'sesi_kuis_id' => $sesi_kuis_id,
                'durasi' => $durasi,
                'status_mengerjakan_id' => ($status_mengerjakan_id ? $status_mengerjakan_id : 1),
                'pertanyaan_kuis_id_terakhir' => $pertanyaan_kuis_id_terakhir
            ]);
            $label = 'INSERT';

        }

        $return = array();
        
        if($status_mengerjakan_id === 2){
            
            try {
                // rekam poin pengguna kalau sudah selesai
                $exePoin        = PoinController::simpanPoin($pengguna_id, date('Y-m-d H:i:s'), 1, $kuis_id, $sesi_kuis_id, (int)$skor <= 100 ? $skor : 100);
                $exePoinJariyah = PoinController::simpanPoin($pengguna_id, date('Y-m-d H:i:s'), 3, $kuis_id, $sesi_kuis_id, (int)$skor <= 100 ? $skor : 100);

                $return['poin'] = $exePoin ? true : false;
                $return['poin_jariyah'] = $exePoinJariyah ? true : false;
            
            } catch (\Throwable $th) {
                //do nothing
            }

        }


        if($exe){
            $return['rows'] = DB::connection('sqlsrv_2')->table('pengguna_kuis')
                                ->where('pengguna_id','=',$pengguna_id)
                                ->where('kuis_id','=',$kuis_id)
                                ->where('sesi_kuis_id','=',$sesi_kuis_id)
                                ->get();
            $return['status'] = 'BERHASIL';
            $return['label'] = $label;

            if((int)$status_mengerjakan_id == 2){
                //simpan linimasa
                try {
                    //code...
                    $linimasa_id = RuangController::generateUUID();
                    $linimasa = LinimasaController::simpanLinimasa($linimasa_id, $pengguna_id,3,'','',null,$sesi_kuis_id);
        
                    if($linimasa){
                        $return['sukses_linimasa'] = true;
                    }else{
                        $return['sukses_linimasa'] = false;
                    }
                } catch (\Throwable $th) {
                    $return['sukses_linimasa'] = false;
                }

                try {
                    //code...
                    $aktivitas_id = RuangController::generateUUID();
                    $aktivitas = LinimasaController::simpanAktivitas($aktivitas_id, $pengguna_id, 1, 'ikut-kuis', $sesi_kuis_id);
        
                    if($aktivitas){
                        $return['sukses_aktivitas'] = true;
                    }else{
                        $return['sukses_aktivitas'] = false;
                    }
                } catch (\Throwable $th) {
                    $return['sukses_aktivitas'] = false;
                }
            }
        }else{
            $return['status'] = 'GAGAL';
            $return['label'] = $label;
        }

        return $return;
    }

    public function getKuis(Request $request){
        $kuis_id = $request->input('kuis_id');
        $sesi_kuis_id = $request->input('sesi_kuis_id');
        $kode_kuis = $request->input('kode_kuis');
        $pengguna_id = $request->input('pengguna_id');
        $tampil_jumlah_peserta = $request->input('tampil_jumlah_peserta');
        $keyword = $request->input('keyword');
        $sesi = $request->input('sesi') ? $request->input('sesi') : 'umum';
        $status_privasi = $request->input('status_privasi') ? $request->input('status_privasi') : null;
        $mata_pelajaran_id = $request->input('mata_pelajaran_id') ? $request->input('mata_pelajaran_id') : null;
        $jenjang_id = $request->input('jenjang_id') ? $request->input('jenjang_id') : null;
        $publikasi = $request->input('publikasi') ? $request->input('publikasi') : null;
        $tingkat_pendidikan_id = $request->input('tingkat_pendidikan_id') ? $request->input('tingkat_pendidikan_id') : null;
        $start = $request->input('start') ? $request->input('start') : 0;
        $limit = $request->input('limit') ? $request->input('limit') : 20;
        $tampilkan_pertanyaan = $request->input('tampilkan_pertanyaan') ? $request->input('tampilkan_pertanyaan') : 'Y';
        $tampilkan_stat = $request->input('tampilkan_stat') ? $request->input('tampilkan_stat') : 'N';

        //get sesi kuis

        $fetch = DB::connection('sqlsrv_2')->table('kuis')
        ->join('pengguna','pengguna.pengguna_id','=','kuis.pengguna_id')
        ->leftJoin('ref.jenjang as jenjang','jenjang.jenjang_id','=','kuis.jenjang_id')
        ->leftJoin('ref.tingkat_pendidikan as tingkat','tingkat.tingkat_pendidikan_id','=','kuis.tingkat_pendidikan_id')
        ->leftJoin('ref.mata_pelajaran as mapel','mapel.mata_pelajaran_id','=','kuis.mata_pelajaran_id')
        ->leftJoin(DB::raw("(select kuis_id, sum(1) as jumlah_pertanyaan from pertanyaan_kuis where soft_delete = 0 group by kuis_id) as jumlah_pertanyaan"),'jumlah_pertanyaan.kuis_id','=','kuis.kuis_id')
        ->leftJoin(DB::raw("(select * from sesi_kuis where \"soft_delete\" = 0 and \"default\" = 1) as sesi_kuis"),'sesi_kuis.kuis_id','=','kuis.kuis_id')
        ->where('kuis.soft_delete','=',0)
        ->select(
            'kuis.*',
            'jenjang.nama as jenjang',
            'tingkat.nama as tingkat_pendidikan',
            'mapel.nama as mata_pelajaran',
            'pengguna.nama as pengguna',
            'jumlah_pertanyaan.jumlah_pertanyaan',
            'sesi_kuis.kode_sesi'
        )
        ;

        if($tampilkan_stat){
            $fetch->leftJoin(DB::raw("(SELECT
                kuis_id,
                SUM ( 1 ) AS total 
            FROM
                pengguna_kuis 
            WHERE
                soft_delete = 0
            GROUP BY
                kuis_id) as total_pemain"), 'total_pemain.kuis_id','=','kuis.kuis_id')
            ->leftJoin(DB::raw("(SELECT
                kuis_id,
                SUM ( 1 ) AS total 
            FROM
                sesi_kuis 
            WHERE
                soft_delete = 0
            GROUP BY
                kuis_id) as total_sesi"), 'total_sesi.kuis_id','=','kuis.kuis_id')
            ->select(
                'kuis.*',
                'jenjang.nama as jenjang',
                'tingkat.nama as tingkat_pendidikan',
                'mapel.nama as mata_pelajaran',
                'pengguna.nama as pengguna',
                'jumlah_pertanyaan.jumlah_pertanyaan',
                'sesi_kuis.kode_sesi',
                'sesi_kuis.sesi_kuis_id',
                DB::raw('coalesce(total_pemain.total,0) as total_pemain'),
                DB::raw('coalesce(total_sesi.total,0) as total_sesi')
            );
        }

        if($kuis_id){
            $fetch->where('kuis.kuis_id','=',$kuis_id);
        }
        
        if($kode_kuis){
            $fetch->where('kuis.kode_kuis','=',$kode_kuis);
        }
        
        if($pengguna_id){
            $fetch->where('kuis.pengguna_id','=',$pengguna_id);
        }
        
        if($status_privasi){
            $fetch->where('kuis.status_privasi','=',$status_privasi);
        }
        
        if($mata_pelajaran_id){
            $fetch->where('kuis.mata_pelajaran_id','=',$mata_pelajaran_id);
        }
        
        if($jenjang_id && (int)$jenjang_id != 99){
            $fetch->where('kuis.jenjang_id','=',$jenjang_id);
        }
        
        if($publikasi && (int)$publikasi != 99){
            $fetch->where('kuis.publikasi','=',$publikasi);
        }
        
        if($tingkat_pendidikan_id && (int)$tingkat_pendidikan_id != 99){
            $fetch->where('kuis.tingkat_pendidikan_id','=',$tingkat_pendidikan_id);
        }
        
        if($keyword){
            // $fetch->where('kuis.judul','ilike',DB::raw("'%".$keyword."%'"));
            $fetch->where(function($query) use ($keyword){
                $query->where('kuis.judul','ilike',DB::raw("'%".$keyword."%'"))
                      ->orWhere('kuis.keterangan','ilike',DB::raw("'%".$keyword."%'"));
            });
            // $fetch->whereOr('kuis.judul','ilike',"'%".$keyword."%'");
        }

        if($tampil_jumlah_peserta ==  'Y'){
            $fetch->leftJoin(DB::raw("(select kuis_id, sum(1) as total from pengguna_kuis where soft_delete = 0 group by kuis_id) as jumlah_peserta"),'jumlah_peserta.kuis_id','=','kuis.kuis_id');
            $fetch->select(
                'kuis.*',
                'jenjang.nama as jenjang',
                'tingkat.nama as tingkat_pendidikan',
                'mapel.nama as mata_pelajaran',
                'pengguna.nama as pengguna',
                'jumlah_pertanyaan.jumlah_pertanyaan',
                'jumlah_peserta.total as jumlah_peserta',
                'sesi_kuis.kode_sesi',
                'sesi_kuis.sesi_kuis_id'
            );
        }

        
        $count =  $fetch->count();
        $fetch =  $fetch->orderBy('create_date','DESC')->skip($start)->take($limit)->get();


        for ($iData=0; $iData < sizeof($fetch); $iData++) { 
            
            if($tampilkan_pertanyaan == 'Y'){
                
                $fetch[$iData]->{'pertanyaan_kuis'} = (object)array();
                
                $fetch_pertanyaan = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
                ->leftJoin('bagian_kuis','bagian_kuis.bagian_kuis_id','=','pertanyaan_kuis.bagian_kuis_id')
                ->where('pertanyaan_kuis.kuis_id','=',$fetch[$iData]->{'kuis_id'})->where('pertanyaan_kuis.soft_delete','=',0)
                ->select(
                    'pertanyaan_kuis.*',
                    'bagian_kuis.kode_bagian as kode_bagian_kuis',
                    'bagian_kuis.nama as bagian_kuis'
                )
                ->orderBy('bagian_kuis.kode_bagian', 'ASC')
                ->orderBy('pertanyaan_kuis.kode_pertanyaan', 'ASC')
                ->orderBy('pertanyaan_kuis.create_date', 'ASC')
                ;
                
                if($sesi_kuis_id){
                    $fetch_sesi_kuis = DB::connection('sqlsrv_2')->table('sesi_kuis')
                    ->where('sesi_kuis_id','=',$sesi_kuis_id)
                    ->where('sesi_kuis.soft_delete','=',0)
                    ->get(); 
    
                    if(sizeof($fetch_sesi_kuis) > 0){
                        if((int)$fetch_sesi_kuis[0]->jawaban_diacak == 1){
                            $fetch_pertanyaan =  $fetch_pertanyaan->orderBy(DB::raw("random()"),'ASC')->get();
                        }else{
                            $fetch_pertanyaan =  $fetch_pertanyaan->orderBy('create_date','ASC')->get();
                        }
                    }else{
                        $fetch_pertanyaan =  $fetch_pertanyaan->orderBy('create_date','ASC')->get();
                    }
                }else{
                    $fetch_pertanyaan =  $fetch_pertanyaan->orderBy('create_date','ASC')->get();
                }
    
                
                for ($iPertanyaan=0; $iPertanyaan < sizeof($fetch_pertanyaan); $iPertanyaan++) { 
                    
                    $fetch[$iData]->{'pertanyaan_kuis'}->{$fetch_pertanyaan[$iPertanyaan]->{'pertanyaan_kuis_id'}} = $fetch_pertanyaan[$iPertanyaan];
                    
                    $fetch[$iData]->{'pertanyaan_kuis'}->{$fetch_pertanyaan[$iPertanyaan]->{'pertanyaan_kuis_id'}}->{'pilihan_pertanyaan_kuis'} = (object)array();
                
                    $fetch_pilihan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')
                    ->where('pertanyaan_kuis_id','=',$fetch_pertanyaan[$iPertanyaan]->{'pertanyaan_kuis_id'})
                    ->where('pilihan_pertanyaan_kuis.soft_delete','=',0)
                    ->orderBy('create_date','ASC')->get();
    
                    for ($iPilihan=0; $iPilihan < sizeof($fetch_pilihan); $iPilihan++) { 
                        $fetch[$iData]->{'pertanyaan_kuis'}->{$fetch_pertanyaan[$iPertanyaan]->{'pertanyaan_kuis_id'}}->{'pilihan_pertanyaan_kuis'}->{$fetch_pilihan[$iPilihan]->{'pilihan_pertanyaan_kuis_id'}} = $fetch_pilihan[$iPilihan];
                        // $fetch[$iData]->{'pertanyaan_kuis'}->{$fetch_pertanyaan[$iPertanyaan]->{'pertanyaan_kuis_id'}} = $fetch_pilihan[$iPilihan];
                    }
                }

            }else{

            }

        }

        $return = array();
        $return['total'] = $count;
        $return['rows'] = $fetch;

        return $return;
    }
    
    public function simpanKuis(Request $request){
        // return "oke";
        $data = $request->all();
        $gambar_kuis = $request->input('gambar_kuis') ? $request->input('gambar_kuis') : rand(1,8).".jpg";
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : null;
        $status_privasi = $request->input('status_privasi') ? $request->input('status_privasi') : 1;
        $jenis_kuis_id = $request->input('jenis_kuis_id') ? $request->input('jenis_kuis_id') : 1;
        $simpan_pertanyaan = $request->input('simpan_pertanyaan') ? $request->input('simpan_pertanyaan') : 'Y';
        // return $data;die;

        if($request->input('jenjang_id')){
            $jenjang_id = $request->input('jenjang_id');
        }else{
            $jenjang_id = null;
        }

        if($request->input('tingkat_pendidikan_id')){
            $tingkat_pendidikan_id = $request->input('tingkat_pendidikan_id');
        }else{
            $tingkat_pendidikan_id = null;
        }

        if($request->input('mata_pelajaran_id')){
            $mata_pelajaran_id = $request->input('mata_pelajaran_id');
        }else{
            $mata_pelajaran_id = null;
        }

        //simpan kuisnya dulu
        $query_cek_kuis = DB::connection('sqlsrv_2')
                            ->table('kuis')
                            ->where('kuis_id','=', $data['kuis_id'])
                            ->get();

        // return sizeof($query_cek_kuis);

        if(sizeof($query_cek_kuis) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('kuis')
            ->where('kuis_id','=', $data['kuis_id'])
            ->update([
                'judul' => $data['judul'],
                'keterangan' => $data['keterangan'] ? $data['keterangan'] : '',
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'publikasi' => $data['publikasi'],
                'jenjang_id'=> $jenjang_id,
                'tingkat_pendidikan_id'=> $tingkat_pendidikan_id,
                'mata_pelajaran_id'=> $mata_pelajaran_id,
                'gambar_kuis' => $gambar_kuis,
                'last_update' => date('Y-m-d H:i:s'),
                'status_privasi' => $status_privasi,
                'jenis_kuis_id' => $jenis_kuis_id,
                'a_boleh_assign' => $data['a_boleh_assign']
            ]);
            
            $label = 'UPDATE';

        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('kuis')
            ->insert([
                'kuis_id' => $data['kuis_id'],
                'pengguna_id' => $data['pengguna_id'],
                'judul' => $data['judul'],
                'keterangan' => $data['keterangan'] ? $data['keterangan'] : '',
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'jenjang_id'=> $jenjang_id,
                'tingkat_pendidikan_id'=> $tingkat_pendidikan_id,
                'mata_pelajaran_id'=> $mata_pelajaran_id,
                'publikasi' => $data['publikasi'],
                'kode_kuis' => RuangController::generateRandomString(10),
                'gambar_kuis' => $gambar_kuis,
                'create_date' => date('Y-m-d H:i:s'),
                'last_update' => date('Y-m-d H:i:s'),
                'status_privasi' => $status_privasi,
                'jenis_kuis_id' => $jenis_kuis_id,
                'a_boleh_assign' => $data['a_boleh_assign']
            ]);

            $label = 'INSERT';
        }

        if($exe){
            //insert/update berhasil

            //simpan pertanyaan kuisnya
            // return "berhasil simpan kuis";
            if($simpan_pertanyaan == 'Y'){

                foreach ($data['pertanyaan_kuis'] as $key => $value) {
                    $query_cek_pertanyaan_kuis = DB::connection('sqlsrv_2')
                                                    ->table('pertanyaan_kuis')
                                                    ->where('pertanyaan_kuis_id','=', $value['pertanyaan_kuis_id'])
                                                    ->get();
                    
                    if(sizeof($query_cek_pertanyaan_kuis) > 0){
                        //update
                        $exePertanyaanKuis = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
                        ->where('pertanyaan_kuis_id','=',$value['pertanyaan_kuis_id'])
                        ->update([
                            'teks' => $value['teks'],
                            'pengguna_id' => $data['pengguna_id'],
                            'tanggal' => date('Y-m-d H:i:s'),
                            'kuis_id' => $data['kuis_id'],
                            'last_update' => date('Y-m-d H:i:s')
                        ]);
    
                    }else{
                        //insert
                        $exePertanyaanKuis = DB::connection('sqlsrv_2')->table('pertanyaan_kuis')
                        ->insert([
                            'pertanyaan_kuis_id' => $value['pertanyaan_kuis_id'],
                            'teks' => $value['teks'],
                            'pengguna_id' => $data['pengguna_id'],
                            'tanggal' => date('Y-m-d H:i:s'),
                            'kuis_id' => $data['kuis_id'],
                            'create_date' => date('Y-m-d H:i:s'),
                            'last_update' => date('Y-m-d H:i:s')
                        ]);
    
                    }
    
                    if($exePertanyaanKuis){
                        
                        foreach ($value['pilihan_pertanyaan_kuis'] as $keyPilihan => $valuePilihan) {
                            $query_cek_pilihan = DB::connection('sqlsrv_2')
                                                ->table('pilihan_pertanyaan_kuis')
                                                ->where('pilihan_pertanyaan_kuis_id','=', $valuePilihan['pilihan_pertanyaan_kuis_id'])
                                                ->get();
                            
                            if(sizeof($query_cek_pilihan) > 0){
                                //update
                                $exePilihan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')
                                ->where('pilihan_pertanyaan_kuis_id','=', $valuePilihan['pilihan_pertanyaan_kuis_id'])
                                ->update([
                                    'teks' => $valuePilihan['teks'],
                                    'pengguna_id' => $data['pengguna_id'],
                                    'pertanyaan_kuis_id' => $value['pertanyaan_kuis_id'],
                                    'jawaban_benar' => $valuePilihan['jawaban_benar'],
                                    'last_update' =>date('Y-m-d H:i:s')
                                ]);
    
                            }else{
                                //insert
                                $exePilihan = DB::connection('sqlsrv_2')->table('pilihan_pertanyaan_kuis')
                                ->insert([
                                    'pilihan_pertanyaan_kuis_id' => $valuePilihan['pilihan_pertanyaan_kuis_id'],
                                    'teks' => $valuePilihan['teks'],
                                    'pengguna_id' => $data['pengguna_id'],
                                    'pertanyaan_kuis_id' => $value['pertanyaan_kuis_id'],
                                    'jawaban_benar' => $valuePilihan['jawaban_benar'],
                                    'create_date' => date('Y-m-d H:i:s'),
                                    'last_update' => date('Y-m-d H:i:s')
                                ]);
    
                            }
                        }
    
                    }else{
                        //gagal tambah pertanyaan kuis
                    }
    
                }
                
            }


            //tambah sesi kuis umum
            if($label == 'INSERT'){

                $exeSesiKuis = DB::connection('sqlsrv_2')->table('sesi_kuis')->insert([
                    'sesi_kuis_id' => self::generateUUID(),
                    'pengguna_id' =>  $data['pengguna_id'],
                    'kuis_id' => $data['kuis_id'],  
                    'ruang_id' => null,  
                    'tanggal' => DB::raw('now()::timestamp(0)'),
                    'jawaban_diacak' => 0,  
                    'tampilkan_jawaban_benar' => 0,  
                    'waktu_mulai' => null,  
                    'waktu_selesai' => null,  
                    'keterangan' => 'Umum',  
                    'jumlah_percobaan' => 100,  
                    'create_date' => DB::raw('now()::timestamp(0)'),
                    'last_update' => DB::raw('now()::timestamp(0)'),
                    'soft_delete' => 0,
                    'default' => 1,
                    'kode_sesi' => strtoupper(RuangController::generateRandomString(10))
                ]);

                //tambah poin kalau buat kuis
                try {
                    // rekam poin pengguna kalau sudah selesai
                    $exePoin = PoinController::simpanPoin($data['pengguna_id'], date('Y-m-d H:i:s'), 2, $data['kuis_id'], null, 150);
                    
                    // $return['poin'] = $exePoin ? true : false;
                    
                } catch (\Throwable $th) {
                    //do nothing
                    $exePoin = false;
                }

            }else{
                $exePoin = false;
            }

            //tambah ke kuis_ruang
            // if($ruang_id){

            //     $query_cek_kuis_ruang = DB::connection('sqlsrv_2')
            //                     ->table('kuis_ruang')
            //                     ->where('kuis_id','=', $data['kuis_id'])
            //                     ->where('ruang_id','=', $ruang_id)
            //                     ->get();
                
            //     if(sizeof($query_cek_kuis_ruang) > 0){
            //         //update
            //     }else{
            //         //insert
            //         $exe_kuis_ruang = DB::connection('sqlsrv_2')
            //                         ->table('kuis_ruang')
            //                         ->insert([
            //                             'kuis_id' => $data['kuis_id'],
            //                             'ruang_id' => $ruang_id,
            //                             'create_date' => date('Y-m-d H:i:s')
            //                         ]);
            //     }
            // }

            return response(
                [
                    'success' => true,
                    // 'success' => ($exe ? ($exeSesiKuis ? true : false) : false),
                    'kuis_id' => $data['kuis_id'],
                    'poin' => $exePoin ? true : false
                ],
                200
            );

        }else{

            return response(
                [
                    'success' => false,
                    'kuis_id' => null
                ],
                200
            );

        }

        // return $data['pertanyaan_kuis']['c05c2341-389f-4a3f-b7ec-7a59cb0713f4'];
    }

    static function getSesiKuisPengguna(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        // $sql = "SELECT
        //     kuis.*,
        //     sesi_kuis.*,
        //     kuis.keterangan AS keterangan_kuis 
        // FROM
        //     sesi_kuis
        //     JOIN kuis ON kuis.kuis_id = sesi_kuis.kuis_id 
        // WHERE
        //     sesi_kuis.pengguna_id = '3607341d-a0a4-4754-a7b4-09b057247fad' 
        //     AND kuis.pengguna_id != '3607341d-a0a4-4754-a7b4-09b057247fad' 
        //     AND sesi_kuis.soft_delete = 0 
        //     AND kuis.soft_delete = 0";
        
        // $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        $fetch = DB::connection('sqlsrv_2')
        ->table('sesi_kuis')
        ->join('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
        ->join('pengguna','pengguna.pengguna_id','=','kuis.pengguna_id')
        ->leftJoin('pengguna as sesi_pengguna','sesi_pengguna.pengguna_id','=','sesi_kuis.pengguna_id')
        ->select(
            'kuis.*',
            'sesi_kuis.*',
            'kuis.keterangan as keterangan_kuis',
            'pengguna.nama as pembuat_kuis',
            'sesi_pengguna.nama as pengguna'
        )
        ->where('sesi_kuis.pengguna_id','=', $pengguna_id)
        ->where('kuis.pengguna_id','!=', $pengguna_id)
        ->where('sesi_kuis.soft_delete','=', 0)
        ->where('kuis.soft_delete','=', 0)
        ;

        // return $fetch->toSql();die;
        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->orderBy('sesi_kuis.create_date', 'DESC')->get()
            ],
            200
        );
    }

    static function getKolaborasiKuis(Request $request){
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $fetch = DB::connection('sqlsrv_2')->table('kolaborasi_kuis')
        ->join('pengguna', 'pengguna.pengguna_id','=','kolaborasi_kuis.pengguna_id')
        ->join('kuis', 'kuis.kuis_id','=','kolaborasi_kuis.kuis_id')
        ->join('pengguna as kuisnya_pengguna', 'kuisnya_pengguna.pengguna_id','=','kuis.pengguna_id')
        ->select(
            'pengguna.*',
            'kolaborasi_kuis.*',
            'kuis.*',
            'kuisnya_pengguna.nama as pembuat_kuis',
            'kolaborasi_kuis.create_date as tanggal_kolab',
            'kuis.pengguna_id as pengguna_id_kuis'
        )
        ->where('kolaborasi_kuis.soft_delete','=',0)
        ;

        if($kuis_id){
            $fetch->where('kolaborasi_kuis.kuis_id','=',$kuis_id);
        }

        if($pengguna_id){
            $fetch->where('kolaborasi_kuis.pengguna_id','=',$pengguna_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->orderBy('kolaborasi_kuis.create_date', 'DESC')->get()
            ],
            200
        );
    }

    static function simpanKolaborasiKuis(Request $request){
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $fetch_cek = DB::connection('sqlsrv_2')->table('kolaborasi_kuis')
        ->where('kolaborasi_kuis.kuis_id','=',$kuis_id)
        ->where('kolaborasi_kuis.pengguna_id','=',$pengguna_id)
        ->where('kolaborasi_kuis.soft_delete','=',0)
        ->get();
        ;

        if(sizeof($fetch_cek) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('kolaborasi_kuis')
            ->where('kolaborasi_kuis.kuis_id','=',$kuis_id)
            ->where('kolaborasi_kuis.pengguna_id','=',$pengguna_id)
            ->where('kolaborasi_kuis.soft_delete','=',0)
            ->update([
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('kolaborasi_kuis')
            ->insert([
                'kolaborasi_kuis_id' => self::generateUUID(),
                'pengguna_id' => $pengguna_id,
                'kuis_id' => $kuis_id,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => 0
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('kolaborasi_kuis')
                ->where('kolaborasi_kuis.kuis_id','=',$kuis_id)
                ->where('kolaborasi_kuis.pengguna_id','=',$pengguna_id)
                ->where('kolaborasi_kuis.soft_delete','=',0)
                ->get()
            ],
            200
        );
    }

    static function getAspek(Request $request){
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $bagian_kuis_id = $request->bagian_kuis_id ? $request->bagian_kuis_id : null;

        $fetch = DB::connection('sqlsrv_2')->table('bagian_kuis')
        // ->where('bagian_kuis.kuis_id','=',$kuis_id)
        ->where('bagian_kuis.soft_delete','=',0)        
        ->orderBy('bagian_kuis.kode_bagian', 'ASC')
        ->orderBy('bagian_kuis.create_date', 'ASC')
        ;

        if($kuis_id){
            $fetch->where('bagian_kuis.kuis_id','=',$kuis_id);
        }

        if($bagian_kuis_id){
            $fetch->where('bagian_kuis.bagian_kuis_id','=',$bagian_kuis_id);
        }else{
            $fetch->where('bagian_kuis.level_bagian_kuis_id','=',1);
        }

        $rows = $fetch->get();

        for ($i=0; $i < sizeof($rows); $i++) { 
            if($rows[$i]->level_bagian_kuis_id == 1){
                $fetch_sub = DB::connection('sqlsrv_2')->table('bagian_kuis')
                ->where('bagian_kuis.induk_bagian_kuis_id','=',$rows[$i]->bagian_kuis_id)
                ->where('bagian_kuis.soft_delete','=',0)
                ->orderBy('bagian_kuis.create_date', 'ASC')
                ;

                $rows[$i]->sub_aspek = array('rows' => $fetch_sub->get(), 'total' => $fetch_sub->count());
            }
        }

        return response(
            [
                'rows' => $rows,
                'total' => $fetch->count()            
            ],
            200
        );
    }

    static function simpanAspek(Request $request){
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;
        $bagian_kuis_id = $request->bagian_kuis_id ? $request->bagian_kuis_id : RuangController::generateUUID();
        $kode_bagian = $request->kode_bagian ? $request->kode_bagian : null;
        $induk_bagian_kuis_id = $request->induk_bagian_kuis_id ? $request->induk_bagian_kuis_id : null;

        $fetch_cek = DB::connection('sqlsrv_2')->table('bagian_kuis')
        ->where('bagian_kuis.bagian_kuis_id','=',$bagian_kuis_id)
        ->where('bagian_kuis.soft_delete','=',0)
        ->get();
        ;

        if(sizeof($fetch_cek) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('bagian_kuis')
            ->where('bagian_kuis.bagian_kuis_id','=',$bagian_kuis_id)
            ->where('bagian_kuis.soft_delete','=',0)
            ->update([
                'nama' => $request->nama,
                'kuis_id' => $request->kuis_id,
                'kode_bagian' => $request->kode_bagian,
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete
            ]);
        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('bagian_kuis')
            ->insert([
                'bagian_kuis_id' => $bagian_kuis_id,
                'kuis_id' => $kuis_id,
                'induk_bagian_kuis_id' => $induk_bagian_kuis_id,
                'level_bagian_kuis_id' => ($induk_bagian_kuis_id ? 2 : 1),
                'nama' => $request->nama,
                'kode_bagian' => $request->kode_bagian,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => 0
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('bagian_kuis')
                ->where('bagian_kuis.bagian_kuis_id','=',$bagian_kuis_id)
                ->where('bagian_kuis.soft_delete','=',0)
                ->get()
            ],
            200
        );
    }

    static function getAspekReversed(Request $request){
        $kuis_id = $request->kuis_id ? $request->kuis_id : null;

        $fetch = DB::connection('sqlsrv_2')->table('bagian_kuis')
        // ->join('bagian_kuis as induk','induk.bagian_kuis_id','=','bagian_kuis.induk_bagian_kuis_id')
        ->where('bagian_kuis.kuis_id','=',$kuis_id)
        ->where('bagian_kuis.soft_delete','=',0)
        ->where('bagian_kuis.level_bagian_kuis_id','=',1)
        ->orderBy('bagian_kuis.induk_bagian_kuis_id', 'ASC')
        ->orderBy('bagian_kuis.create_date', 'ASC')
        ->select(
            'bagian_kuis.*'
            // 'induk.nama as induk'
        )
        ;

        return response(
            [
                'rows' => $fetch->get(),
                'total' => $fetch->count()            
            ],
            200
        );
    }
}