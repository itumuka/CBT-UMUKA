@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard Admin</h1>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@stop

@section('content')

<div class="container-fluid">
    <!-- Welcome Message -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-info"></i> Selamat Datang!</h5>
                Selamat datang di Sistem Ujian Online CBT. Anda login sebagai <strong>ADMIN</strong>.
            </div>
        </div>
    </div>

    <!-- Sesi aktif -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert {{ ($sesiAktif->key ?? '') === 'DEMO' ? 'alert-warning' : 'alert-success' }} d-flex justify-content-between align-items-center mb-0">
                <div>
                    <i class="fas fa-satellite-dish me-1"></i>
                    Sesi aktif: <strong>{{ $sesiAktif->name ?? $sesiAktif->key ?? '-' }}</strong>
                    @if(($sesiAktif->key ?? '') === 'DEMO')
                        <span class="badge bg-warning text-dark ms-1">Mode Demo — peserta real terkunci</span>
                    @endif
                </div>
                <a href="{{ route('cbt_sessions.index') }}" class="btn btn-sm btn-outline-dark">Kelola Sesi</a>
            </div>
        </div>
    </div>

    <!-- Statistik per mode -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100" style="background:linear-gradient(135deg,#254baa,#3b63c9);">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $totalSiswaCbt ?? 0 }}</h3>
                        <p class="mb-0">Peserta CBT</p>
                    </div>
                    <i class="fas fa-user-check fa-3x opacity-25"></i>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('cbt.siswa.index') }}" class="text-white">Buka Peserta CBT <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100" style="background:linear-gradient(135deg,#059669,#34d399);">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $totalSiswaTryout ?? 0 }}</h3>
                        <p class="mb-0">Peserta Tryout</p>
                    </div>
                    <i class="fas fa-users fa-3x opacity-25"></i>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('tryout.siswa.index') }}" class="text-white">Buka Peserta Tryout <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white h-100" style="background:linear-gradient(135deg,#7c3aed,#a78bfa);">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ ($totalUjianCbt ?? 0) + ($totalUjianTryout ?? 0) }}</h3>
                        <p class="mb-0">Ujian (CBT {{ $totalUjianCbt ?? 0 }} / Tryout {{ $totalUjianTryout ?? 0 }})</p>
                    </div>
                    <i class="fas fa-file-alt fa-3x opacity-25"></i>
                </div>
                <div class="card-footer bg-transparent border-0 d-flex gap-3">
                    <a href="{{ route('cbt.ujian.index') }}" class="text-white">CBT</a>
                    <a href="{{ route('tryout.ujian.index') }}" class="text-white">Tryout</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card h-100 {{ ($soalBermasalah ?? 0) > 0 ? 'text-white bg-danger' : 'text-white bg-secondary' }}">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $soalBermasalah ?? 0 }}</h3>
                        <p class="mb-0">Soal Bermasalah</p>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-25"></i>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('soal.bermasalah') }}" class="{{ ($soalBermasalah ?? 0) > 0 ? 'text-white' : 'text-white-50' }}">Perbaiki <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $totalSiswa }}</h3>
                        <p class="mb-0">Total Siswa</p>
                    </div>
                    <i class="fas fa-users fa-3x opacity-25"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('siswa.index') }}" class="text-white">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $totalMapel }}</h3>
                        <p class="mb-0">Total Mata Pelajaran</p>
                    </div>
                    <i class="fas fa-book fa-3x opacity-25"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('mapel.index') }}" class="text-white">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $totalUjian }}</h3>
                        <p class="mb-0">Total Ujian</p>
                    </div>
                    <i class="fas fa-file-alt fa-3x opacity-25"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('ujian.index') }}" class="text-dark">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card text-white bg-danger h-100">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="card-title">{{ $totalSoal }}</h3>
                        <p class="mb-0">Total Soal</p>
                    </div>
                    <i class="fas fa-question-circle fa-3x opacity-25"></i>
                </div>
                <div class="card-footer">
                    <a href="{{ route('soal.index') }}" class="text-white">Lihat Detail <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Grafik Ujian per Mata Pelajaran</h3>
                    <small class="text-muted">6 terakhir</small>
                </div>
                <div class="card-body">
                    <canvas id="examsChart" height="120"></canvas>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Ujian Mendatang</h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @forelse($recentExams as $exam)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $exam->judul ?? $exam->nama ?? 'Ujian' }}</strong>
                                    <div class="text-muted small">Mapel: {{ optional($exam->mapel)->nama_mapel ?? $exam->mapel_id }}</div>
                                </div>
                                <span class="badge badge-primary">{{ optional($exam->terlambat)?->format('d M H:i') ?? '-' }}</span>
                            </li>
                        @empty
                            <li class="list-group-item">Tidak ada ujian.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ringkasan Cepat</h3>
                </div>
                <div class="card-body">
                    <p>Total Hasil Ujian: <strong>{{ $totalHasil }}</strong></p>
                    <p>Attempt Hari Ini: <strong>{{ $attemptsToday ?? 0 }}</strong></p>
                    <p>Ujian Aktif: <strong>{{ \App\Models\GuruTes::where('terlambat', '>', now())->count() }}</strong></p>
                    <hr>
                    <h5>Hasil Terbaru</h5>
                    <ul class="list-unstyled">
                        @foreach($recentResults as $r)
                            <li class="mb-2">
                                <strong>{{ optional($r->siswa)->nama ?? 'Siswa' }}</strong>
                                <div class="small text-muted">Ujian: {{ optional($r->guruTes)->judul ?? $r->guru_tes_id }} — Skor: {{ $r->score ?? $r->nilai ?? '-' }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Siswa Terbaru</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentStudents as $s)
                                <tr>
                                    <td>{{ $s->nama ?? $s->name }}</td>
                                    <td>{{ $s->email ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Mata Pelajaran Paling Populer (Peserta Real)</h3>
                </div>
                <div class="card-body p-0">
                    <div class="p-2 d-flex justify-content-end">
                        <form method="GET" class="d-flex align-items-center" style="gap:8px">
                            <label class="small mb-0">Tampilkan:</label>
                            <select name="mapel_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="real" {{ (isset($mapelStatus) && $mapelStatus==='real') ? 'selected' : '' }}>Peserta Real</option>
                                <option value="test" {{ (isset($mapelStatus) && $mapelStatus==='test') ? 'selected' : '' }}>Peserta Test</option>
                                <option value="all" {{ (isset($mapelStatus) && $mapelStatus==='all') ? 'selected' : '' }}>Semua</option>
                            </select>
                            {{-- preserve other query params like search/status/sort_by/sort_dir --}}
                            @foreach(request()->except('mapel_status') as $k => $v)
                                @if(is_array($v))
                                    @foreach($v as $vv)
                                        <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                @endif
                            @endforeach
                        </form>
                    </div>
                    @if(!empty($popularMapel) && count($popularMapel) > 0)
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Mapel</th>
                                    <th class="text-end">Wajib</th>
                                    <th class="text-end">Pilihan</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($popularMapel as $m)
                                    <tr>
                                        <td>{{ $m['nama'] }}</td>
                                        <td class="text-end">{{ $m['wajib'] }}</td>
                                        <td class="text-end">{{ $m['pilihan'] }}</td>
                                        <td class="text-end"><strong>{{ $m['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-3">
                            <em>Tidak ada data mapel untuk peserta real.</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    <style>
        .small-box {
            border-radius: 10px;
        }
        .alert {
            border-radius: 10px;
        }
        .card {
            border-radius: 10px;
        }
    </style>
@stop

@section('js')
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        console.log("Dashboard Admin Loaded");

        (function () {
            // Prepare data from PHP
            const examsPerMapel = {!! json_encode($examsPerMapel->pluck('count')) !!};
            const examsLabels = {!! json_encode($examsPerMapel->pluck('mapel')) !!};

            const ctx = document.getElementById('examsChart');
            if (ctx) {
                new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: examsLabels,
                        datasets: [{
                            label: 'Jumlah Ujian',
                            data: examsPerMapel,
                            backgroundColor: 'rgba(54, 162, 235, 0.6)'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        })();
    </script>
@stop
