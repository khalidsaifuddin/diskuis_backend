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

class SiswaController extends Controller
{   
    static function simpanKehadiranRuang(Request $request){
        // $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $data = $request->data ? $request->data : null;
        // $kehadiran_siswa_id = $request->kehadiran_siswa_id ? $request->kehadiran_siswa_id : RuangController::generateUUID();

        $berhasil = 0;
        $gagal = 0;
        $total = 0;

        foreach ($data as $key => $value) {

            // return $value['dirty'];die;

            if((int)$value['dirty'] === 1){
                $total++;

                $cek = DB::connection('sqlsrv_2')->table('kehadiran_siswa')
                ->where('pengguna_id', '=', $value['pengguna_id'])
                ->where('sekolah_id', '=', $sekolah_id)
                ->where('tanggal', '=', $tanggal)
                ->get()
                ;
    
                if(sizeof($cek) > 0){
                    //update
                    $exe = DB::connection('sqlsrv_2')->table('kehadiran_siswa')
                    ->where('pengguna_id', '=', $value['pengguna_id'])
                    ->where('sekolah_id', '=', $sekolah_id)
                    ->where('tanggal', '=', $tanggal)
                    ->update([
                        'soft_delete' => (int)$value['hadir'] === 1 ? '0' : '1',
                        'waktu_datang' => $value['waktu_datang'] ? $value['waktu_datang'] : ((int)$value['hadir'] === 1 ? DB::raw("now()") : null),
                        'waktu_pulang' => $value['waktu_pulang']
                    ]);
                    $label = 'UPDATE';

                    iF($exe){
                        $berhasil++;
                    }else{
                        $gagal++;
                    }

                }else{
                    //insert
                    $kehadiran_siswa_id = RuangController::generateUUID();

                    $exe = DB::connection('sqlsrv_2')->table('kehadiran_siswa')
                    ->insert([
                        'kehadiran_siswa_id' => $kehadiran_siswa_id,
                        'pengguna_id' => $value['pengguna_id'],
                        'sekolah_id' => $sekolah_id,
                        'tanggal' => $tanggal,
                        'soft_delete' => (int)$value['hadir'] === 1 ? '0' : '1',
                        'create_date' => DB::raw("now()"),
                        'last_update' => DB::raw("now()"),
                        'media_input_kehadiran_id' => 1,
                        'jenis_kehadiran_id' => 1,
                        'waktu_datang' => $value['waktu_datang'] ? $value['waktu_datang'] : ((int)$value['hadir'] === 1 ? DB::raw("now()") : null),
                        'waktu_pulang' => $value['waktu_pulang']
                    ]);
                    $label = 'INSERT';

                    iF($exe){
                        $berhasil++;
                    }else{
                        $gagal++;
                    }

                }



            }

        }


        return response(
            [
                'berhasil' => $berhasil,
                'gagal' => $gagal,
                'total' => $total,
                'rows' => DB::connection('sqlsrv_2')->table('kehadiran_siswa')
                ->where('sekolah_id', '=', $sekolah_id)
                ->where('tanggal', '=', $tanggal)
                ->get()
            ],
            200
        );

    }

    static function getKehadiranRuang(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : 'edc76932-d40d-45c5-8ac8-a2ca2ce4fd1e';
        $ruang_id = $request->ruang_id ? $request->ruang_id : '9abfb80b-69b7-4575-a01b-4cf445cdf898';
        $tanggal = $request->tanggal ? $request->tanggal : '2020-12-23';
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : '2020';

        $sql = "SELECT
            kehadiran_siswa.*,
            pengguna.pengguna_id,
            pengguna.nama,
            pengguna.gambar,
            pengguna.username,
            ruang_sekolah.ruang_id,
            ruang_sekolah.sekolah_id,
            ta.nama as tahun_ajaran
        FROM
            pengguna
            JOIN pengguna_ruang ON pengguna_ruang.pengguna_id = pengguna.pengguna_id
            JOIN ruang ON ruang.ruang_id = pengguna_ruang.ruang_id
            JOIN ruang_sekolah ON ruang_sekolah.ruang_id = pengguna_ruang.ruang_id
            JOIN ref.tahun_ajaran ta on ta.tahun_ajaran_id = ruang_sekolah.tahun_ajaran_id
            LEFT JOIN kehadiran_siswa ON kehadiran_siswa.pengguna_id = pengguna.pengguna_id 
            AND kehadiran_siswa.soft_delete = 0 
            AND kehadiran_siswa.tanggal = '".$tanggal."' 
        WHERE
            pengguna.soft_delete = 0 
            AND pengguna_ruang.soft_delete = 0 
            AND ruang.soft_delete = 0 
            AND ruang_sekolah.soft_delete = 0 
            AND ruang_sekolah.tahun_ajaran_id = '".$tahun_ajaran_id."' 
            AND ruang_sekolah.sekolah_id = '".$sekolah_id."' 
            AND ruang_sekolah.ruang_id = '".$ruang_id."'
            AND pengguna_ruang.jabatan_ruang_id = 3";

        $fetch = DB::connection('sqlsrv_2')->select($sql);

        return response(
            [
                'total' => sizeof($fetch),
                'rows' => $fetch
            ],
            200
        );
    }

