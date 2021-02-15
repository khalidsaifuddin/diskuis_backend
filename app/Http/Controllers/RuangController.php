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

use App\Http\Controllers\PertanyaanController;
use App\Http\Controllers\LinimasaController;

class RuangController extends Controller
{
    static public function generateUUID()
    {
        $uuid = DB::connection('sqlsrv_2')
        ->table(DB::raw('pengguna'))
        ->select(DB::raw('uuid_generate_v4() as uuid'))
        ->first();

        return $uuid->{'uuid'};
    }

    static public function generateRandomString($length = 10) {
        $characters = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return strtoupper($randomString);
    }

    static public function simpanPertanyaanRuang(Request $request){
        $ruang_id = $request->input('ruang_id');
        $pengguna_id = $request->input('pengguna_id');
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $arrPertanyaan = $request->input('arrPertanyaan') ? json_decode($request->input('arrPertanyaan')) : null;

        $berhasil = 0;
        $gagal = 0;
        $skip = 0;

        if($pertanyaan_id != null){
            $fetch_cek = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')
            ->where('ruang_id','=', $ruang_id)
            ->where('pertanyaan_id','=', $pertanyaan_id)
            ->get();

            if(sizeof($fetch_cek) > 0){
                //sudah ada
                $exe = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')
                ->where('ruang_id','=', $ruang_id)
                ->where('pertanyaan_id','=', $pertanyaan_id)
                ->update([
                    'last_update' => DB::raw('now()'),
                    'soft_delete' => 0
                ]); 

            }else{
                //belum ada
                $exe = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')->insert([
                    'ruang_id' => $ruang_id,
                    'pengguna_id' => $pengguna_id,
                    'pertanyaan_id' => $pertanyaan_id
                    // 'pertanyaan_id' => $arrPertanyaan[$iPertanyaan],
                ]);
            }

