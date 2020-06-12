@extends('template')

@section('main')
  <body class="demo-5">
    <div class="wrapper"> 
      <?= view('menu', compact('data')) ?>
  
			<div class="sab_banner overlay">
				<div class="container">
					<div class="sab_banner_text">
            <h2>Berita</h2>
            
						<ul class="breadcrumb">
						  <li class="breadcrumb-item"><a href="#">Beranda</a></li>
						  <li class="breadcrumb-item active">Berita</li>
						</ul>
					</div>
				</div>
			</div>
			
			<div class="city_blog2_wrap">
				<div class="container">
					<div class="row">

            <?php foreach ($data['data'] as $key): ?>
            <div class="col-md-4 col-sm-6">
							<div class="city_blog2_fig">
								<figure class="overlay">
									<img src="{{ $key->images }}" alt="{{ $key->judul }}" />
                  
                  <a class="paly_btn" data-rel="prettyPhoto" href="https://www.youtube.com/watch?v=SAaevusBnNI">+</a>
                  
                  <span class="city_blog2_met">Meeting</span>
                </figure>
                
								<div class="city_blog2_list">
									<ul class="city_meta_list">
										<li><a href="#"><i class="fa fa-calendar"></i>{{ $key->tanggal_publis }}</a></li>
										<li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>
                  
									<div class="city_blog2_text">
										<h5><a href="#">{{ $key->judul }}</a></h5>
                    
                    <p>{{ $key->deskripsi }}</p>
                    
                    <a class="see_more_btn" href="/berita-detail/?slug={{ $key->slug }}" tabindex="0">Baca Selengkapnya</a>
									</div>
								</div>
							</div>
            </div>
            <?php endforeach; ?>
            
						<!-- <div class="col-md-4 col-sm-6">
							<div class="city_blog2_fig">
								<figure class="overlay">
									<img src="http://kodeforest.net/html/baldiyat/extra-images/blog06.jpg" alt="" />
                  
                  <a class="paly_btn" data-rel="prettyPhoto" href="https://www.youtube.com/watch?v=SAaevusBnNI">+</a>
                  
                  <span class="city_blog2_met">Meeting</span>
								</figure>
                
                <div class="city_blog2_list">
									<ul class="city_meta_list">
										<li><a href="#"><i class="fa fa-calendar"></i>March 7,2018</a></li>
										<li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>
                  
									<div class="city_blog2_text">
										<h5><a href="#">Rilis Aplikasi Pemetaan PMP 2018.04</a></h5>
                    
                    <p>This is Photoshop's version  Lorem Ipsum. Proin gravida nibh vel velit Ipsum. Proin gravida nibh vel velit</p>
                    
                    <a class="see_more_btn" href="./berita-detil.html">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
									</div>
								</div>
							</div>
            </div>
            
						<div class="col-md-4 col-sm-6">
							<div class="city_blog2_fig">
								<figure class="overlay">
                  <img src="http://kodeforest.net/html/baldiyat/extra-images/blog03.jpg" alt="" />
                  
                  <a class="paly_btn" data-rel="prettyPhoto" href="https://www.youtube.com/watch?v=SAaevusBnNI">+</a>
                  
									<span class="city_blog2_met">Meeting</span>
                </figure>
                
								<div class="city_blog2_list">
									<ul class="city_meta_list">
										<li><a href="#"><i class="fa fa-calendar"></i>March 7,2018</a></li>
										<li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>
                  
									<div class="city_blog2_text">
										<h5><a href="#">Rilis Pembaruan Aplikasi Pemetaan PMP 2018.07</a></h5>
                    
                    <p>This is Photoshop's version  Lorem Ipsum. Proin gravida nibh vel velit Ipsum. Proin gravida nibh vel velit</p>
                    
                    <a class="see_more_btn" href="./berita-detil.html">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
									</div>
								</div>
							</div>
            </div>

            <div class="col-md-4 col-sm-6">
							<div class="city_blog2_fig">
								<figure class="overlay">
                  <img src="http://kodeforest.net/html/baldiyat/extra-images/blog01.jpg" alt="" />
                  
                  <a class="paly_btn" data-rel="prettyPhoto" href="https://www.youtube.com/watch?v=SAaevusBnNI">+</a>
                  
									<span class="city_blog2_met">Meeting</span>
                </figure>
                
								<div class="city_blog2_list">
									<ul class="city_meta_list">
										<li><a href="#"><i class="fa fa-calendar"></i>March 7,2018</a></li>
										<li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>
                  
									<div class="city_blog2_text">
                    <h5><a href="#">Pengiriman Ulang dan Pemrosesan Rapor Mutu Data PMP 2018</a></h5>
                    
										<p>This is Photoshop's version  Lorem Ipsum. Proin gravida nibh vel velit Ipsum. Proin gravida nibh vel velit</p>
										<a class="see_more_btn" href="./berita-detil.html">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
									</div>
								</div>
							</div>
            </div>
            
						<div class="col-md-4 col-sm-6">
							<div class="city_blog2_fig">
								<figure class="overlay">
                  <img src="http://kodeforest.net/html/baldiyat/extra-images/blog02.jpg" alt="" />
                  
                  <a class="paly_btn" data-rel="prettyPhoto" href="https://www.youtube.com/watch?v=SAaevusBnNI">+</a>
                  
									<span class="city_blog2_met">Meeting</span>
                </figure>
                
								<div class="city_blog2_list">
									<ul class="city_meta_list">
										<li><a href="#"><i class="fa fa-calendar"></i>March 7,2018</a></li>
										<li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>
                  
									<div class="city_blog2_text">
                    <h5><a href="#">RILIS UPDATER PMP 2018.08 DAN PERPANJANGAN CUT OFF PMP TAHUN 2018</a></h5>
                    
                    <p>This is Photoshop's version  Lorem Ipsum. Proin gravida nibh vel velit Ipsum. Proin gravida nibh vel velit</p>
                    
										<a class="see_more_btn" href="./berita-detil.html">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
									</div>
								</div>
							</div>
            </div>
            
						<div class="col-md-4 col-sm-6">
							<div class="city_blog2_fig">
								<figure class="overlay">
                  <img src="http://kodeforest.net/html/baldiyat/extra-images/blog04.jpg" alt="" />
                  
                  <a class="paly_btn" data-rel="prettyPhoto" href="https://www.youtube.com/watch?v=SAaevusBnNI">+</a>
                  
									<span class="city_blog2_met">Meeting</span>
                </figure>
                
								<div class="city_blog2_list">
									<ul class="city_meta_list">
										<li><a href="#"><i class="fa fa-calendar"></i>March 7,2018</a></li>
										<li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>
                  
									<div class="city_blog2_text">
                    <h5><a href="#">Surat Edaran Dirjen Dikdasmen No 21/D/PO/2018 Tentang PMP</a></h5>
                    
										<p>This is Photoshop's version  Lorem Ipsum. Proin gravida nibh vel velit Ipsum. Proin gravida nibh vel velit</p>
                    
                    <a class="see_more_btn" href="./berita-detil.html">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
									</div>
								</div>
							</div>
            </div> -->
            
						<div class="col-md-12">
							<div class="pagination">
								<ul>
									<li><a href="#"><i class="fa fa-angle-left"></i></a></li>
									<li><a href="#">01</a></li>
									<li><a href="#">02</a></li>
									<li><a href="#">....</a></li>
									<li><a href="#">08</a></li>
									<li><a href="#"><i class="fa fa-angle-right"></i></a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="city_requset_wrap requst02">
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
