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

// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Http\Controllers\RuangController;

class PenggunaController extends Controller
{
    static public function generateUUID()
    {
        $uuid = DB::connection('sqlsrv_2')
        ->table(DB::raw('pengguna'))
        ->select(DB::raw('uuid_generate_v4() as uuid'))
        ->first();

        return $uuid->{'uuid'};
    }

    static function simpanPengaturanPengguna(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $pengaturan_pengguna_id = $request->pengaturan_pengguna_id ? $request->pengaturan_pengguna_id : RuangController::generateUUID();

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengaturan_pengguna')
        ->where('pengaturan_pengguna.pengguna_id','=',$pengguna_id)
        ->where('pengaturan_pengguna.soft_delete','=',0)
        ->get();

        if(sizeof($fetch_cek) > 0){
            //siudah ada
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_pengguna')
            ->where('pengaturan_pengguna.pengguna_id','=',$pengguna_id)
            ->where('pengaturan_pengguna.soft_delete','=',0)
            ->update([
                'tampilkan_beranda_sekolah' => $request->tampilkan_beranda_sekolah ? $request->tampilkan_beranda_sekolah : '0',
                'hide_menu_sekolah' => $request->hide_menu_sekolah ? $request->hide_menu_sekolah : '0',
                'custom_logo_sekolah' => $request->custom_logo_sekolah ? $request->custom_logo_sekolah : '0',
                'last_update' => DB::raw('now()::timestamp(0)'),
            ]);
        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pengaturan_pengguna')
            ->insert([
                'pengaturan_pengguna_id' => $pengaturan_pengguna_id,
                'pengguna_id' => $pengguna_id,
                'tampilkan_beranda_sekolah' => $request->tampilkan_beranda_sekolah ? $request->tampilkan_beranda_sekolah : '0',
                'hide_menu_sekolah' => $request->hide_menu_sekolah ? $request->hide_menu_sekolah : '0',
                'custom_logo_sekolah' => $request->custom_logo_sekolah ? $request->custom_logo_sekolah : '0',
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'soft_delete' => '0',    
            ]);
        }

        return response(
            [
                'sukses' => ($exe ? true : false),
                'rows' => DB::connection('sqlsrv_2')->table('pengaturan_pengguna')
                ->where('pengaturan_pengguna.pengguna_id','=',$pengguna_id)
                ->where('pengaturan_pengguna.soft_delete','=',0)
                ->get()
            ],
            200
        );
    }

    static function getPengaturanPengguna(Request $request){
        $pengguna_id = $request->pengguna_id ? $request->pengguna_id : null;
        $limit = $request->limit ? $request->limit : 20;
        $start = $request->start ? $request->start : 0;

        $fetch = DB::connection('sqlsrv_2')->table('pengaturan_pengguna')
        ->where('pengaturan_pengguna.pengguna_id','=',$pengguna_id)
        ->where('pengaturan_pengguna.soft_delete','=',0)
        ;

        return response(
            [
                'total' => $fetch->count(),
                'rows' => $fetch->skip($start)->take($limit)->get()
            ],
            200
        );
    }

    static function daftarPengguna(Request $request){
        $username = $request->input('username');
        $password = $request->input('password');
        $nama = $request->input('nama');
        $pengguna_id = self::generateUUID();
        
        $return = array();

        $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna')
        ->where('username','=',$username)
        ->where('soft_delete','=',0)
        ->get();

        if(sizeof($fetch_cek) > 0){
            //sudah ada
            $return['sukses'] = false;
            $return['pesan'] = 'Pengguna dengan username '.$username.' telah terdaftar. Silakan gunakan username yang lain';

        }else{
            //belum ada
            $exe = DB::connection('sqlsrv_2')->table('pengguna')->insert([
                'pengguna_id' => $pengguna_id,
                'nama' => $nama,
                'username' => $username,
                'password' => md5($password),
                'soft_delete' => '0',
                'gambar' => 'https://be.diskuis.id/assets/img/diskuis_avatar.jpg',
                'aktif' => '1',
                'create_date' => DB::raw('now()::timestamp(0)'),
                'last_update' => DB::raw('now()::timestamp(0)'),
                'verified' => '10'
            ]);

            if($exe){
                $return['sukses'] = true;
                $return['pesan'] = 'Berhasil menambah pengguna baru';
                $return['username'] = $username;
                $return['password'] = $password;
            }else{
                $return['sukses'] = true;
                $return['pesan'] = 'Gagal menambah pengguna baru';
            }
        }

        return $return;
    }

