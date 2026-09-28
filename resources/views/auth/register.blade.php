@extends('layouts.app')

@section('content')

@section('title', 'Register')

<style>
    .auth-wrapper { min-height: calc(100vh - 3.5rem); }
    @media (min-width: 768px) {
        .auth-card { max-width: 680px; }
    }
    @media (max-width: 767px) {
        .auth-card { width: 100%; }
    }
    .brand-logo { display:block; margin: 0 auto 0.5rem; }
    .mapel-list { padding-left: 1rem; }
</style>

<div class="container auth-wrapper d-flex align-items-center justify-content-center">
    <div class="card auth-card shadow-sm w-100">
        <div class="card-body p-4">
            <div class="text-center mb-3">
                <img src="/image/umuka.png" alt="Logo" class="brand-logo img-fluid" width="64">
                <h5 class="mb-0 fw-bold">CBT UMUKA</h5>
                @if(isset($activeSession) && $activeSession)
                    <span class="badge bg-success mt-2">Sesi aktif: {{ strtoupper($activeSession->name) }}</span>
                @else
                    <span class="badge bg-secondary mt-2">Sesi aktif: CBT</span>
                @endif
            </div>

            <div>
            @if(!isset($registrationOpen) || $registrationOpen)
            <form method="POST" action="{{ route('register.post') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input id="nama" type="text" class="form-control @error('nama') is-invalid @enderror" 
                               name="nama" value="{{ old('nama') }}" required autofocus>
                        @error('nama')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="sekolah" class="form-label">Sekolah</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-school"></i></span>
                        <input id="sekolah" type="text" class="form-control @error('sekolah') is-invalid @enderror" 
                               name="sekolah" value="{{ old('sekolah') }}" required autofocus>
                        @error('sekolah')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required maxlength="255"
                               placeholder="nama@domain.com">
                        @error('email')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="no_hp" class="form-label">Nomor HP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input id="no_hp" 
                            type="tel" 
                            class="form-control @error('no_hp') is-invalid @enderror" 
                            name="no_hp" 
                            value="{{ old('no_hp') }}" 
                            required 
                            maxlength="13"
                            pattern="08[0-9]{8,11}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                            placeholder="Contoh: 081234567890">
                        @error('no_hp')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                {{-- MAPEL WAJIB --}}
                <div class="mb-3">
                                    <label class="form-label">Mapel Wajib</label>
                                    <ul>
                                        @foreach($wajib as $item)
                                            <li>{{ $item->nama }}</li>
                                            {{-- kirim id mapel wajib --}}
                                            <input type="hidden" name="mapel_wajib[]" value="{{ $item->id }}">
                                        @endforeach
                                    </ul>

                                    @error('mapel_wajib')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                </div>

                    {{-- MAPEL PILIHAN --}}
                    <div class="mb-3">
                        <label class="form-label">Mapel Pilihan (maksimal 2)</label>
                        @foreach($pilihan as $item)
                            <div class="form-check">
                                <input type="checkbox"
                                    class="form-check-input mapel-pilihan"
                                    name="mapel_pilihan[]"
                                    value="{{ $item->id }}"
                                    {{ in_array($item->id, old('mapel_pilihan', [])) ? 'checked' : '' }}>
                                <label class="form-check-label">{{ $item->nama }}</label>
                            </div>
                        @endforeach

                        @error('mapel_pilihan')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" required>
                        @error('password')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Status Akun</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_akun" 
                                   id="status_real" value="real" checked>
                            <label class="form-check-label" for="status_real">
                                <i class="fas fa-user"></i> Akun Peserta
                            </label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status_akun" 
                                   id="status_test" value="test">
                            <label class="form-check-label" for="status_test">
                                <i class="fas fa-vial"></i> Akun Tester
                            </label>
                        </div>
                    </div>
                    @error('status_akun')
                        <span class="text-danger"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
            @else
                <div class="alert alert-danger text-center">
                    <h5 class="mb-1">Pendaftaran Ditutup</h5>
                    <div>Pendaftaran untuk tryout saat ini sedang ditutup. Silakan cek kembali nanti atau hubungi panitia untuk informasi lebih lanjut.</div>
                </div>
            @endif

            <div class="text-center mt-3">
                <p class="mb-0 text-muted">
                    Sudah memiliki akun? 
                    <a href="{{ url('/') }}" class="text-primary text-decoration-none fw-bold">
                        Masuk sekarang <i class="fas fa-arrow-right"></i>
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.mapel-pilihan');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const checked = document.querySelectorAll('.mapel-pilihan:checked');
                
                        // Cegah jika lebih dari 2
                        if (checked.length > 2) {
                            this.checked = false;
                            // nicer UX
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ icon: 'warning', title: 'Pilih maksimal 2', text: 'Maksimal hanya boleh memilih 2 mapel pilihan.' });
                            } else {
                                alert('Maksimal hanya boleh memilih 2 mapel pilihan!');
                            }
                        }
            });
        });

        // Validasi nomor HP sebelum submit
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            const noHp = document.getElementById('no_hp').value.trim();
            const re = /^08[0-9]{8,11}$/; // 10-13 digit total starting with 08
            if (!re.test(noHp)) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Nomor HP tidak valid', text: 'Pastikan dimulai dengan 08 dan memiliki panjang antara 10 hingga 13 digit.' });
                } else {
                    alert('Nomor HP tidak valid. Pastikan dimulai dengan 08 dan memiliki panjang antara 10 hingga 13 digit.');
                }
                return false;
            }
        });
    });
</script>
<!-- include SweetAlert2 for register page if available -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
