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
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'last_update' => DB::raw('now()::timestamp(0)'),
            ]);
        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_sekolah')
            ->insert([
                'pengaturan_sekolah_id' => $pengaturan_sekolah_id,
                'sekolah_id' => $sekolah_id,
                'sabtu_masuk_sekolah' => $request->sabtu_masuk_sekolah ? $request->sabtu_masuk_sekolah : '0',
                'jam_masuk' => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
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
            bulans.*,
	        hadir.*
        FROM
            ( SELECT d :: DATE AS tanggal_bulan FROM generate_series ( '".$tahun."-".$bulan."-1', '".$tahun."-".$bulan."-".$tanggal_terakhir."', '1 day' :: INTERVAL ) d ) bulans
            LEFT JOIN kehadiran_guru hadir ON hadir.tanggal = bulans.tanggal_bulan 
            AND hadir.soft_delete = 0 
            AND hadir.pengguna_id = '".$pengguna_id."' 
            AND hadir.sekolah_id = '".$sekolah_id."'";

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

        $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
        ->where('pengguna_id','=', $pengguna_id)
        ->where('sekolah_id','=', $sekolah_id)
        ->update([
            'administrator' => 1,
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

    static function getSekolah(Request $request){
        $sekolah_id = $request->sekolah_id;
        $pengguna_id = $request->pengguna_id;
        $administrator = $request->administrator;
        $aktif = $request->aktif;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

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

        if($sekolah_id){
            $fetch->where('sekolah_pengguna.sekolah_id','=',$sekolah_id);
        }

        if($pengguna_id){
            $fetch->where('sekolah_pengguna.pengguna_id','=',$pengguna_id);
        }
        
        if($jabatan_sekolah_id){
            $fetch->where('sekolah_pengguna.jabatan_sekolah_id','=',$jabatan_sekolah_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->orderBy('sekolah_utama', 'DESC')->get()
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

}