<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class rekapPoinPenggunaHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rekap:poin_pengguna_harian';

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
        //
        $delete = DB::connection('sqlsrv_2')->table('rekap.poin_pengguna_harian')->delete();
        
        echo PHP_EOL."[DELETE]: ".($delete ? "Berhasil" : "Gagal");

        // if($delete){
            //berhasil delete
            $exe = DB::connection('sqlsrv_2')->statement("
            INSERT INTO rekap.poin_pengguna_harian (
                SELECT
                    pengguna.pengguna_id,
                    pengguna.nama,
                    pengguna.gambar,
                    aaa.poin,
                    now() AS tanggal_rekap_terakhir 
                FROM
                    ( SELECT pengguna_id, SUM ( nilai_poin ) AS poin FROM poin_pengguna GROUP BY pengguna_id ) aaa
                    JOIN pengguna ON pengguna.pengguna_id = aaa.pengguna_id 
                    AND pengguna.soft_delete = 0 
                ORDER BY
                poin DESC 
            )");

            echo PHP_EOL."[INSERT]: ".($exe ? "Berhasil" : "Gagal");

        // }else{
        //     //gagal delete
        //     echo PHP_EOL."[INSERT]: Gagal";
        // }


    }
}
