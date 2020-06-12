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
            <li class="breadcrumb-item"><a href="/">Beranda</a></li>
            <li class="breadcrumb-item active">Berita</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="city_blog2_wrap" style="background: #FFFFFF">
      <div class="container">
        <div class="row">
          <?php foreach ($data['data'] as $key): ?>
            <div class="col-md-4 col-sm-6">
              <div class="city_blog2_fig">
                <figure class="overlay">
                  <div class="city_blog_thumb" style="background-image: url('{{ $key->images }}')"></div>

                  <a class="paly_btn" data-rel="prettyPhoto" href="{{ $key->images }}">+</a>

                  <span class="city_blog2_met">Informasi</span>
                </figure>

                <div class="city_blog2_list">
                  <ul class="city_meta_list">
                    <li><a href="#"><i class="fa fa-calendar"></i>{{ $key->tanggal_publis }}</a></li>
                    <li><a href="#"><i class="fa fa-comment-o"></i>0 Comments</a></li>
                  </ul>

                  <div class="city_blog2_text">
                    <h5><a href="/berita-detail/?slug={{ $key->slug }}">{{ $key->judul }}</a></h5>

                    <p>{{ $key->deskripsi }}</p>

                    <a class="see_more_btn" href="/berita-detail/?slug={{ $key->slug }}" tabindex="0">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <div class="col-md-12">
          <!-- <div class="pagination"> -->
          <!-- <ul>
            <li><a href="#"><i class="fa fa-angle-left"></i></a></li>
            <li><a class="active" href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">....</a></li>
            <li><a href="#">8</a></li>
            <li><a href="#"><i class="fa fa-angle-right"></i></a></li>
          </ul> -->
          <div class="pagination-holder clearfix">
            <div id="light-pagination" class="pagination"></div>
          </div>
          <!-- </div> -->
        </div>
      </div>
    </div>
  </div>
  <?= view('footer', compact('data')); ?>
</div>
<script type="text/javascript">
  $(document).ready(function(){        
    var selector = $('#light-pagination').pagination({
      pages: "<?= $data['pages'] ?>",
      cssStyle: 'light-theme',
    });

    $(selector).pagination('drawPage', "<?= $data['page'] ?>",);
  });
</script>
</body>
@stop
