<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::post('/', function () {
//     return view('welcome');
// });
//Portal HTML
Route::get('/', 'DashboardController@hendel_now');
Route::get('berita', 'DashboardController@berita');
Route::get('berita/{page}', 'DashboardController@berita');
Route::get('berita-detail', 'DashboardController@berita_detail');
Route::get('unduhan', 'DashboardController@unduhan');
Route::get('daftar-lpmp', 'DashboardController@daftar_lpmp');
Route::get('daftar-lpmp-detail', 'DashboardController@lpmp_detail');

// Portal React
Route::get('getApp', 'ReactDashboardController@App');
Route::get('getNews', 'ReactDashboardController@getNews');
Route::get('lpmp_das', 'ReactDashboardController@lpmp');
Route::get('agenda', 'ReactDashboardController@agenda');
Route::get('berita_rct', 'ReactBeritaController@berita');
Route::get('berita-lpmp-rct', 'ReactBeritaController@berita_lpmp');
Route::get('berita_detail_rct', 'ReactBeritaController@detail');
Route::get('kegiatan', 'ReactKegiatanController@tampil');
Route::get('unduhan_rct', 'ReactUnduhanController@tampil');
Route::get('lpmp', 'ReactLpmpController@tampil');
Route::get('faq', 'ReactFaqController@tampil');
Route::get('daftar_foto_public', 'GaleriController@daftar_foto_public');
Route::get('daftar_video_public', 'GaleriController@daftar_video_public');

//Publik Manajemen PMP
Route::get('getCountProgresPengiriman', 'DashboardReactController@getCountProgresPengiriman');
Route::get('getmainRekap', 'DashboardReactController@getmainRekap');
Route::get('getCountSyncBulanan', 'DashboardReactController@getCountSyncBulanan');
Route::get('getCountSyncMingguan', 'DashboardReactController@getCountSyncMingguan');
Route::get('getProgresKirim', 'DashboardReactController@getProgresKirim');
Route::get('sekolah_detail', 'SekolahController@sekolah_detail');

Route::middleware('web')->group(function(){
	Route::get('login', 'LoginController@login');
	Route::post('login', 'LoginController@login');

	Route::prefix('Berita')->group(function () {
		Route::get('top_5', 'BeritaController@berita_list_top_5');
		Route::get('list', 'BeritaController@berita_list');
		Route::get('satu', 'BeritaController@berita_satu');
	});
	
	// Route::get('pmpstatus', 'PMPController@status');
	// Route::get('pmpstatusServer', 'PMPController@statusServer');
});
