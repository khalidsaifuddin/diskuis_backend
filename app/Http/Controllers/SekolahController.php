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
        $m = $km * 1000;
        //echo ' '.$km; 
        // return $km; 
        return response(
            [
                'km' => $km,
                'm' => $m,
            ],
            200
        );
    }

    static function getGuruSiswaPengguna(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : 2020;

        $sql = "SELECT
            * 
        FROM
            (
            SELECT ROW_NUMBER
                () OVER ( PARTITION BY siswa_guru.sekolah_id ORDER BY siswa_guru.jabatan_sekolah_id ASC ) AS urutan,
                * 
            FROM
                (
                SELECT
                    pengguna.pengguna_id,
                    sekolah.sekolah_id,
                    pengguna.nama,
                    sekolah.nama AS nama_sekolah,
                    sekolah_pengguna.jabatan_sekolah_id,
                    jabatan_sekolah.nama AS jabatan_sekolah 
                FROM
                    pengguna
                    JOIN sekolah_pengguna ON sekolah_pengguna.pengguna_id = pengguna.pengguna_id
                    JOIN sekolah ON sekolah.sekolah_id = sekolah_pengguna.sekolah_id
                    JOIN REF.jabatan_sekolah AS jabatan_sekolah ON jabatan_sekolah.jabatan_sekolah_id = sekolah_pengguna.jabatan_sekolah_id 
                WHERE
                    pengguna.soft_delete = 0 
                    AND sekolah_pengguna.soft_delete = 0 
                    AND sekolah.soft_delete = 0 
                    AND pengguna.pengguna_id = '".$pengguna_id."' 
                    AND sekolah_pengguna.VALID = 1 UNION
                SELECT
                    pengguna_ruang.pengguna_id,
                    sekolah.sekolah_id,
                    pengguna.nama,
                    sekolah.nama AS nama_sekolah,
                    2 AS jabatan_sekolah_id,
                    'Siswa' AS jabatan_sekolah 
                FROM
                    pengguna_ruang
                    JOIN ruang ON ruang.ruang_id = pengguna_ruang.ruang_id
                    JOIN ruang_sekolah ON ruang_sekolah.ruang_id = ruang.ruang_id
                    JOIN sekolah ON sekolah.sekolah_id = ruang_sekolah.sekolah_id
                    JOIN pengguna ON pengguna.pengguna_id = pengguna_ruang.pengguna_id 
                WHERE
                    pengguna_ruang.pengguna_id = '".$pengguna_id."' 
                    AND pengguna_ruang.soft_delete = 0 
                    AND ruang_sekolah.soft_delete = 0 
                    AND ruang.soft_delete = 0 
                    AND pengguna_ruang.jabatan_ruang_id = 3 
                    AND ruang_sekolah.tahun_ajaran_id = ".$tahun_ajaran_id."
                ) siswa_guru 
            ) aaa 
        WHERE
            urutan = 1";
    }

    static function getRuangSekolah(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : null;
        $ruang_sekolah_id = $request->ruang_sekolah_id ? $request->ruang_sekolah_id : null;
        $keyword = $request->keyword ? $request->keyword : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $cek = DB::connection('sqlsrv_2')->table('ruang_sekolah')
        ->join('ruang','ruang.ruang_id','=','ruang_sekolah.ruang_id')
        ->join('sekolah','sekolah.sekolah_id','=','ruang_sekolah.sekolah_id')
        ->join('pengguna','pengguna.pengguna_id','=','ruang.pengguna_id')
        ->where('ruang_sekolah.soft_delete', '=', 0)
        ->skip($start)
        ->take($limit)
        ->select(
            'ruang_sekolah.*',
            'ruang.*',
            'pengguna.nama as pembuat'
        );

        if($ruang_id){
            $cek->where('ruang_sekolah.ruang_id', '=', $ruang_id);
        }

        if($sekolah_id){
            $cek->where('ruang_sekolah.sekolah_id', '=', $sekolah_id);
        }

        if($tahun_ajaran_id){
            $cek->where('ruang_sekolah.tahun_ajaran_id', '=', $tahun_ajaran_id);
        }

        if($ruang_sekolah_id){
            $cek->where('ruang_sekolah.ruang_sekolah_id', '=', $ruang_sekolah_id);
        }
        
        if($keyword){
            $cek->where('ruang.nama', 'ilike', DB::raw("'%".$keyword."%'"));
        }

        $fetch = $cek->get();

        for ($i=0; $i < sizeof($fetch); $i++) { 
            $pengikut = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->where('soft_delete','=',0)
            ->where('ruang_id','=',$fetch[$i]->ruang_id)
            ->where('jabatan_ruang_id','=',3)
            ->count();

            $fetch[$i]->pengikut = $pengikut;
            
            $kuis = DB::connection('sqlsrv_2')->table('kuis_ruang')
            ->where('soft_delete','=',0)
            ->where('ruang_id','=',$fetch[$i]->ruang_id)
            ->count();

            $fetch[$i]->kuis = $kuis;
        }

        return response(
            [
                'total' => $cek->count(),
                'rows' => $fetch
            ],
            200
        );
    }

    static function simpanRuangSekolah(Request $request){
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : null;
        $soft_delete = $request->soft_delete ? $request->soft_delete : '0';
        $ruang_sekolah_id = $request->ruang_sekolah_id ? $request->ruang_sekolah_id : RuangController::generateUUID();

        $cek = DB::connection('sqlsrv_2')->table('ruang_sekolah')
        ->where('ruang_id', '=', $ruang_id)
        ->where('sekolah_id', '=', $sekolah_id)
        ->where('tahun_ajaran_id', '=', $tahun_ajaran_id)
        ->get();

        if(sizeof($cek) > 0){
            //update 
            $exe = DB::connection('sqlsrv_2')->table('ruang_sekolah')
            ->where('ruang_id', '=', $ruang_id)
            ->where('sekolah_id', '=', $sekolah_id)
            ->where('tahun_ajaran_id', '=', $tahun_ajaran_id)
            ->update([
                'soft_delete' => $soft_delete,
                'last_update' => DB::raw("now()")
            ]);

        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('ruang_sekolah')
            ->insert([
                'ruang_sekolah_id' => $ruang_sekolah_id,
                'ruang_id' => $ruang_id,
                'sekolah_id' => $sekolah_id,
                'tahun_ajaran_id' => $tahun_ajaran_id,
                'soft_delete' => $soft_delete,
                'create_date' => DB::raw("now()"),
                'last_update' => DB::raw("now()")
            ]);

        }

        if($exe){
            //simpan pengguna sekolah siswanya
            $fetch_siswa = DB::connection('sqlsrv_2')->select("
            SELECT
                uuid_generate_v4 () AS sekolah_pengguna_id,
                '".$sekolah_id."' as sekolah_id,
                pengguna_ruang.pengguna_id,
                2 as jabatan_sekolah_id,
                null as mata_pelajaran_id,
                now() as create_date,
                now() as last_update,
                0 as soft_delete,
                0 as pendiri,
                1 as valid,
                0 as sekolah_utama,
                0 as administrator,
                1 as aktif
            FROM
                pengguna_ruang 
            WHERE
                pengguna_ruang.soft_delete = 0 
                AND jabatan_ruang_id = 3 
                AND ruang_id = '".$ruang_id."'"); 

            for ($i=0; $i < sizeof($fetch_siswa); $i++) { 

                $siswa_masuk = 0;
                $siswa_gagal = 0;
                $siswa_insert = 0;
                $siswa_update = 0;

                $cek = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                ->where('sekolah_id','=',$fetch_siswa[$i]->sekolah_id)
                ->where('pengguna_id','=',$fetch_siswa[$i]->pengguna_id)
                // ->where('jabatan_sekolah_id','=',$fetch_siswa[$i]->jabatan_sekolah_id)
                ->get();

                if(sizeof($cek) > 0){
                    //update
                    $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                    ->where('sekolah_id','=',$fetch_siswa[$i]->sekolah_id)
                    ->where('pengguna_id','=',$fetch_siswa[$i]->pengguna_id)
                    // ->where('jabatan_sekolah_id','=',$fetch_siswa[$i]->jabatan_sekolah_id)
                    ->update([
                        'soft_delete' => 0,
                        'last_update' => DB::raw("now()")
                    ]);

                    $siswa_update++;

                }else{
                    //insert
                    $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                    ->insert([
                        'sekolah_pengguna_id' => $fetch_siswa[$i]->sekolah_pengguna_id,
                        'sekolah_id' => $fetch_siswa[$i]->sekolah_id,
                        'pengguna_id' => $fetch_siswa[$i]->pengguna_id,
                        'jabatan_sekolah_id' => $fetch_siswa[$i]->jabatan_sekolah_id,
                        'mata_pelajaran_id' => $fetch_siswa[$i]->mata_pelajaran_id,
                        'create_date' => $fetch_siswa[$i]->create_date,
                        'last_update' => $fetch_siswa[$i]->last_update,
                        'soft_delete' => $fetch_siswa[$i]->soft_delete,
                        'pendiri' => $fetch_siswa[$i]->pendiri,
                        'valid' => $fetch_siswa[$i]->valid,
                        'sekolah_utama' => $fetch_siswa[$i]->sekolah_utama,
                        'administrator' => $fetch_siswa[$i]->administrator,
                        'aktif' => $fetch_siswa[$i]->aktif
                    ]);

                    $siswa_insert++;
                }

                if($exe){
                    $siswa_masuk++;
                }else{
                    $siswa_gagal++;
                }
            }
        }

        return response(
            [
                'sukses' => $exe ? true : false,
                'siswa' => array(
                    'siswa_masuk' => $siswa_masuk,
                    'siswa_gagal' => $siswa_gagal,
                    'siswa_insert' => $siswa_insert,
                    'siswa_update' => $siswa_update
                ),
                'rows' => DB::connection('sqlsrv_2')->table('ruang_sekolah')
                    ->where('ruang_id', '=', $ruang_id)
                    ->where('sekolah_id', '=', $sekolah_id)
                    ->where('tahun_ajaran_id', '=', $tahun_ajaran_id)
                    ->get()
            ],
            200
        );
    }

    static function getTahunAjaran(Request $request){
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : null;
        
        $cek = DB::connection('sqlsrv_2')->table('ref.tahun_ajaran')
        ->whereNull('expired_date')
        ->orderBy('aktif', 'DESC')
        ;

        if($tahun_ajaran_id){
            $cek->where('tahun_ajaran_id','=',$tahun_ajaran_id);
        }

        return response(
            [
                'total' => $cek->count(),
                'rows' => $cek->get()
            ],
            200
        );
    }
    
    static function getDokumenGuru(Request $request){
        $pengguna_id = $request->pengguna_id;
        $sekolah_id = $request->sekolah_id;

        $cek = DB::connection('sqlsrv_2')->table('dokumen_guru')
        ->join('ref.jenis_berkas as jenis','jenis.jenis_berkas_id','=','dokumen_guru.jenis_berkas_id')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('sekolah_id','=',$sekolah_id)
        ->where('soft_delete','=',0)
        ->select(
            'dokumen_guru.*',
            'jenis.nama as jenis_berkas'
        )
        ->get();

        return response(
            [
                'total' => sizeof($cek),
                'rows' => $cek
            ],
            200
        );
    }

    static function uploadDokumenGuru(Request $request){
        // return "oke";
        $dokumen_guru_id = $request->dokumen_guru_id ? $request->dokumen_guru_id : RuangController::generateUUID();
        $jenis_berkas_id = $request->jenis_berkas_id ? $request->jenis_berkas_id : 99;
        $nama_file = $request->file_nama_file;
        $caption = $request->nama_file;
        $pengguna_id = $request->pengguna_id;
        $sekolah_id = $request->sekolah_id;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $cek = DB::connection('sqlsrv_2')->table('dokumen_guru')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('sekolah_id','=',$sekolah_id)
        ->where('dokumen_guru_id','=',$dokumen_guru_id)
        ->get();

        if(sizeof($cek) > 0){
            //update
            $exe = DB::connection('sqlsrv_2')->table('dokumen_guru')
            ->where('pengguna_id','=',$pengguna_id)
            ->where('sekolah_id','=',$sekolah_id)
            ->where('dokumen_guru_id','=',$dokumen_guru_id)
            ->update([
                'nama_file' => $nama_file,
                'caption' => $caption,
                'jenis_berkas_id' => $jenis_berkas_id,
                'soft_delete' => $soft_delete,
                'last_update' => date('Y-m-d H:i:s')
            ]);
        }else{
            //insert
            $exe = DB::connection('sqlsrv_2')->table('dokumen_guru')
            ->insert([
                'dokumen_guru_id' => $dokumen_guru_id,
                'jenis_berkas_id' => $jenis_berkas_id,
                'pengguna_id' => $pengguna_id,
                'sekolah_id' => $sekolah_id,
                'nama_file' => $nama_file,
                'caption' => $caption,
                'last_update' => date('Y-m-d H:i:s'),
                'create_date' => date('Y-m-d H:i:s'),
                'soft_delete' => $soft_delete
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('dokumen_guru')
                ->where('pengguna_id','=',$pengguna_id)
                ->where('sekolah_id','=',$sekolah_id)
                ->get()
            ],
            200
        );
    }

    static function unduhLaporanKehadiranGuru(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $bulan = $request->bulan ? $request->bulan : null;
        $tahun = $request->tahun ? $request->tahun : 2020;

        // return cal_days_in_month(CAL_GREGORIAN, 10, 2020);die;

        $str_kolom = "";
        $str_kolom_total = "";

        for ($i=0; $i < cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun); $i++) { 
            $str_kolom .= "
            SUM ( CASE WHEN kehadiran_guru.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN 1 ELSE 0 END ) AS tanggal_".$tahun."_".$bulan."_".($i+1).",
            MAX ( CASE WHEN kehadiran_guru.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN kehadiran_guru.waktu_datang ELSE null END ) AS tanggal_".$tahun."_".$bulan."_".($i+1)."_datang,
            MAX ( CASE WHEN kehadiran_guru.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN kehadiran_guru.waktu_pulang ELSE null END ) AS tanggal_".$tahun."_".$bulan."_".($i+1)."_pulang,"
            ;
            $str_kolom_total .= "COALESCE( SUM ( CASE WHEN kehadiran_guru.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN 1 ELSE 0 END ),0 ) +";
        }
        
        $str_kolom_total = substr($str_kolom_total,0,(strlen($str_kolom_total)-2));

        // return $str_kolom;die;

        $sql = "SELECT
                    * 
                FROM
                    (
                    SELECT
                        pengguna.nama,
                        pengguna.att_1,
                        guru.att_2,
                        jenis.nama AS jenis_guru,
                        mapel.nama AS att_3, 
                        pengguna.pengguna_id
                    FROM
                        sekolah_pengguna
                        JOIN pengguna ON pengguna.pengguna_id = sekolah_pengguna.pengguna_id
                        LEFT JOIN guru ON guru.pengguna_id = pengguna.pengguna_id 
                        AND guru.sekolah_id = '".$sekolah_id."'
                        LEFT JOIN REF.jenis_guru jenis ON jenis.jenis_guru_id = guru.jenis_guru_id
                        LEFT JOIN REF.mata_pelajaran mapel ON mapel.mata_pelajaran_id = guru.mata_pelajaran_id 
                    WHERE
                        sekolah_pengguna.sekolah_id = '".$sekolah_id."' 
                        AND sekolah_pengguna.soft_delete = 0 
                        AND pengguna.soft_delete = 0 
                        AND sekolah_pengguna.jabatan_sekolah_id = 1
                    ORDER BY
                    pengguna.nama ASC 
                    ) gurus
                    LEFT JOIN (
                    SELECT
                        ".$str_kolom."
                        (
                            ".$str_kolom_total."
                        ) as total,
                        pengguna_id
                    FROM
                        kehadiran_guru 
                    WHERE
                        sekolah_id = '".$sekolah_id."' 
                    GROUP BY
                        pengguna_id
                    ) hadirs on hadirs.pengguna_id = gurus.pengguna_id
                    ORDER BY gurus.nama ASC";

                    // return $sql;die;
        
        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        return view('excel/rekapKehadiran', [
            'return' => $fetch, 
            'nama_ruang' => $bulan."-".$tahun, 
            'bulan'=>$bulan, 
            'tahun'=>$tahun,
            'tipe' => 'guru'
        ]);
        // return $fetch;
    }

    static function unduhLaporanKehadiranSiswa(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $bulan = $request->bulan ? $request->bulan : null;
        $tahun = $request->tahun ? $request->tahun : 2020;

        // return cal_days_in_month(CAL_GREGORIAN, 10, 2020);die;

        $str_kolom = "";
        $str_kolom_total = "";

        for ($i=0; $i < cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun); $i++) { 
            $str_kolom .= "
            SUM ( CASE WHEN kehadiran_siswa.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN 1 ELSE 0 END ) AS tanggal_".$tahun."_".$bulan."_".($i+1).",
            MAX ( CASE WHEN kehadiran_siswa.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN kehadiran_siswa.waktu_datang ELSE null END ) AS tanggal_".$tahun."_".$bulan."_".($i+1)."_datang,
            MAX ( CASE WHEN kehadiran_siswa.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN kehadiran_siswa.waktu_pulang ELSE null END ) AS tanggal_".$tahun."_".$bulan."_".($i+1)."_pulang,"
            ;
            $str_kolom_total .= "COALESCE( SUM ( CASE WHEN kehadiran_siswa.tanggal = '".$tahun."-".$bulan."-".($i < 9 ? "0" : "").($i+1)."' THEN 1 ELSE 0 END ),0 ) +";
        }
        
        $str_kolom_total = substr($str_kolom_total,0,(strlen($str_kolom_total)-2));

        // return $str_kolom;die;

        $sql = "SELECT
                    * 
                FROM
                    (
                    SELECT
                        pengguna.nama,
                        pengguna.nik as att_1,
                        siswa.nisn as att_2,
                        null as att_3,
                        -- jenis.nama AS jenis_guru,
                        -- mapel.nama AS mapel, 
                        pengguna.pengguna_id
                    FROM
                        sekolah_pengguna
                        JOIN pengguna ON pengguna.pengguna_id = sekolah_pengguna.pengguna_id
                        LEFT JOIN siswa ON siswa.pengguna_id = pengguna.pengguna_id 
                        AND siswa.sekolah_id = '".$sekolah_id."'
                        -- LEFT JOIN REF.jenis_guru jenis ON jenis.jenis_guru_id = guru.jenis_guru_id
                        -- LEFT JOIN REF.mata_pelajaran mapel ON mapel.mata_pelajaran_id = guru.mata_pelajaran_id 
                    WHERE
                        sekolah_pengguna.sekolah_id = '".$sekolah_id."' 
                        AND sekolah_pengguna.soft_delete = 0 
                        AND pengguna.soft_delete = 0 
                        AND sekolah_pengguna.jabatan_sekolah_id = 2
                    ORDER BY
                    pengguna.nama ASC 
                    ) gurus
                    LEFT JOIN (
                    SELECT
                        ".$str_kolom."
                        (
                            ".$str_kolom_total."
                        ) as total,
                        pengguna_id
                    FROM
                        kehadiran_siswa 
                    WHERE
                        sekolah_id = '".$sekolah_id."' 
                    GROUP BY
                        pengguna_id
                    ) hadirs on hadirs.pengguna_id = gurus.pengguna_id
                    ORDER BY gurus.nama ASC";

                    // return $sql;die;
        
        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        return view('excel/rekapKehadiran', [
            'return' => $fetch, 
            'nama_ruang' => $bulan."-".$tahun, 
            'bulan'=>$bulan, 
            'tahun'=>$tahun,
            'tipe' => 'siswa'
        ]);
        // return $fetch;
    }
    
    static function getJarakSekolah(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $lintang = $request->lintang ? $request->lintang : null;
        $bujur = $request->bujur ? $request->bujur : null;

        $fetch_sekolah = DB::connection('sqlsrv_2')
        ->table('pengaturan_sekolah')
        ->where('sekolah_id','=',$sekolah_id)
        ->first();

        if($fetch_sekolah){
            $jarak = self::distance($lintang, $bujur, $fetch_sekolah->lintang, $fetch_sekolah->bujur);
        }else{
            $jarak = 0;
        }

        return $jarak;

    }

    static function simpanPengaturanSekolah(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengaturan_sekolah_id = $request->pengaturan_sekolah_id ? $request->pengaturan_sekolah_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengaturan_sekolah')
        ->where('pengaturan_sekolah.sekolah_id','=',$sekolah_id)
        ->where('pengaturan_sekolah.soft_delete','=',0)
        ->get();

        if(sizeof($fetch_cek) > 0){
            //siudah ada
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_sekolah')
            ->where('pengaturan_sekolah.sekolah_id','=',$sekolah_id)
            ->where('pengaturan_sekolah.soft_delete','=',0)
            ->update([
                'sabtu_masuk_sekolah' => $request->sabtu_masuk_sekolah ? $request->sabtu_masuk_sekolah : '0',
                'masuk_01' => $request->masuk_01,
                'masuk_02' => $request->masuk_02,
                'masuk_03' => $request->masuk_03,
                'masuk_04' => $request->masuk_04,
                'masuk_05' => $request->masuk_05,
                'masuk_06' => $request->masuk_06,
                'masuk_07' => $request->masuk_07,
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'radius_absen_aktif' => $request->radius_absen_aktif,
                'radius_absen_sekolah_guru' => $request->radius_absen_sekolah_guru,
                'radius_absen_sekolah_siswa' => $request->radius_absen_sekolah_siswa,
                'lintang' => $request->lintang,
                'bujur' => $request->bujur,
                'last_update' => DB::raw('now()::timestamp(0)'),
            ]);

        }else{
            
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_sekolah')
            ->insert([
                'pengaturan_sekolah_id' => $pengaturan_sekolah_id,
                'sekolah_id' => $sekolah_id,
                'sabtu_masuk_sekolah' => $request->sabtu_masuk_sekolah ? $request->sabtu_masuk_sekolah : '0',
                'masuk_01' => $request->masuk_01,
                'masuk_02' => $request->masuk_02,
                'masuk_03' => $request->masuk_03,
                'masuk_04' => $request->masuk_04,
                'masuk_05' => $request->masuk_05,
                'masuk_06' => $request->masuk_06,
                'masuk_07' => $request->masuk_07,
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'radius_absen_aktif' => $request->radius_absen_aktif,
                'radius_absen_sekolah_guru' => $request->radius_absen_sekolah_guru,
                'radius_absen_sekolah_siswa' => $request->radius_absen_sekolah_siswa,
                'lintang' => $request->lintang,
                'bujur' => $request->bujur,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => '0',    
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('pengaturan_sekolah')
                ->where('pengaturan_sekolah.sekolah_id','=',$sekolah_id)
                ->where('pengaturan_sekolah.soft_delete','=',0)
                ->get()
            ],
            200
        );
    }

    static function getPengaturanSekolah(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('pengaturan_sekolah')
        ->where('pengaturan_sekolah.sekolah_id','=',$sekolah_id)
        ->where('pengaturan_sekolah.soft_delete','=',0)
        ;

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function kehadiranRekapGuru(Request $request){
        $tanggal_terakhir = $request->tanggal_terakhir ? $request->tanggal_terakhir : 30;
        $bulan = $request->bulan ? $request->bulan : 1;
        $tahun = $request->tahun ? $request->tahun : 2020;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

        $sql = "SELECT
            -- 'masuk_' || substring(cast(bulans.tanggal_bulan as varchar(20)),9,2) as tanggalnya,
            extract(dow from  bulans.tanggal_bulan)+1 as urut_hari,
            bulans.*,
	        hadir.*
        FROM
            ( SELECT d :: DATE AS tanggal_bulan FROM generate_series ( '".$tahun."-".$bulan."-1', '".$tahun."-".$bulan."-".$tanggal_terakhir."', '1 day' :: INTERVAL ) d ) bulans
            LEFT JOIN (
                SELECT
                    kehadiran_guru.sekolah_id,
                    kehadiran_guru.tanggal,
                    SUM ( 1 ) AS total,
                    MAX ( gurus.total_guru ) AS total_guru,
                    (
                        SUM ( 1 ) / CAST(SUM ( gurus.total_guru ) as float) * 100
                    ) as persen
                FROM
                    kehadiran_guru
                    LEFT JOIN (
                    SELECT
                        sekolah_pengguna.sekolah_id,
                        SUM ( 1 ) AS total_guru 
                    FROM
                        sekolah_pengguna 
                    WHERE
                        sekolah_pengguna.jabatan_sekolah_id = 1 
                        AND sekolah_pengguna.soft_delete = 0 
                        AND sekolah_pengguna.VALID = 1 
                        AND sekolah_pengguna.sekolah_id = '".$sekolah_id."' 
                    GROUP BY
                        sekolah_pengguna.sekolah_id 
                    ) gurus ON gurus.sekolah_id = kehadiran_guru.sekolah_id 
                WHERE
                    kehadiran_guru.soft_delete = 0 
                    AND kehadiran_guru.sekolah_id = '".$sekolah_id."' 
                GROUP BY
                    kehadiran_guru.sekolah_id,
                    kehadiran_guru.tanggal
            ) hadir on hadir.tanggal = bulans.tanggal_bulan
            ";

        return DB::connection('sqlsrv_2')->select(DB::raw($sql));
    }
    
    static function kehadiranHarianGuru(Request $request){
        $tanggal_terakhir = $request->tanggal_terakhir ? $request->tanggal_terakhir : 30;
        $bulan = $request->bulan ? $request->bulan : 1;
        $tahun = $request->tahun ? $request->tahun : 2020;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

        $sql = "SELECT
            extract(dow from  bulans.tanggal_bulan)+1 as urut_hari,
            bulans.*,
	        hadir.*
        FROM
            ( SELECT d :: DATE AS tanggal_bulan FROM generate_series ( '".$tahun."-".$bulan."-1', '".$tahun."-".$bulan."-".$tanggal_terakhir."', '1 day' :: INTERVAL ) d ) bulans
            LEFT JOIN kehadiran_guru hadir ON hadir.tanggal = bulans.tanggal_bulan 
            AND hadir.soft_delete = 0 
            AND hadir.pengguna_id = '".$pengguna_id."' 
            AND hadir.sekolah_id = '".$sekolah_id."'
        ORDER BY tanggal_bulan ASC";

        return DB::connection('sqlsrv_2')->select(DB::raw($sql));
    }

    static function simpanKehadiranGuru(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $kehadiran_guru_id = $request->kehadiran_guru_id ? $request->kehadiran_guru_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('kehadiran_guru')
        ->where('soft_delete','=',0)
        ->where('kehadiran_guru.sekolah_id','=',$sekolah_id)
        ->where('kehadiran_guru.pengguna_id','=',$pengguna_id)
        ->where('kehadiran_guru.tanggal','=',$tanggal)
        ->get()
        ;

        if(sizeof($fetch_cek) > 0){
            //sudah ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('kehadiran_guru')
            ->where('soft_delete','=',0)
            ->where('kehadiran_guru.sekolah_id','=',$sekolah_id)
            ->where('kehadiran_guru.pengguna_id','=',$pengguna_id)
            ->where('kehadiran_guru.tanggal','=',$tanggal)
            ->update([
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'media_input_kehadiran_id' => $request->media_input_kehadiran_id,
                'tanggal' => $request->tanggal,
                'lintang' => $request->lintang,
                'bujur' => $request->bujur,
                'keterangan' => $request->keterangan,
                'jenis_kehadiran_id' => $request->jenis_kehadiran_id,
                'waktu_datang' => $request->waktu_datang,
                'waktu_pulang' => $request->waktu_pulang,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);

        }else{
            //belum ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('kehadiran_guru')
            ->insert([
                'kehadiran_guru_id' => $kehadiran_guru_id,
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'media_input_kehadiran_id' => $request->media_input_kehadiran_id,
                'tanggal' => $request->tanggal,
                'lintang' => $request->lintang,
                'bujur' => $request->bujur,
                'keterangan' => $request->keterangan,
                'jenis_kehadiran_id' => $request->jenis_kehadiran_id,
                'waktu_datang' => $request->waktu_datang,
                'waktu_pulang' => $request->waktu_pulang,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => 0
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('kehadiran_guru')
                ->where('kehadiran_guru.sekolah_id','=',$sekolah_id)
                ->where('kehadiran_guru.pengguna_id','=',$pengguna_id)
                ->where('kehadiran_guru.tanggal','=',$tanggal)
                ->get()
            ],
            200
        );
    }

    static function getKehadiranGuru(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')
        ->table('kehadiran_guru')
        ->where('soft_delete','=',0)
        ->select(
            'kehadiran_guru.*'
        )
        ;

        if($pengguna_id){
            $fetch->where('kehadiran_guru.pengguna_id','=',$pengguna_id);
        }
        
        if($sekolah_id){
            $fetch->where('kehadiran_guru.sekolah_id','=',$sekolah_id);
        }
        
        if($tanggal){
            $fetch->where('kehadiran_guru.tanggal','=',$tanggal);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanGuru(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $guru_id = $request->guru_id ? $request->guru_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('guru')
        ->where('soft_delete','=',0)
        ->where('guru.sekolah_id','=',$sekolah_id)
        ->where('guru.pengguna_id','=',$pengguna_id)
        ->get()
        ;

        if(sizeof($fetch_cek) > 0){
            //sudah ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('guru')
            ->where('soft_delete','=',0)
            ->where('guru.sekolah_id','=',$sekolah_id)
            ->where('guru.pengguna_id','=',$pengguna_id)
            ->update([
                'nip' => $request->nip,
                'nuptk' => $request->nuptk,
                'jenis_guru_id' => $request->jenis_guru_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'nomor_surat_tugas' => $request->nomor_surat_tugas,
                'tanggal_surat_tugas' => $request->tanggal_surat_tugas,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);

        }else{
            //belum ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('guru')
            ->insert([
                'guru_id' => $guru_id,
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'nip' => $request->nip,
                'nuptk' => $request->nuptk,
                'jenis_guru_id' => $request->jenis_guru_id,
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'nomor_surat_tugas' => $request->nomor_surat_tugas,
                'tanggal_surat_tugas' => $request->tanggal_surat_tugas,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => 0
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('guru')
                ->where('guru.sekolah_id','=',$sekolah_id)
                ->where('guru.pengguna_id','=',$pengguna_id)
                ->get()
            ],
            200
        );
    }

    static function getGuru(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $guru_id = $request->guru_id ? $request->guru_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')
        ->table('guru')
        ->leftJoin('ref.jenis_guru as jenis_guru','jenis_guru.jenis_guru_id','=','guru.jenis_guru_id')
        ->leftJoin('ref.mata_pelajaran as mapel','mapel.mata_pelajaran_id','=','guru.mata_pelajaran_id')
        ->where('soft_delete','=',0)
        ->select(
            'guru.*',
            'jenis_guru.nama as jenis_guru',
            'mapel.nama as mata_pelajaran'
        )
        ;

        if($pengguna_id){
            $fetch->where('guru.pengguna_id','=',$pengguna_id);
        }
        
        if($sekolah_id){
            $fetch->where('guru.sekolah_id','=',$sekolah_id);
        }

        if($guru_id){
            $fetch->where('guru.guru_id','=',$guru_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function getUndanganSekolah(Request $request){
        $undangan_sekolah_id = $request->undangan_sekolah_id;
        $sekolah_id = $request->sekolah_id;
        $kode_sekolah = $request->kode_sekolah;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('undangan_sekolah')
        ->join('sekolah','sekolah.sekolah_id','=','undangan_sekolah.sekolah_id')
        ->join('pengguna','pengguna.pengguna_id','=','undangan_sekolah.pengguna_id')
        ->join('ref.jabatan_sekolah as jabatan','jabatan.jabatan_sekolah_id','=','undangan_sekolah.jabatan_sekolah_id')
        ->where('undangan_sekolah.soft_delete','=',0)
        ->where('sekolah.soft_delete','=',0)
        ->select(
            'undangan_sekolah.*',
            'pengguna.*',
            'sekolah.*',
            'undangan_sekolah.kode_sekolah as kode_sekolah',
            'undangan_sekolah.keterangan as keterangan',
            'pengguna.nama as nama_pengguna',
            'jabatan.nama as jabatan_sekolah'
        )
        ;

        if($undangan_sekolah_id){
            $fetch->where('undangan_sekolah.undangan_sekolah_id','=',$undangan_sekolah_id);
        }

        if($sekolah_id){
            $fetch->where('undangan_sekolah.sekolah_id','=',$sekolah_id);
        }
        
        if($kode_sekolah){
            $fetch->where('undangan_sekolah.kode_sekolah','=',$kode_sekolah);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanUndanganSekolah(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;
        $jabatan_sekolah_id = $request->jabatan_sekolah_id;
        $waktu_mulai = $request->waktu_mulai;
        $waktu_selesai = $request->waktu_selesai;
        $keterangan = $request->keterangan;
        $undangan_sekolah_id = RuangController::generateUUID();
        $kode_sekolah = strtoupper(RuangController::generateRandomString(10));

        $exe = DB::connection('sqlsrv_2')->table('undangan_sekolah')
        ->insert([
            'undangan_sekolah_id' => $undangan_sekolah_id,
            'pengguna_id' => $pengguna_id,
            'sekolah_id' => $sekolah_id,
            'jabatan_sekolah_id' => $jabatan_sekolah_id,
            'kode_sekolah' => $kode_sekolah,
            'waktu_mulai' => $waktu_mulai,
            'waktu_selesai' => $waktu_selesai,
            'keterangan' => $keterangan,
            'create_date' => DB::raw('now()::timestamp(0)'),
            'last_update' => DB::raw('now()::timestamp(0)'),
            'soft_delete' => 0,
            'aktif' => 1
        ]);

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('undangan_sekolah')->where('undangan_sekolah_id','=',$undangan_sekolah_id)->get()
            ],
            200
        );

    }

    static function aktifkanSekolah(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;

        // $fetch_sekolah_id = DB::connection('sqlsrv_2')
        // ->table('sekolah')
        // ->join('sekolah_pengguna','sekolah_pengguna.sekolah_id','=','sekolah.sekolah_id')
        // ->where('sekolah.soft_delete','=',0)
        // ->where('sekolah_pengguna.soft_delete','=',0)
        // ->where('sekolah_pengguna.pengguna_id','=',$pengguna_id)
        // ->get();

        // $arr_sekolah_id = array();

        // for ($i=0; $i < sizeof($fetch_sekolah_id); $i++) { 
        //     array_push($arr_sekolah_id, $fetch_sekolah_id[$i]->sekolah_id);
        // }

        // return $arr_sekolah_id;die;
        // $exe_yang_lain = DB::connection('sqlsrv_2')
        // ->table('sekolah')
        // ->whereIn('sekolah_id', $arr_sekolah_id)
        // ->get();

        // return $exe_yang_lain;die;

        $exe_yang_lain = DB::connection('sqlsrv_2')
        ->table('sekolah_pengguna')
        ->where('sekolah_pengguna.pengguna_id','=',$pengguna_id)
        // ->whereIn('sekolah_id', $arr_sekolah_id)
        ->update([
            'aktif' => '0',
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        if($exe_yang_lain){
            //setAktif
            $exe_aktif = DB::connection('sqlsrv_2')
            ->table('sekolah_pengguna')
            ->where('sekolah_pengguna.pengguna_id','=',$pengguna_id)
            ->where('sekolah_pengguna.sekolah_id','=',$sekolah_id)
            ->update([
                'aktif' => '1',
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);
        }else{
            $exe_aktif = false;
        }

        return response(
            [
                'sukses' => ($exe_aktif ? true : false)
            ],
            200
        );


    }

    static function simpanAdministrator(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;
        $admin = $request->admin ? $request->admin : "0";

        // return $admin;

        $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
        ->where('pengguna_id','=', $pengguna_id)
        ->where('sekolah_id','=', $sekolah_id)
        ->update([
            'administrator' => $admin,
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                            ->where('pengguna_id','=', $pengguna_id)
                            ->where('sekolah_id','=', $sekolah_id)
                            ->get()
            ],
            200
        );
    }

    static function getSekolahIndividu(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;
        $administrator = $request->administrator;
        $aktif = $request->aktif;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;
        $keyword = $request->keyword ? $request->keyword : null;

        $fetch = DB::connection('sqlsrv_2')
        ->table('sekolah')
        ->where('sekolah.soft_delete','=',0)
        ->select(
            'sekolah.*'
        )
        ->orderBy('sekolah.aktif','DESC')
        ;
        
        if($sekolah_id){
            $fetch->where('sekolah.sekolah_id','=',DB::raw("'".$sekolah_id."'"))
            ;
        }
        
        if($keyword){
            $fetch->where('sekolah.nama','ilike',DB::raw("'%".$keyword."%'"))
            ;
        }

        // return $fetch->toSql();die;

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function getSekolah(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;
        $administrator = $request->administrator;
        $aktif = $request->aktif;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;
        $keyword = $request->keyword ? $request->keyword : null;

        $fetch = DB::connection('sqlsrv_2')
        ->table('sekolah')
        ->leftJoin('sekolah_pengguna','sekolah_pengguna.sekolah_id','=','sekolah.sekolah_id')
        ->where('sekolah.soft_delete','=',0)
        ->where('sekolah_pengguna.soft_delete','=',0)
        ->select(
            'sekolah.*',
            'sekolah_pengguna.*'
        )
        ->orderBy('sekolah.aktif','DESC')
        ;

        if($pengguna_id && !$administrator){
            $fetch->where('sekolah_pengguna.pengguna_id','=',DB::raw("'".$pengguna_id."'"))
            // ->where('sekolah_pengguna.pendiri','=',1)
            ;
        }
        
        if($administrator){
            $fetch->where('sekolah_pengguna.administrator','=',DB::raw(1))
            ->where('sekolah_pengguna.pengguna_id','=',DB::raw("'".$pengguna_id."'"))
            ;
        }
        
        if($sekolah_id){
            $fetch->where('sekolah.sekolah_id','=',DB::raw("'".$sekolah_id."'"))
            ;
        }
        
        if($aktif){
            $fetch->where('sekolah_pengguna.aktif','=',DB::raw("'".$aktif."'"))
            ;
        }

        if($keyword){
            $fetch->where('sekolah.nama','ilike',DB::raw("'%".$keyword."%'"))
            ;
        }

        // return $fetch->toSql();die;

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

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
            $exe_update_aktif = DB::connection('sqlsrv_2')->table('sekolah')
            ->where('pengguna_id','=',$pengguna_id)
            ->update([
                'aktif' => 0,
                'last_update' => DB::raw('now()::timestamp(0)')
            ])
            ;

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
                'soft_delete' => $soft_delete,
                'aktif' => 1
            ]);

            if($exe){
                //insert ke sekolah pengguna
                $exeSekolahPengguna = DB::connection('sqlsrv_2')->table('sekolah_pengguna')->insert([
                    'sekolah_pengguna_id' => RuangController::generateUUID(),
                    'sekolah_id' => $sekolah_id,
                    'pengguna_id' => $pengguna_id,
                    'pendiri' => 1,
                    'administrator' => 1,
                    'jabatan_sekolah_id' => 1,
                    'valid' => 1,
                    'create_date' => DB::raw('now()::timestamp(0)'),
                    'last_update' => DB::raw('now()::timestamp(0)'),
                    'soft_delete' => $soft_delete
                ]);

            }
        }

        return response(
            [
                'success' => ($exe ? true: false),
                'rows' => DB::connection('sqlsrv_2')->table('sekolah')->where('sekolah_id','=',$sekolah_id)->get()
            ],
            200
        );
    }

    static function getSekolahPengguna(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $jabatan_sekolah_id = $request->jabatan_sekolah_id ? $request->jabatan_sekolah_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : 2020;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $fetch = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
        ->join('pengguna','pengguna.pengguna_id','=','sekolah_pengguna.pengguna_id')
        ->join('sekolah','sekolah.sekolah_id','=','sekolah_pengguna.sekolah_id')
        ->join('ref.jabatan_sekolah as jabatan_sekolah','jabatan_sekolah.jabatan_sekolah_id','=','sekolah_pengguna.jabatan_sekolah_id')
        // ->where('sekolah_pengguna.sekolah_id','=',$sekolah_id)
        // ->where('sekolah_pengguna.pengguna_id','=',$pengguna_id)
        ->where('sekolah_pengguna.soft_delete','=',0)
        ->where('pengguna.soft_delete','=',0)
        ->where('sekolah.soft_delete','=',0)
        ->select(
            'pengguna.*',
            // 'sekolah.*',
            'sekolah_pengguna.*',
            'sekolah.nama as nama_sekolah',
            'sekolah.gambar_logo',
            'sekolah.gambar_latar',
            'jabatan_sekolah.nama as jabatan_sekolah'
        )
        ;

        if((int)$jabatan_sekolah_id === 2){
            $fetch->leftJoin(DB::raw("(SELECT
                * 
            FROM
                (
                SELECT ROW_NUMBER
                    () OVER ( PARTITION BY pengguna_ruang.pengguna_id ORDER BY pengguna_ruang.create_date DESC ) AS urutan,
                    pengguna_ruang.*,
                    ruang.nama as nama_ruang,
                    ruang_sekolah.tahun_ajaran_id 
                FROM
                    pengguna_ruang
                    JOIN ruang ON ruang.ruang_id = pengguna_ruang.ruang_id
                    JOIN ruang_sekolah ON ruang_sekolah.ruang_id = ruang.ruang_id 
                WHERE
                    pengguna_ruang.soft_delete = 0 
                    AND ruang.soft_delete = 0 
                    AND ruang_sekolah.soft_delete = 0 
                    AND pengguna_ruang.jabatan_ruang_id = 3 
                ) aaa 
            WHERE
                aaa.urutan = 1) ruangnya_pengguna"),'ruangnya_pengguna.pengguna_id','=','pengguna.pengguna_id')
            ->select(
                'pengguna.*',
                // 'sekolah.*',
                'sekolah_pengguna.*',
                'sekolah.nama as nama_sekolah',
                'sekolah.gambar_logo',
                'sekolah.gambar_latar',
                'jabatan_sekolah.nama as jabatan_sekolah',
                'ruangnya_pengguna.ruang_id',
                'ruangnya_pengguna.nama_ruang'
            );

            if($ruang_id){
                $fetch->where('ruang_id','=',$ruang_id);
            }
            
            if($tahun_ajaran_id){
                $fetch->where('tahun_ajaran_id','=',$tahun_ajaran_id);
            }
        }

        if($sekolah_id){
            $fetch->where('sekolah_pengguna.sekolah_id','=',$sekolah_id);
        }

        if($pengguna_id){
            $fetch->where('sekolah_pengguna.pengguna_id','=',$pengguna_id);
        }
        
        if($jabatan_sekolah_id){
            $fetch->where('sekolah_pengguna.jabatan_sekolah_id','=',$jabatan_sekolah_id);
        }

        $data = $fetch->skip($start)->take($limit)->orderBy('sekolah_utama', 'DESC')->get();

        for ($i=0; $i < sizeof($data); $i++) { 
            // $ruang = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            // ->join('ruang', 'ruang.ruang_id','=','pengguna_ruang.ruang_id')
            // ->join('ruang_sekolah', 'ruang.ruang_id','=','ruang_sekolah.ruang_id')
            // ->where('pengguna_ruang.soft_delete', '=', 0)
            // ->where('ruang.soft_delete', '=', 0)
            // ->where('ruang_sekolah.soft_delete', '=', 0)
            // ->where('pengguna_ruang.jabatan_ruang_id','=',3)
            // ->where('pengguna_ruang.pengguna_id','=',$data[$i]->pengguna_id)
            // ->where('ruang_sekolah.tahun_ajaran_id','=', $tahun_ajaran_id)
            // ->where('ruang_sekolah.sekolah_id','=', $sekolah_id)
            // ->first();

            // if($ruang){
            //     $data[$i]->nama_ruang = $ruang->nama;
            //     $data[$i]->ruang_id = $ruang->ruang_id;
            // }else{
            //     $data[$i]->nama_ruang = 'Belum masuk ruang';
            //     $data[$i]->ruang_id = null;
            // }

        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $data
            ],
            200
        );
    }

    static function simpanSekolahUtama(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_utama = $request->sekolah_utama ? $request->sekolah_utama : 0;

        $exe_normalisasi = Db::connection('sqlsrv_2')->table('sekolah_pengguna')
        // ->where('sekolah_id','=',$sekolah_id)
        ->where('pengguna_id','=',$pengguna_id)
        ->where('soft_delete','=','0')
        ->update([
            'sekolah_utama' => '0',
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        $exe_utama = Db::connection('sqlsrv_2')->table('sekolah_pengguna')
        ->where('sekolah_id','=',$sekolah_id)
        ->where('pengguna_id','=',$pengguna_id)
        ->where('soft_delete','=','0')
        ->update([
            'sekolah_utama' => $sekolah_utama,
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        return response(
            [
                'sukses' => ($exe_utama ? true: false),
                'rows' => DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                ->where('sekolah_id','=',$sekolah_id)
                ->where('pengguna_id','=',$pengguna_id)
                ->where('soft_delete','=','0')
                ->get()
            ],
            200
        );

    }

    static function simpanSekolahPengguna(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $jabatan_sekolah_id = $request->jabatan_sekolah_id ? $request->jabatan_sekolah_id : null;
        $valid = $request->valid ? $request->valid : 0;
        $soft_delete = $request->soft_delete ? $request->soft_delete : 0;

        $fetch_cek = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
        ->where('sekolah_id','=',$sekolah_id)
        ->where('pengguna_id','=',$pengguna_id)
        ->get();

        if(sizeof($fetch_cek)){
            //sudah ada update
            if($request->undangan == 'Y'){
                return response(
                    [
                        'sukses' => false,
                        'pesan' => 'pengguna_sudah_terdaftar',
                        'rows' => DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                        ->where('sekolah_id','=',$sekolah_id)
                        ->where('pengguna_id','=',$pengguna_id)
                        ->get()
                    ],
                    200
                );
            }

            $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
            ->where('sekolah_id','=',$sekolah_id)
            ->where('pengguna_id','=',$pengguna_id)
            ->update([
                'soft_delete' => $soft_delete,
                'jabatan_sekolah_id' => $jabatan_sekolah_id,
                'valid' => $valid,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);
        }else{
            //belum ada insert
            $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
            ->insert([
                'sekolah_pengguna_id' => RuangController::generateUUID(),
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'jabatan_sekolah_id' => $jabatan_sekolah_id,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => $soft_delete,
                'pendiri' => 0,
                'valid' => $valid
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true: false),
                'rows' => DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                ->where('sekolah_id','=',$sekolah_id)
                ->where('pengguna_id','=',$pengguna_id)
                ->get()
            ],
            200
        );
    }


    //siswa
    static function kehadiranRekapSiswa(Request $request){
        $tanggal_terakhir = $request->tanggal_terakhir ? $request->tanggal_terakhir : 30;
        $bulan = $request->bulan ? $request->bulan : 1;
        $tahun = $request->tahun ? $request->tahun : 2020;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

        $sql = "SELECT
            -- 'masuk_' || substring(cast(bulans.tanggal_bulan as varchar(20)),9,2) as tanggalnya,
            extract(dow from  bulans.tanggal_bulan)+1 as urut_hari,
            bulans.*,
	        hadir.*
        FROM
            ( SELECT d :: DATE AS tanggal_bulan FROM generate_series ( '".$tahun."-".$bulan."-1', '".$tahun."-".$bulan."-".$tanggal_terakhir."', '1 day' :: INTERVAL ) d ) bulans
            LEFT JOIN (
                SELECT
                    kehadiran_siswa.sekolah_id,
                    kehadiran_siswa.tanggal,
                    SUM ( 1 ) AS total,
                    MAX ( gurus.total_guru ) AS total_siswa,
                    (
                        SUM ( 1 ) / CAST(SUM ( gurus.total_guru ) as float) * 100
                    ) as persen
                FROM
                    kehadiran_siswa
                    LEFT JOIN (
                    SELECT
                        sekolah_pengguna.sekolah_id,
                        SUM ( 1 ) AS total_guru 
                    FROM
                        sekolah_pengguna 
                    WHERE
                        sekolah_pengguna.jabatan_sekolah_id = 2 
                        AND sekolah_pengguna.soft_delete = 0 
                        AND sekolah_pengguna.VALID = 1 
                        AND sekolah_pengguna.sekolah_id = '".$sekolah_id."' 
                    GROUP BY
                        sekolah_pengguna.sekolah_id 
                    ) gurus ON gurus.sekolah_id = kehadiran_siswa.sekolah_id 
                WHERE
                    kehadiran_siswa.soft_delete = 0 
                    AND kehadiran_siswa.sekolah_id = '".$sekolah_id."' 
                GROUP BY
                    kehadiran_siswa.sekolah_id,
                    kehadiran_siswa.tanggal
            ) hadir on hadir.tanggal = bulans.tanggal_bulan
            ";

        return DB::connection('sqlsrv_2')->select(DB::raw($sql));
    }
    
    static function kehadiranHarianSiswa(Request $request){
        $tanggal_terakhir = $request->tanggal_terakhir ? $request->tanggal_terakhir : 30;
        $bulan = $request->bulan ? $request->bulan : 1;
        $tahun = $request->tahun ? $request->tahun : 2020;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;

        $sql = "SELECT
            extract(dow from  bulans.tanggal_bulan)+1 as urut_hari,
            bulans.*,
	        hadir.*
        FROM
            ( SELECT d :: DATE AS tanggal_bulan FROM generate_series ( '".$tahun."-".$bulan."-1', '".$tahun."-".$bulan."-".$tanggal_terakhir."', '1 day' :: INTERVAL ) d ) bulans
            LEFT JOIN kehadiran_siswa hadir ON hadir.tanggal = bulans.tanggal_bulan 
            AND hadir.soft_delete = 0 
            AND hadir.pengguna_id = '".$pengguna_id."' 
            AND hadir.sekolah_id = '".$sekolah_id."'
        ORDER by tanggal_bulan asc";

        return DB::connection('sqlsrv_2')->select(DB::raw($sql));
    }

    static function simpanKehadiranSiswa(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $kehadiran_siswa_id = $request->kehadiran_siswa_id ? $request->kehadiran_siswa_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('kehadiran_siswa')
        ->where('soft_delete','=',0)
        ->where('kehadiran_siswa.sekolah_id','=',$sekolah_id)
        ->where('kehadiran_siswa.pengguna_id','=',$pengguna_id)
        ->where('kehadiran_siswa.tanggal','=',$tanggal)
        ->get()
        ;

        if(sizeof($fetch_cek) > 0){
            //sudah ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('kehadiran_siswa')
            ->where('soft_delete','=',0)
            ->where('kehadiran_siswa.sekolah_id','=',$sekolah_id)
            ->where('kehadiran_siswa.pengguna_id','=',$pengguna_id)
            ->where('kehadiran_siswa.tanggal','=',$tanggal)
            ->update([
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'media_input_kehadiran_id' => $request->media_input_kehadiran_id,
                'tanggal' => $request->tanggal,
                'lintang' => $request->lintang,
                'bujur' => $request->bujur,
                'keterangan' => $request->keterangan,
                'jenis_kehadiran_id' => $request->jenis_kehadiran_id,
                'waktu_datang' => $request->waktu_datang,
                'waktu_pulang' => $request->waktu_pulang,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);

        }else{
            //belum ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('kehadiran_siswa')
            ->insert([
                'kehadiran_siswa_id' => $kehadiran_siswa_id,
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'media_input_kehadiran_id' => $request->media_input_kehadiran_id,
                'tanggal' => $request->tanggal,
                'lintang' => $request->lintang,
                'bujur' => $request->bujur,
                'keterangan' => $request->keterangan,
                'jenis_kehadiran_id' => $request->jenis_kehadiran_id,
                'waktu_datang' => $request->waktu_datang,
                'waktu_pulang' => $request->waktu_pulang,
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => 0
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('kehadiran_siswa')
                ->where('kehadiran_siswa.sekolah_id','=',$sekolah_id)
                ->where('kehadiran_siswa.pengguna_id','=',$pengguna_id)
                ->where('kehadiran_siswa.tanggal','=',$tanggal)
                ->get()
            ],
            200
        );
    }

    static function getKehadiranSiswa(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')
        ->table('kehadiran_siswa')
        ->where('soft_delete','=',0)
        ->select(
            'kehadiran_siswa.*'
        )
        ;

        if($pengguna_id){
            $fetch->where('kehadiran_siswa.pengguna_id','=',$pengguna_id);
        }
        
        if($sekolah_id){
            $fetch->where('kehadiran_siswa.sekolah_id','=',$sekolah_id);
        }
        
        if($tanggal){
            $fetch->where('kehadiran_siswa.tanggal','=',$tanggal);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }
}