<?php
  $title = $data['title'];
?>

<header class="clearfix">
    <div class="city_top_wrap">
        <div class="container-fluid clearfix">
            <div class="city_container">
                <div class="city_top_logo">
                    <a href="#" class="clearfix">
                        <img src="/assets/img/logo-pmp.png" alt="PMP Dikdasmen" />

                        <div class="title-logo">
                            <h3>Penjaminan Mutu Pendidikan</h3>
                            
                            <span>Direktorat Jendral Pendidikan Dasar dan Menengah</span>
                        </div>
                    </a>
                </div>

                <div class="city_top_social">
                    <ul>
                        <li><a target="_blank" href="https://facebook.com/PMP-Dikdasmen-1721700981428935/"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a target="_blank" href="https://twitter.com/PMPDikdasmen"><i class="fab fa-twitter"></i></a></li>
                        <!-- <li><a target="_blank" href="#"><i class="fab fa-linkedin"></i></a></li> -->
                        <li><a target="_blank" href="https://youtube.com/user/pmpdikdasmen"><i class="fab fa-youtube"></i></a></li>
                        <li><a target="_blank" href="https://plus.google.com/+PMPDikdasmen"><i class="fab fa-google"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="city_top_news">
            <span>Berita Terkini</span>

            <div class="city-news-slider">
              <?php foreach ($data['berita_top_5']['data'] as $key): ?>
                <div>
                    <a href="/berita-detail/?slug={{ $key->slug }}" title="{{ $key->judul }}">{{ $key->judul }}</a>
                </div>
              <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="city_top_navigation">
        <nav class="navbar navbar-expand-lg navbar-light">
            <a class="navbar-brand clearfix" href="/">
                <img src="/assets/img/logo-pmp.png" alt="PMP Dikdasmen" />

                <div class="title-logo">
                    <h3>Penjaminan Mutu Pendidikan</h3>
                    
                    <span>Ditjen Dikdasmen</span>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarPortalPmp" aria-controls="navbarPortalPmp" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        
            <div class="collapse navbar-collapse" id="navbarPortalPmp">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item {{ $title == 'beranda' ? 'active' : '' }}">
                        <a class="nav-link" href="/">Beranda <span class="sr-only">(current)</span></a>
                    </li>

                    <li class="nav-item {{ $title == 'berita' ? 'active' : '' }}">
                        <a class="nav-link" href="/berita">Berita</a>
                    </li>

                    <li class="nav-item {{ $title == 'unduhan' ? 'active' : '' }}">
                        <a class="nav-link" href="/unduhan">Unduhan</a>
                    </li>

                    <li class="nav-item {{ $title == 'daftar-lpmp' ? 'active' : '' }}">
                        <a class="nav-link" href="/daftar-lpmp">Daftar LPMP</a>
                    </li>
                </ul>

                <div class="navbar-right">
                    <form class="form-inline my-2 my-lg-0">
                        <input class="form-control mr-sm-2" type="search" placeholder="Pencarian" aria-label="Pencarian" />

                        <button class="btn btn-outline-success my-2 my-sm-0" type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </nav>
    </div>
</header>
