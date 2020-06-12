@extends('template')

@section('main')
<body class="demo-5">
  <div class="wrapper"> 
    <?= view('menu', compact('data')) ?>
    <div class="section_hero">
      <div class="row" style="margin: 0">
        <div class="col-md-9" style="padding: 0">
          <div class="city_main_banner">
            <div class="main-banner-slider">
              <?php foreach ($data['berita_top_5']['data'] as $key): ?>
                <div>
                  <figure class="overlay">
                    <div class="slick-item-img" style="background-image: url('{{ $key->images }}')"></div>

                    <div class="banner_text">
                      <div class="small_text animated">{{ $key->kategori_berita }}</div>

                      <div class="large_text animated"><a href="/berita-detail/?slug={{ $key->slug }}" style="color: #ffffff">{{ $key->judul }}</a></div>

                      <div class="banner_btn">
                        <a class="theam_btn animated" href="/berita-detail/?slug={{ $key->slug }}">Baca Selengkapnya</a>
                      </div>
                    </div>
                  </figure>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="col-md-3" style="padding: 0">
          <div class="city_main_banner">
            <div class="main-banner-slider download-banner">
              <div>
                <figure class="overlay">
                  <div class="slick-item-img" style="background-image: url('/portal_pmp/assets/img/unduh-aplikasi.jpg')"></div>

                  <div class="download-app">
                    <h6><strong style="color: #ffffff; text-transform: uppercase">Tauntan Aplikasi PMP</strong></h6>

                    <a target="_blank" href="http://pmp.dikdasmen.kemdikbud.go.id:1745" class="clearfix">
                      <img src="/portal_pmp/assets/img/icons/online.png" alt="PMP Online" />

                      <div class="download-text">
                        <span>Menuju ke Aplikasi</span>

                        <h5>PMP Online</h5>
                      </div>
                    </a>

                    <a target="_blank" href="https://play.google.com/store/apps/details?id=io.timkayu.pmp" class="clearfix">
                      <img src="/portal_pmp/assets/img/icons/google-play.svg" alt="Android Download App" />

                      <div class="download-text">
                        <span>Unduh Aplikasi PMP di</span>

                        <h5>Google Play</h5>
                      </div>
                    </a>

                    <a target="_blank" href="http://bit.ly/2Z02e6r" class="clearfix">
                      <img src="/portal_pmp/assets/img/icons/apple.svg" alt="iOS Download App" />

                      <div class="download-text">
                        <span>Unduh Aplikasi PMP di</span>

                        <h5>App store</h5>
                      </div>
                    </a>

                    <a target="_blank" href="#" class="offline clearfix">
                      <img src="/portal_pmp/assets/img/icons/offline.png" alt="PMP Offline" />

                      <div class="download-text">
                        <span>Unduh Aplikasi PMP di</span>

                        <h5>PMP Offline</h5>
                      </div>
                    </a>
                  </div>
                </figure>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="city_news_wrap" style="background-color: #f9f9f9">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="section_heading margin-bottom">
              <span>Info untuk anda hari ini!</span>

              <h2>Berita Terupdate</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 col-sm-6">
            <div class="city_news_fig">
              <figure class="box">
                <div class="box-layer layer-1"></div>
                <div class="box-layer layer-2"></div>
                <div class="box-layer layer-3"></div>

                <img src="{{ $data['berita_top_5']['data'][0]->images }}" alt="{{ $data['berita_top_5']['data'][0]->judul }}" />
              </figure>

              <div class="city_news_text">
                <h2>{{ $data['berita_top_5']['data'][0]->judul }}</h2>

                <ul class="city_news_meta">
                  <li style="margin-left: 5px; margin-right: 5px;"><a href="#"><span class="fa fa-calendar-week"></span> {{ $data['berita_top_5']['data'][0]->tanggal_publis }}</a></li>
                  <li style="margin-left: 5px; margin-right: 5px;"><a href="#"><span class="fa fa-cogs"></span> {{ $data['berita_top_5']['data'][0]->kategori_berita }}</a></li>
                  <li style="margin-left: 5px; margin-right: 5px;"><a href="#"><span class="fa fa-user"></span> {{ $data['berita_top_5']['data'][0]->pengguna }}</a></li>
                </ul>

                <p>{{ $data['berita_top_5']['data'][0]->deskripsi }}</p>

                <a class="theam_btn border-color color" href="/berita-detail/?slug={{ $data['berita_top_5']['data'][0]->slug }}" tabindex="0">Baca Selengkapnya</a>
              </div>
            </div>
          </div>

          <div class="col-md-6 col-sm-6">
            <div class="city_news_row">
              <ul>
                <?php foreach ($data['berita_top_five']['data'] as $berita): ?>
                  <li>
                    <div class="city_news_list">
                      <figure class="box">
                        <div class="box-layer layer-1"></div>
                        <div class="box-layer layer-2"></div>
                        <div class="box-layer layer-3"></div>

                        <div class="city_news_img" style="background-image: url({{ $berita->images }})"></div>
                      </figure>

                      <div class="city_news_list_text">
                        <h5><a href="/berita-detail/?slug={{ $berita->slug }}">{{ $berita->judul }}</a></h5>

                        <ul class="city_news_meta">
                          <li style="margin-left: 5px; margin-right: 5px;"><a href="#"><span class="fa fa-calendar-week"></span> {{ $berita->tanggal_publis }}</a></li>
                          <li style="margin-left: 5px; margin-right: 5px;"><a href="#"><span class="fa fa-cogs"></span> {{ $berita->kategori_berita }}</a></li>
                          <li style="margin-left: 5px; margin-right: 5px;"><a href="#"><span class="fa fa-user"></span> {{ $berita->pengguna }}</a></li>
                        </ul>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>	
      </div>
    </div>

    <div class="city_services2_wrap">
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="section_heading margin-bottom">
              <span>Lembaga Penjaminan Mutu Pendidikan</span>

              <h2>Daftar LPMP</h2>
            </div>
          </div>
        </div>

        <div class="row">
          <?php foreach ($data['daftar_lpmp'] as $key): ?>
            <div class="col-md-3 col-sm-6">
              <div class="city_service2_fig">
                <figure class="overlay">
                  <div class="city_service2_fig_img" style="background-image: url('{{ $key->gambar }}')"></div>

                  <div class="city_service2_list">
                    <div class="city_service2_caption">
                      <h4>{{ $key->nama }}</h4>
                    </div>
                  </div>
                </figure>

                <div class="city_service2_text">
                  <p>{{ $key->alamat_jalan }}</p>

                  <span class="city_service2_phone"><i class="fas fa-phone"></i> {{ $key->no_telepon }}</span>

                  <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal" onclick="return modal_detail('{{ $key->lpmp_id }}')">
                    Lihat Detil <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach ?>
        </div>

        <div class="row">
          <div class="col-md-12">
            <div class="text-center">
              <a class="theam_btn border-color color" href="/daftar-lpmp" tabindex="0">Lihat Daftar LPMP Lainnya</a>
            </div>
          </div>
        </div>
      </div>	
    </div>

    <div class="modal lpmp-modal fade" id="lpmpModal" tabindex="-1" role="dialog" aria-labelledby="lpmpModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="lpmpModalTitle">LPMP Propinsi Aceh</h5>

            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>

          <div class="modal-body lpmp-body">
            <div class="row">
              <div class="col-sm-6">
                <div class="lpmp-modal-img" style="background-image: url(http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Nanggroe-Aceh-Darussalam.jpg)"></div>
              </div>

              <div class="col-sm-6">
                <div class="lpmp-modal-content">
                  <p><span>Alamat</span>Jl. Banda Aceh Medan KM. 12,5 Desa Niron Kec. Suka Makmur Kab. Aceh Besar Provinsi Aceh</p>

                  <p><span>Kode Pos</span>23361</p>

                  <p style="color: #1c94e1"><span>Lintang</span>5.488049</p>

                  <p style="color: #1c94e1"><span>Bujur</span>95.383112</p>

                  <p><span>Telepon</span>(0651) 7556304</p>

                  <p><span>Website</span><a href="http://lpmp-aceh.com" target="_blank">http://lpmp-aceh.com</a></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?= view('footer', compact('data')); ?>
  </div>

   <script type="text/javascript">
            function modal_detail(lpmp_id) {
                // $('#lpmpModalTitle').html(lpmp_id);
                $('#lpmp-img').css({
                    "background-image" : "url('/portal_pmp/assets/img/no-image.jpg')",
                });

                $.ajax({
                    url     : "/daftar-lpmp-detail?lpmp_id="+lpmp_id,
                    method  : 'get',
                    success : function(msg){
                        if(msg.count === 1){
                            var data = msg.data[0];
                            $('#alamat_jalan').html(data.alamat_jalan);
                            $('#bujur').html(data.bujur);
                            $('#kode_pos').html(data.kode_pos);
                            $('#lpmpModalTitle').html(data.nama);
                            $('#lintang').html(data.lintang);
                            $('#no_telepon').html(data.no_telepon);
                            $('#website').html(data.website);
                            $('#website').attr("href", data.website);

                            $('#lpmp-img').css({
                                "background-image" : "url('"+ data.gambar +"')",
                            });

                            // $('#label').html(data.create_date);
                            // $('#label').html(data.email);
                            // $('#label').html(data.gambar);
                            // $('#label').html(data.kode_wilayah);
                            // $('#label').html(data.last_update);                            
                            // $('#label').html(data.lpmp_id);
                            // $('#label').html(data.soft_delete);
                            // $('#label').html(data.updater_id);
                            // $('#label').html(data.wilayah);

                        }
                    }
                })
            }
        </script>
</body>
@stop