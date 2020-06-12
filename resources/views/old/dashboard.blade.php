@extends('template')

@section('main')
  <body class="demo-5">
    <div class="wrapper"> 
      <?= view('menu', compact('data')) ?>
  
      <div class="city_main_banner">
        <div class="main-banner-slider">
          <div>
            <figure class="overlay">
              <img src="/portal_pmp/assets/img/unduh-aplikasi.jpg" alt="Unduhan Aplikasi" />

              <div class="download-app">
                <a href="#" class="clearfix">
                  <img src="/portal_pmp/assets/img/icons/online.png" alt="PMP Online" />

                  <div class="download-text">
                    <span>Menuju ke Aplikasi</span>

                    <h5>PMP Online</h5>
                  </div>
                </a>

                <a href="#" class="clearfix">
                    <img src="/portal_pmp/assets/img/icons/google-play.svg" alt="Android Download App" />
  
                    <div class="download-text">
                      <span>Unduh Aplikasi PMP di</span>
  
                      <h5>Google Play</h5>
                    </div>
                  </a>

                <a href="#" class="clearfix">
                  <img src="/portal_pmp/assets/img/icons/apple.svg" alt="iOS Download App" />

                  <div class="download-text">
                    <span>Unduh Aplikasi PMP di</span>

                    <h5>App store</h5>
                  </div>
                </a>

                <a href="#" class="offline clearfix">
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
    
      <div class="city_news_wrap">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <div class="section_heading margin-bottom">
                <span>Info untuk anda hari ini!</span>

                <h2>Berita Terupdate</h2>
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
                        <li><a href="#">{{ $data['berita_top_5']['data'][0]->tanggal_publis }}</a></li>
                        <li><a href="#">{{ $data['berita_top_5']['data'][0]->kategori_berita }}</a></li>
                        <li><a href="#">{{ $data['berita_top_5']['data'][0]->pengguna }}</a></li>
                      </ul>

                      <p>{{ $data['berita_top_5']['data'][0]->deskripsi }}</p>
                      
                      <a class="theam_btn border-color color" href="/berita-detail/?slug={{ $data['berita_top_5']['data'][0]->slug }}" tabindex="0">Baca Selengkapnya</a>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 col-sm-6">
                  <div class="city_news_row">
                    <ul>
                      <?php foreach ($data['berita_top_5']['data'] as $berita): ?>
                      <li>
                        <div class="city_news_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="{{ $berita->images }}" alt="{{ $berita->judul }}" />
                          </figure>

                          <div class="city_news_list_text">
                            <h5>{{ $berita->judul }}</h5>

                            <ul class="city_news_meta">
                              <li><a href="#">{{ $berita->tanggal_publis }}</a></li>
                              <li><a href="#">{{ $berita->kategori_berita }}</a></li>
                              <li><a href="#">{{ $berita->pengguna }}</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>
                      <?php endforeach; ?>

                      <!--<li>
                        <div class="city_news_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/news-fig2.jpg" alt="" />
                          </figure>

                          <div class="city_news_list_text">
                            <h5>Rilis Pembaruan Aplikasi Pemetaan PMP 2018.05</h5>

                            <ul class="city_news_meta">
                              <li><a href="#">29 Apr 2019</a></li>
                              <li><a href="#">Informasi</a></li>
                              <li><a href="#">Admin PMP</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>
                      
                      <li>
                        <div class="city_news_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>
                            
                            <img src="http://kodeforest.net/html/baldiyat/extra-images/news-fig3.jpg" alt="" />
                          </figure>

                          <div class="city_news_list_text">
                            <h5>Rilis Pembaruan Aplikasi Pemetaan PMP 2018.07</h5>

                            <ul class="city_news_meta">
                              <li><a href="#">29 Apr 2019</a></li>
                              <li><a href="#">Informasi</a></li>
                              <li><a href="#">Admin PMP</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>

                      <li>
                        <div class="city_news_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/news-fig4.jpg" alt="" />
                          </figure>

                          <div class="city_news_list_text">
                            <h5>Surat Edaran Dirjen Dikdasmen No 21/D/PO/2018 Tentang Pemetaan Mutu Pendidikan Tahun Ajaran 2018/2019</h5>

                            <ul class="city_news_meta">
                              <li><a href="#">29 Apr 2019</a></li>
                              <li><a href="#">Informasi</a></li>
                              <li><a href="#">Admin PMP</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>

                      <li>
                        <div class="city_news_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/news-fig5.jpg" alt="" />
                          </figure>

                          <div class="city_news_list_text">
                            <h5>Rilis Aplikasi Pemetaan PMP 2018.04</h5>

                            <ul class="city_news_meta">
                              <li><a href="#">29 Apr 2019</a></li>
                              <li><a href="#">Informasi</a></li>
                              <li><a href="#">Admin PMP</a></li>
                            </ul>
                          </div>
                        </div>
                      </li> -->
                    </ul>
                  </div>
                </div>
              </div>	
            </div>
          </div>
        </div>
      </div>

			<div class="city_requset_wrap">
				<div class="container">
					<div class="row">
						<div class="col-md-6 col-sm-6">
							<div class="city_request_list">
								<div class="city_request_row">
                  <span><i class="fas fa-question"></i></span>
                  
									<div class="city_request_text">
                    <span>Recent</span>
                    
										<h4>Top Request</h4>
									</div>
                </div>
                
								<div class="city_request_link">
									<ul>
										<li><a href="#">Pay a Parking Ticket</a></li>
										<li><a href="#">Building Violation</a></li>
										<li><a href="#">Affordable Housing</a></li>
										<li><a href="#">Graffiti Removal</a></li>
										<li><a href="#">Civil Service Exams</a></li>
										<li><a href="#">Rodent Baiting</a></li>
										<li class="margin0"><a href="#">Cleaning</a></li>
										<li class="margin0"><a href="#">Uncleared Sidewalk</a></li>
									</ul>
								</div>
							</div>
            </div>
            
						<div class="col-md-6 col-sm-6">
							<div class="city_request_list">
								<div class="city_request_row">
                  <span><i class="fas fa-bullhorn"></i></span>
                  
									<div class="city_request_text">
                    <span>Recent</span>
                    
										<h4>Announcement</h4>
									</div>
                </div>
                
								<div class="city_request_link">
									<ul>
										<li><a href="#">Pay a Parking Ticket</a></li>
										<li><a href="#">Building Violation</a></li>
										<li><a href="#">Affordable Housing</a></li>
										<li><a href="#">Graffiti Removal</a></li>
										<li><a href="#">Civil Service Exams</a></li>
										<li><a href="#">Rodent Baiting</a></li>
										<li class="margin0"><a href="#">Cleaning</a></li>
										<li class="margin0"><a href="#">Uncleared Sidewalk</a></li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
      <?= view('fotter'); ?>
    </div>
  </body>
@stop