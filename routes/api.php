<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::post('Auth/login', 'PenggunaController@authenticate');
// Route::prefix('Buku')->group(function () {
	// });

Route::prefix('Pertanyaan')->group(function () {
	Route::post('simpanPantauan', 'PertanyaanController@simpanPantauan');
	Route::post('simpanPertanyaan', 'PertanyaanController@simpanPertanyaan');
	Route::post('getPertanyaan', 'PertanyaanController@getPertanyaan');
	Route::post('getPertanyaanPantauan', 'PertanyaanController@getPertanyaanPantauan');
	Route::post('simpanJawaban', 'PertanyaanController@simpanJawaban');
	Route::post('getJawaban', 'PertanyaanController@getJawaban');
	Route::post('simpanKomentar', 'PertanyaanController@simpanKomentar');
	Route::post('simpanDukungan', 'PertanyaanController@simpanDukungan');
});

Route::prefix('Notifikasi')->group(function () {
	Route::post('simpanNotifikasi', 'NotifikasiController@simpanNotifikasi');
	Route::post('getNotifikasi', 'NotifikasiController@getNotifikasi');
});

Route::prefix('Linimasa')->group(function () {
	Route::post('getLinimasa', 'LinimasaController@getLinimasa');
});

Route::prefix('Aktivitas')->group(function () {
	Route::post('getAktivitas', 'AktivitasController@getAktivitas');
});

Route::prefix('Pengikut')->group(function () {
	Route::post('simpanPengikut', 'PengikutController@simpanPengikut');
	Route::post('getPengikut', 'PengikutController@getPengikut');
	Route::post('cekMengikuti', 'PengikutController@cekMengikuti');
});

Route::prefix('Kuis')->group(function () {
	Route::post('generateUUID', 'KuisController@generateUUID');
	Route::post('getKuis', 'KuisController@getKuis');
	Route::post('getPertanyaanKuis', 'KuisController@getPertanyaanKuis');
	Route::post('simpanKuis', 'KuisController@simpanKuis');
	Route::post('getPenggunaKuis', 'KuisController@getPenggunaKuis');
	Route::post('simpanPenggunaKuis', 'KuisController@simpanPenggunaKuis');
	Route::post('simpanJawabanKuis', 'KuisController@simpanJawabanKuis');
	Route::post('getKuisDiikuti', 'KuisController@getKuisDiikuti');
	Route::post('getKuisRuang', 'KuisController@getKuisRuang');
	Route::post('setSesiKuis', 'KuisController@setSesiKuis');
	Route::post('getSesiKuis', 'KuisController@getSesiKuis');
	Route::post('getKuisTrending', 'KuisController@getKuisTrending');
	Route::post('getLaporanHasilKuis', 'KuisController@getLaporanHasilKuis');
	Route::get('getLaporanHasilKuis', 'KuisController@getLaporanHasilKuis');
	Route::post('hapusSesiKuis', 'KuisController@hapusSesiKuis');
	Route::post('hapusKuis', 'KuisController@hapusKuis');
	Route::post('aktivitasKuis', 'KuisController@aktivitasKuis');
	Route::post('simpanPertanyaanKuis', 'KuisController@simpanPertanyaanKuis');
	Route::post('getStatKuis', 'KuisController@getStatKuis');
	Route::post('uploadAudio', 'KuisController@uploadAudio');
	// Route::get('getLaporanHasilKuis_excel', 'KuisController@getLaporanHasilKuis_excel');
});

Route::prefix('Ruang')->group(function () {
	Route::post('simpanRuang', 'RuangController@simpanRuang');
	Route::post('simpanPertanyaanRuang', 'RuangController@simpanPertanyaanRuang');
	Route::post('simpanPenggunaRuang', 'RuangController@simpanPenggunaRuang');
	Route::post('getRuang', 'RuangController@getRuang');
	Route::post('getPenggunaRuang', 'RuangController@getPenggunaRuang');
	Route::post('upload', 'RuangController@upload');
	Route::post('getRuangDiikuti', 'RuangController@getRuangDiikuti');
	Route::post('generateRandomString', 'RuangController@generateRandomString');
	Route::post('hapusRuang', 'RuangController@hapusRuang');
});

Route::prefix('Sekolah')->group(function () {
	Route::post('simpanSekolah', 'SekolahController@simpanSekolah');
});


Route::prefix('Ref')->group(function () {
	Route::post('getJenjang', 'RefController@getJenjang');
	Route::post('getTingkatPendidikan', 'RefController@getTingkatPendidikan');
	Route::post('getMataPelajaran', 'RefController@getMataPelajaran');
});

Route::prefix('Otentikasi')->group(function () {
	Route::post('masuk', 'PenggunaController@authenticate');
	Route::post('getPengguna', 'PenggunaController@getPengguna');
	Route::post('simpanPengguna', 'PenggunaController@simpanPengguna');
	Route::post('buatPengguna', 'PenggunaController@buatPengguna');
	Route::post('daftarPengguna', 'PenggunaController@daftarPengguna');
	Route::post('upload', 'PenggunaController@upload');
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

Route::prefix('app')->group(function () {
	Route::post('getWilayah', 'AppController@getWilayah');
	Route::post('getGeoJsonBasic', 'AppController@getGeoJsonBasic');
	Route::post('getMapel', 'AppController@getMapel');
	Route::post('getStatistik', 'AppController@getStatistik');
	Route::post('getStatEmpu', 'AppController@getStatEmpu');
});

Route::middleware('token')->group(function(){
	Route::options('{any}', function($any){ return Response('OK', 200); });
	Route::options('{a}/{b}', function($a, $b){ return Response('OK', 200); });
	Route::options('{a}/{b}/{c}', function($a,$b,$c){ return Response('OK', 200); });
	Route::options('{a}/{b}/{c}/{d}', function($a,$b,$c,$d){ return Response('OK', 200); });
	Route::options('{a}/{b}/{c}/{d}/{e}', function($a,$b,$c,$d,$e){ return Response('OK', 200); });
});

Route::options('{any}', function($any){ return Response('OK', 200); });
Route::options('{a}/{b}', function($a, $b){ return Response('OK', 200); });
Route::options('{a}/{b}/{c}', function($a,$b,$c){ return Response('OK', 200); });
Route::options('{a}/{b}/{c}/{d}', function($a,$b,$c,$d){ return Response('OK', 200); });
Route::options('{a}/{b}/{c}/{d}/{e}', function($a,$b,$c,$d,$e){ return Response('OK', 200); });
