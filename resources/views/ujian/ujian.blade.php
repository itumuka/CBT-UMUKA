@extends('layouts.admin')

@section('title', 'Ujian')

@section('plugins.Sweetalert2', true)

@section('content_header')
    <h1 class="m-0">Ujian @isset($modeLabel)<span class="badge bg-{{ $modeBadge ?? 'secondary' }}">{{ $modeLabel }}</span>@endisset</h1>
@stop

@section('content')
@php($rp = $rp ?? '')

<div class="exam-wrap">
    <div class="exam-card">
        <div class="exam-main">
            <span class="exam-eyebrow">Computer Based Test</span>
            <h1 class="exam-title">Selamat datang, {{ $siswa->nama ?? 'Siswa' }}</h1>
            <p class="exam-subtitle">Masuk ke ujian Anda dengan memasukkan token akun di bawah ini. Semoga sukses — tetap tenang dan kerjakan dengan seksama.</p>

            <form method="GET" action="{{ route('konfirmasi_ujian') }}" id="tokenForm" class="exam-token-form" novalidate>
                <label for="token" class="exam-label">Token Akun</label>
                <div class="exam-token-row">
                    <div class="exam-input-wrap">
                        <i class="fas fa-key exam-input-icon"></i>
                        <input type="text" name="token" id="token" class="form-control @error('token') is-invalid @enderror" placeholder="Contoh: ABCDE" value="{{ old('token') }}" aria-label="Token akun siswa" autocomplete="off" maxlength="16">
                        <button type="button" class="exam-toggle" id="tokenToggle" title="Tampilkan/sembunyikan token" aria-label="Tampilkan token">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary exam-submit" type="submit" id="btnSubmit">
                        <span class="btn-label"><i class="fas fa-arrow-right me-1"></i> Masuk Ujian</span>
                        <span class="btn-loading d-none"><span class="spinner-border spinner-border-sm me-1"></span> Memeriksa...</span>
                    </button>
                </div>
                @error('token')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </form>

            <div class="exam-guide">
                <div class="exam-guide-title"><i class="fas fa-circle-info me-1"></i> Belum punya token? Ikuti langkah ini:</div>
                <ol class="exam-steps">
                    <li>Buka halaman <a href="{{ route('siswa.profile') }}" class="fw-bold">Profil Saya</a>.</li>
                    <li>Pada kartu <strong>Token Ujian</strong>, klik <strong>Tampilkan</strong>, masukkan password akun, lalu klik <strong>Salin</strong>.</li>
                    <li>Kembali ke sini, tempel token di kolom atas lalu klik <strong>Masuk Ujian</strong>.</li>
                </ol>
            </div>
        </div>

        <div class="exam-side" aria-hidden="true">
            <div class="exam-shield"><i class="fas fa-shield-halved"></i></div>
            <div class="exam-tips">
                <div class="exam-tip"><i class="fas fa-wifi"></i> Pastikan koneksi stabil</div>
                <div class="exam-tip"><i class="fas fa-battery-three-quarters"></i> Baterai perangkat cukup</div>
                <div class="exam-tip"><i class="fas fa-clock"></i> Perhatikan sisa waktu</div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    <style>
        .exam-wrap { padding: 24px 12px; display: flex; justify-content: center; }
        .exam-card { display: flex; width: 100%; max-width: 1020px; background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 18px 50px rgba(37,75,170,.14); }
        .exam-main { flex: 1; padding: 40px 36px; }
        .exam-eyebrow { display: inline-block; font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #254baa; background: #e8eefc; border-radius: 999px; padding: 4px 12px; margin-bottom: 12px; }
        .exam-title { font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .exam-subtitle { color: #475569; margin-bottom: 22px; }
        .exam-label { font-weight: 600; color: #0f172a; margin-bottom: 6px; display: block; }
        .exam-token-row { display: flex; gap: 10px; }
        .exam-input-wrap { position: relative; flex: 1; }
        .exam-input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        .exam-input-wrap .form-control { height: 50px; padding-left: 42px; padding-right: 44px; border-radius: 12px; font-size: 1.05rem; letter-spacing: .08em; text-transform: uppercase; }
        .exam-toggle { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: #94a3b8; padding: 6px 8px; }
        .exam-toggle:hover { color: #254baa; }
        .exam-submit { border-radius: 12px; padding: 0 22px; height: 50px; font-weight: 600; white-space: nowrap; }
        .exam-guide { margin-top: 24px; background: #f1f5fd; border: 1px solid #dbe5fa; border-radius: 14px; padding: 16px 18px; }
        .exam-guide-title { font-weight: 700; color: #1e3a8a; margin-bottom: 8px; }
        .exam-steps { margin: 0; padding-left: 20px; color: #334155; }
        .exam-steps li { margin-bottom: 4px; }
        .exam-side { width: 320px; background: linear-gradient(160deg, #254baa 0%, #06b6d4 100%); color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 22px; padding: 36px 28px; }
        .exam-shield { width: 110px; height: 110px; border-radius: 50%; background: rgba(255,255,255,.16); display: flex; align-items: center; justify-content: center; font-size: 3rem; }
        .exam-tips { display: flex; flex-direction: column; gap: 10px; width: 100%; }
        .exam-tip { background: rgba(255,255,255,.14); border-radius: 10px; padding: 10px 14px; font-size: .9rem; }
        .exam-tip i { margin-right: 8px; }
        @media (max-width: 820px) {
            .exam-card { flex-direction: column; }
            .exam-side { width: 100%; padding: 24px; }
            .exam-main { padding: 28px 20px; }
            .exam-token-row { flex-direction: column; }
            .exam-submit { width: 100%; }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Toggle tampil/sembunyi token
            var tokenInput = document.getElementById('token');
            var toggle = document.getElementById('tokenToggle');
            if (toggle && tokenInput) {
                toggle.addEventListener('click', function () {
                    var show = tokenInput.type === 'password';
                    tokenInput.type = show ? 'text' : 'password';
                    toggle.innerHTML = show ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
                });
                tokenInput.type = 'password';
            }

            // Validasi + loading state
            var form = document.getElementById('tokenForm');
            var btn = document.getElementById('btnSubmit');
            if (form && btn) {
                form.addEventListener('submit', function (e) {
                    if (!tokenInput || tokenInput.value.trim() === '') {
                        e.preventDefault();
                        if (window.pesertaToast) pesertaToast('warning', 'Silakan masukkan token akun Anda.');
                        else alert('Silakan masukkan token akun Anda.');
                        tokenInput && tokenInput.focus();
                        return;
                    }
                    btn.disabled = true;
                    btn.querySelector('.btn-label').classList.add('d-none');
                    btn.querySelector('.btn-loading').classList.remove('d-none');
                });
            }
        });
    </script>
@stop
