@extends('layouts.admin')

@section('title', 'Profil Saya')

@section('content_header')
    <h1 class="m-0">Profil Saya</h1>
@stop

@section('css')
    <style>
        /* Modern profile styling */
        .profile-hero {
            background: linear-gradient(90deg,#06b6d4 0%,#6366f1 100%);
            color: white;
            border-radius: .6rem;
            padding: 1.25rem;
        }
        .profile-avatar {
            width:96px;height:96px;border-radius:50%;object-fit:cover;border:4px solid rgba(255,255,255,0.18);
        }
        .card-modern { border-radius:.6rem; box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
        .token-box code { font-size:1.05rem; background:transparent; border:0; color:#0f172a; }
        @media (max-width:576px){ .profile-avatar{width:72px;height:72px} }
    </style>
@stop

@section('content')
    @php
        $tokenActive = $tokenActive ?? false;
        $tokenEnabled = $tokenEnabled ?? false;
    @endphp

    <div class="container-fluid">
        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card card-modern card-body profile-hero text-center">
                    <div class="d-flex flex-column align-items-center">
                        <img src="{{ $siswa->avatar_url ?? asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="Avatar" class="profile-avatar mb-3">
                        <h4 class="mb-0">{{ $siswa->nama }}</h4>
                        <div class="small opacity-75">{{ $siswa->sekolah ?? 'Belum ada sekolah' }}</div>

                        <div class="w-100 mt-3 token-box">
                            <div class="bg-white rounded p-3 text-start" style="color:#0f172a;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-semibold">Token Ujian</div>
                                    <div>
                                        @if($tokenActive)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-warning">Nonaktif</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <code id="token-display">{{ str_repeat('•', max(6, strlen($siswa->token ?? '')) ) }}</code>
                                    <div>
                                        <button id="btn-reveal-token" class="btn btn-sm btn-primary me-1">Tampilkan</button>
                                        <button id="btn-copy-token" class="btn btn-sm btn-outline-secondary" style="display:none;">Salin</button>
                                    </div>
                                </div>
                                <div id="token-hint" class="small text-muted mt-2">Token disembunyikan. Tampilkan untuk melihat.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-modern card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="mb-1">Informasi Akun</h5>
                            <div class="text-muted small">Detail profil dan mata pelajaran terdaftar</div>
                        </div>
                        <div>
                            <a href="/ujian" class="btn btn-primary btn-sm">
                                <i class="fas fa-play"></i> Mulai Ujian
                            </a>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Email</div>
                        <div class="col-sm-8">{{ $siswa->email ?? '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">No HP</div>
                        <div class="col-sm-8">{{ $siswa->no_hp ?? '-' }}</div>
                    </div>
                    <hr>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Mapel Wajib</div>
                        <div class="col-sm-8">{{ $mapelWajibNames !== '' ? $mapelWajibNames : '-' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4 text-muted">Mapel Pilihan</div>
                        <div class="col-sm-8">{{ $mapelPilihanNames !== '' ? $mapelPilihanNames : '-' }}</div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@stop

@section('adminlte_js')
    @parent
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Fallback local load handled elsewhere if CDN fails
        document.addEventListener('DOMContentLoaded', function(){
            const revealBtn = document.getElementById('btn-reveal-token');
            const copyBtn = document.getElementById('btn-copy-token');
            const tokenDisplay = document.getElementById('token-display');

            function showError(msg){
                if (typeof Swal !== 'undefined') Swal.fire({ icon:'error', title:'Gagal', text: msg });
                else alert(msg);
            }

            if (revealBtn){
                revealBtn.addEventListener('click', function(e){
                    e.preventDefault();
                    if (typeof Swal === 'undefined'){
                        const pwd = prompt('Masukkan password akun untuk melihat token:');
                        if (!pwd) return;
                        // fallback fetch
                        fetch('{{ route('siswa.revealToken') }}', {
                            method: 'POST',
                            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: JSON.stringify({ password: pwd })
                        }).then(r => r.json()).then(data => {
                            if (data.token){ tokenDisplay.textContent = data.token; copyBtn.style.display='inline-block'; }
                            else showError(data.error || data.message || 'Verifikasi gagal');
                        }).catch(()=> showError('Terjadi kesalahan jaringan'));
                        return;
                    }

                    Swal.fire({
                        title: 'Verifikasi Password',
                        input: 'password',
                        inputPlaceholder: 'Masukkan password akun Anda',
                        showCancelButton: true,
                        confirmButtonText: 'Verifikasi',
                        preConfirm: (pwd) => {
                            if (!pwd) Swal.showValidationMessage('Password diperlukan');
                            return fetch('{{ route('siswa.revealToken') }}', {
                                method: 'POST',
                                headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                                body: JSON.stringify({ password: pwd })
                            }).then(r => r.json()).catch(()=> ({ error: 'Terjadi kesalahan jaringan' }));
                        }
                    }).then(result => {
                        if (result.isConfirmed && result.value){
                            const data = result.value;
                            if (data.token){ tokenDisplay.textContent = data.token; copyBtn.style.display='inline-block'; if (window.pesertaToast) pesertaToast('success', 'Token ditampilkan. Salin untuk dipakai ujian.'); }
                            else showError(data.error || data.message || 'Verifikasi gagal');
                        }
                    });
                });
            }

            if (copyBtn){
                copyBtn.addEventListener('click', async function(){
                    const text = tokenDisplay.textContent.trim();
                    const ok = function(){ if (window.pesertaToast) pesertaToast('success', 'Token disalin. Tempel di halaman ujian.'); else if (typeof Swal !== 'undefined') Swal.fire({ icon:'success', title:'Disalin' }); };
                    try { await navigator.clipboard.writeText(text); ok(); }
                    catch(e){
                        const t = document.createElement('input'); document.body.appendChild(t); t.value=text; t.select(); document.execCommand('copy'); document.body.removeChild(t);
                        ok();
                    }
                });
            }
        });
    </script>
@stop
