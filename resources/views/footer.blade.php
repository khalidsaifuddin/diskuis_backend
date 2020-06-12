<div class="city_requset_wrap">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="city_request_list">
                    <div class="city_request_row">
                        <span><i class="far fa-newspaper"></i></span>

                        <div class="city_request_text">
                            <span>10 Berita Terbaru</span>

                            <h4>Berita Terkini!</h4>
                        </div>
                    </div>

                    <div class="city_request_link">
                        <ul class="clearfix">
                            <?php foreach ($data['berita_top_5']['data'] as $key): ?>
                                <li>
                                    <a href="/berita-detail/?slug={{ $key->slug }}" title="{{ $key->judul }}">{{ $key->judul }}</a>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </div>

          <!--   <div class="col-md-6 col-sm-6">
                <div class="city_request_list">
                    <div class="city_request_row">
                        <span><i class="fas fa-calendar-week"></i></span>

                        <div class="city_request_text">
                            <span>5 Agenda Terbaru</span>

                            <h4>Agenda Terbaru</h4>
                        </div>
                    </div>

                    <div class="city_request_link">
                        <ul>
                            <?php foreach ($data['kegiatan_top_5'] as $key): ?>
                                <li><a href="/kegiatam-detail/?slug={{ $key->kegiatan_id }}" title="{{ $key->nama }}">{{ $key->nama }}</a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<footer>
    <div class="widget_wrap overlay">
        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <div class="widget_list">
                        <h4 class="widget_title">Lokasi Kami</h4>

                        <div class="widget_text">
                            <ul>
                                <li><a href="#">Gedung E Lantai 14</a></li>
                                <li><a href="#">Kementerian Pendidikan dan Kebudayaan</a></li>
                                <li><a href="#">Jalan Jendral Sudirman Senayan Jakarta</a></li>
                                <li>&nbsp;</li>
                                <li><a href="#" style="color: #8ed3ff"><i class="fas fa-envelope-square"></i> pmp.dikdasmen@kemdikbud.go.id</a></li>
                                <li><a href="#" style="color: #8ed3ff"><i class="fas fa-envelope-square"></i> satgas.pmp@gmail.com</a></li>
                                <li><a href="#" style="color: #8ed3ff"><i class="fas fa-envelope-square"></i> helpdeskpmp07@gmail.com</a></li>
                            </ul>
                        </div> 
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="row">
                        <div class="col-md-4 col-sm-6">
                            <div class="widget_list">
                                <h4 class="widget_title">Layanan Utama</h4>

                                <div class="widget_service">
                                    <ul>
                                        <li><a href="http://dapo.dikdasmen.kemdikbud.go.id/" title="Data Pokok Pendidikan Dasar dan Menengah" target="_blank">Dapodikdasmen</a></li>
                                        <li><a href="http://sekolah.data.kemdikbud.go.id/" title="Sekolah Kita" target="_blank">Sekolah Kita</a></li>
                                        <li><a href="http://gtk.kemdikbud.go.id/" title="Guru dan Tenaga Kependidikan" target="_blank">GTK</a></li>
                                        <li><a href="http://referensi.data.kemdikbud.go.id/" title="Referensi Data" target="_blank">Referensi Data</a></li>
                                        <li><a href="http://publikasi.data.kemdikbud.go.id/" title="Statistik Pendidikan" target="_blank">Statistik Pendidikan</a></li>
                                        <li><a href="http://bansm.or.id/" title="Akreditasi Sekolah" target="_blank">Akreditasi Sekolah</a></li>
                                        <li><a href="http://bsnp-indonesia.org/" title="Standar Nasional Pendidikan" target="_blank">Standar Nasional Pendidikan</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="widget_list">
                                <h4 class="widget_title">Informasi</h4>

                                <div class="widget_service">
                                    <ul>
                                        <li><a href="/berita">Berita</a></li>
                                        <li><a href="/daftar-lpmp">Daftar LPMP</a></li>
                                        <!-- <li><a href="/kontak">Kontak</a></li> -->
                                        <!-- <li><a href="/feedback">Feedback</a></li> -->
                                        <!-- <li><a href="/faq">F.A.Q.</a></li> -->
                                        <!-- <li><a href="/layanan/agenda">Agenda</a></li> -->
                                        <!-- <li><a href="#">Gallery</a></li> -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-6">
                            <div class="widget_list">
                                <h4 class="widget_title">Sosial Media</h4>

                                <div class="widget_service">
                                    <ul>
                                        <li><a title="Facebook PMP Dikdasmen" target="_blank" href="https://facebook.com/PMP-Dikdasmen-1721700981428935/"><i class="fab fa-facebook"></i> Facebook</a></li>
                                        <li><a title="Twitter PMP Dikdasmen" target="_blank" href="https://twitter.com/PMPDikdasmen"><i class="fab fa-twitter"></i> Twitter</a></li>
                                        <li><a title="Google+ PMP Dikdasmen" target="_blank" href="https://plus.google.com/+PMPDikdasmen"><i class="fab fa-google-plus"></i> Google Plus</a></li>
                                        <li><a title="Youtube PMP Dikdasmen" target="_blank" href="https://youtube.com/user/pmpdikdasmen"><i class="fab fa-youtube"></i> YouTube</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="widget_copyright">
                    <div class="copyright_text">
                        <p><span>Copyright &copysr; 2019 Kementerian Pendidikan dan Kebudayaan.</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>