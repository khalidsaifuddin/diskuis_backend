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
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
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

    static public function simpanPenggunaRuang(Request $request){
        $ruang_id = $request->input('ruang_id');
        $pengguna_id = $request->input('pengguna_id');
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
                'soft_delete' => $soft_delete   
            ]);

        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pengguna_ruang')->insert([
                'ruang_id' => $ruang_id,
                'pengguna_id' => $pengguna_id
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
                $linimasa_id = self::generateUUID();
                $linimasa = LinimasaController::simpanLinimasa($linimasa_id, $pengguna_id, 1, '','',$ruang_id,null);
    
                if($linimasa){
                    $return['sukses_linimasa'] = true;
                }else{
                    $return['sukses_linimasa'] = false;
                }
            } catch (\Throwable $th) {
                $return['sukses_linimasa'] = false;
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
        $ruang_id = self::generateUUID();
        
        $return = array();

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
            $return['ruang_id'] = $ruang_id;
            $return['rows'] = DB::connection('sqlsrv_2')->table('ruang')->where('ruang_id','=',$ruang_id)->first();
        }else{
            $return['sukses'] = false;
            $return['rows'] = [];
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
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('ruang')
        ->join('pengguna','pengguna.pengguna_id','=','ruang.pengguna_id')
        ->where('ruang.soft_delete','=',0)
        ->select(
            'ruang.*',
            'pengguna.nama as pengguna'
        )
        ->take(20)
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
                $request->merge(['pengguna_id'=>null, 'dengan_rows'=>'Y', 'ruang_id' => $fetch[$iFetch]->ruang_id]);
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
        $dengan_rows = $request->input('dengan_rows') ? $request->input('dengan_rows') : 'N';
        $return = array();

        $fetch = DB::connection('sqlsrv_2')->table('pengguna_ruang')
        ->join('pengguna','pengguna.pengguna_id','=','pengguna_ruang.pengguna_id')
        ->join('ruang','ruang.ruang_id','=','pengguna_ruang.ruang_id')
        ->where('pengguna_ruang.soft_delete','=',0)
        ->where('ruang.soft_delete','=',0)
        ->select(
            'pengguna_ruang.*',
            'pengguna.nama as pengguna',
            'ruang.nama as ruang',
            'pengguna.gambar as gambar'
        )
        // ->take(20)
        ->orderBy('room_master','DESC')
        ->orderBy('create_date','DESC')
        ;

        if($ruang_id){
            $fetch->where('ruang.ruang_id','=',$ruang_id);
        }
        
        if($pengguna_id){
            $fetch->where('pengguna_ruang.pengguna_id','=',$pengguna_id);
        }

        // return $fetch->toSql();die;

        if($dengan_rows == 'Y'){
            $fetch->take(20);
            $fetch = $fetch->get();
            
            // for ($iFetch=0; $iFetch < sizeof($fetch); $iFetch++) { 
            //     //loop for records
                
            // }
            
            $return['rows'] = $fetch;
            $return['total'] = sizeof($fetch);

        }else{
            $fetch = $fetch->count();
            $return['total'] = $fetch;
        }

        return $return;
    }
}