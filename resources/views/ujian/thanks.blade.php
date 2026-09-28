@extends('layouts.app')

@section('content')
<div class="container mt-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <!-- Header Decorative Area -->
                <div class="py-5 text-center" style="background: linear-gradient(135deg, #254baa 0%, #1e3a8a 100%);">
                    <div class="mb-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-white text-success rounded-circle shadow" style="width: 80px; height: 80px;">
                            <i class="fas fa-check fa-3x"></i>
                        </div>
                    </div>
                    <h2 class="text-white fw-bold">Ujian Selesai</h2>
                </div>

                <div class="card-body p-5 text-center">
                    @if($isCbt)
                        <!-- Pesan Khusus CBT -->
                        <div class="mb-4">
                            <h3 class="text-primary fw-bold mb-3">Selamat!</h3>
                            <p class="lead text-dark">
                                Anda telah <strong>Lolos Seleksi</strong> Computer Based Test (CBT).
                            </p>
                            <div class="alert alert-success border-0 bg-opacity-10 py-3">
                                <i class="fas fa-info-circle me-2"></i>
                                No. Register Anda <strong>{{ $siswa->nim }}</strong> telah terverifikasi sistem.
                            </div>
                            <p class="text-muted small">
                                Silakan simpan halaman ini sebagai bukti penyelesaian ujian atau hubungi panitia untuk langkah registrasi selanjutnya.
                            </p>
                        </div>
                    @else
                        <!-- Pesan Standar / Tester -->
                        <div class="mb-4">
                            <h4 class="fw-normal mb-3">Terima kasih telah berpartisipasi</h4>
                            <p class="text-secondary">
                                Jawaban Anda telah berhasil kami simpan dalam sistem. Hasil penilaian akan diumumkan secara resmi melalui portal pengumuman sesuai dengan jadwal yang ditentukan.
                            </p>
                        </div>
                    @endif

                    <hr class="my-4 opacity-25">

                    <div class="d-grid gap-2">
                        <a href="{{ route('daftar_ujian') }}" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-home me-2"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <p class="text-muted small">&copy; {{ date('Y') }} Panitia PMB. All Rights Reserved.</p>
            </div>
        </div>
    </div>
</div>

<style>
    body { background-color: #f4f7f6; }
    .btn-primary { background-color: #254baa; border: none; padding: 12px 30px; }
    .btn-primary:hover { background-color: #1e3a8a; transform: translateY(-2px); transition: all 0.3s ease; }
</style>
@endsection
