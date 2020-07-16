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

class AktivitasController extends Controller
{   
    static function getAktivitas(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $tipe = $request->tipe ? $request->tipe : 'publik';

        // $sql = "SELECT
        //             * 
        //         FROM
        //             (
        //             SELECT
        //                 aktivitas.*,
        //                 pengguna.nama as nama_pengguna,
        //                 pengguna.gambar as gambar 
        //             FROM
        //                 aktivitas 
        //             JOIN pengguna on pengguna.pengguna_id = aktivitas.pengguna_id
        //             WHERE
        //                 aktivitas.pengguna_id = '".$pengguna_id."' 
        //                 AND aktivitas.soft_delete = 0
        //                 AND pengguna.soft_delete = 0 UNION
        //             SELECT
        //                 aktivitas.*,
        //                 pengguna.nama as nama_pengguna,
        //                 pengguna.gambar as gambar 
        //             FROM
        //                 pengikut_pengguna
        //                 JOIN aktivitas ON aktivitas.pengguna_id = pengikut_pengguna.pengguna_id 
        //                 JOIN pengguna on pengguna.pengguna_id = pengikut_pengguna.pengguna_id
        //             WHERE
        //                 pengikut_pengguna.pengguna_id_pengikut = '".$pengguna_id."' 
        //                 AND pengikut_pengguna.soft_delete = 0 
        //                 AND aktivitas.soft_delete = 0 
        //                 AND pengguna.soft_delete = 0 
        //                 AND aktivitas.keterangan != 'diikuti-pengguna'
        //             ) aktivitas_all 
        //         ORDER BY
        //             create_date DESC
        //             OFFSET ".$start." LIMIT 20";
        
        switch ($tipe) {
            case 'publik':
                $param_restriksi = "AND aktivitas.jenis_aktivitas_id != 2
                                    AND aktivitas.keterangan != 'diikuti-pengguna'
                                    AND aktivitas.keterangan != 'tambah-kuis-ke-ruang'";
                break;
            
            default:
                $param_restriksi = "AND aktivitas.keterangan != 'diikuti-pengguna'";
                break;
        }
    

        $sql = "SELECT
                    uuid_generate_v4() as aktivitas_id,
                    *
                FROM
                    (
                    SELECT
                        aktivitas.pengguna_id,
                        aktivitas.create_date,
                        aktivitas.last_update,
                        aktivitas.soft_delete,
                        'aktivitas-' || aktivitas.jenis_aktivitas_id as jenis,
                        aktivitas.keterangan,
                        aktivitas.related_id,
                        null as tautan,
                        pengguna.nama AS nama_pengguna,
                        pengguna.gambar AS gambar 
                    FROM
                        aktivitas
                        JOIN pengguna ON pengguna.pengguna_id = aktivitas.pengguna_id 
                    WHERE
                        aktivitas.pengguna_id = '".$pengguna_id."'  
                        {$param_restriksi}
                        AND aktivitas.soft_delete = 0
                        AND pengguna.soft_delete = 0 UNION
                    SELECT
                        aktivitas.pengguna_id,
                        aktivitas.create_date,
                        aktivitas.last_update,
                        aktivitas.soft_delete,
                        'aktivitas-' || aktivitas.jenis_aktivitas_id as jenis,
                        aktivitas.keterangan,
                        aktivitas.related_id,
                        null as tautan,
                        pengguna.nama AS nama_pengguna,
                        pengguna.gambar AS gambar 
                    FROM
                        pengikut_pengguna
                        JOIN aktivitas ON aktivitas.pengguna_id = pengikut_pengguna.pengguna_id
                        JOIN pengguna ON pengguna.pengguna_id = pengikut_pengguna.pengguna_id 
                    WHERE
                        pengikut_pengguna.pengguna_id_pengikut = '".$pengguna_id."'  
                        AND pengikut_pengguna.soft_delete = 0 
                        AND aktivitas.soft_delete = 0 
                        AND pengguna.soft_delete = 0 
                        {$param_restriksi}
                    UNION
                    SELECT
                    linimasa.pengguna_id_pelaku as pengguna_id,
                    linimasa.create_date,
                    linimasa.last_update,
                    linimasa.soft_delete,
                    'linimasa-' || linimasa.jenis_linimasa_id as jenis,
                    linimasa.keterangan,
                    (case when linimasa.sesi_kuis_id is null then linimasa.ruang_id else linimasa.sesi_kuis_id end) as related_id,
                    linimasa.tautan,
                    pengguna.nama as nama_pengguna,
                    pengguna.gambar as gambar
                FROM
                    pengguna_ruang 
                    join linimasa on linimasa.ruang_id = pengguna_ruang.ruang_id
                    join pengguna on pengguna.pengguna_id = linimasa.pengguna_id_pelaku
                WHERE
                    pengguna_ruang.soft_delete = 0
                    AND pengguna_ruang.pengguna_id = '".$pengguna_id."' 
                    AND linimasa.Soft_delete = 0
                    ) aktivitas_all 
                ORDER BY
                    create_date DESC 
                OFFSET ".$start." LIMIT 20";
        
        // return $sql;die;

        $fetch = DB::connection('sqlsrv_2')->select(DB::raw($sql));

        for ($i=0; $i < sizeof($fetch); $i++) { 
            switch ($fetch[$i]->jenis) {
                case 'aktivitas-4':
                    
                    switch ($fetch[$i]->keterangan) {
                        case 'mengikuti-pengguna':
                            $pengguna_yang_diikuti = DB::connection('sqlsrv_2')
                            ->table('pengguna')
                            ->leftJoin(DB::raw("(select * from pengikut_pengguna where pengguna_id_pengikut = '".$pengguna_id."' and soft_delete = 0) as pengikut"), 'pengikut.pengguna_id','=','pengguna.pengguna_id')
                            ->where('pengguna.pengguna_id','=',$fetch[$i]->related_id)
                            ->select(
                                'pengguna.*',
                                'pengikut.pengguna_id_pengikut as flag_pengikut'
                            )
                            ->first();

                            $keterangan_teks = $fetch[$i]->nama_pengguna.' mulai mengikuti <a href="/tampilPengguna/'.$fetch[$i]->related_id.'"><b>'.$pengguna_yang_diikuti->nama.'</b></a>';
                            $fetch[$i]->keterangan_teks = $keterangan_teks;
                            $fetch[$i]->nama_followed = $pengguna_yang_diikuti->nama;
                            $fetch[$i]->gambar_followed = $pengguna_yang_diikuti->gambar;
                            $fetch[$i]->flag_pengikut = $pengguna_yang_diikuti->flag_pengikut;
                            $fetch[$i]->pengguna_id_pengikut = $pengguna_yang_diikuti->pengguna_id;
                            break;
                        case 'diikuti-pengguna':
                            $pengguna_yang_diikuti = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$fetch[$i]->related_id)->first();

                            $keterangan_teks = '<a href="/tampilPengguna/'.$pengguna_yang_diikuti->pengguna_id.'"><b>'.$pengguna_yang_diikuti->nama.'</b></a> mulai mengikutimu';
                            // $keterangan_teks = $pengguna_yang_diikuti->nama.' mulai mengikuti <a href="/tampilPengguna/'.$fetch[$i]->pengguna_id.'"><b>'.$fetch[$i]->nama_pengguna.'</b></a>';
                            $fetch[$i]->keterangan_teks = $keterangan_teks;
                            break;
                        default:
                            $fetch[$i]->keterangan_teks = $fetch[$i]->keterangan;
                            break;
                    }

                    break;
                case 'aktivitas-2':
                    switch ($fetch[$i]->keterangan) {
                        case 'tambah-kuis-ke-ruang':
                            $sesi_kuis = DB::connection('sqlsrv_2')
                            ->table('sesi_kuis')
                            ->leftJoin('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
                            ->leftJoin('ruang','ruang.ruang_id','=','sesi_kuis.ruang_id')
                            ->where('sesi_kuis_id','=',$fetch[$i]->related_id)
                            ->select(
                                'sesi_kuis.*',
                                'ruang.nama as nama_ruang',
                                'kuis.judul as judul_kuis'
                            )
                            ->first();

                            $keterangan_teks = $fetch[$i]->nama_pengguna.' menambahkan kuis <a href="/praTampilKuis/'.$sesi_kuis->kode_sesi.'"><b>'.$sesi_kuis->judul_kuis.'</b></a> ke ruang <a href="/tampilRuang/'.$sesi_kuis->ruang_id.'"><b>'.$sesi_kuis->nama_ruang.'</b></a>';
                            $fetch[$i]->keterangan_teks = $keterangan_teks;
                            break;
                        
                        default:
                            $fetch[$i]->keterangan_teks = $fetch[$i]->keterangan;
                            break;
                    }
                    break;
                case 'aktivitas-1':
                    switch ($fetch[$i]->keterangan) {
                        case 'ikut-kuis':
                            $sesi_kuis = DB::connection('sqlsrv_2')
                            ->table('sesi_kuis')
                            ->leftJoin('kuis','kuis.kuis_id','=','sesi_kuis.kuis_id')
                            ->leftJoin('pengguna','pengguna.pengguna_id','=','kuis.pengguna_id')
                            ->leftJoin('ruang','ruang.ruang_id','=','sesi_kuis.ruang_id')
                            ->where('sesi_kuis_id','=',$fetch[$i]->related_id)
                            ->select(
                                'sesi_kuis.*',
                                'ruang.nama as nama_ruang',
                                'kuis.judul as judul_kuis',
                                'kuis.gambar_kuis as gambar_kuis',
                                'pengguna.nama as pembuat_kuis'
                            )
                            ->first();

                            $keterangan_teks = $fetch[$i]->nama_pengguna.' mengikuti kuis <a href="/praTampilKuis/'.$sesi_kuis->kode_sesi.'"><b>'.$sesi_kuis->judul_kuis.'</b></a> sesi '.$sesi_kuis->keterangan;
                            $fetch[$i]->keterangan_teks = $keterangan_teks;
                            $fetch[$i]->judul_kuis_diikuti = $sesi_kuis->judul_kuis;
                            $fetch[$i]->gambar_kuis_diikuti = $sesi_kuis->gambar_kuis;
                            $fetch[$i]->pembuat_kuis_diikuti = $sesi_kuis->pembuat_kuis;
                            break;
                        
                        default:
                            $fetch[$i]->keterangan_teks = $fetch[$i]->keterangan;
                            break;
                    }
                    break;
                default:
                    $fetch[$i]->keterangan_teks = $fetch[$i]->keterangan;
                    break;
            }
        }

        // return $fetch;
        return response(
            [
                'rows' => $fetch,
                'total' => sizeof($fetch)
            ],
            200
        );
    }
}