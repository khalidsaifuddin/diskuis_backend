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

Route::prefix('Blog')->group(function () {
	Route::post('simpanArtikel', 'BlogController@simpanArtikel');
	Route::post('getArtikel', 'BlogController@getArtikel');
});

Route::prefix('Pertanyaan')->group(function () {
	Route::post('simpanPantauan', 'PertanyaanController@simpanPantauan');
	Route::post('simpanPertanyaan', 'PertanyaanController@simpanPertanyaan');
	Route::post('simpanPertanyaanSekolah', 'PertanyaanController@simpanPertanyaanSekolah');
	Route::post('getPertanyaanSekolah', 'PertanyaanController@getPertanyaanSekolah');
	Route::post('getPertanyaanRuang', 'PertanyaanController@getPertanyaanRuang');
	Route::post('getPertanyaan', 'PertanyaanController@getPertanyaan');
	Route::post('getPertanyaanPublik', 'PertanyaanController@getPertanyaanPublik');
	Route::post('getPertanyaanPantauan', 'PertanyaanController@getPertanyaanPantauan');
	Route::post('simpanJawaban', 'PertanyaanController@simpanJawaban');
	Route::post('hapusJawaban', 'PertanyaanController@hapusJawaban');
	Route::post('hapusPertanyaan', 'PertanyaanController@hapusPertanyaan');
	Route::post('getJawaban', 'PertanyaanController@getJawaban');
	Route::post('simpanKomentar', 'PertanyaanController@simpanKomentar');
	Route::post('simpanDukungan', 'PertanyaanController@simpanDukungan');
});

Route::prefix('Notifikasi')->group(function () {
	Route::post('simpanNotifikasi', 'NotifikasiController@simpanNotifikasi');
	Route::post('getNotifikasi', 'NotifikasiController@getNotifikasi');
	Route::post('simpanNotifikasiKomentar', 'NotifikasiController@simpanNotifikasiKomentar');
	Route::post('simpanNotifikasiRuang', 'NotifikasiController@simpanNotifikasiRuang');
	Route::post('simpanNotifikasiSekolah', 'NotifikasiController@simpanNotifikasiSekolah');
	Route::post('getNotifikasiRedis', 'NotifikasiController@getNotifikasiRedis');
	Route::post('bacaNotifikasi', 'NotifikasiController@bacaNotifikasi');
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
	Route::post('simpanJawabanKuisIsian', 'KuisController@simpanJawabanKuisIsian');
	Route::post('simpanJawabanKuisCheckbox', 'KuisController@simpanJawabanKuisCheckbox');
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
	Route::get('getLaporanSesiKuis', 'KuisController@getLaporanSesiKuis');
	Route::post('getCountKuisUmum', 'KuisController@getCountKuisUmum');
	Route::post('getSesiKuisPengguna', 'KuisController@getSesiKuisPengguna');
	Route::post('getKolaborasiKuis', 'KuisController@getKolaborasiKuis');
	Route::post('simpanKolaborasiKuis', 'KuisController@simpanKolaborasiKuis');
	Route::post('simpanAspek', 'KuisController@simpanAspek');
	Route::post('getAspek', 'KuisController@getAspek');
	Route::post('getAspekReversed', 'KuisController@getAspekReversed');
	Route::post('getJawabanPenggunaKuis', 'KuisController@getJawabanPenggunaKuis');
	Route::get('getHasilKuisPenggunaExcel', 'KuisController@getHasilKuisPenggunaExcel');
	// Route::get('getLaporanHasilKuis_excel', 'KuisController@getLaporanHasilKuis_excel');
});

Route::prefix('Ruang')->group(function () {
	Route::post('simpanRuang', 'RuangController@simpanRuang');
	Route::post('simpanPertanyaanRuang', 'RuangController@simpanPertanyaanRuang');
	Route::post('simpanPenggunaRuang', 'RuangController@simpanPenggunaRuang');
	Route::post('simpanPenggunaRuangBulk', 'RuangController@simpanPenggunaRuangBulk');
	Route::post('getRuang', 'RuangController@getRuang');
	Route::post('getPenggunaRuang', 'RuangController@getPenggunaRuang');
	Route::post('upload', 'RuangController@upload');
	Route::post('getRuangDiikuti', 'RuangController@getRuangDiikuti');
	Route::post('generateRandomString', 'RuangController@generateRandomString');
	Route::post('hapusRuang', 'RuangController@hapusRuang');
});

