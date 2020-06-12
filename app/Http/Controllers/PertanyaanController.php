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

// use App\Http\Controllers\NotifikasiController;

class PertanyaanController extends Controller
{
    static public function generateUUID()
    {
        $uuid = DB::connection('sqlsrv_2')
        ->table(DB::raw('pengguna'))
        ->select(DB::raw('uuid_generate_v4() as uuid'))
        ->first();

        return $uuid->{'uuid'};
    }

    static public function simpanPantauan(Request $request){
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $pantauan_id = self::generateUUID();
        $return = array();

        $fetch_cari = DB::connection('sqlsrv_2')->table('pantauan')
        ->where('pengguna_id','=',$pengguna_id)
        ->where('pertanyaan_id','=',$pertanyaan_id)
        ->get();

        if(sizeof($fetch_cari) > 0){
            
            $insert = DB::connection('sqlsrv_2')->table('pantauan')
            ->where('pantauan_id','=', $fetch_cari[0]->pantauan_id)
            ->update([
                'soft_delete' => 0,
                'last_update' => DB::raw('now()')
            ]);

            $return['rows'] = DB::connection('sqlsrv_2')->table('pantauan')->where('pantauan_id','=',$fetch_cari[0]->pantauan_id)->first();
            
        }else{
            
            $insert = DB::connection('sqlsrv_2')->table('pantauan')->insert([
                'pantauan_id' => $pantauan_id,
                'pengguna_id' => $pengguna_id,
                'pertanyaan_id' => $pertanyaan_id
            ]);
        
            $return['rows'] = DB::connection('sqlsrv_2')->table('pantauan')->where('pantauan_id','=',$pantauan_id)->first();
        }


        if($insert){
            $return['sukses'] = true;
        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
        
    }

    static public function getPertanyaanPantauan(Request $request){

        // return "oke";die;
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $pantauan = $request->input('pantauan') ? $request->input('pantauan') : null;
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('pertanyaan')
        ->join('pengguna','pengguna.pengguna_id','=','pertanyaan.pengguna_id')
        ->leftJoin(DB::raw("(SELECT
            pertanyaan_id,
            SUM ( 1 ) AS jumlah_jawaban 
        FROM
            jawaban 
        WHERE
            soft_delete = 0 
        GROUP BY
            pertanyaan_id) as jawaban"),'jawaban.pertanyaan_id','=','pertanyaan.pertanyaan_id')
        ->join(DB::raw("(SELECT
            pertanyaan_id,
            SUM ( 1 ) AS jumlah_pantauan 
        FROM
            pantauan 
        WHERE
            soft_delete = 0
        AND pengguna_id = '".$pengguna_id."' 
        GROUP BY
            pertanyaan_id) as pantauan"),'pantauan.pertanyaan_id','=','pertanyaan.pertanyaan_id')
        ->where('pertanyaan.soft_delete','=',0)
        ->select(
            'pertanyaan.*',
            'pengguna.nama as pengguna',
            DB::raw('COALESCE(jawaban.jumlah_jawaban,0) as jumlah_jawaban'),
            DB::raw('COALESCE(pantauan.jumlah_pantauan,0) as jumlah_pantauan')
            // DB::raw("COALESCE((select sum(1) as jumlah_jawaban from jawaban where jawaban.soft_delete = 0 and jawaban.pertanyaan_id = pertanyaan.pertanyaan_id),0) as jumlah_jawaban")
        )
        ->take(20)
        ->orderBy('create_date','DESC');

        if($pengguna_id && !$pantauan){
            $fetch->where('pertanyaan.pengguna_id','=',$pengguna_id);
        }

        if($pertanyaan_id){
            $fetch->where('pertanyaan.pertanyaan_id','=',$pertanyaan_id);
        }

        // return $fetch->toSql();die;

        $fetch = $fetch->get();

        $return['rows'] = $fetch;
        $return['result'] = sizeof($fetch);

        return $return;
    }

    static public function getPertanyaan(Request $request){

        // return "oke";die;
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $keyword = $request->input('keyword') ? $request->input('keyword') : null;
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : null;
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('pertanyaan')
        ->join('pengguna','pengguna.pengguna_id','=','pertanyaan.pengguna_id')
        ->leftJoin(DB::raw("(SELECT
            pertanyaan_id,
            SUM ( 1 ) AS jumlah_jawaban 
        FROM
            jawaban 
        WHERE
            soft_delete = 0 
        GROUP BY
            pertanyaan_id) as jawaban"),'jawaban.pertanyaan_id','=','pertanyaan.pertanyaan_id')
        ->leftJoin(DB::raw("(SELECT
            pertanyaan_id,
            SUM ( 1 ) AS jumlah_pantauan 
        FROM
            pantauan 
        WHERE
            soft_delete = 0 
        GROUP BY
            pertanyaan_id) as pantauan"),'pantauan.pertanyaan_id','=','pertanyaan.pertanyaan_id')
        ->where('pertanyaan.soft_delete','=',0)
        ->select(
            'pertanyaan.*',
            'pengguna.nama as pengguna',
            DB::raw('COALESCE(jawaban.jumlah_jawaban,0) as jumlah_jawaban'),
            DB::raw('COALESCE(pantauan.jumlah_pantauan,0) as jumlah_pantauan')
            // DB::raw("COALESCE((select sum(1) as jumlah_jawaban from jawaban where jawaban.soft_delete = 0 and jawaban.pertanyaan_id = pertanyaan.pertanyaan_id),0) as jumlah_jawaban")
        )
        ->take(20)
        ->orderBy('create_date','DESC');

        if($pengguna_id){
            $fetch->where('pertanyaan.pengguna_id','=',$pengguna_id);
        }

        if($pertanyaan_id){
            $fetch->where('pertanyaan.pertanyaan_id','=',$pertanyaan_id);
        }

        if($keyword){
            $fetch->where('pertanyaan.judul','LIKE',DB::raw("'%".$keyword."%'"));
        }

        if($ruang_id){
            $fetch->join('pertanyaan_ruang','pertanyaan_ruang.pertanyaan_id','=','pertanyaan.pertanyaan_id');
            $fetch->join('ruang','ruang.ruang_id','=','pertanyaan_ruang.ruang_id');
            $fetch->where('pertanyaan_ruang.ruang_id', '=', $ruang_id);
            $fetch->select(
                'pertanyaan.*',
                'pengguna.nama as pengguna',
                DB::raw('COALESCE(jawaban.jumlah_jawaban,0) as jumlah_jawaban'),
                DB::raw('COALESCE(pantauan.jumlah_pantauan,0) as jumlah_pantauan'),
                'ruang.nama as ruang',
                'ruang.ruang_id as ruang_id'
                // DB::raw("COALESCE((select sum(1) as jumlah_jawaban from jawaban where jawaban.soft_delete = 0 and jawaban.pertanyaan_id = pertanyaan.pertanyaan_id),0) as jumlah_jawaban")
            );
        }else{
            // $fetch->leftJoin('pertanyaan_ruang','pertanyaan_ruang.pertanyaan_id','=','pertanyaan.pertanyaan_id');
            // $fetch->leftJoin('ruang','ruang.ruang_id','=','pertanyaan_ruang.ruang_id');
            // $fetch->where('pertanyaan_ruang.ruang_id', '=', $ruang_id);
            $fetch->select(
                'pertanyaan.*',
                'pengguna.nama as pengguna',
                DB::raw('COALESCE(jawaban.jumlah_jawaban,0) as jumlah_jawaban'),
                DB::raw('COALESCE(pantauan.jumlah_pantauan,0) as jumlah_pantauan')
                // 'ruang.nama as ruang',
                // 'ruang.ruang_id as ruang_id'
                // DB::raw("COALESCE((select sum(1) as jumlah_jawaban from jawaban where jawaban.soft_delete = 0 and jawaban.pertanyaan_id = pertanyaan.pertanyaan_id),0) as jumlah_jawaban")
            );
        }

        $fetch = $fetch->get();
        // return $fetch->toSql();die;

        for ($i=0; $i < sizeof($fetch); $i++) { 
            $fetch_ruang = DB::connection('sqlsrv_2')
            ->select(DB::raw("select
                                ruang.* 
                            from 
                                pertanyaan_ruang 
                            join ruang on ruang.ruang_id = pertanyaan_ruang.ruang_id 
                            where 
                                pertanyaan_id = '".$fetch[$i]->pertanyaan_id."'
                            "));
            
            // $fetch[$i]->ruang['rows'] = $fetch_ruang;
            // $fetch[$i]->ruang['total'] = sizeof($fetch_ruang);

            $fetch[$i]->ruang = $fetch_ruang;
        }

        $return['rows'] = $fetch;
        $return['result'] = sizeof($fetch);

        return $return;
    }

    static public function simpanPertanyaan(Request $request){
        // return "oke";
        $judul = $request->input('judul');
        $konten = $request->input('konten');
        $publikasi = $request->input('publikasi');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_id = self::generateUUID();
        $topik_pertanyaan_id = $request->input('topik_pertanyaan_id');
        
        $return = array();

        $insert = DB::connection('sqlsrv_2')->table('pertanyaan')->insert([
            'pertanyaan_id' => $pertanyaan_id,
            'judul' => $judul,
            'konten' => $konten,
            'publikasi' => $publikasi,
            'pengguna_id' => $pengguna_id,
            'topik_pertanyaan_id' => $topik_pertanyaan_id
        ]);

        if($insert){
            $return['sukses'] = true;
            $return['rows'] = DB::connection('sqlsrv_2')->table('pertanyaan')->where('pertanyaan_id','=',$pertanyaan_id)->first();
        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
    }

    static public function getJawaban(Request $request){
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $jawaban_id = $request->input('jawaban_id') ? $request->input('jawaban_id') : null;
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('jawaban')
        ->join('pengguna','pengguna.pengguna_id','=','jawaban.pengguna_id')
        ->leftJoin(DB::raw("(SELECT
            jawaban_id,
            SUM ( 1 ) AS jumlah_komentar 
        FROM
            komentar 
        WHERE
            soft_delete = 0 
        AND induk_komentar_id is NULL
        GROUP BY
            jawaban_id) as komentar"),'komentar.jawaban_id','=','jawaban.jawaban_id')
        ->leftJoin(DB::raw("(SELECT
            jawaban_id,
            SUM ( 1 ) AS jumlah_dukungan 
        FROM
            dukungan 
        WHERE
            soft_delete = 0
        GROUP BY
            jawaban_id) as dukungan"),'dukungan.jawaban_id','=','jawaban.jawaban_id')
        ->leftJoin(DB::raw("(SELECT
            pengguna_id, jawaban_id 
        FROM
            dukungan 
        WHERE
            pengguna_id ".($pengguna_id ? " = '".$pengguna_id."'" : " IS NULL").") as dukungan_pengguna"),'dukungan_pengguna.jawaban_id','=','jawaban.jawaban_id')
        ->where('jawaban.soft_delete','=',0)
        ->select(
            'jawaban.*',
            'pengguna.nama as pengguna',
            DB::raw('COALESCE(komentar.jumlah_komentar,0) as jumlah_komentar'),
            DB::raw('COALESCE(dukungan.jumlah_dukungan,0) as jumlah_dukungan'),
            'dukungan_pengguna.pengguna_id as dukungan_pengguna_id'
        )
        ->take(20)
        ->orderBy('create_date','DESC');

        // return $fetch->toSql();die;

        // if($pengguna_id){
        //     $fetch->where('jawaban.pengguna_id','=',$pengguna_id);
        // }

        if($pertanyaan_id){
            $fetch->where('jawaban.pertanyaan_id','=',$pertanyaan_id);
        }

        if($jawaban_id){
            $fetch->where('jawaban.jawaban_id','=',$jawaban_id);
        }

        $fetch = $fetch->get();

        for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
            //cari komentar
            // $fetch[$iFetch]->komentar = [
            //     'rows' => [],
            //     'total' => 0
            // ];

            $fetch[$iFetch]->komentar = self::getKomentar($fetch[$iFetch]->jawaban_id);
            
        }

        $return['rows'] = $fetch;
        $return['result'] = sizeof($fetch);

        return $return;
    }

    static function getKomentar($jawaban_id = null, $induk_komentar_id = null){
        // $jawaban_id = $request->input('jawaban_id');

        $fetch = DB::connection('sqlsrv_2')->table('komentar')
        ->join('pengguna','pengguna.pengguna_id','=','komentar.pengguna_id')
        ->where('komentar.soft_delete','=',0)
        ->select(
            'komentar.*',
            'pengguna.nama as pengguna'
        )
        ->take(20)
        ->orderBy('create_date','DESC');

        if($jawaban_id){
            $fetch->where('jawaban_id','=', $jawaban_id);
        }

        $fetch = $fetch->get();

        $return['rows'] = $fetch;
        $return['result'] = sizeof($fetch);

        return $return;
    }

    static public function simpanJawaban(Request $request){
        // return "oke";
        $konten = $request->input('konten');
        $publikasi = $request->input('publikasi');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_id = $request->input('pertanyaan_id');
        $jawaban_id  = self::generateUUID();
        $return = array();

        $insert = DB::connection('sqlsrv_2')->table('jawaban')->insert([
            'jawaban_id' => $jawaban_id,
            'konten' => $konten,
            'publikasi' => $publikasi,
            'pengguna_id' => $pengguna_id,
            'pertanyaan_id' => $pertanyaan_id
        ]);

        if($insert){
            $return['sukses'] = true;
            $return['rows'] = DB::connection('sqlsrv_2')->table('jawaban')->where('jawaban_id','=',$jawaban_id)->first();
        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
    }

    static public function simpanKomentar(Request $request){
        // return "oke";
        $konten = $request->input('konten');
        $pengguna_id = $request->input('pengguna_id');
        $jawaban_id = $request->input('jawaban_id');
        $komentar_id  = self::generateUUID();
        $return = array();

        $insert = DB::connection('sqlsrv_2')->table('komentar')->insert([
            'komentar_id' => $komentar_id,
            'konten' => $konten,
            'pengguna_id' => $pengguna_id,
            'jawaban_id' => $jawaban_id
        ]);

        if($insert){
            $return['sukses'] = true;
            $return['rows'] = DB::connection('sqlsrv_2')->table('komentar')->where('komentar_id','=',$komentar_id)->first();
        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
    }

    static public function simpanDukungan(Request $request){
        // return "oke";
        $pengguna_id = $request->input('pengguna_id');
        $jawaban_id = $request->input('jawaban_id');
        $jenis_dukungan_id = $request->input('jenis_dukungan_id');
        $dukungan_id = self::generateUUID();
        $return = array();

        $fetch_cek = DB::connection('sqlsrv_2')->table('dukungan')
        ->where('pengguna_id', '=', $pengguna_id)
        ->where('jawaban_id', '=', $jawaban_id)
        ->get();

        if(sizeof($fetch_cek) > 0){

            $insert = DB::connection('sqlsrv_2')->table('dukungan')->update([
                'last_update' => DB::raw("now()"),
                'soft_delete' => '0'
            ]);

        }else{

            $insert = DB::connection('sqlsrv_2')->table('dukungan')->insert([
                'dukungan_id' => $dukungan_id,
                'pengguna_id' => $pengguna_id,
                'jawaban_id' => $jawaban_id,
                'jenis_dukungan_id' => $jenis_dukungan_id
            ]);
        }


        if($insert){
            $return['sukses'] = true;
            $return['rows'] = DB::connection('sqlsrv_2')->table('dukungan')->where('dukungan_id','=',$dukungan_id)->first();

            //notifikasi
            

        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
    }

}

?>