    public function buatPengguna(Request $request){
        $data = $request->input('data') ? $request->input('data') : null;

        // return $data['username'];die;
        $uuid = self::generateUUID();
        // return $uuid;die;

        try {

            $execute = DB::connection('sqlsrv_2')->table('pengguna')->insert([
                'pengguna_id' => $uuid,
                'username' => $data['username'],
                'nama' => $data['nama'],
                'gambar' => $data['gambar'],
                'create_date' => date('Y-m-d H:i:s'),
                'last_update' => date('Y-m-d H:i:s'),
                'last_sync' => '1990-01-01 01:01:01',
                'soft_delete' => '0',
                'verified' => '0',
                'aktif' => '1',
                'akun_google' => $data['username']
            ]);

            if($execute){
                // return array("status" => "berhasil");
                $user = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$uuid)->get();

                $return = array();
                $return['total'] = sizeof($user);
                $return['rows'] = $user;

                return $return;
            }else{
                // return array("status" => "gagal query");

                $return = array();
                $return['total'] = 0;
                $return['rows'] = [];

                return $return;
            }
            
        } catch (\Throwable $th) {
            // return array("status" => "gagal exception", "exception" => $th);
            $return = array();
            $return['total'] = 0;
            $return['rows'] = [];

            return $return;
        }
    }

    public function simpanPengguna(Request $request) { 
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $username = $request->input('username') ? $request->input('username') : null;
        $data = $request->input('data') ? $request->input('data') : null;

        // if(array_key_exists('password_lama', $data)){
        //    if() 
        // }
        
        if(array_key_exists('password_lama', $data)){
            $fetch_cek = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->first();

            if(md5($data['password_lama']) !== $fetch_cek->password){
                return array("status" => "gagal", 'pesan' => 'password_tidak_sama');
                die;
            }
        }


        unset($data['password_lama']);
        unset($data['agama']);
        unset($data['peran']);
        unset($data['wilayah']);
        unset($data['kode_wilayah_provinsi']);
        unset($data['kode_wilayah_kabupaten']);

        // return $data;die;

        if(array_key_exists('password', $data)){
            $data['password'] = md5($data['password']);
            unset($data['password_ulang']);
        }

        // return var_dump($data);die;

        try {
            //code...
            if($pengguna_id){
                $execute = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id', '=', DB::raw("'".$pengguna_id."'"))->update($data);

                // return $execute;die;
            }else{
                if($username){
                    $execute = DB::connection('sqlsrv_2')->table('pengguna')->where('username', '=', DB::raw("'".$username."'"))->update($data);
                }
            }
    
            
            if($execute){
                return array("status" => "berhasil", "row" => DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->get());
            }else{
                return array("status" => "gagal");
            }

        } catch (\Throwable $th) {
            return array("status" => "gagal exception");
        }


    }

    public function getPengguna(Request $request) { 
        $pengguna_id = $request->input('pengguna_id') ? $request->input('pengguna_id') : null;
        $username = $request->input('username') ? $request->input('username') : null;
        $start = $request->input('start') ? $request->input('start') : 0;
        $limit = $request->input('limit') ? $request->input('limit') : 20;
        $keyword = $request->input('keyword') ? $request->input('keyword') : null;
        $pengguna_id_pengikut = $request->input('pengguna_id_pengikut') ? $request->input('pengguna_id_pengikut') : null;

        if($pengguna_id){
            $user = DB::connection('sqlsrv_2')
            ->table(DB::raw('pengguna'))
            ->leftJoin('ref.peran as peran','peran.peran_id','=','pengguna.peran_id')
            ->leftJoin('ref.mst_wilayah as wilayah', 'wilayah.kode_wilayah','=','pengguna.kode_wilayah')
            ->where('pengguna_id', '=', DB::raw("'".$pengguna_id."'"))
            ->where('soft_delete', '=', 0)
            ->select(
                'pengguna.*',
                'peran.nama as peran',
                'wilayah.nama as wilayah'
            )
            ->get();
        }

        if($username){
            $user = DB::connection('sqlsrv_2')
            ->table(DB::raw('pengguna'))
            ->leftJoin('ref.peran as peran','peran.peran_id','=','pengguna.peran_id')
            ->leftJoin('ref.mst_wilayah as wilayah', 'wilayah.kode_wilayah','=','pengguna.kode_wilayah')
            ->where('username', '=', DB::raw("'".$username."'"))
            ->where('soft_delete', '=', 0)
            ->select(
                'pengguna.*',
                'peran.nama as peran',
                'wilayah.nama as wilayah'
            )
            ->get();
        }

        if(!$pengguna_id && !$username){
            $builder = DB::connection('sqlsrv_2')
            ->table(DB::raw('pengguna'))
            ->leftJoin('ref.peran as peran','peran.peran_id','=','pengguna.peran_id')
            ->leftJoin('ref.mst_wilayah as wilayah', 'wilayah.kode_wilayah','=','pengguna.kode_wilayah')
            ->where('soft_delete', '=', 0);

            if($request->input('peran_id') && $request->input('peran_id') != 99){
                $builder->where('peran.peran_id','=',$request->input('peran_id'));
            }

            if($request->input('verified') != null && $request->input('verified') != 99){
                $builder->where('pengguna.verified','=',$request->input('verified'));
            }
            if($request->input('keyword') != null){
                $builder->where('pengguna.nama','like', '%'.$request->input('keyword').'%');
            }
            
            $count = $builder->select(DB::raw('sum(1) as total'))->first();
            $user = $builder->select(
                'pengguna.*',
                'peran.nama as peran',
                'wilayah.nama as wilayah'
            )
            ->skip($start)
            ->take($limit)->get();
        }

        if($keyword){
            $user = DB::connection('sqlsrv_2')
            ->table(DB::raw('pengguna'))
            ->leftJoin('ref.peran as peran','peran.peran_id','=','pengguna.peran_id')
            ->leftJoin('ref.mst_wilayah as wilayah', 'wilayah.kode_wilayah','=','pengguna.kode_wilayah')
            // ->leftJoin(DB::raw("(select * from pengikut_pengguna where pengguna_id_pengikut = '".$pengguna_id_pengikut."' and soft_delete = 0) as pengikuts"), 'pengikuts.pengguna_id','=','pengguna.pengguna_id')
            // ->where('pengguna.nama', 'ilike', DB::raw("'%".$keyword."%'"))
            ->where(function($query) use ($keyword){
                $query->where('pengguna.nama', 'ilike', DB::raw("'%".$keyword."%'"))
                      ->orWhere('pengguna.username', 'ilike', DB::raw("'%".$keyword."%'"));
            })
            ->where('pengguna.soft_delete', '=', 0)
            ->select(
                'pengguna.*',
                'peran.nama as peran',
                'wilayah.nama as wilayah'
            )
            ->get();
        }

        if($keyword && $pengguna_id_pengikut){
            $user = DB::connection('sqlsrv_2')
            ->table(DB::raw('pengguna'))
            ->leftJoin('ref.peran as peran','peran.peran_id','=','pengguna.peran_id')
            ->leftJoin('ref.mst_wilayah as wilayah', 'wilayah.kode_wilayah','=','pengguna.kode_wilayah')
            ->leftJoin(DB::raw("(select * from pengikut_pengguna where pengguna_id_pengikut = '".$pengguna_id_pengikut."' and soft_delete = 0) as pengikuts"), 'pengikuts.pengguna_id','=','pengguna.pengguna_id')
            // ->where('pengguna.nama', 'ilike', DB::raw("'%".$keyword."%'"))
            ->where(function($query) use ($keyword){
                $query->where('pengguna.nama', 'ilike', DB::raw("'%".$keyword."%'"))
                      ->orWhere('pengguna.username', 'ilike', DB::raw("'%".$keyword."%'"));
            })
            ->where('pengguna.soft_delete', '=', 0)
            ->select(
                'pengguna.*',
                'peran.nama as peran',
                'wilayah.nama as wilayah',
                'pengikuts.pengguna_id as validasi_pengikut'
            )
            ->get();
        }

        if($keyword && (int)$request->sekolah_pengguna == 1){
            $user = DB::connection('sqlsrv_2')
            ->table(DB::raw('pengguna'))
            ->leftJoin('ref.peran as peran','peran.peran_id','=','pengguna.peran_id')
            ->leftJoin('ref.mst_wilayah as wilayah', 'wilayah.kode_wilayah','=','pengguna.kode_wilayah')
            ->leftJoin(DB::raw("(select * from sekolah_pengguna where sekolah_id = '".$request->sekolah_id."' and soft_delete = 0) as sekolah_pengguna"), 'sekolah_pengguna.pengguna_id','=','pengguna.pengguna_id')
            ->where(function($query) use ($keyword){
                $query->where('pengguna.nama', 'ilike', DB::raw("'%".$keyword."%'"))
                      ->orWhere('pengguna.username', 'ilike', DB::raw("'%".$keyword."%'"));
            })
            ->where('pengguna.soft_delete', '=', 0)
            ->select(
                'pengguna.*',
                'peran.nama as peran',
                'wilayah.nama as wilayah',
                'sekolah_pengguna.pengguna_id as cek_pilih'
            )
            ->get();
        }

        $return = array();
        $return['total'] = ($pengguna_id || $username || $keyword ? sizeof($user) : $count->total);
        $return['rows'] = $user;
        
        return $return;
    }

    public function authenticate(Request $request) { 
        $username = $request->input('username');
        $password = $request->input('password');
        $passCode = md5($password);

        $user = DB::connection('sqlsrv_2')->table(DB::raw('pengguna'))->where('username', '=', DB::raw("'".$username."'"))->where('soft_delete', '=', 0)->first();

        if($user){
            if($passCode == $user->password){
                try { 
                    // verify the credentials and create a token for the user
                    // if ( !$token = JWTAuth::encode($payload) ) {
                    
                    $factory = JWTFactory::customClaims([
                        'sub'   => env('API_ID'),
                        'email' => $user->{'username'},
                        'password' => $user->{'password'}
                    ]);
                    $payload = $factory->make();

                    if ( !$token = JWTAuth::encode($payload)) { 
                        return response()->json(['error' => 'invalid_credentials'], 401);
                    } 

                } catch (JWTException $e) { 
                    // something went wrong 
                    return response()->json(['error' => 'could_not_create_token'], 500); 
                
                }

                $return = array();
                $return['token'] = (string) $token;
                $return['user'] = $user;
                
                return $return;

            }else{
                return response()->json(['error' => 'Password yang Anda gunakan salah. Silakan mencoba kembali menggunakan password lain'], 200);
            }
        }else{
            return response()->json(['error' => 'Pengguna yang Anda gunakan tidak ditemukan. Silakan mencoba kembali menggunakan username lain'], 200);
        }
    }

    public function upload(Request $request)
    {
        $data = $request->all();
        $file = $data['image'];
        $pengguna_id = $data['pengguna_id'];
        $jenis = $data['jenis'];

        if(($file == 'undefined') OR ($file == '')){
            return response()->json(['msg' => 'tidak_ada_file']);
        }

        $ext = $file->getClientOriginalExtension();
        $name = $file->getClientOriginalName();

        $destinationPath = base_path('/public/assets/berkas');
        $upload = $file->move($destinationPath, $name);

        $msg = $upload ? 'sukses' : 'gagal';

        if($upload){
            $execute = DB::connection('sqlsrv_2')->table('pengguna')->where('pengguna_id','=',$pengguna_id)->update([
                $jenis => "/assets/berkas/".$name
            ]);

            if($execute){
                return response(['msg' => $msg, 'filename' => "/assets/berkas/".$name, 'jenis' => $jenis]);
            }
        }

    }
}

?>