Route::prefix('Poin')->group(function () {
	Route::post('getLeaderboardPengguna', 'PoinController@getLeaderboardPengguna');
	Route::post('getLeaderboardGlobal', 'PoinController@getLeaderboardGlobal');
});

Route::prefix('Siswa')->group(function () {
	Route::post('getSiswaSekolah', 'SiswaController@getSiswaSekolah');
	Route::post('getDepositSiswaSekolah', 'SiswaController@getDepositSiswaSekolah');
	Route::post('simpanDepositSiswa', 'SiswaController@simpanDepositSiswa');
	Route::post('getDepositSiswa', 'SiswaController@getDepositSiswa');
	Route::post('getKehadiranRuang', 'SiswaController@getKehadiranRuang');
	Route::post('getSiswa', 'SiswaController@getSiswa');
	Route::post('getOrangtua', 'SiswaController@getOrangtua');
	Route::post('simpanSiswa', 'SiswaController@simpanSiswa');
	Route::post('simpanOrangtua', 'SiswaController@simpanOrangtua');
	Route::post('simpanKehadiranRuang', 'SiswaController@simpanKehadiranRuang');
});

Route::prefix('Pesan')->group(function () {
	Route::post('simpanKelompokPesan', 'PesanController@simpanKelompokPesan');
	Route::post('getKelompokPesan', 'PesanController@getKelompokPesan');
	Route::post('simpanPesan', 'PesanController@simpanPesan');
	Route::post('getPesan', 'PesanController@getPesan');
	Route::post('getDaftarPesan', 'PesanController@getDaftarPesan');
	Route::post('simpanPesanDibaca', 'PesanController@simpanPesanDibaca');
});

Route::prefix('Tugas')->group(function () {
	Route::post('simpanTugas', 'TugasController@simpanTugas');
	Route::post('getTugas', 'TugasController@getTugas');
});

Route::prefix('UnitUsaha')->group(function () {
	Route::post('simpanUnitUsaha', 'UnitUsahaController@simpanUnitUsaha');
	Route::post('getUnitUsaha', 'UnitUsahaController@getUnitUsaha');
});

Route::prefix('Langganan')->group(function () {
	Route::post('getLangganan', 'LanggananController@getLangganan');
});

Route::prefix('Playlist')->group(function () {
	Route::post('simpanPlaylist', 'PlaylistController@simpanPlaylist');
	Route::post('getPlaylist', 'PlaylistController@getPlaylist');
	Route::post('getPlaylistKuis', 'PlaylistController@getPlaylistKuis');
	Route::post('simpanPlaylistKuis', 'PlaylistController@simpanPlaylistKuis');
});

Route::prefix('Sekolah')->group(function () {
	Route::post('simpanSekolah', 'SekolahController@simpanSekolah');
	Route::post('simpanSekolahPengguna', 'SekolahController@simpanSekolahPengguna');
	Route::post('getSekolah', 'SekolahController@getSekolah');
	Route::post('getSekolahIndividu', 'SekolahController@getSekolahIndividu');
	Route::post('getSekolahPengguna', 'SekolahController@getSekolahPengguna');
	Route::post('aktifkanSekolah', 'SekolahController@aktifkanSekolah');
	Route::post('getUndanganSekolah', 'SekolahController@getUndanganSekolah');
	Route::post('simpanUndanganSekolah', 'SekolahController@simpanUndanganSekolah');
	Route::post('getGuru', 'SekolahController@getGuru');
	Route::post('simpanGuru', 'SekolahController@simpanGuru');
	Route::post('getKehadiranGuru', 'SekolahController@getKehadiranGuru');
	Route::post('simpanKehadiranGuru', 'SekolahController@simpanKehadiranGuru');
	Route::post('kehadiranHarianGuru', 'SekolahController@kehadiranHarianGuru');
	Route::post('kehadiranRekapGuru', 'SekolahController@kehadiranRekapGuru');
	
	Route::post('getKehadiranSiswa', 'SekolahController@getKehadiranSiswa');
	Route::post('simpanKehadiranSiswa', 'SekolahController@simpanKehadiranSiswa');
	Route::post('kehadiranHarianSiswa', 'SekolahController@kehadiranHarianSiswa');
	Route::post('kehadiranRekapSiswa', 'SekolahController@kehadiranRekapSiswa');

	Route::post('getPengaturanSekolah', 'SekolahController@getPengaturanSekolah');
	Route::post('simpanPengaturanSekolah', 'SekolahController@simpanPengaturanSekolah');
	Route::post('simpanSekolahUtama', 'SekolahController@simpanSekolahUtama');
	Route::post('simpanAdministrator', 'SekolahController@simpanAdministrator');
	Route::post('getJarakSekolah', 'SekolahController@getJarakSekolah');
	Route::get('unduhLaporanKehadiranGuru', 'SekolahController@unduhLaporanKehadiranGuru');
	Route::get('unduhLaporanKehadiranSiswa', 'SekolahController@unduhLaporanKehadiranSiswa');
	Route::post('uploadDokumenGuru', 'SekolahController@uploadDokumenGuru');
	Route::post('getDokumenGuru', 'SekolahController@getDokumenGuru');
	Route::post('getTahunAjaran', 'SekolahController@getTahunAjaran');
	Route::post('simpanRuangSekolah', 'SekolahController@simpanRuangSekolah');
	Route::post('getRuangSekolah', 'SekolahController@getRuangSekolah');

	Route::get('getSekolahPengguna_excel', 'SekolahController@getSekolahPengguna_excel');
});


