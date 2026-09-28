@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
@php $rp = $rp ?? ''; @endphp
<div class="container mt-4">
    <div class="card w-50">
        <div class="card-header text-white bg-primary" >
            Tambah Data Siswa @isset($modeLabel)<span class="badge bg-light text-dark">{{ $modeLabel }}</span>@endisset
        </div>
        <div class="card-body">
            <form action="{{ route($rp.'siswa.store') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="nim" class="form-label">NIM @if(($mode ?? 'cbt') === 'tryout')(opsional untuk Tryout)@endif</label>
                    <input type="text" name="nim" class="form-control @error('nim') is-invalid @enderror" value="{{ old('nim') }}" @if(($mode ?? 'cbt') === 'cbt')required @endif>
                    @error('nim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                @if(($mode ?? 'cbt') === 'tryout')
                <div class="mb-3">
                    <label for="email" class="form-label">Email (untuk login)</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                @endif
                
                <div class="mb-3">
                    <label for="jurusan" class="form-label">Jurusan</label>
                    <select name="jurusan" class="form-select @error('jurusan') is-invalid @enderror">
                        <option value="" disabled selected>Pilih Jurusan</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Akupuntur dan Pengobatan Herbal">Akupuntur dan Pengobatan Herbal</option>
                        <option value="Bisnis Digital">Bisnis Digital</option>
                        <option value="Fisioterapi">Fisioterapi</option>
                        <option value="Hukum Bisnis">Hukum Bisnis</option>
                        <option value="Informatika">Informatika</option>  
                        <option value="Ilmu Komunikasi">Ilmu Komunikasi</option>
                        <option value="Keperawatan Anestesiologi">Keperawatan Anestesiologi</option>
                        <option value="Peternakan">Peternakan</option>
                        <option value="Pendidikan Bahasa Arab">Pendidikan Bahasa Arab</option>
                        <option value="Pendidikan Kepelatihan Olahrag">Pendidikan Kepelatihan Olahraga</option>
                        <option value="Perhotelan">Perhotelan</option>
                        <option value="Teknik Komputer">Teknik Komputer</option>

                        
                    </select>
                    @error('jurusan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn text-white bg-success">Tambah Siswa</button>
                <a href="{{ route($rp.'siswa.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