            if($exe){
                $berhasil++;
            }else{
                $gagal++;
            }

        }else{

            for ($iPertanyaan=0; $iPertanyaan < sizeof($arrPertanyaan); $iPertanyaan++) { 
    
                if($arrPertanyaan[$iPertanyaan]->status == true){
                    $fetch_cek = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')
                    ->where('ruang_id','=', $ruang_id)
                    ->where('pertanyaan_id','=', $arrPertanyaan[$iPertanyaan]->pertanyaan_id)
                    ->get();
    
                    if(sizeof($fetch_cek) > 0){
                        //sudah ada
                        $exe = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')
                        ->where('ruang_id','=', $ruang_id)
                        ->where('pertanyaan_id','=', $arrPertanyaan[$iPertanyaan]->pertanyaan_id)
                        ->update([
                            'last_update' => DB::raw('now()'),
                            'soft_delete' => 0
                        ]); 
    
                    }else{
                        //belum ada
                        $exe = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')->insert([
                            'ruang_id' => $ruang_id,
                            'pengguna_id' => $pengguna_id,
                            'pertanyaan_id' => $arrPertanyaan[$iPertanyaan]->pertanyaan_id
                            // 'pertanyaan_id' => $arrPertanyaan[$iPertanyaan],
                        ]);
                    }
    
                    if($exe){
                        $berhasil++;
                    }else{
                        $gagal++;
                    }
    
                }else{
    
                    //nggak dipilih. yaudah skip
                    $skip++;
                }
    
            }
        }


        return '{"status": true, "berhasil": '.$berhasil.', "gagal": '.$gagal.', "skip": '.$skip.'}';
    }

    static public function simpanPenggunaRuangBulk(Request $request){
        $ruang_id = $request->input('ruang_id');
        $jabatan_ruang_id = $request->input('jabatan_ruang_id') ? $request->input('jabatan_ruang_id') : '4';
        $soft_delete = $request->input('soft_delete') ? $request->input('soft_delete') : '0';

        $arrPengguna = json_decode($request->arrPengguna);

        $pengguna_ruang_berhasil = 0;
        $pengguna_ruang_gagal = 0;
        $linimasa_berhasil = 0;
        $linimasa_gagal = 0;
        $sekolah_pengguna_berhasil = 0;
        $sekolah_pengguna_gagal = 0;

        for ($i=0; $i < sizeof($arrPengguna); $i++) { 

            $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->where('ruang_id','=', $ruang_id)
            ->where('pengguna_id','=', $arrPengguna[$i]->pengguna_id)
            ->get();

            if(sizeof($fetch_cek) > 0){
                
                //sudah ada
                $exe = DB::connection('sqlsrv_2')->table('pengguna_ruang')
                ->where('ruang_id','=', $ruang_id)
                ->where('pengguna_id','=', $arrPengguna[$i]->pengguna_id)
                ->update([
                    'last_update' => DB::raw('now()'),
                    'soft_delete' => $soft_delete,
                    'jabatan_ruang_id' => $jabatan_ruang_id,
                    'no_absen' => ($request->input('no_absen') ? $request->input('no_absen') : null),
                    'jabatan_ruang_id' => $jabatan_ruang_id
                ]);
    
            }else{

                //belum ada
                $exe = DB::connection('sqlsrv_2')->table('pengguna_ruang')->insert([
                    'ruang_id' => $ruang_id,
                    'pengguna_id' => $arrPengguna[$i]->pengguna_id,
                    'no_absen' => ($request->input('no_absen') ? $request->input('no_absen') : null),
                    'jabatan_ruang_id' => $jabatan_ruang_id
                ]);

            }

            if($exe){
                
                $pengguna_ruang_berhasil++;

                //simpan linimasa
                try {
                    //code...
                    if(sizeof($fetch_cek) > 0){
                        //nggak disimpan
                        // $return['sukses_linimasa'] = false;
                        $linimasa_berhasil++;
                    }else{
                        //disimpan
                        $linimasa_id = self::generateUUID();
                        $linimasa = LinimasaController::simpanLinimasa($linimasa_id, $arrPengguna[$i]->pengguna_id, 1, '', '', $ruang_id, null);
            
                        if($linimasa){
                            $linimasa_berhasil++;
                        }else{
                            $linimasa_gagal++;
                        }
                    }

                } catch (\Throwable $th) {
                    $linimasa_gagal++;
                }

                try {

                    if((int)$jabatan_ruang_id === 3){
                        
                        $sql = "SELECT * FROM ruang_sekolah WHERE ruang_id = '".$ruang_id."'";
        
                        $data_ruang_sekolah = DB::connection('sqlsrv_2')->select(DB::raw($sql));
        
                        for ($iRuangSekolah=0; $iRuangSekolah < sizeof($data_ruang_sekolah); $iRuangSekolah++) { 
                            $cek_sekolah_pengguna = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                            ->where('sekolah_id','=', $data_ruang_sekolah[$iRuangSekolah]->sekolah_id)
                            ->where('pengguna_id','=', $arrPengguna[$i]->pengguna_id)
                            ->get();
                            ;
        
                            if(sizeof($cek_sekolah_pengguna) > 0){
                                //update
                                $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                                    ->where('sekolah_id','=', $data_ruang_sekolah[$iRuangSekolah]->sekolah_id)
                                    ->where('pengguna_id','=', $arrPengguna[$i]->pengguna_id)
                                    ->update([
                                        'soft_delete' => 0,
                                        'valid' => 1,
                                        'last_update' => DB::raw("now()")
                                    ]);
                            }else{
                                //insert
                                $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                                ->insert([
                                    'sekolah_pengguna_id' => RuangController::generateUUID(),
                                    'sekolah_id' => $data_ruang_sekolah[$iRuangSekolah]->sekolah_id,
                                    'pengguna_id' => $arrPengguna[$i]->pengguna_id,
                                    'pendiri' => 0,
                                    'administrator' => 0,
                                    'jabatan_sekolah_id' => 2,
                                    'valid' => 1,
                                    'create_date' => DB::raw('now()::timestamp(0)'),
                                    'last_update' => DB::raw('now()::timestamp(0)'),
                                    'soft_delete' => 0
                                ]);
                            }
                        }
                        
                        $sekolah_pengguna_berhasil++;
    
                    }else{
                        $sekolah_pengguna_gagal++;
                    }
    
    
                } catch (\Throwable $th) {
                    $sekolah_pengguna_gagal++;
                }

            }else{

                $pengguna_ruang_gagal++;

            }

        }

        $return = array();
        $return['pengguna_ruang'] = array(
            'berhasil' => $pengguna_ruang_berhasil,
            'gagal' => $pengguna_ruang_gagal
        );
        $return['linimasa'] = array(
            'berhasil' => $linimasa_berhasil,
            'gagal' => $linimasa_gagal
        );
        $return['sekolah_pengguna'] = array(
            'berhasil' => $sekolah_pengguna_berhasil,
            'gagal' => $sekolah_pengguna_gagal
        );

        return $return;

    }

    static public function simpanPenggunaRuang(Request $request){
        $ruang_id = $request->input('ruang_id');
        $pengguna_id = $request->input('pengguna_id');
        $jabatan_ruang_id = $request->input('jabatan_ruang_id') ? $request->input('jabatan_ruang_id') : '4';
        $soft_delete = $request->input('soft_delete') ? $request->input('soft_delete') : '0';

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna_ruang')
        ->where('ruang_id','=', $ruang_id)
        ->where('pengguna_id','=', $pengguna_id)
        ->get();

        if(sizeof($fetch_cek) > 0){
            //sudah ada
            $exe = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->where('ruang_id','=', $ruang_id)
            ->where('pengguna_id','=', $pengguna_id)
            ->update([
                'last_update' => DB::raw('now()'),
                'soft_delete' => $soft_delete,
                'jabatan_ruang_id' => $jabatan_ruang_id,
                'no_absen' => ($request->input('no_absen') ? $request->input('no_absen') : null),
                'jabatan_ruang_id' => $jabatan_ruang_id
            ]);

        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pengguna_ruang')->insert([
                'ruang_id' => $ruang_id,
                'pengguna_id' => $pengguna_id,
                'no_absen' => ($request->input('no_absen') ? $request->input('no_absen') : null),
                'jabatan_ruang_id' => $jabatan_ruang_id
            ]);
        }

        if($exe){
            $return['sukses'] = true;
            $return['ruang_id'] = $ruang_id;
            $return['pengguna_id'] = $pengguna_id;
            $return['rows'] = DB::connection('sqlsrv_2')->table('pengguna_ruang')->where('pengguna_id','=',$pengguna_id)->where('ruang_id','=',$ruang_id)->first();

            //simpan linimasa
            try {
                //code...
                if(sizeof($fetch_cek) > 0){
                    //nggak disimpan
                    $return['sukses_linimasa'] = false;
                }else{
                    //disimpan
                    $linimasa_id = self::generateUUID();
                    $linimasa = LinimasaController::simpanLinimasa($linimasa_id, $pengguna_id, 1, '','',$ruang_id,null);
        
                    if($linimasa){
                        $return['sukses_linimasa'] = true;
                    }else{
                        $return['sukses_linimasa'] = false;
                    }
                }

            } catch (\Throwable $th) {
                $return['sukses_linimasa'] = false;
            }

            //simpan sekolah pengguna kalau ruang ini tergabung juga ke sekolah
            try {

                if((int)$jabatan_ruang_id === 3){
                    
                    $sql = "SELECT * FROM ruang_sekolah WHERE ruang_id = '".$ruang_id."'";
    
                    $data_ruang_sekolah = DB::connection('sqlsrv_2')->select(DB::raw($sql));
    
                    for ($iRuangSekolah=0; $iRuangSekolah < sizeof($data_ruang_sekolah); $iRuangSekolah++) { 
                        $cek_sekolah_pengguna = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                        ->where('sekolah_id','=', $data_ruang_sekolah[$iRuangSekolah]->sekolah_id)
                        ->where('pengguna_id','=', $pengguna_id)
                        ->get();
                        ;
    
                        if(sizeof($cek_sekolah_pengguna) > 0){
                            //update
                            $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                                ->where('sekolah_id','=', $data_ruang_sekolah[$iRuangSekolah]->sekolah_id)
                                ->where('pengguna_id','=', $pengguna_id)
                                ->update([
                                    'soft_delete' => $soft_delete,
                                    'jabatan_sekolah_id' => 2,
                                    'valid' => 1,
                                    'last_update' => DB::raw("now()")
                                ]);
                        }else{
                            //insert
                            $exe = DB::connection('sqlsrv_2')->table('sekolah_pengguna')
                            ->insert([
                                'sekolah_pengguna_id' => RuangController::generateUUID(),
                                'sekolah_id' => $data_ruang_sekolah[$iRuangSekolah]->sekolah_id,
                                'pengguna_id' => $pengguna_id,
                                'pendiri' => 0,
                                'administrator' => 0,
                                'jabatan_sekolah_id' => 2,
                                'valid' => 1,
                                'create_date' => DB::raw('now()::timestamp(0)'),
                                'last_update' => DB::raw('now()::timestamp(0)'),
                                'soft_delete' => $soft_delete
                            ]);
                        }
                    }
                    
                    $return['sukses_sekolah_pengguna'] = $exe ? true : false;

                }else{
                    $return['sukses_sekolah_pengguna'] = false;
                }


            } catch (\Throwable $th) {
                $return['sukses_sekolah_pengguna'] = false;
            }

        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
        }

        return $return;
        
    }

    static public function hapusRuang(Request $request){
        $ruang_id = $request->ruang_id;

        $exe1 = DB::connection('sqlsrv_2')->table('ruang')
        ->where('ruang_id','=', $ruang_id)
        ->update([
            'soft_delete' => 1,
            'last_update' => DB::raw('now()::timestamp(0)')
        ]);

        if($exe1){
            $exe2 = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->where('ruang_id','=', $ruang_id)
            ->update([
                'soft_delete' => 1,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);

            $exe3 = DB::connection('sqlsrv_2')->table('sesi_kuis')
            ->where('ruang_id','=', $ruang_id)
            ->update([
                'soft_delete' => 1,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);
            
            $exe4 = DB::connection('sqlsrv_2')->table('pertanyaan_ruang')
            ->where('ruang_id','=', $ruang_id)
            ->update([
                'soft_delete' => 1,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);

            return response(
                [
                    '1' => ($exe1 ? true : false),
                    '2' => ($exe2 ? true : false),
                    '3' => ($exe3 ? true : false),
                    '4' => ($exe4 ? true : false),
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

    static public function simpanRuang(Request $request){
        // return "oke";
        $nama = $request->input('nama');
        $deskripsi = $request->input('deskripsi');
        $pengguna_id = $request->input('pengguna_id');
        $jenis_ruang_id = $request->input('jenis_ruang_id');
        $gambar_ruang = $request->input('gambar_ruang') ? $request->input('gambar_ruang') : rand(1,8).".jpg";
        $kode_ruang = self::generateRandomString(10);
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : self::generateUUID();
        
        $return = array();

        $fetch_cek = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$ruang_id)->get();

        if(sizeof($fetch_cek) > 0) {
            //sudah ada
            $insert = DB::connection('sqlsrv_2')->table('ruang')
            ->where('ruang_id','=',$ruang_id)
            ->update([
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'jenis_ruang_id' => $jenis_ruang_id,
                'gambar_ruang' => $gambar_ruang,
                'pengguna_id' => $pengguna_id,
                'soft_delete' => 0,
                'last_update' => DB::raw('now()::timestamp(0)')
            ]);

            if($insert){
    
                $return['sukses'] = true;
                $return['label'] = 'UPDATE';
                $return['ruang_id'] = $ruang_id;
                $return['rows'] = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$ruang_id)->first();
            }else{
                $return['sukses'] = false;
                $return['rows'] = [];
            }

        }else{
            //belum ada
            $insert = DB::connection('sqlsrv_2')->table('ruang')->insert([
                'ruang_id' => $ruang_id,
                'nama' => $nama,
                'deskripsi' => $deskripsi,
                'jenis_ruang_id' => $jenis_ruang_id,
                'gambar_ruang' => $gambar_ruang,
                'pengguna_id' => $pengguna_id,
                'kode_ruang' => $kode_ruang
            ]);
    
            if($insert){
    
                $insert = DB::connection('sqlsrv_2')->table('pengguna_ruang')->insert([
                    'pengguna_id' => $pengguna_id,
                    'ruang_id' => $ruang_id,
                    'create_date' => DB::raw('now()::timestamp(0)'),
                    'last_update' => DB::raw('now()::timestamp(0)'),
                    'soft_delete' => 0,
                    'room_master' => 1
                ]);
    
                $return['sukses'] = true;
                $return['label'] = 'INSERT';
                $return['ruang_id'] = $ruang_id;
                $return['rows'] = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$ruang_id)->first();
            }else{
                $return['sukses'] = false;
                $return['rows'] = [];
            }
        }

        return $return;
    }

    public function upload(Request $request)
    {
        $data = $request->all();
        $file = $data['image'];
        $guid = $data['guid'];
        // $pengguna_id = $data['pengguna_id'];
        // $jenis = $data['jenis'];

        if(($file == 'undefined') OR ($file == '')){
            return response()->json(['msg' => 'tidak_ada_file']);
        }

        $ext = $file->getClientOriginalExtension();
        $name = $file->getClientOriginalName();

        // return $ext;die;

        // $uuid = DB::connection('sqlsrv_2')->select(DB::raw("select uuid_generate_v4() as uui from ruang limit 1"));

        $destinationPath = base_path('/public/assets/berkas');
        // $upload = $file->move($destinationPath, $name);
        $upload = $file->move($destinationPath, $guid.".".$ext);

        // $ext = $file->getClientOriginalExtension();
        // $name = $file->getClientOriginalName();

        // $destinationPath = base_path('/public/assets/berkas');

        $msg = $upload ? 'sukses' : 'gagal';

        if($upload){
            // $execute = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->update([
            //     $jenis => "/assets/berkas/".$name
            // ]);

            // if($execute){
            return response(['msg' => $msg, 'filename' => "/assets/berkas/".$guid.".".$ext]);
            // }
        }

    }

    static public function getRuangDiikuti(Request $request){
        $pengguna_id = $request->input('pengguna_id');

        $fetch = DB::connection('sqlsrv_2')->table('pengguna_ruang')
        ->join('ruang','ruang.ruang_id','=','pengguna_ruang.ruang_id')
        ->where('pengguna_ruang.pengguna_id','=',$pengguna_id)
        ->where('pengguna_ruang.soft_delete','=',DB::raw("0"))
        ->where('ruang.soft_delete','=',DB::raw("0"))
        ->select(
            'ruang.*',
            'pengguna_ruang.create_date as tanggal_ikut'
        );

        $fetch = $fetch->get();

        $return = array();
        $return['rows'] = $fetch;

        if(sizeof($fetch) > 0){

            for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
                //loop for records
                $request->merge(['pengguna_id'=>null, 'dengan_rows'=>'Y', 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $fetch[$iFetch]->ruang = self::getPenggunaRuang($request);

                $request->merge(['pengguna_id'=>null, 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $fetch[$iFetch]->pertanyaan = PertanyaanController::getPertanyaan($request);

                $request->merge(['pengguna_id'=>$pengguna_id, 'dengan_rows'=>'Y', 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $pengguna = self::getPenggunaRuang($request);

                // $fetch[$iFetch]->self_pengguna_ruang = $pengguna;

                if($pengguna['total'] > 0){
                    $fetch[$iFetch]->self_pengguna_ruang = $pengguna['rows'][0];
                }else{
                    $fetch[$iFetch]->self_pengguna_ruang = (object)[];
                }
            }

        }

        $return['total'] = sizeof($fetch);

        return $return;
    }

    static public function getRuang(Request $request){
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $pertanyaan_id = $request->input('pertanyaan_id') ? $request->input('pertanyaan_id') : null;
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : null;
        $kode_ruang = $request->input('kode_ruang') ? $request->input('kode_ruang') : null;
        $jenis_ruang_id = $request->input('jenis_ruang_id') ? $request->input('jenis_ruang_id') : null;
        $start = $request->input('start') ? $request->input('start') : 0;
        $limit = $request->input('limit') ? $request->input('limit') : 20;
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('ruang')
        ->join('pengguna','pengguna.pengguna_id','=','ruang.pengguna_id')
        ->where('ruang.soft_delete','=',0)
        ->select(
            'ruang.*',
            'pengguna.nama as pengguna'
        )
        ->skip($start)
        ->take($limit)
        ->orderBy('create_date','DESC');

        if($ruang_id){
            $fetch->where('ruang.ruang_id','=',$ruang_id);
        }
        
        if($kode_ruang){
            $fetch->where('ruang.kode_ruang','=',$kode_ruang);
        }
        
        if($pengguna_id){
            $fetch->where('ruang.pengguna_id','=',$pengguna_id);
        }
        
        if($jenis_ruang_id){
            $fetch->where('ruang.jenis_ruang_id','=',$jenis_ruang_id);
        }

        // return $fetch->toSql();die;

        $fetch = $fetch->get();

        // return $fetch;die;

        // if(sizeof($fetch) > 1){

            for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
                //loop for records
                $request->merge(['pengguna_id'=>null, 'limit' => 1000,'dengan_rows'=>'Y', 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $fetch[$iFetch]->ruang = self::getPenggunaRuang($request);

                $request->merge(['pengguna_id'=>null, 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $fetch[$iFetch]->pertanyaan = PertanyaanController::getPertanyaan($request);

                $request->merge(['pengguna_id'=>null, 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $fetch[$iFetch]->sesi_kuis = KuisController::getSesiKuis($request);

                $request->merge(['pengguna_id'=>$pengguna_id, 'dengan_rows'=>'Y', 'ruang_id' => $fetch[$iFetch]->ruang_id]);
                $pengguna = self::getPenggunaRuang($request);

                // $fetch[$iFetch]->self_pengguna_ruang = $pengguna;

                if($pengguna['total'] > 0){
                    $fetch[$iFetch]->self_pengguna_ruang = $pengguna['rows'][0];
                }else{
                    $fetch[$iFetch]->self_pengguna_ruang = (object)[];
                }
            }

        // }else if(sizeof($fetch) == 1){
        //     $request->merge(['pengguna_id'=>null, 'ruang_id' => $fetch[$iFetch]->ruang_id]);
        //     $fetch[$iFetch]->sesi_kuis = KuisController::getSesiKuis($request);

        //     $request->merge(['pengguna_id'=>$pengguna_id, 'dengan_rows'=>'Y', 'ruang_id' => $ruang_id]);
        //     $pengguna = self::getPenggunaRuang($request);


        //     if($pengguna['total'] > 0){
        //         $fetch[0]->self_pengguna_ruang = $pengguna['rows'][0];
        //     }else{
        //         $fetch[0]->self_pengguna_ruang = (object)[];
        //     }

        // }else{

        // }


        $return['rows'] = $fetch;
        $return['result'] = sizeof($fetch);

        return $return;
    }

    static public function getPenggunaRuang(Request $request){
        $ruang_id = $request->input('ruang_id') ? $request->input('ruang_id') : null;
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $keyword = $request->input('keyword') ? $request->input('keyword') : null;
        $dengan_rows = $request->input('dengan_rows') ? $request->input('dengan_rows') : 'N';
        $start = $request->input('start') ? $request->input('start') : 0;
        $limit = $request->input('limit') ? $request->input('limit') : 20;
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('pengguna_ruang')
        ->join('pengguna','pengguna.pengguna_id','=','pengguna_ruang.pengguna_id')
        ->join('ruang','ruang.ruang_id','=','pengguna_ruang.ruang_id')
        ->leftJoin('ref.jabatan_ruang as jabatan_ruang','jabatan_ruang.jabatan_ruang_id','=','pengguna_ruang.jabatan_ruang_id')
        ->where('pengguna_ruang.soft_delete','=',0)
        ->where('ruang.soft_delete','=',0)
        ->select(
            'pengguna_ruang.*',
            'pengguna.nama as pengguna',
            'ruang.nama as ruang',
            'pengguna.gambar as gambar',
            'jabatan_ruang.nama as jabatan_ruang'
        )
        // ->take(20)
        ->orderBy('room_master','DESC')
        ->orderBy('jabatan_ruang_id','ASC')
        ->orderBy('create_date','DESC')
        ;

        if($ruang_id){
            $fetch->where('ruang.ruang_id','=',$ruang_id);
        }
        
        if($pengguna_id){
            $fetch->where('pengguna_ruang.pengguna_id','=',$pengguna_id);
        }
        
        if($keyword){
            $fetch->where('pengguna.nama','ilike',DB::raw("'%".$keyword."%'"));
        }

        // return $fetch->toSql();die;

        if($dengan_rows == 'Y'){
            $count = $fetch->count();
            $fetch->skip($start)->take($limit);
            $fetch = $fetch->get();
            
            // for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
            //     //loop for records
                
            // }
            
            $return['rows'] = $fetch;
            $return['total'] = $count;

        }else{
            $fetch = $fetch->count();
            $return['total'] = $fetch;
        }

        return $return;
    }

    static function getKehadiranRuangSiswa(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')
        ->table('kehadiran_ruang_siswa')
        ->where('soft_delete','=',0)
        ->select(
            'kehadiran_ruang_siswa.*'
        )
        ;

        if($pengguna_id){
            $fetch->where('kehadiran_ruang_siswa.pengguna_id','=',$pengguna_id);
        }
        
        if($ruang_id){
            $fetch->where('kehadiran_ruang_siswa.ruang_id','=',$ruang_id);
        }
        
        if($tanggal){
            $fetch->where('kehadiran_ruang_siswa.tanggal','=',$tanggal);
        }

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanKehadiranRuangSiswa(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $tanggal = $request->tanggal ? $request->tanggal : null;
        $kehadiran_ruang_siswa_id = $request->kehadiran_ruang_siswa_id ? $request->kehadiran_ruang_siswa_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('kehadiran_ruang_siswa')
        ->where('soft_delete','=',0)
        ->where('kehadiran_ruang_siswa.ruang_id','=',$ruang_id)
        ->where('kehadiran_ruang_siswa.pengguna_id','=',$pengguna_id)
        ->where('kehadiran_ruang_siswa.tanggal','=',$tanggal)
        ->get()
        ;

        if(sizeof($fetch_cek) > 0){
            //sudah ada recordnya
            $exe = DB::connection('sqlsrv_2')->table('kehadiran_ruang_siswa')
            ->where('soft_delete','=',0)
            ->where('kehadiran_ruang_siswa.ruang_id','=',$ruang_id)
            ->where('kehadiran_ruang_siswa.pengguna_id','=',$pengguna_id)
            ->where('kehadiran_ruang_siswa.tanggal','=',$tanggal)
            ->update([
                'ruang_id' => $ruang_id,
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
            $exe = DB::connection('sqlsrv_2')->table('kehadiran_ruang_siswa')
            ->insert([
                'kehadiran_ruang_siswa_id' => $kehadiran_ruang_siswa_id,
                'ruang_id' => $ruang_id,
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
                'rows' => DB::connection('sqlsrv_2')->table('kehadiran_ruang_siswa')
                ->where('kehadiran_ruang_siswa.ruang_id','=',$ruang_id)
                ->where('kehadiran_ruang_siswa.pengguna_id','=',$pengguna_id)
                ->where('kehadiran_ruang_siswa.tanggal','=',$tanggal)
                ->get()
            ],
            200
        );
    }

    public function simpanJabatanRuang(Request $request){
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $jabatan_ruang_id = $request->jabatan_ruang_id ? $request->jabatan_ruang_id : null;

        try {
            //code...
            $exe = DB::connection('sqlsrv_2')->table('pengguna_ruang')
            ->where('ruang_id','=',$ruang_id)
            ->where('pengguna_id','=',$pengguna_id)
            ->update([
                'jabatan_ruang_id' => $jabatan_ruang_id,
                'soft_delete' => 0,
                'last_update' => DB::raw("now()")
            ])
            ;

        } catch (\Throwable $th) {
            //throw $th;
            $exe = false;
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('pengguna_ruang')
                ->where('ruang_id','=',$ruang_id)
                ->where('pengguna_id','=',$pengguna_id)
                ->where('soft_delete','=',0)
                ->get()
            ],
            200
        );

        
    }

    static function getPengaturanRuang(Request $request){
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('pengaturan_ruang')
        ->where('pengaturan_ruang.ruang_id','=',$ruang_id)
        ->where('pengaturan_ruang.soft_delete','=',0)
        ;

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function simpanPengaturanRuang(Request $request){
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;
        $pengaturan_ruang_id = $request->pengaturan_ruang_id ? $request->pengaturan_ruang_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengaturan_ruang')
        ->where('pengaturan_ruang.ruang_id','=',$ruang_id)
        ->where('pengaturan_ruang.soft_delete','=',0)
        ->get();

        if(sizeof($fetch_cek) > 0){
            //siudah ada
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_ruang')
            ->where('pengaturan_ruang.ruang_id','=',$ruang_id)
            ->where('pengaturan_ruang.soft_delete','=',0)
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
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_ruang')
            ->insert([
                'pengaturan_ruang_id' => $pengaturan_ruang_id,
                'ruang_id' => $ruang_id,
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
                'rows' => DB::connection('sqlsrv_2')->table('pengaturan_ruang')
                ->where('pengaturan_ruang.ruang_id','=',$ruang_id)
                ->where('pengaturan_ruang.soft_delete','=',0)
                ->get()
            ],
            200
        );
    }

    static function kehadiranRekapRuangSiswa(Request $request){
        $tanggal_terakhir = $request->tanggal_terakhir ? $request->tanggal_terakhir : 30;
        $bulan = $request->bulan ? $request->bulan : 1;
        $tahun = $request->tahun ? $request->tahun : 2020;
        $ruang_id = $request->ruang_id ? $request->ruang_id : null;

        $sql = "SELECT
            extract(dow from  bulans.tanggal_bulan)+1 as urut_hari,
            bulans.*,
	        hadir.*
        FROM
            ( SELECT d :: DATE AS tanggal_bulan FROM generate_series ( '".$tahun."-".$bulan."-1', '".$tahun."-".$bulan."-".$tanggal_terakhir."', '1 day' :: INTERVAL ) d ) bulans
            LEFT JOIN (
                SELECT
                    kehadiran_ruang_siswa.ruang_id,
                    kehadiran_ruang_siswa.tanggal,
                    SUM ( 1 ) AS total,
                    MAX ( gurus.total_guru ) AS total_guru,
                    (
                        SUM ( 1 ) / CAST(SUM ( gurus.total_guru ) as float) * 100
                    ) as persen
                FROM
                    kehadiran_ruang_siswa
                    LEFT JOIN (
                    SELECT
                        pengguna_ruang.ruang_id,
                        SUM ( 1 ) AS total_guru 
                    FROM
                        pengguna_ruang 
                    WHERE
                        pengguna_ruang.jabatan_ruang_id = 3 
                        AND pengguna_ruang.soft_delete = 0 
                        AND pengguna_ruang.ruang_id = '".$ruang_id."' 
                    GROUP BY
                        pengguna_ruang.ruang_id 
                    ) gurus ON gurus.ruang_id = kehadiran_ruang_siswa.ruang_id 
                WHERE
                    kehadiran_ruang_siswa.soft_delete = 0 
                    AND kehadiran_ruang_siswa.ruang_id = '".$ruang_id."' 
                GROUP BY
                    kehadiran_ruang_siswa.ruang_id,
                    kehadiran_ruang_siswa.tanggal
            ) hadir on hadir.tanggal = bulans.tanggal_bulan
            ";

        return DB::connection('sqlsrv_2')->select(DB::raw($sql));
    }
    
    public function unduhLaporanKehadiranRuangSiswa(Request $request){
        // return "oke";

        $arrHari = json_decode($request->hari_terpilih);

        $str_kolom = '';

        for ($iHari=0; $iHari < sizeof($arrHari); $iHari++) { 
            $str_kolom .= ",sum(case when tanggal = '".$arrHari[$iHari]."' then 1 else 0 end) as masuk_".str_replace("-","",$arrHari[$iHari]);
        }

        $fetch_pengguna_ruang = DB::connection('sqlsrv_2')->table('pengguna_ruang')
        ->join('pengguna','pengguna.pengguna_id','=','pengguna_ruang.pengguna_id')
        ->leftJoin(DB::raw("(SELECT
            pengguna_id,
            ruang_id
            ".$str_kolom."
        FROM
            kehadiran_ruang_siswa 
        WHERE
            soft_delete = 0 
            AND ruang_id = '".$request->ruang_id."'
            group by pengguna_id, ruang_id) as kehadiran"), 'kehadiran.pengguna_id','=','pengguna_ruang.pengguna_id')
        ->where('pengguna_ruang.soft_delete','=',0)
        ->where('pengguna_ruang.ruang_id','=',$request->ruang_id)
        ->where('pengguna_ruang.jabatan_ruang_id','=',3)
        ->orderBy('pengguna_ruang.no_absen','ASC')
        ->orderBy('pengguna.nama','ASC')
        ->select(
            'pengguna_ruang.*',
            'pengguna.nama',
            'kehadiran.*'
        )
        ->get()
        ;

        $fetch_ruang = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$request->ruang_id)->first();

        // return $fetch_pengguna_ruang;die;

        // return $request->hari_terpilih;
        // $fetch = [];

        return view('excel/TemplateRekapAbsen', [
            'return' => $fetch_pengguna_ruang, 
            'ruang_id' => $request->ruang_id, 
            'nama_ruang' => $fetch_ruang->nama,
            'hari_terpilih' => $request->hari_terpilih
        ]);

    }
}