    static function getSiswaSekolah(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $tahun_ajaran_id = $request->tahun_ajaran_id ? $request->tahun_ajaran_id : '2020';
        $keyword = $request->keyword ? $request->keyword : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        // $sql = "SELECT
        //     pengguna.pengguna_id,
        //     pengguna.nama,
        //     pengguna.username,
        //     abc.jumlah_total AS jumlah_ruang,
        //     pengguna.gambar 
        // FROM
        //     pengguna
        //     JOIN (
        //     SELECT
        //         pengguna_ruang.pengguna_id,
        //         SUM ( 1 ) AS jumlah_total 
        //     FROM
        //         pengguna_ruang
        //         JOIN ruang ON ruang.ruang_id = pengguna_ruang.ruang_id
        //         JOIN ruang_sekolah ON ruang_sekolah.ruang_id = ruang.ruang_id 
        //     WHERE
        //         ruang.soft_delete = 0 
        //         AND pengguna_ruang.soft_delete = 0 
        //         AND ruang_sekolah.soft_delete = 0 
        //         AND ruang_sekolah.sekolah_id = '{$sekolah_id}' 
        //         AND jabatan_ruang_id = 3 
        //         AND ruang_sekolah.tahun_ajaran_id = '{$tahun_ajaran_id}' 
        //     GROUP BY
        //         pengguna_ruang.pengguna_id 
        //     ) abc ON abc.pengguna_id = pengguna.pengguna_id 
        // WHERE
        //     pengguna.soft_delete = 0";

        $fetch = DB::connection('sqlsrv_2')->table('pengguna')
        ->where('pengguna.soft_delete', '=', 0)
        ->join(DB::raw("(
            SELECT
                pengguna_ruang.pengguna_id,
                SUM ( 1 ) AS jumlah_total
            FROM
                pengguna_ruang
                JOIN ruang ON ruang.ruang_id = pengguna_ruang.ruang_id
                JOIN ruang_sekolah ON ruang_sekolah.ruang_id = ruang.ruang_id 
            WHERE
                ruang.soft_delete = 0 
                AND pengguna_ruang.soft_delete = 0 
                AND ruang_sekolah.soft_delete = 0 
                AND ruang_sekolah.sekolah_id = '{$sekolah_id}' 
                AND jabatan_ruang_id = 3 
                AND ruang_sekolah.tahun_ajaran_id = '{$tahun_ajaran_id}' 
                ".($ruang_id ? " AND ruang.ruang_id = '{$ruang_id}' " : " ")."
            GROUP BY
                pengguna_ruang.pengguna_id 
            ) abc"), 'abc.pengguna_id', '=', 'pengguna.pengguna_id')
        ->select(
            'pengguna.pengguna_id',
            'pengguna.nama',
            'pengguna.username',
            'abc.jumlah_total AS jumlah_ruang',
            'pengguna.gambar'
        )
        ;

        if($keyword){
            $fetch->where('pengguna.nama', 'ilike', DB::raw("'%".$keyword."%'"));
        }

        $data = $fetch->skip($start)->take($limit)->get();

        for ($i=0; $i < sizeof($data); $i++) { 
            $ruang = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->join('ruang', 'ruang.ruang_id','=','pengguna_ruang.ruang_id')
            ->join('ruang_sekolah', 'ruang.ruang_id','=','ruang_sekolah.ruang_id')
            ->where('pengguna_ruang.soft_delete', '=', 0)
            ->where('ruang.soft_delete', '=', 0)
            ->where('ruang_sekolah.soft_delete', '=', 0)
            ->where('pengguna_ruang.jabatan_ruang_id','=',3)
            ->where('pengguna_ruang.pengguna_id','=',$data[$i]->pengguna_id)
            ->where('ruang_sekolah.tahun_ajaran_id','=', $tahun_ajaran_id)
            ->where('ruang_sekolah.sekolah_id','=', $sekolah_id)
            ->first();

            $data[$i]->nama_ruang = $ruang->nama;
            $data[$i]->ruang_id = $ruang->ruang_id;
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $data
            ],
            200
        );
    }

    static function getSiswa(Request $request){
        // return "oke";
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('siswa')
        ->where('soft_delete','=',0);

        if($sekolah_id){
            $fetch->where('siswa.sekolah_id','=',$sekolah_id);
        }

        if($pengguna_id){
            $fetch->where('siswa.pengguna_id','=',$pengguna_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }
    
    static function getOrangtua(Request $request){
        // return "oke";
        $orangtua_id = $request->orangtua_id ? $request->orangtua_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('orangtua')
        ->where('soft_delete','=',0);

        if($pengguna_id){
            $fetch->where('orangtua.pengguna_id','=',$pengguna_id);
        }
        
        if($orangtua_id){
            $fetch->where('orangtua.orangtua_id','=',$orangtua_id);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanSiswa(Request $request){
        return "oke";
    }
    
    static function simpanOrangtua(Request $request){
        return "oke";
    }
}