Route::prefix('Ref')->group(function () {
	Route::post('getJenjang', 'RefController@getJenjang');
	Route::post('getTingkatPendidikan', 'RefController@getTingkatPendidikan');
	Route::post('getMataPelajaran', 'RefController@getMataPelajaran');
	Route::post('getRef', 'RefController@getRef');
});

Route::prefix('Otentikasi')->group(function () {
	Route::post('masuk', 'PenggunaController@authenticate');
	Route::post('getPengguna', 'PenggunaController@getPengguna');
	Route::post('simpanPengguna', 'PenggunaController@simpanPengguna');
	Route::post('simpanPenggunaManual', 'PenggunaController@simpanPenggunaManual');
	Route::post('buatPengguna', 'PenggunaController@buatPengguna');
	Route::post('daftarPengguna', 'PenggunaController@daftarPengguna');
	Route::post('upload', 'PenggunaController@upload');
});

// Route::get('/clear-cache', function() {
//     Artisan::call('cache:clear');
//     return "Cache is cleared";
// });

Route::prefix('app')->group(function () {
	Route::post('getWilayah', 'AppController@getWilayah');
	Route::post('getWilayahHirarki', 'AppController@getWilayahHirarki');
	Route::post('getGeoJsonBasic', 'AppController@getGeoJsonBasic');
	Route::post('getMapel', 'AppController@getMapel');
	Route::post('getStatistik', 'AppController@getStatistik');
	Route::post('getStatEmpu', 'AppController@getStatEmpu');
	Route::post('getRataKuis', 'AppController@getRataKuis');
	Route::post('getPengaturanPengguna', 'PenggunaController@getPengaturanPengguna');
	Route::post('simpanPengaturanPengguna', 'PenggunaController@simpanPengaturanPengguna');
	Route::post('getJenisBerkas', 'AppController@getJenisBerkas');
	Route::post('getRekap', 'AppController@getRekap');
	Route::post('getRekapBulanan', 'AppController@getRekapBulanan');
	Route::post('getRekapKumulatif', 'AppController@getRekapKumulatif');
	Route::post('getRekapBulananKumulatif', 'AppController@getRekapBulananKumulatif');
});

Route::prefix('PPDB')->group(function () {
	Route::post('getCalonPesertaDidik', 'PPDBController@getCalonPesertaDidik');
	Route::post('getPesertaDidikDapodik', 'PPDBController@getPesertaDidikDapodik');
	Route::post('cekNik', 'PPDBController@cekNik');
	Route::post('cekNisn', 'PPDBController@cekNisn');
	Route::post('simpanCalonPesertaDidik', 'PPDBController@simpanCalonPesertaDidik');
	Route::post('simpanLintangBujur', 'PPDBController@simpanLintangBujur');
	Route::post('getJalur', 'PPDBController@getJalur');
	Route::post('getSekolahPPDB', 'PPDBController@getSekolahPPDB');
	Route::post('simpanSekolahPilihan', 'PPDBController@simpanSekolahPilihan');
	Route::post('getSekolahPilihan', 'PPDBController@getSekolahPilihan');
	Route::post('getJalurBerkas', 'PPDBController@getJalurBerkas');
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
