    @extends('layouts.admin')

    @section('title', 'Dashboard')

    @section('plugins.Sweetalert2', true)

    @section('content_header')
        <h1 class="m-0">Konfirmasi Ujian</h1>
    @stop

    @section('content')

    <div class="container konfirmasi-wrap">
        <!-- Card Utama -->
        <div class="card shadow-lg p-0 konfirmasi-card">
            <div class="card-header text-white py-3 konfirmasi-head">
                <div class="container d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Konfirmasi Ujian</h4>
                        <div class="small opacity-75">Periksa data sebelum memulai ujian</div>
                    </div>
                    <div>
                        @if ($status == 'Sudah dimulai')
                            <span class="badge bg-success fs-6"><i class="fas fa-circle-play me-1"></i>{{ $status }}</span>
                        @else
                            <span class="badge bg-secondary fs-6">{{ $status }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                @if(isset($ujian) && isset($siswa))
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="info-tile">
                                <div class="info-label"><i class="fas fa-user me-1"></i> Nama</div>
                                <div class="info-value">{{ $siswa->nama }}</div>
                            </div>
                            <div class="info-tile">
                                <div class="info-label"><i class="fas fa-school me-1"></i> Sekolah</div>
                                <div class="info-value">{{ $siswa->sekolah ?? '-' }}</div>
                            </div>

                            <div class="info-tile">
                                <div class="info-label"><i class="fas fa-book me-1"></i> Mapel</div>
                                <div class="mt-1">
                                    @if(isset($mapels) && $mapels->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($mapels as $m)
                                                <span class="badge bg-info text-dark">{{ $m->nama }}</span>
                                            @endforeach
                                        </div>
                                    @elseif(isset($mapel))
                                        <span class="badge bg-info text-dark">{{ $mapel->nama }}</span>
                                    @else
                                        <div>-</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="info-tile highlight">
                                <div class="row">
                                    <div class="col-6 mb-2"><div class="info-label">Nama Ujian</div><div class="info-value">{{ $ujian->nama_ujian }}</div></div>
                                    <div class="col-6 mb-2"><div class="info-label">Durasi</div><div class="info-value">{{ $ujian->waktu }} menit</div></div>
                                    <div class="col-12 mb-2"><div class="info-label">Mulai</div><div>{{ isset($mulai) ? $mulai->format('d M Y H:i') : $ujian->tgl_mulai }}</div></div>
                                    <div class="col-12"><div class="info-label">Berakhir</div><div>{{ isset($terlambat) ? $terlambat->format('d M Y H:i') : $ujian->terlambat }}</div></div>
                                </div>
                            </div>

                            <div class="mt-3">
                                @if ($status == 'Sudah dimulai')
                                    @if(!empty($ongoingAttempt) && $ongoingAttempt)
                                        <div class="alert alert-warning">Anda memiliki sesi ujian yang sedang berlangsung. Klik tombol di bawah untuk melanjutkan.</div>
                                        <a href="{{ route('mulai_ujian') }}" class="btn btn-lg btn-warning btn-mulai"><i class="fas fa-forward me-1"></i> Lanjutkan Ujian</a>
                                    @elseif(!empty($alreadyParticipated) && $alreadyParticipated)
                                        <div class="alert alert-info">Sudah ujian: Anda telah mengumpulkan jawaban pada <strong>{{ $lastAttemptTime }}</strong>. Pengulangan tidak diperbolehkan kecuali admin membuka sesi tambahan.</div>
                                        <a href="{{ route('daftar_ujian') }}" class="btn btn-lg btn-secondary">Kembali</a>
                                    @else
                                        <div class="alert alert-success small"><i class="fas fa-circle-check me-1"></i> Data valid. Klik tombol di bawah untuk mulai — timer berjalan setelah halaman soal terbuka.</div>
                                        <a href="{{ route('mulai_ujian') }}" class="btn btn-lg btn-success btn-mulai"><i class="fas fa-play me-1"></i> Mulai Ujian</a>
                                    @endif
                                @else
                                    <div class="alert alert-secondary">Ujian belum dimulai. Silakan kembali dan coba lagi setelah waktu mulai.</div>
                                    <a href="{{ route('daftar_ujian') }}" class="btn btn-lg btn-secondary">Kembali</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">Tidak ada data ujian.</div>
                @endif
            </div>
        </div>
        <!-- End of Card Utama -->
    </div> <!-- End of Container -->

    @stop

    @section('plugins.Sweetalert2', true)

    @section('css')
        <style>
            .konfirmasi-wrap { max-width: 960px; }
            .konfirmasi-card { border-radius: 18px; overflow: hidden; border: 0; }
            .konfirmasi-head { background: linear-gradient(120deg, #254baa 0%, #06b6d4 100%); border: 0; }
            .info-tile { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; }
            .info-tile.highlight { background: #eef4ff; border-color: #c9dbff; }
            .info-label { font-size: .75rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #64748b; margin-bottom: 2px; }
            .info-value { font-weight: 600; color: #0f172a; }
            .btn-mulai { border-radius: 12px; padding: 12px 30px; font-weight: 700; }
            .shadow-lg {
                border-radius: 10px;
                background-color: #fff;
            }
            .shadow-sm {
                border-radius: 8px;
            }
        </style>
    @stop

    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            console.log("Hi, I'm using the Laravel-AdminLTE package!");
        </script>
    @stop
