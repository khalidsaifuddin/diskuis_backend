@extends('template')

@section('main')
<body class="demo-5">
    <div class="wrapper"> 
        <?= view('menu', compact('data')) ?>

        <div class="sab_banner overlay">
            <div class="container">
                <div class="sab_banner_text">
                    <h2>Unduhan</h2>

                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                        <li class="breadcrumb-item active">Unduhan</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="city_health_wrap">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        <div class="city_health_text">
                            <h2>Sudah tahu belum, <span><strong style="color: #14284b">Apa itu Aplikasi PMP?</strong></span></h2>

                            <p>Seiring dengan perkembangan kebutuhan pemanfaatan data Dapodik dan PMP juga menindaklanjuti laporan ditemukannya beberapa masalah teknis pada Aplikasi Pemetaan PMP versi 2018.05, serta dalam rangka terus meningkatkan kualitas data mutu pendidikan maka senantiasa dilakukan perbaikan dan penyempurnaan Aplikasi Pemetaan PMP </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="city_health_fig">
                            <figure class="box">
                                <div class="box-layer layer-1"></div>
                                <div class="box-layer layer-2"></div>
                                <div class="box-layer layer-3"></div>

                                <img src="/portal_pmp/assets/img/app-fig.jpg" alt="Aplikasi PMP" />
                            </figure>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="city_service_detail_wrap">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="sidebar_widget">
                            <div class="city_service_tabs tabs">
                                <ul class="tab-links">
                                    <li class="active"><a href="#tab1">Aplikasi PMP Online</a></li>
                                    <li><a href="#tab2">Aplikasi PMP Android</a></li>
                                    <li><a href="#tab3">Aplikasi PMP iOS</a></li>
                                    <li><a href="#tab4">Aplikasi PMP Offline</a></li>
                                    <li><a href="#tab5">Dokumentasi</a></li>
                                </ul>
                            </div>

                            <div class="city_side_info">
                                <span><i class="fab fa-github"></i></span>

                                <h4>Butuh Bantuan?</h4>

                                <h6 style="font-size: 12px;">
                                    pmp.dikdasmen@kemdikbud.go.id<br/>
                                    satgas.pmp@gmail.com<br/>
                                    helpdeskpmp07@gmail.com<br/>
                                </h6>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="tabs">
                            <div class="tab-content">
                                <div id="tab1" class="tab active">
                                    <div class="city_department_list">
                                        <ul>
                                            <?php foreach ($data['unduhan_online'] as $key): ?>
                                                <li>
                                                    <div class="city_department2_fig">
                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            <div class="box-layer layer-2"></div>
                                                            <div class="box-layer layer-3"></div>

                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Aplikasi PMP Online" />
                                                        </figure>

                                                        <div class="city_department2_text">
                                                            <h5>{{ $key->nama }}</h5>

                                                            <?= $key->keterangan ?>
                                                            
                                                            <a target="_blank" class="theam_btn border-color color" style="margin-top: 15px" href="{{ $key->link }}" tabindex="0">{{ $key->link == '' ? 'Segera': 'Buka Aplikasi' }}</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>

                                <div id="tab2" class="tab">
                                    <div class="city_department_list">
                                        <ul>
                                            <?php foreach ($data['unduhan_andrroid'] as $key): ?>
                                                <li>
                                                    <div class="city_department2_fig">
                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            <div class="box-layer layer-2"></div>
                                                            <div class="box-layer layer-3"></div>

                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Aplikasi PMP Online" />
                                                        </figure>

                                                        <div class="city_department2_text">
                                                            <h5>{{ $key->nama }}</h5>

                                                            <?= $key->keterangan ?>
                                                            
                                                            <a target="_blank" class="theam_btn border-color color" style="margin-top: 15px" href="{{ $key->link }}" tabindex="0">{{ $key->link == '' ? 'Segera': 'Unduh Aplikasi' }}</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>

                                <div id="tab3" class="tab">
                                    <div class="city_department_list">
                                        <ul>
                                            <?php foreach ($data['unduhan_ios'] as $key): ?>
                                                <li>
                                                    <div class="city_department2_fig">
                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            <div class="box-layer layer-2"></div>
                                                            <div class="box-layer layer-3"></div>

                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Aplikasi PMP Online" />
                                                        </figure>

                                                        <div class="city_department2_text">
                                                            <h5>{{ $key->nama }}</h5>

                                                            <?= $key->keterangan ?>
                                                            
                                                            <a target="_blank" class="theam_btn border-color color" style="margin-top: 15px" href="{{ $key->link }}" tabindex="0">{{ $key->link == '' ? 'Segera': 'Unduh Aplikasi' }}</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>

                                <div id="tab4" class="tab">
                                    <div class="city_department_list">
                                        <ul>
                                            <?php foreach ($data['unduhan_offline'] as $key): ?>
                                                <li>
                                                    <div class="city_department2_fig">
                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            <div class="box-layer layer-2"></div>
                                                            <div class="box-layer layer-3"></div>

                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Aplikasi PMP Online" />
                                                        </figure>

                                                        <div class="city_department2_text">
                                                            <h5>{{ $key->nama }}</h5>

                                                            <?= $key->keterangan ?>
                                                            
                                                            <a target="_blank" class="theam_btn border-color color" style="margin-top: 15px" href="{{ $key->link }}" tabindex="0">{{ $key->link == '' ? 'Segera': 'Unduh Aplikasi' }}</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                </div>

                                <div id="tab5" class="tab">
                                    <div class="city_department_list">
                                        <?php foreach ($data['unduhan_dokumen'] as $key): ?>
                                            <li>
                                                <div class="city_department2_fig" style=" margin-bottom: 16px;">
                                                    <figure class="box" style="width: 15% !important">
                                                        <div class="box-layer layer-1"></div>
                                                        <div class="box-layer layer-2"></div>
                                                        <div class="box-layer layer-3"></div>

                                                        <img src="{{ $key->keterangan }}" alt="Aplikasi PMP Online" />
                                                    </figure>

                                                    <div class="city_department2_text">
                                                        <h5>{{ $key->nama }}</h5>
                                                        <a target="_blank" class="theam_btn border-color color" style="margin-top: 15px" href="{{ $key->link }}" tabindex="0">Unduh Pedoman</a>
                                                    </div>
                                                </div>
                                            </li>
                                        <?php endforeach ?>
                                            <!-- <h4>Daftar Pembaruan Versi 18.07</h4>
                                            <ol>
                                                <li><b style="color:blue">[Pembaruan]</b> Penyesuaian untuk Sekolah Pendidikan Kerjasama (SPK)</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Menu rapor mutu sekolah</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Menu upload file rapor mutu sekolah dari website PMP (pmp.dikdasmen.kemdikbud.go.id)</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Tampilan validasi jawaban sekolah pada kuesioner kepala sekolah</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Pakta Integritas sebelum konfirmasi penyelesaian pengisian kuesioner</li>
                                                <li><b style="color:green">[Perbaikan]</b> Perbaikan validasi jawaban sekolah pada tampilan kuesioner pengawas</li>
                                                <li><b style="color:green">[Perbaikan]</b> Perbaikan fungsi restore data </li>
                                            </ol>


                                            <h4>Daftar Pembaruan Versi 2018.05</h4>
                                            <ol>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan kesalahan validasi keterisian seluruh responden untuk pengawas</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan fitur backup data</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan deteksi kepala sekolah di Dapodik</li>
                                            </ol>
                                            
                                            <h4>Daftar Pembaruan Versi 2018.04</h4>
                                            <ol>
                                                <li><b style="color:blue">[Pembaruan]</b> Kolom validasi jawaban sekolah di form kuesioner khusus pengawas</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Validasi isian persentase hanya bisa diisi oleh angka</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Validasi isian persentase batas maksimal persentase 100 persen dan batas minimal 0 persen</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Prosedur pengawas boleh mengisi kuesioner setelah jumlah responden minimal yang mengerjakan kuesioner di sekolah telah terpenuhi</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Rekaman login pengguna</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Rekaman durasi pengerjaan kuesioner pengguna</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Cetak kuesioner yang telah terisi</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Muat ulang halaman kuesioner</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Pembaruan tampilan aplikasi</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Fitur hapus data pengguna lebih dari satu secara bersamaan</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Fitur tukar pengguna dari menu manajemen pengguna</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Fitur tukar pengguna dari menu verifikasi</li>
                                                <li><b style="color:blue">[Pembaruan]</b> Unduhan instrumen PMP 2018</li>
                                            </ol>

                                            <h4>Daftar Pembaruan Versi 2.2</h4>
                                            <ol>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan salin peserta didik mengakomodir kelas terbuka</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Daftar peserta didik tidak tampil di tabel verifikasi peserta didik</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Hanya Menampilkan pengguna yang telah mengisi kuesioner di tabel verifikasi</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Deteksi kepala sekolah sesuai sekolah yang sedang login</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Deteksi kepala sekolah sesuai aturan dari GTK di dapodik</li>
                                                <li><b style="color:#2196F3">[Pembaruan]</b> Tombol tukar pengguna di tabel verifikasi peserta didik</li>
                                                <li><b style="color:#2196F3">[Pembaruan]</b> Tombol tukar pengguna di tabel verifikasi PTK</li>
                                                <li><b style="color:#2196F3">[Pembaruan]</b> Perbaikan backup dan restore pengguna</li>
                                            </ol>

                                            <h4>Daftar Pembaruan Versi 2.1</h4>
                                            <ol>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan link verifikasi dan kirim data di beranda</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Tambah label bidang studi untuk pilihan tukar pengguna guru</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Tambah label tingkat kelas untuk pilihan tukar pengguna peserta didik</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan bug verifikasi PTK/PD tidak bisa 100%</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan bug jawaban isian bebas</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan bug data ganda ketika restore file backup</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Optimasi pemuatan data verifikasi peserta didik</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Tambah isian pencarian menggunakan nama/NISN di verifikasi peserta didik</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Tambah isian pencarian menggunakan nama/email di verifikasi PTK</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Mengikutsertakan data konfirmasi instrumen pengguna ketika proses restore file backup</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan tampilan kepala sekolah mengikuti prosedur baru Dapodik versi 2018</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan fungsi hapus pengguna (PTK dan peserta didik) yang telah tidak aktif di dapodik</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan salin tanggal lahir dan NIK untuk peserta didik</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan salin tanggal lahir, NIK, NUPTK, dan NIP untuk PTK</li>
                                                <li><b style="color:#4CAF50">[Perbaikan]</b> Perbaikan data pengguna telah konfirmasi tidak terekap di tabel kirim data</li>
                                            </ol> -->
                                            <!-- <ul>
                                                <li>
                                                    <div class="city_department2_fig">
                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            <div class="box-layer layer-2"></div>
                                                            <div class="box-layer layer-3"></div>
                                                            
                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Dokumentasi Aplikasi PMP v1.3.0" />
                                                        </figure>

                                                        <div class="city_department2_text">
                                                            <h5>Dokumentasi Aplikasi PMP v1.3.0</h5>
                                                            
                                                            <p>Poin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit.</p>
                                                            
                                                            <a class="theam_btn border-color color" style="margin-top: 15px" href="#" tabindex="0">Unduh Aplikasi</a>
                                                        </div>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="city_department2_fig">
                                                        <div class="city_department2_text text2">
                                                            <h5>Dokumentasi Aplikasi PMP v1.2.0</h5>
                                                            
                                                            <p>Poin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit.</p>
                                                            
                                                            <a class="theam_btn border-color color" style="margin-top: 15px" href="#" tabindex="0">Unduh Aplikasi</a>
                                                        </div>

                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            
                                                            <div class="box-layer layer-2"></div>
                                                            
                                                            <div class="box-layer layer-3"></div>
                                                            
                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Dokumentasi Aplikasi PMP v1.2.0" />
                                                        </figure>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="city_department2_fig">
                                                        <figure class="box">
                                                            <div class="box-layer layer-1"></div>
                                                            <div class="box-layer layer-2"></div>
                                                            <div class="box-layer layer-3"></div>
                                                            
                                                            <img src="/portal_pmp/assets/img/app-pmp.jpg" alt="Dokumentasi Aplikasi PMP v1.0.0" />
                                                        </figure>
                                                        
                                                        <div class="city_department2_text">
                                                            <h5>Dokumentasi Aplikasi PMP v1.0.0</h5>
                                                            
                                                            <p>Poin gravida nibh vel velit auctor aliquet. Aenean sollicitudin, lorem quis bibendum auctor, nisi elit consequat ipsum, nec sagittis sem nibh id elit.</p>
                                                            
                                                            <a class="theam_btn border-color color" style="margin-top: 15px" href="#" tabindex="0">Unduh Aplikasi</a>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
      <?= view('footer', compact('data')); ?>
  </div>
</body>
@stop
