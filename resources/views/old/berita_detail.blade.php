@extends('template')

@section('main')
  <body class="demo-5">
    <div class="wrapper"> 
      <?= view('menu', compact('data')) ?>
  
			<div class="sab_banner overlay">
        <div class="container">
          <div class="sab_banner_text">
            <h2>Rilis Pembaruan Aplikasi Pemetaan PMP 2018.05</h2>

            <ul class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Beranda</a></li>
              <li class="breadcrumb-item"><a href="#">Berita</a></li>
              <li class="breadcrumb-item active">Rilis Pembaruan Aplikasi Pemetaan PMP 2018.05</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="city_blog2_wrap">
        <div class="container">
          <div class="row">
            <div class="col-md-8">
              <div class="city_blog2_fig fig2 detail">

                <div class="blog_detail_row">
                  <div class="city_blog2_list">
                    <ul class="city_meta_list">
                      <li><a href="#"><i class="fa fa-calendar"></i>{{ $data['berita'][0]->tanggal_publis }}</a></li>
                      <li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                    </ul>

                    <div class="city_blog2_text">
                      <h4><a href="#">{{ $data['berita'][0]->judul }}</a></h4>
                    </div>
                  </div>

                  <?= $data['berita'][0]->konten_berita ?>

                  <div class="city_event_meta">
                    <div class="city_event_tags">
                      <span>Kategori:</span>

                      <span>{{ $data['berita'][0]->kategori_berita }}</span>
                    </div>

                    <div class="city_top_social">
                      <span>Bagikan:</span>

                      <ul>
                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fab fa-youtube"></i></a></li>
                        <li><a href="#"><i class="fab fa-google"></i></a></li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div class="blog_next_post">
                  <ul>
                    <li><a href="#"><i class="fa fa-angle-left"></i>Berita Sebelumnya</a></li>
                    <li><a href="#">Berita Selanjutnya<i class="fa fa-angle-right"></i></a></li>
                  </ul>
                </div>
              </div>

              <div class="blog_post_author">
                <figure class="box">
                  <div class="box-layer layer-1"></div>
                  <div class="box-layer layer-2"></div>
                  <div class="box-layer layer-3"></div>

                  <img src="http://kodeforest.net/html/baldiyat/extra-images/blog-authore.jpg" alt="" />
                </figure>

                <div class="blog_post_author_text">
                  <h5>Monica Brandson Author</h5>
                  <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed odio</p>
                  <div class="city_top_social">
                    <ul>
                      <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                      <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                      <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                      <li><a href="#"><i class="fab fa-youtube"></i></a></li>
                      <li><a href="#"><i class="fab fa-google"></i></a></li>
                    </ul>
                  </div>
                </div>
              </div>

              <div class="blog_post_slide">
                <h4 class="sidebar_heading">Berita Terkait</h4>

                <div class="blog-post-slider">
                  <div>
                    <div class="blog_post_slide_fig">
                      <figure class="box">
                        <div class="box-layer layer-1"></div>
                        <div class="box-layer layer-2"></div>
                        <div class="box-layer layer-3"></div>

                        <img src="http://kodeforest.net/html/baldiyat/extra-images/post-slide.jpg" alt="" />
                      </figure>

                      <div class="blog_post_slide_text">
                        <h6><a href="#">Financial Crisis in Worldwide <br>Economics and Banking</a></h6>
                      </div>
                    </div>
                  </div>

                  <div>
                    <div class="blog_post_slide_fig">
                      <figure class="box">
                        <div class="box-layer layer-1"></div>
                        <div class="box-layer layer-2"></div>
                        <div class="box-layer layer-3"></div>

                        <img src="http://kodeforest.net/html/baldiyat/extra-images/post-slide1.jpg" alt="" />
                      </figure>

                      <div class="blog_post_slide_text">
                        <h6><a href="#">Financial Crisis in Worldwide <br>Economics and Banking</a></h6>
                      </div>
                    </div>
                  </div>

                  <div>
                    <div class="blog_post_slide_fig">
                      <figure class="box">
                        <div class="box-layer layer-1"></div>
                        <div class="box-layer layer-2"></div>
                        <div class="box-layer layer-3"></div>

                        <img src="http://kodeforest.net/html/baldiyat/extra-images/post-slide.jpg" alt="" />
                      </figure>

                      <div class="blog_post_slide_text">
                        <h6><a href="#">Financial Crisis in Worldwide <br>Economics and Banking</a></h6>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="blog_user_comment_row">
                <div class="blog_user_comment">
                  <h4 class="sidebar_heading">Komentar Pengguna</h4>

                  <ul class="forum_replie_list">
                    <li>
                      <div class="forum_user_replay padding0">
                        <figure class="box">
                          <div class="box-layer layer-1"></div>
                          <div class="box-layer layer-2"></div>
                          <div class="box-layer layer-3"></div>

                          <img src="http://kodeforest.net/html/baldiyat/extra-images/replie-fig.jpg" alt="" />
                        </figure>

                        <div class="forum_user_detail">
                          <div class="forum_user_meta">
                            <h5>Monica Brandson</h5>

                            <ul class="city_meta_list">
                              <li><a href="#"><i class="fa fa-calendar"></i>June 15, 2018 23:00</a></li>
                              <li><a href="#"><i class="fa fa-reply-all"></i>Jawab</a></li>
                            </ul>
                          </div>

                          <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed </p>
                        </div>
                      </div>

                      <ul class="chlid">
                        <li>
                          <div class="forum_user_replay">
                            <figure class="box">
                              <div class="box-layer layer-1"></div>
                              <div class="box-layer layer-2"></div>
                              <div class="box-layer layer-3"></div>

                              <img src="http://kodeforest.net/html/baldiyat/extra-images/replie-fig1.jpg" alt="" />
                            </figure>
                            
                            <div class="forum_user_detail">
                              <div class="forum_user_meta">
                                <h5>Monica Brandson</h5>

                                <ul class="city_meta_list">
                                  <li><a href="#"><i class="fa fa-calendar"></i>June 15, 2018 23:00</a></li>
                                  <li><a href="#"><i class="fa fa-reply-all"></i>Jawab</a></li>
                                </ul>
                              </div>

                              <p>This is Photoshop's Lorem Ipsum. Proin gravida nibh. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. </p>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </li>

                    <li>
                      <div class="forum_user_replay border-none">
                        <figure class="box">
                          <div class="box-layer layer-1"></div>
                          <div class="box-layer layer-2"></div>
                          <div class="box-layer layer-3"></div>

                          <img src="http://kodeforest.net/html/baldiyat/extra-images/replie-fig2.jpg" alt="" />
                        </figure>

                        <div class="forum_user_detail">
                          <div class="forum_user_meta">
                            <h5>Monica Brandson</h5>

                            <ul class="city_meta_list">
                              <li><a href="#"><i class="fa fa-calendar"></i>June 15, 2018 23:00</a></li>
                              <li><a href="#"><i class="fa fa-reply-all"></i>Jawab</a></li>
                            </ul>
                          </div>

                          <p>This is Photoshop's version  of Lorem Ipsum. Proin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit. Duis sed </p>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>

                <div class="event_booking_form">
                  <h4 class="sidebar_heading">Tinggalkan Komentar</h4>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="event_booking_field">
                        <input type="text" placeholder="Nama" />
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="event_booking_field">
                        <input type="text" placeholder="Email" />
                      </div>
                    </div>

                    <div class="col-md-12">
                      <div class="event_booking_area">
                        <textarea>Komentar</textarea>
                      </div>

                      <a class="theam_btn btn2" href="#">Kirim</a>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-4">
              <div class="sidebar_widget">
                <div class="event_sidebar">
                  <h4 class="sidebar_heading">Pencarian</h4>

                  <div class="sidebar_search">
                    <input type="text" placeholder="Pencarian" required />

                    <button><i class="fas fa-search"></i></button>
                  </div>
                </div>

                <div class="event_sidebar">
                  <h4 class="sidebar_heading">Kategori</h4>

                  <div class="categories_list">
                    <ul>
                      <?php foreach ($data['kategori'] as $key): ?>
                      <li><a href="#">{{ $key->nama }}</a></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>

                <div class="event_sidebar">
                  <h4 class="sidebar_heading">Ikuti Kami</h4>

                  <div class="city_top_social">
                    <ul>
                      <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                      <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                      <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                      <li><a href="#"><i class="fab fa-youtube"></i></a></li>
                      <li><a href="#"><i class="fab fa-google"></i></a></li>
                      <li><a href="#"><i class="fab fa-google"></i></a></li>
                    </ul>
                  </div>
                </div>

                <div class="event_sidebar">
                  <h4 class="sidebar_heading">Berita Terbaru</h4>

                  <div class="event_categories">
                    <ul>
                      <li>
                        <div class="event_categories_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/post-fig.jpg" alt="" />
                          </figure>

                          <div class="event_categories_text">
                            <h6><span>5 ldeas for Fun</span> Family Activites</h6>

                            <ul class="blog_author_date">
                              <li><a href="#">by author</a></li>
                              <li><a href="#">15, Aug 2018</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>

                      <li>
                        <div class="event_categories_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/post-fig1.jpg" alt="" />
                          </figure>

                          <div class="event_categories_text">
                            <h6><span>5 ldeas for Fun</span> Family Activites</h6>

                            <ul class="blog_author_date">
                              <li><a href="#">by author</a></li>
                              <li><a href="#">15, Aug 2018</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>

                      <li>
                        <div class="event_categories_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/post-fig2.jpg" alt="" />
                          </figure>

                          <div class="event_categories_text">
                            <h6><span>5 ldeas for Fun</span> Family Activites</h6>

                            <ul class="blog_author_date">
                              <li><a href="#">by author</a></li>
                              <li><a href="#">15, Aug 2018</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>

                      <li>
                        <div class="event_categories_list">
                          <figure class="box">
                            <div class="box-layer layer-1"></div>
                            <div class="box-layer layer-2"></div>
                            <div class="box-layer layer-3"></div>

                            <img src="http://kodeforest.net/html/baldiyat/extra-images/post-fig3.jpg" alt="" />
                          </figure>

                          <div class="event_categories_text">
                            <h6><span>5 ldeas for Fun</span> Family Activites</h6>

                            <ul class="blog_author_date">
                              <li><a href="#">by author</a></li>
                              <li><a href="#">15, Aug 2018</a></li>
                            </ul>
                          </div>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>

                <div class="event_sidebar">
                  <h4 class="sidebar_heading">Arsip</h4>
                  <div class="categories_list archive">
                    <ul>
                      <li><a href="#">March</a></li>
                      <li><a href="#">Febuary</a></li>
                      <li><a href="#">January</a></li>
                      <li><a href="#">December</a></li>
                      <li><a href="#">November</a></li>
                      <li><a href="#">October</a></li>
                    </ul>
                  </div>
                </div>

                <div class="event_sidebar margin0">
                  <h4 class="sidebar_heading">Tag Populer</h4>

                  <div class="blog_tags">
                    <a href="#">fashion</a>
                    <a href="#">woman</a>
                    <a href="#">studio</a>
                    <a href="#">photo</a>
                    <a href="#">man</a>
                    <a href="#">html</a>
                    <a href="#">css</a>
                    <a href="#">joomla</a>
                    <a href="#">wp</a>
                    <a href="#">fashion</a>
                    <a href="#">woman</a>
                    <a href="#">studio</a>
                    <a href="#">photo</a>
                  </div>
                </div>
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
