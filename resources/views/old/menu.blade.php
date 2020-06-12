<?php
$title = $data['title'];

?>
<header>
  <div class="city_top_wrap">
    <div class="container-fluid">
      <div class="city_top_logo">
        <a href="#" class="clearfix">
          <img src="/assets/img/logo-pmp.png" alt="PMP Dikdasmen" />

          <div class="title-logo">
            <h3>Penjaminan Mutu Pendidikan</h3>
            
            <span>Direktorat Jendral Pendidikan Dasar dan Menengah</span>
          </div>
        </a>
      </div>

      <div class="city_top_news">
        <span>Berita Terkini</span>

        <div class="city-news-slider">
          <div>
            <p>Pengiriman Ulang dan Pemrosesan Rapor Mutu Data PMP 2018</p>
          </div>

          <div>
            <p>RILIS UPDATER PMP 2018.08 DAN PERPANJANGAN CUT OFF PMP TAHUN 2018</p>
          </div>

          <div>
            <p>Rilis Pembaruan Aplikasi Pemetaan PMP 2018.07</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="city_top_navigation">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-9">
          <div class="navigation">
            <ul>
              <li>
                <a class="{{ $title == 'beranda' ? 'active' : '' }}" href="/">Beranda</a>
              </li>
              
              <li>
                <a class="{{ $title == 'berita' ? 'active' : '' }}" href="/berita">Berita</a>
              </li>

              <li>
                <a class="{{ $title == 'unduhan' ? 'active' : '' }}" href="/unduhan">Unduhan</a>
              </li>
            </ul>     
          </div>

          <div id="kode-responsive-navigation" class="dl-menuwrapper">
            <button class="dl-trigger">Open Menu</button>

            <ul class="dl-menu">
              <li>
                <a class="{{ $title == 'Beranda' ? 'active' : '' }}" href="/">Beranda</a>
              </li>
              
              <li class="menu-item kode-parent-menu">
                <a class="{{ $title == 'berita' ? 'active' : '' }}" href="/berita">Berita</a>
              </li>

              <li class="menu-item kode-parent-menu">
                <a class="{{ $title == 'unduhan' ? 'active' : '' }}" href="/unduhan">Unduhan</a>
              </li>
            </ul>
          </div>
        </div>

        <div class="col-md-3">
          <div class="city_top_form">
            <div class="city_top_search">
              <input type="text" placeholder="Pencarian" />
              <a href="#"><i class="fa fa-search"></i></a>
            </div>

            <a class="top_user" href="#"><i class="fa fa-user"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="city_top_navigation hide">
    <div class="container-fluid">
      <div class="row">
        <div class="col-md-9">
          <div class="navigation">
            <ul>
              <li class="current">
                <a class="{{ $title == 'beranda' ? 'active' : '' }}" href="/">Beranda</a>
              </li>
              
              <li>
                <a class="{{ $title == 'berita' ? 'active' : '' }}" href="/berita">Berita</a>
              </li>

              <li>
                <a class="{{ $title == 'unduhan' ? 'active' : '' }}" href="/unduhan">Unduhan</a>
              </li>
            </ul>                 
          </div>

          <div id="kode-responsive-navigation1" class="dl-menuwrapper">
            <button class="dl-trigger">Open Menu</button>

            <ul class="dl-menu">
              <li class="current">
                <a class="{{ $title == 'beranda' ? 'active' : '' }}" href="/">Beranda</a>
              </li>
              
              <li class="menu-item kode-parent-menu">
                <a class="{{ $title == 'berita' ? 'active' : '' }}" href="/berita">Berita</a>
              </li>

              <li class="menu-item kode-parent-menu">
                <a class="{{ $title == 'unduhan' ? 'active' : '' }}" href="/unduhan">Unduhan</a>
              </li>
            </ul>
          </div>
        </div>

        <div class="col-md-3">
          <div class="city_top_form">
            <div class="city_top_search">
              <input type="text" placeholder="Pencarian" />
              <a href="#"><i class="fas fa-search"></i></a>
            </div>

            <a class="top_user" href="#"><i class="fas fa-user"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>