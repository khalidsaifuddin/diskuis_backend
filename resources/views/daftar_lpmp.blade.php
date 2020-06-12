@extends('template')

@section('main')
<body class="demo-5">
    <div class="wrapper"> 
        <?= view('menu', compact('data')) ?>

        <div class="sab_banner overlay">
            <div class="container">
                <div class="sab_banner_text">
                    <h2>Daftar LPMP</h2>

                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                        <li class="breadcrumb-item active">Daftar LPMP</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="city_services2_wrap">
            <div class="container">
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
                                    <p>
                                        {{ $key->alamat_jalan }}
                                        <!-- <span class="city_service2_phone"><a style="font-size: 11px; color: #666 !important;" target="_blank" href="{{ $key->website }}"><i class="fas fa-globe"></i> Website LPMP</a></span> -->
                                        <span class="city_service2_phone"><i class="fas fa-phone"></i> {{ $key->no_telepon }} | <a style="font-size: 11px; color: #666 !important;" target="_blank" href="{{ $key->website }}"><i class="fas fa-globe"></i> Website</a></span>
                                    </p>
                                    <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal" onclick="return modal_detail('{{ $key->lpmp_id }}')">
                                        Lihat Detil <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>

                    <!-- <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Bali.jpg')"></div>
                                
                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP</span>Bali</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Letda Tantular No. 14, Renon, Denpasar Tim., Kota Denpasar, Bali</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0361) 225666</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Bangka-Belitung.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Bangka Belitung</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Pulau Bangka Air Ilam Pangkalpinang</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0717) 439420</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Banten.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Banten</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Siliwangi No. 208 Kab. Lebak, Banten</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0252) 209209</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Bengkulu.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Bengkulu</h4>
                                    </div>
                                </div>
                            </figure>
                            
                            <div class="city_service2_text">
                                <p>Jl. Zainul Arifin No. 2 Singaran Pati, Kota Bengkulu</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0736) 26848</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Yogyakarta.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>D.I. Yogyakarta</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Tirtomartani, Kalasan, Sleman, Yogyakarta</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0274) 496921</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-DKI-Jakarta.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>DKI Jakarta</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Nangka No. 60 Tanjung Barat Jagakarsa, Jakarta Selatan</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (021) 7824149</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Gorontalo.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Gorontalo</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. BPG, Desa Tunggulo, Kecamatan Tilongkabla Kabupaten Bone Bolango, Prov. Gorontalo</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0435) 827730</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Nanggroe-Aceh-Darussalam.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Jambi</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Professor Doktor Ny. Sri Sudewi, Sungai Putri, Telanaipura, Kota Jambi, Jambi</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0741) 60449, 669559</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Bangka-Belitung.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Bangka Belitung</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Pulau Bangka Air Ilam Pangkalpinang</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0717) 439420</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Banten.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Banten</h4>
                                    </div>
                                </div>
                            </figure>

                            <div class="city_service2_text">
                                <p>Jl. Siliwangi No. 208 Kab. Lebak, Banten</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0252) 209209</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="city_service2_fig">
                            <figure class="overlay">
                            <div class="city_service2_fig_img" style="background-image: url('http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Bengkulu.jpg')"></div>

                                <div class="city_service2_list">
                                    <div class="city_service2_caption">
                                        <h4><span>LPMP Propinsi</span>Bengkulu</h4>
                                    </div>
                                </div>
                            </figure>
                            
                            <div class="city_service2_text">
                                <p>Jl. Zainul Arifin No. 2 Singaran Pati, Kota Bengkulu</p>

                                <span class="city_service2_phone"><i class="fas fa-phone"></i> (0736) 26848</span>
                                
                                <button type="button" class="see_more_btn" data-toggle="modal" data-target="#lpmpModal">
                                    Lihat Detil <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div> -->
                </div>
            </div>	
        </div>

        <div class="modal lpmp-modal fade" id="lpmpModal" tabindex="-1" role="dialog" aria-labelledby="lpmpModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lpmpModalTitle">...</h5>
                        
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body lpmp-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="lpmp-modal-img" id="lpmp-img" style="background-image: url(http://pmp.dikdasmen.kemdikbud.go.id/files/lpmp/LPMP-Provinsi-Nanggroe-Aceh-Darussalam.jpg)"></div>
                            </div>
                            
                            <div class="col-sm-6">
                                <div class="lpmp-modal-content">
                                    <p><span>Alamat</span><p style="margin-top: -18px;" id="alamat_jalan">...</p></p>

                                    <p><span>Kode Pos</span><p style="margin-top: -18px;" id="kode_pos">...</p></p>

                                    <p style="color: #1c94e1"><span>Lintang</span><p style="margin-top: -18px;" id="lintang">...</p></p>

                                    <p style="color: #1c94e1"><span>Bujur</span><p style="margin-top: -18px;" id="bujur">...</p></p>

                                    <p><span>Telepon</span><p style="margin-top: -18px;" id="no_telepon">...</p></p>

                                    <p><span>Website</span><a href="#" target="_blank" id="website">...</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?= view('footer', compact('data')); ?>

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
    </div>
</body>
@stop
