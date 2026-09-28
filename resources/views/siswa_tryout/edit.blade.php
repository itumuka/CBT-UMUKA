@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="container mt-4 d-flex justify-content-center">
    <div class="card w-50">
        <div class="card-header bg-primary text-white">
            Edit Data Siswa
        </div>
        <div class="card-body">
            <form action="{{ route('siswa_tryout.update', $siswa->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $siswa->nama) }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="sekolah" class="form-label">Sekolah</label>
                    <input type="text" name="sekolah" class="form-control @error('sekolah') is-invalid @enderror" value="{{ old('sekolah', $siswa->sekolah) }}" required>
                    @error('sekolah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="no_hp" class="form-label">Nomor HP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input id="no_hp" 
                            type="text" 
                            class="form-control @error('no_hp') is-invalid @enderror" 
                            name="no_hp" 
                            value="{{ old('no_hp', $siswa->no_hp) }}"
                            required 
                            maxlength="15"
                            pattern="[0-9]+"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                            placeholder="Contoh: 081234567890">
                        @error('no_hp')
                            <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                {{-- MAPEL WAJIB --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Mapel Wajib</label>
                    @foreach($mapelWajib as $item)
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="mapel_wajib[]"
                                value="{{ $item->id }}"
                                {{ in_array($item->id, old('mapel_wajib', $mapelWajibSiswa ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $item->nama }}</label>
                        </div>
                    @endforeach
                    @error('mapel_wajib')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                {{-- MAPEL PILIHAN --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Mapel Pilihan</label>
                    @foreach($mapelPilihan as $item)
                        <div class="form-check">
                            <input type="checkbox"
                                class="form-check-input"
                                name="mapel_pilihan[]"
                                value="{{ $item->id }}"
                                {{ in_array($item->id, old('mapel_pilihan', $mapelPilihanSiswa ?? [])) ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $item->nama }}</label>
                        </div>
                    @endforeach
                    @error('mapel_pilihan')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                
                <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                <a href="{{ route('siswa_tryout.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
