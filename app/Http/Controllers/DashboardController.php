<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class DashboardController extends Controller
{
    public function hendel_now(Request $request)
    {
        return "PMP";
    }

	public function index(Request $request)
	{
		$data['berita_top_5']     = $this->berita_list_top_5();
		$data['berita_top_five']  = $this->berita_list_top_five();
		$data['kegiatan_top_5']   = $this->kegiatan_top_5();
        $data['daftar_lpmp']      = $this->daftar_lpmp_top();
		$data['title']            = 'beranda';
		return view('dashboard', compact('data'));
	}

	public function berita($page = 0, $pageSize = 6)
	{
        $page       = $page == 0 ? $page : ($page - 1);
        $pageSize   = $pageSize;
        $start      = ($page) * $pageSize;

        $berita = DB::table('view_berita')->orderBy('last_update', 'DESC')->where(['soft_delete' => '0', 'jenis_berita_id' => '1']);

        $return   = [
            'page'  => intval($page) + 1,
            'pages' => ceil(($berita->count() / $pageSize)),
            'count' => $berita->count(),
            'data'  => $berita->take($pageSize)->skip($start)->get(),
            'title' => 'berita',
            'berita_top_5' => $this->berita_list_top_5(),
            'kegiatan_top_5' => $this->kegiatan_top_5(),
        ];

        $no = $start + 1;
        for ($i=0; $i < count($return['data']); $i++) { 
            $return['data'][$i]->images = $return['data'][$i]->images == '' ? '/portal_pmp/assets/img/default.jpg' : $return['data'][$i]->images;
            $return['data'][$i]->deskripsi = str_replace("&nbsp;", "", substr(str_replace("\n", "", strip_tags($return['data'][$i]->konten_berita)), 0, 100))."...";
            unset($return['data'][$i]->konten_berita);
        }

        $data = $return;

		return view('berita', compact('data'));
	}

	public function berita_detail(Request $request)
	{
		$data['berita'] = DB::table('view_berita')->where($request->all('slug'))->where('soft_delete', '0')->limit('1')->get()->toArray();
		$data['berita_top_5'] = $this->berita_list_top_5();
		$data['kegiatan_top_5'] = $this->kegiatan_top_5();
		$data['kategori'] = $this->kategori_list();
		$data['title'] = 'berita';
		return view('berita_detail', compact('data'));
	}

	public function unduhan(Request $request)
	{
        $data['berita_top_5'] = $this->berita_list_top_5();
        $data['kegiatan_top_5'] = $this->kegiatan_top_5();
        $data['unduhan_online'] = $this->unduhan_('Online');
        $data['unduhan_offline'] = $this->unduhan_('Offline');
        $data['unduhan_andrroid'] = $this->unduhan_('android');
        $data['unduhan_ios'] = $this->unduhan_('IOS');
        $data['unduhan_dokumen'] = $this->unduhan_('Dokumen');
		$data['title'] = 'unduhan';
		return view('unduhan', compact('data'));
	}

    public function daftar_lpmp(Request $request)
    {
        $data['daftar_lpmp'] = DB::table('view_lpmp')->where('soft_delete', '0')->get()->toArray();
        $data['berita_top_5'] = $this->berita_list_top_5();
		$data['kegiatan_top_5'] = $this->kegiatan_top_5();
        $data['title'] = 'daftar-lpmp';
		return view('daftar_lpmp', compact('data'));
    }

    public function lpmp_detail(Request $request)
    {
        $where = $request->all('lpmp_id');
        $data['data'] = DB::table('view_lpmp')->where($where)->get()->toArray();
        $data['count'] = count($data['data']);
        return $data;
    }

    public function daftar_lpmp_top($limit = 8)
    {
        $LPMP = DB::table('view_lpmp')->limit($limit)->orderBy('create_date', 'DESC')->get()->toArray();
        return $LPMP;
    }

	public function unduhan_($jenis_unduhan='')
	{
		$Unduhan = DB::table('view_unduhan')->where('jenis_unduhan', $jenis_unduhan)->get()->toArray();
		return $Unduhan;
	}

	public function gambar_pertama($string, $default = NULL) {
        preg_match('@<img.+src="(.*)".*>@Uims', $string, $matches);
        $src = $matches ? $matches[1] : $default;
        return $src;
    }
    
    public function berita_list_top_5()
    {
        $Berita = DB::table('view_berita')
        ->orderBy('create_date', 'DESC')
        ->where('soft_delete', '0')
        ->where('jenis_berita_id', '1') //Berita Pusat
        ->take('10')
        ->get()
        ->toArray();

        $i = 0;
        foreach ($Berita as $key) {
            $Berita[$i]->images = $Berita[$i]->images == '' ? '/portal_pmp/assets/img/default.jpg' : $Berita[$i]->images;
            $Berita[$i]->deskripsi = str_replace("&nbsp;", "", substr(str_replace("\n", "", strip_tags($Berita[$i]->konten_berita)), 0, 100))."...";
            unset($Berita[$i]->konten_berita);
            $i++;
        }

        $return = [
            'data' => $Berita,
            'count' => count($Berita),
        ];

        return $return;
    }

    public function berita_list_top_five()
    {
        $Berita = DB::table('view_berita')
        ->orderBy('create_date', 'DESC')
        ->where('soft_delete', '0')
        ->where('jenis_berita_id', '1') //Berita Pusat
        ->take('5')
        ->get()
        ->toArray();

        $i = 0;
        foreach ($Berita as $key) {
            $Berita[$i]->images = $Berita[$i]->images == '' ? '/portal_pmp/assets/img/default.jpg' : $Berita[$i]->images;
            $Berita[$i]->deskripsi = str_replace("&nbsp;", "", substr(str_replace("\n", "", strip_tags($Berita[$i]->konten_berita)), 0, 100))."...";
            unset($Berita[$i]->konten_berita);
            $i++;
        }

        $return = [
            'data' => $Berita,
            'count' => count($Berita),
        ];

        return $return;
    }

    public function kegiatan_top_5()
    {
    	$Kegiatan = DB::table('view_kegiatan')->limit(5)->orderBy('create_date', 'DESC')->get()->toArray();
    	return $Kegiatan;
    }

    public function kategori_list()
    {
    	$Kategori = DB::table('view_kategori')->get()->toArray();
    	return $Kategori;
    }

    public function berita_list(Request $request)
    {
        $data       = $request->all('page', 'pageSize');
        $page       = $data['page'] === '' ? 0 : $data['page'];
        $pageSize   = $data['pageSize'] === '' ? 10 : $data['pageSize'];
        $start      = ($page) * $pageSize;

        $berita = DB::table('view_berita')->orderBy('last_update', 'DESC')->where(['soft_delete' => '0', 'jenis_berita_id' => '1']);

        $return   = [
            'page'  => intval($page),
            'pages' => ceil(($berita->count() / $pageSize)),
            'count' => $berita->count(),
            'data'  => $berita->take($pageSize)->skip($start)->get(),
        ];

        $no = $start + 1;
        for ($i=0; $i < count($return['data']); $i++) { 
            $return['data'][$i]->images = $return['data'][$i]->images == '' ? '/portal_pmp/assets/img/default.jpg' : $return['data'][$i]->images;
            $return['data'][$i]->deskripsi = substr(str_replace("\n", "", strip_tags($return['data'][$i]->konten_berita)), 0, 100)."...";
            unset($return['data'][$i]->konten_berita);
        }

        return Response($return);
    }

    public function berita_satu(Request $request)
    {
        $Berita = DB::table('view_berita')
        ->where($request->all())
        ->where('soft_delete', '0')
        ->get();

        return Response($Berita);
    }
}
