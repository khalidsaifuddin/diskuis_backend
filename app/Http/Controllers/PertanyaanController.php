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

    static function getPertanyaanPublik(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $sql = "SELECT * FROM
        (
        SELECT
            pertanyaan_ruang.pertanyaan_id AS pertanyaan_id_ruang,
            pertanyaan_sekolah.pertanyaan_id AS pertanyaan_id_sekolah,
            pertanyaan_ruang.ruang_id,
            pertanyaan_sekolah.sekolah_id,
            pengguna.nama AS nama,
            pengguna.gambar AS gambar,
            pertanyaan.* 
        FROM
            pertanyaan
        JOIN pengguna ON pengguna.pengguna_id = pertanyaan.pengguna_id
        LEFT JOIN pertanyaan_ruang ON pertanyaan_ruang.pertanyaan_id = pertanyaan.pertanyaan_id 
        AND pertanyaan_ruang.soft_delete = 0
        LEFT JOIN pertanyaan_sekolah ON pertanyaan_sekolah.pertanyaan_id = pertanyaan.pertanyaan_id 
        AND pertanyaan_sekolah.soft_delete = 0 
        WHERE
            pertanyaan.soft_delete = 0 
        AND pertanyaan.pengguna_id = '".$pengguna_id."' 
        AND pertanyaan.jenis_pertanyaan_aktivitas_id = 1 
        AND pertanyaan_ruang.pertanyaan_id IS NULL 
        AND pertanyaan_sekolah.pertanyaan_id IS NULL 
        UNION
        SELECT
            pertanyaan_ruang.pertanyaan_id AS pertanyaan_id_ruang,
            pertanyaan_sekolah.pertanyaan_id AS pertanyaan_id_sekolah,
            pertanyaan_ruang.ruang_id,
            pertanyaan_sekolah.sekolah_id,
            pengguna.nama AS nama,
            pengguna.gambar AS gambar,
            pertanyaan.* 
        FROM
            pertanyaan
        JOIN pengguna ON pengguna.pengguna_id = pertanyaan.pengguna_id
        LEFT JOIN (
        SELECT
            pertanyaan_ruang.* 
        FROM
            pertanyaan_ruang
            JOIN ruang_sekolah ON ruang_sekolah.ruang_id = pertanyaan_ruang.ruang_id 
        WHERE
            pertanyaan_ruang.soft_delete = 0 
            AND ruang_sekolah.soft_delete = 0 
            AND ruang_sekolah.sekolah_id IN ( SELECT sekolah_id FROM sekolah_pengguna WHERE pengguna_id = '".$pengguna_id."' AND soft_delete = 0 ) 
        ) pertanyaan_ruang ON pertanyaan_ruang.pertanyaan_id = pertanyaan.pertanyaan_id 
        AND pertanyaan_ruang.soft_delete = 0
        LEFT JOIN pertanyaan_sekolah ON pertanyaan_sekolah.pertanyaan_id = pertanyaan.pertanyaan_id 
        AND pertanyaan_sekolah.sekolah_id IN ( SELECT sekolah_id FROM sekolah_pengguna WHERE pengguna_id = '".$pengguna_id."' AND soft_delete = 0 ) 
        AND pertanyaan_sekolah.soft_delete = 0 
        WHERE
            pertanyaan.soft_delete = 0 
        AND pertanyaan.jenis_pertanyaan_aktivitas_id = 1
        AND (pertanyaan_ruang.pertanyaan_id IS NOT NULL OR pertanyaan_sekolah.pertanyaan_id IS NOT NULL)
        UNION
        SELECT
            null as pertanyaan_id_ruang,
            pertanyaan_sekolah.pertanyaan_id AS pertanyaan_id_sekolah,
            null as ruang_id,
            pertanyaan_sekolah.sekolah_id,
            pengguna.nama AS nama,
            pengguna.gambar AS gambar,
            pertanyaan.* 
        FROM
            pertanyaan
        JOIN pengguna ON pengguna.pengguna_id = pertanyaan.pengguna_id
        JOIN pengikut_pengguna ON pengikut_pengguna.pengguna_id = pengguna.pengguna_id
        AND pengikut_pengguna.soft_delete = 0 
        AND pengikut_pengguna.pengguna_id_pengikut = '".$pengguna_id."'
        LEFT JOIN pertanyaan_sekolah ON pertanyaan_sekolah.pertanyaan_id = pertanyaan.pertanyaan_id 
        AND pertanyaan_sekolah.soft_delete = 0
        LEFT JOIN pertanyaan_ruang ON pertanyaan_ruang.pertanyaan_id = pertanyaan.pertanyaan_id 
		AND pertanyaan_ruang.soft_delete = 0	 
        WHERE
            pertanyaan.soft_delete = 0 
        AND pertanyaan.jenis_pertanyaan_aktivitas_id = 1 
        
        AND pertanyaan_ruang.pertanyaan_id IS NULL
        AND pertanyaan_sekolah.pertanyaan_id IS NULL
        UNION
        SELECT
            pertanyaan_ruang.pertanyaan_id AS pertanyaan_id_ruang,
            null AS pertanyaan_id_sekolah,
            pertanyaan_ruang.ruang_id,
            null as sekolah_id,
            pengguna.nama AS nama,
            pengguna.gambar AS gambar,
            pertanyaan.* 
        FROM
            pertanyaan
        INNER JOIN pengguna ON pengguna.pengguna_id = pertanyaan.pengguna_id
        INNER JOIN pengguna_ruang ON pengguna_ruang.pengguna_id = pengguna.pengguna_id
        LEFT JOIN ref.jabatan_ruang AS jabatan ON jabatan.jabatan_ruang_id = pengguna_ruang.jabatan_ruang_id
        LEFT JOIN pertanyaan_ruang ON pertanyaan_ruang.pertanyaan_id = pertanyaan.pertanyaan_id 
        AND pertanyaan_ruang.ruang_id IN (SELECT ruang_id FROM pengguna_ruang WHERE pengguna_id = '".$pengguna_id."' AND soft_delete = 0 )  
        AND pertanyaan_ruang.soft_delete = 0 
        WHERE
            pertanyaan.soft_delete = 0 
        AND pertanyaan.jenis_pertanyaan_aktivitas_id = 1 
        AND pengguna_ruang.ruang_id IN (SELECT ruang_id FROM pengguna_ruang WHERE pengguna_id = '".$pengguna_id."' AND soft_delete = 0 ) 
        AND ( pertanyaan_ruang.pertanyaan_id IS NOT NULL ) 
        ) aaa
        order by aaa.create_date desc
        offset {$start} limit {$limit}";
        
        // $count = DB::connection('sqlsrv_2')->select(str_replace("SELECT * FROM", "SELECT count(1) as total FROM", $sql))[0]->total;

        // return $sql;die;

        $data = DB::connection('sqlsrv_2')->select($sql);

        for ($i=0; $i < sizeof($data); $i++) { 
            try {

                if($data[$i]->ruang_id){
                    $fetch_ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$data[$i]->ruang_id)->first();
                    $data[$i]->nama_ruang = $fetch_ruang->nama;
                }else{
                    $data[$i]->nama_ruang = null;
                }

            } catch (\Throwable $th) {
                $data[$i]->nama_ruang = null;
            }

            try {

                if($data[$i]->sekolah_id){
                    $fetch_ruang = DB::connection('sqlsrv_2')->table('sekolah')->where('sekolah_id','=',$data[$i]->sekolah_id)->first();
                    $data[$i]->nama_sekolah = $fetch_ruang->nama;
                }else{
                    $data[$i]->nama_sekolah = null;
                }

            } catch (\Throwable $th) {
                $data[$i]->nama_sekolah = null;
            }
        }

        return response(
            [
                'total' => sizeof($data),
                'rows' => $data
            ],
            200
        );
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
        $jenis_pertanyaan_aktivitas_id = $request->input('jenis_pertanyaan_aktivitas_id') ? $request->input('jenis_pertanyaan_aktivitas_id') : null;
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
            'pengguna.nama as nama',
            'pengguna.gambar as gambar',
            DB::raw('COALESCE(jawaban.jumlah_jawaban,0) as jumlah_jawaban'),
            DB::raw('COALESCE(pantauan.jumlah_pantauan,0) as jumlah_pantauan')
            // DB::raw("COALESCE((select sum(1) as jumlah_jawaban from jawaban where jawaban.soft_delete = 0 and jawaban.pertanyaan_id = pertanyaan.pertanyaan_id),0) as jumlah_jawaban")
        )
        ->take(20)
        ->orderBy('create_date','DESC');

        if($jenis_pertanyaan_aktivitas_id){

            if($jenis_pertanyaan_aktivitas_id === 2){
                $fetch->whereNull('pertanyaan.jenis_pertanyaan_aktivitas_id');
            }else{
                $fetch->where('pertanyaan.jenis_pertanyaan_aktivitas_id','=',$jenis_pertanyaan_aktivitas_id);
            }

        }else{
            $fetch->whereNull('pertanyaan.jenis_pertanyaan_aktivitas_id');
        }

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
                'pengguna.nama as nama',
                'pengguna.gambar as gambar',
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
    
    static function getPertanyaanSekolah(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $fetch = DB::connection('sqlsrv_2')->table('pertanyaan')
        // ->join('pertanyaan_sekolah','pertanyaan.pertanyaan_id','=','pertanyaan_sekolah.pertanyaan_id')
        ->join('pengguna','pengguna.pengguna_id','=','pertanyaan.pengguna_id')
        ->join('sekolah_pengguna','sekolah_pengguna.pengguna_id','=','pengguna.pengguna_id')
        ->join('ref.jabatan_sekolah as jabatan','jabatan.jabatan_sekolah_id','=','sekolah_pengguna.jabatan_sekolah_id')
        ->leftJoin('pertanyaan_sekolah', function($join) use ($sekolah_id)
        {
            $join->on('pertanyaan_sekolah.pertanyaan_id', '=', 'pertanyaan.pertanyaan_id');
            $join->on('pertanyaan_sekolah.sekolah_id', '=', DB::raw("'".$sekolah_id."'"));
            $join->on('pertanyaan_sekolah.soft_delete', '=', DB::raw("0"));
        })
        ->leftJoin(DB::raw("(
            SELECT
                pertanyaan_ruang.* 
            FROM
                pertanyaan_ruang
                JOIN ruang_sekolah ON ruang_sekolah.ruang_id = pertanyaan_ruang.ruang_id 
            WHERE
                pertanyaan_ruang.soft_delete = 0 
                AND ruang_sekolah.soft_delete = 0 
                AND ruang_sekolah.sekolah_id = '".$sekolah_id."' 
            ) pertanyaan_ruang"), function($join)
        {
            $join->on('pertanyaan_ruang.pertanyaan_id', '=', 'pertanyaan.pertanyaan_id');
            $join->on('pertanyaan_ruang.soft_delete', '=', DB::raw("0"));
        })
        // ->where('pertanyaan_sekolah.soft_delete','=',0)
        ->where('pertanyaan.soft_delete','=',0)
        // ->where('pertanyaan_sekolah.sekolah_id','=',$sekolah_id)
        ->where('sekolah_pengguna.sekolah_id','=',$sekolah_id)
        ->where('pertanyaan.jenis_pertanyaan_aktivitas_id','=',1)
        ->where(function ($query) use ($sekolah_id, $ruang_id){

            if($sekolah_id){

                $query->whereNotNull('pertanyaan_sekolah.pertanyaan_id')
                      ->orWhereNotNull('pertanyaan_ruang.pertanyaan_id');
                // $query->where('pertanyaan_sekolah.sekolah_id','=',$sekolah_id);
            
            }else if($ruang_id){

                $query->orWhereNotNull('pertanyaan_ruang.pertanyaan_id');
            
            }


        })
        ->select(
            'pengguna.*',
            // 'pertanyaan_sekolah.*',
            'pertanyaan.*',
            'jabatan.nama as jabatan_sekolah',
            'pertanyaan_sekolah.pertanyaan_id as pertanyaan_id_sekolah',
            'pertanyaan_ruang.pertanyaan_id as pertanyaan_id_ruang',
            'pertanyaan_ruang.ruang_id as ruang_id'
        )
        ->orderBy('pertanyaan.create_date','DESC')
        ;

        // return $fetch->toSql();die;

        $data = $fetch->skip($start)->take($limit)->get();

        for ($i=0; $i < sizeof($data); $i++) { 
            try {

                if($data[$i]->ruang_id){
                    $fetch_ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$data[$i]->ruang_id)->first();
                    $data[$i]->nama_ruang = $fetch_ruang->nama;
                }else{
                    $data[$i]->nama_ruang = null;
                }

            } catch (\Throwable $th) {
                $data[$i]->nama_ruang = null;
            }
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $data
            ],
            200
        );
    }

    static function getPertanyaanRuang(Request $request){
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 20;

        $fetch = DB::connection('sqlsrv_2')->table('pertanyaan')
        // ->join('pertanyaan_sekolah','pertanyaan.pertanyaan_id','=','pertanyaan_sekolah.pertanyaan_id')
        ->join('pengguna','pengguna.pengguna_id','=','pertanyaan.pengguna_id')
        ->join('pengguna_ruang','pengguna_ruang.pengguna_id','=','pengguna.pengguna_id')
        ->leftJoin('ref.jabatan_ruang as jabatan','jabatan.jabatan_ruang_id','=','pengguna_ruang.jabatan_ruang_id')
        // ->leftJoin('pertanyaan_sekolah', function($join) use ($sekolah_id)
        // {
        //     $join->on('pertanyaan_sekolah.pertanyaan_id', '=', 'pertanyaan.pertanyaan_id');
        //     $join->on('pertanyaan_sekolah.sekolah_id', '=', DB::raw("'".$sekolah_id."'"));
        //     $join->on('pertanyaan_sekolah.soft_delete', '=', DB::raw("0"));
        // })
        ->leftJoin('pertanyaan_ruang', function($join) use ($ruang_id)
        {
            $join->on('pertanyaan_ruang.pertanyaan_id', '=', 'pertanyaan.pertanyaan_id');
            $join->on('pertanyaan_ruang.ruang_id', '=', DB::raw("'".$ruang_id."'"));
            $join->on('pertanyaan_ruang.soft_delete', '=', DB::raw("0"));
        })
        // ->where('pertanyaan_sekolah.soft_delete','=',0)
        ->where('pertanyaan.soft_delete','=',0)
        // ->where('pertanyaan_sekolah.sekolah_id','=',$sekolah_id)
        // ->where('sekolah_pengguna.sekolah_id','=',$sekolah_id)
        ->where('pertanyaan.jenis_pertanyaan_aktivitas_id','=',1)
        ->where('pengguna_ruang.ruang_id','=',$ruang_id)
        ->where(function ($query) use ($sekolah_id, $ruang_id){

            if($sekolah_id){

                $query->whereNotNull('pertanyaan_sekolah.pertanyaan_id')
                      ->orWhereNotNull('pertanyaan_ruang.pertanyaan_id');
            
            }else if($ruang_id){

                $query->orWhereNotNull('pertanyaan_ruang.pertanyaan_id');
            
            }


        })
        ->select(
            'pengguna.*',
            // 'pertanyaan_sekolah.*',
            'pertanyaan.*',
            // 'jabatan.nama as jabatan_sekolah',
            // 'pertanyaan_sekolah.pertanyaan_id as pertanyaan_id_sekolah',
            'pertanyaan_ruang.pertanyaan_id as pertanyaan_id_ruang'
        )
        ->orderBy('pertanyaan.create_date','DESC')
        ;

        // return $fetch->toSql();die;

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static public function simpanPertanyaanSekolah(Request $request){
        $pertanyaan_id = $request->pertanyaan_id ? $request->pertanyaan_id : null;
        $sekolah_id = $request->sekolah_id ? $request->sekolah_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $pertanyaan_sekolah_id = $request->pertanyaan_sekolah_id ? $request->pertanyaan_sekolah_id : null;

        if($pertanyaan_sekolah_id){
            //update
        }else{
            //insert
            $pertanyaan_sekolah_id = self::generateUUID();

            $exe = DB::connection('sqlsrv_2')->table('pertanyaan_sekolah')
            ->insert([
                'pertanyaan_sekolah_id' => $pertanyaan_sekolah_id,
                'sekolah_id' => $sekolah_id,
                'pengguna_id' => $pengguna_id,
                'pertanyaan_id' => $pertanyaan_id,
                'create_date' => DB::raw("now()"),
                'last_update' => DB::raw("now()"),
                'soft_delete' => 0
            ]);
        }

        return response(
            [
                'sukses' => $exe ? true : false,
                'rows' => DB::connection('sqlsrv_2')->table('pertanyaan_sekolah')
                ->where('pertanyaan_sekolah_id','=',$pertanyaan_sekolah_id)
                ->get()
            ],
            200
        );
    }

    static public function simpanPertanyaan(Request $request){
        // return "oke";
        $judul = $request->input('judul');
        $konten = $request->input('konten');
        $publikasi = $request->input('publikasi');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $topik_pertanyaan_id = $request->input('topik_pertanyaan_id');
        $jenis_pertanyaan_aktivitas_id = $request->input('jenis_pertanyaan_aktivitas_id') ? $request->input('jenis_pertanyaan_aktivitas_id') : null;
        
        $return = array();

        if($pertanyaan_id){
            //edit
            $insert = DB::connection('sqlsrv_2')->table('pertanyaan')
            ->where('pertanyaan_id','=',$pertanyaan_id)
            ->update([
                'konten' => $konten,
                'judul' => $judul,
                'topik_pertanyaan_id' => $topik_pertanyaan_id,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);
            
        }else{
            //insert
            $pertanyaan_id = self::generateUUID();
            
            $insert = DB::connection('sqlsrv_2')->table('pertanyaan')->insert([
                'pertanyaan_id' => $pertanyaan_id,
                'judul' => $judul,
                'konten' => $konten,
                'publikasi' => $publikasi,
                'pengguna_id' => $pengguna_id,
                'topik_pertanyaan_id' => $topik_pertanyaan_id,
                'jenis_pertanyaan_aktivitas_id' => $jenis_pertanyaan_aktivitas_id
            ]);
        }


        if($insert){
            $return['sukses'] = true;
            $return['pertanyaan_id'] = $pertanyaan_id;
            $return['rows'] = DB::connection('sqlsrv_2')->table('pertanyaan')->where('pertanyaan_id','=',$pertanyaan_id)->first();
        }else{
            $return['sukses'] = false;
            $return['pertanyaan_id'] = $pertanyaan_id;
            $return['rows'] = [];
        }

        return $return;
    }

    static public function getJawaban(Request $request){
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $jawaban_id = $request->input('jawaban_id') ? $request->input('jawaban_id') : null;
        $start = $request->start ? $request->start : 0;
        $limit = $request->limit ? $request->limit : 30;
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
            'pengguna.gambar as gambar_pengguna',
            DB::raw('COALESCE(komentar.jumlah_komentar,0) as jumlah_komentar'),
            DB::raw('COALESCE(dukungan.jumlah_dukungan,0) as jumlah_dukungan'),
            'dukungan_pengguna.pengguna_id as dukungan_pengguna_id'
        )
        ->orderBy('create_date','ASC');

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

        $count = $fetch->count();

        $fetch = $fetch
        ->skip($start)
        ->take($limit)
        ->get();

        for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
            //cari komentar
            // $fetch[$iFetch]->komentar = [
            //     'rows' => [],
            //     'total' => 0
            // ];

            $fetch[$iFetch]->komentar = self::getKomentar($fetch[$iFetch]->jawaban_id);
            
        }

        $return['rows'] = $fetch;
        $return['result'] = $count;

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

    static public function hapusPertanyaan(Request $request){
        
        $pertanyaan_id  = $request->input('pertanyaan_id');
        $return = array();

        $insert = DB::connection('sqlsrv_2')->table('pertanyaan')
        ->where('pertanyaan_id','=',$pertanyaan_id)
        ->update([
            'soft_delete' => 1,
            'last_update' => DB::raw("now()")
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
    
    static public function hapusJawaban(Request $request){
        
        $jawaban_id  = $request->input('jawaban_id');
        $return = array();

        $insert = DB::connection('sqlsrv_2')->table('jawaban')
        ->where('jawaban_id','=',$jawaban_id)
        ->update([
            'soft_delete' => 1,
            'last_update' => DB::raw("now()")
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

            //update notifikasi

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