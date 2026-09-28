@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
@php $rp = $rp ?? ''; @endphp
<div class="container mt-4 d-flex justify-content-center">
    <div class="card w-50">
        <div class="card-header bg-primary text-white">
            Edit Data Siswa @isset($modeLabel)<span class="badge bg-light text-dark">{{ $modeLabel }}</span>@endisset
        </div>
        <div class="card-body">
            <form action="{{ route($rp.'siswa.update', $siswa->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $siswa->nama) }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="nim" class="form-label">NO REGISTER @if(($mode ?? 'cbt') === 'tryout')(opsional)@endif</label>
                    <input type="text" name="nim" class="form-control @error('nim') is-invalid @enderror" value="{{ old('nim', $siswa->nim) }}" @if(($mode ?? 'cbt') === 'cbt')required @endif>
                    @error('nim')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="jurusan" class="form-label">Jurusan</label>
                    <select name="jurusan" class="form-select @error('jurusan') is-invalid @enderror">
                        <option value="Teknik Informatika" {{ $siswa->jurusan == 'Teknik Informatika' ? 'selected' : '' }}>Teknik Informatika</option>
                        <option value="Akuntansi" {{ $siswa->jurusan == 'Akuntansi' ? 'selected' : '' }}>Akuntansi</option>
                        <option value="Bisnis Digital" {{ $siswa->jurusan == 'Bisnis Digital' ? 'selected' : '' }}>Bisnis Digital</option>
                        <option value="Ilmu Komunikasi" {{ $siswa->jurusan == 'Ilmu Komunikasi' ? 'selected' : '' }}>Ilmu Komunikasi</option>
                        <option value="Fisioterapi" {{ $siswa->jurusan == 'Fisioterapi' ? 'selected' : '' }}>Fisioterapi</option>
                    </select>
                    @error('jurusan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="{{ route($rp.'siswa.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
