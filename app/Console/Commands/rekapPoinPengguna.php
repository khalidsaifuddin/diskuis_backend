<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Facades\JWTFactory;

class rekapPoinPengguna extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rekap:poin_pengguna';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // populate pengguna dulu
        $fetch_pengguna = DB::connection('sqlsrv_2')->table('pengguna')
        ->where('soft_delete','=',0)
        ->orderBy('create_date','ASC')
        ->skip(5641)
        ->get()
        ;

        for ($iPengguna=0; $iPengguna < sizeof($fetch_pengguna); $iPengguna++) { 
            //poin pengguna dari membuat kuis
            echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."]";

            $sql_buat_kuis = "INSERT INTO poin_pengguna (
                SELECT
                    uuid_generate_v4 () AS poin_pengguna_id,
                    kuis.create_date AS create_date,
                    now() AS last_update,
                    0 AS soft_delete,
                    2 AS jenis_poin_id,
                    100 AS nilai_poin,
                    'Poin dari pembuatan kuis ' || kuis.judul AS keterangan,
                    kuis.pengguna_id AS pengguna_id 
                FROM
                    kuis 
                    LEFT JOIN ( SELECT * FROM poin_pengguna WHERE soft_delete = 0 ) poins ON poins.pengguna_id = kuis.pengguna_id 
                    AND kuis.create_date = poins.create_date 
                WHERE
                    kuis.soft_delete = 0
                    AND kuis.pengguna_id = '".$fetch_pengguna[$iPengguna]->pengguna_id."'
                    AND kuis.publikasi = 1
                    AND poins.poin_pengguna_id IS NULL
                )";
            
            $exe_buat_kuis = DB::connection('sqlsrv_2')->statement($sql_buat_kuis);

            if($exe_buat_kuis){
                echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."] - poin dari buat kuis BERHASIL";
            }else{
                echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."] - poin dari buat kuis GAGAL";
            }




            $sql_ngerjain_kuis = "INSERT INTO poin_pengguna (
                SELECT
                    uuid_generate_v4 () AS poin_pengguna_id,
                    pengguna_kuis.create_date AS create_date,
                    now() AS last_update,
                    0 AS soft_delete,
                    1 AS jenis_poin_id,
                    COALESCE ( CEIL ( skor ), 0 ) AS nilai_poin,
                    'Poin dari pengerjaan kuis ' || kuis.judul || ' sesi ' || sesi_kuis.keterangan AS keterangan,
                    pengguna_kuis.pengguna_id AS pengguna_id 
                FROM
                    pengguna_kuis
                    JOIN kuis ON kuis.kuis_id = pengguna_kuis.kuis_id
                    JOIN sesi_kuis ON sesi_kuis.sesi_kuis_id = pengguna_kuis.sesi_kuis_id
                    LEFT JOIN ( SELECT * FROM poin_pengguna WHERE soft_delete = 0 ) poins ON poins.pengguna_id = pengguna_kuis.pengguna_id 
                    AND pengguna_kuis.create_date = poins.create_date 
                WHERE
                    pengguna_kuis.soft_delete = 0 
                    AND kuis.soft_delete = 0 
                    AND sesi_kuis.soft_delete = 0 
                    AND pengguna_kuis.pengguna_id = '".$fetch_pengguna[$iPengguna]->pengguna_id."'
                    AND poins.poin_pengguna_id IS NULL 
                    AND pengguna_kuis.pengguna_id != kuis.pengguna_id
                ORDER BY
                    pengguna_kuis.create_date ASC 
                )";
            
            $exe_ngerjain_kuis = DB::connection('sqlsrv_2')->statement($sql_ngerjain_kuis);

            if($exe_ngerjain_kuis){
                echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."] - poin dari ngerjain kuis BERHASIL";
            }else{
                echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."] - poin dari ngerjain kuis GAGAL";
            }




            
            $sql_jariyah = "INSERT INTO poin_pengguna (
                SELECT
                    uuid_generate_v4 () AS poin_pengguna_id,
                    pengguna_kuis.create_date AS create_date,
                    now() AS last_update,
                    0 AS soft_delete,
                    3 AS jenis_poin_id,
                    10 AS nilai_poin,
                    'Poin dari pengerjaan kuis ' || kuis.judul || ' oleh ' || pengguna.nama AS keterangan,
                    kuis.pengguna_id AS pengguna_id 
                FROM
                    kuis 
                    join pengguna_kuis on pengguna_kuis.kuis_id = kuis.kuis_id
                    join pengguna on pengguna.pengguna_id = pengguna_kuis.pengguna_id
                    LEFT JOIN ( SELECT * FROM poin_pengguna WHERE soft_delete = 0 ) poins ON poins.pengguna_id = kuis.pengguna_id
                    AND pengguna_kuis.create_date = poins.create_date 
                WHERE
                    kuis.soft_delete = 0
                    and pengguna_kuis.soft_delete = 0
                    and pengguna.soft_delete = 0
                    and kuis.pengguna_id = '".$fetch_pengguna[$iPengguna]->pengguna_id."'
                    AND pengguna_kuis.pengguna_id != kuis.pengguna_id
                    AND poins.poin_pengguna_id IS NULL 
                )";
            
            $exe_jariyah = DB::connection('sqlsrv_2')->statement($sql_jariyah);

            if($exe_jariyah){
                echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."] - poin dari jariyah kuis BERHASIL";
            }else{
                echo PHP_EOL."[".($iPengguna+1)."/".sizeof($fetch_pengguna)."] [".$fetch_pengguna[$iPengguna]->pengguna_id."] - poin dari jariyah kuis GAGAL";
            }
        }

    }
}
