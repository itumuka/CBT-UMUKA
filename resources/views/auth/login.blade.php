@extends('layouts.app')

@section('content')

@section('title', 'Login')

<style>
    body { background: linear-gradient(150deg, #eef3ff 0%, #f8fafc 55%, #e6f7fb 100%); }
    .auth-wrapper { min-height: calc(100vh - 3.5rem); }
    @media (min-width: 768px) {
        .auth-card { max-width: 480px; }
    }
    @media (max-width: 767px) {
        .auth-card { width: 100%; }
    }
    .brand-logo { display:block; margin: 0 auto 0.5rem; }
    .auth-card { border: 0; border-radius: 20px; overflow: hidden; }
    .auth-head { background: linear-gradient(120deg, #254baa 0%, #06b6d4 100%); }
</style>

<div class="container auth-wrapper d-flex align-items-center justify-content-center">
    <div class="card auth-card shadow-sm w-100">
        <div class="auth-head text-center text-white px-4 pt-4 pb-3">
            <img src="/image/umuka.png" alt="Logo" class="brand-logo img-fluid bg-white rounded-circle p-1" width="64">
            <h5 class="mb-0 fw-bold">CBT UMUKA</h5>
            <div class="small opacity-75">Masuk untuk mengikuti ujian</div>
        </div>
        <div class="card-body p-4">

            <div>
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input id="email" type="email" class="form-control" 
                               name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <small class="form-text text-muted">Masukkan email yang terdaftar pada akun Anda.</small>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" class="form-control" 
                               name="password" required>
                        <button class="btn btn-outline-secondary" type="button" id="pwToggle" title="Tampilkan password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                @error('email')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                @enderror
                @error('password')
                    <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                @enderror        
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary" id="loginBtn">Login</button>
                    <a href="{{ url('register-tryout') }}" class="text-center mt-2">Register Tryout</a>
                </div>
            </form>
        </div>
    </div>
</div>

@include('ujian._toast')

@endsection

@push('scripts')
    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil',
                    html: `Terima kasih! Akun Anda telah berhasil dibuat. Silakan masuk menggunakan username dan password yang Anda daftarkan.`,
                    confirmButtonText: 'Masuk sekarang'
                });
            @endif

            var pw = document.getElementById('password');
            var tg = document.getElementById('pwToggle');
            if (pw && tg) {
                tg.addEventListener('click', function () {
                    var show = pw.type === 'password';
                    pw.type = show ? 'text' : 'password';
                    tg.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
                });
            }

            var form = document.querySelector('form[action="{{ route('login.post') }}"]');
            var btn = document.getElementById('loginBtn');
            if (form && btn) {
                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memeriksa...';
                });
            }
        });
    </script>
@endpush
