@extends('layouts.admin')

@section('title', 'Tambah Grup Ujian')

@section('content_header')
    <h1>Tambah Grup Ujian</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Form Tambah Grup Ujian</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('grup_ujian.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="nama_grup">Nama Grup Ujian</label>
                            <input type="text" class="form-control @error('nama_grup') is-invalid @enderror" 
                                id="nama_grup" name="nama_grup" value="{{ old('nama_grup') }}" required>
                            @error('nama_grup')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="deskripsi">Deskripsi (Opsional)</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_mulai">Waktu Mulai</label>
                                    <input type="datetime-local" class="form-control @error('waktu_mulai') is-invalid @enderror" 
                                        id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                                    @error('waktu_mulai')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="waktu_selesai">Waktu Selesai</label>
                                    <input type="datetime-local" class="form-control @error('waktu_selesai') is-invalid @enderror" 
                                        id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                                    @error('waktu_selesai')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="durasi_menit">Durasi Ujian (Menit)</label>
                            <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" 
                                id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit') }}" required min="1">
                            @error('durasi_menit')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Pilih Mata Pelajaran</label>
                            <div class="select2-purple">
                                <select class="select2 select2-hidden-accessible @error('mapel_ids') is-invalid @enderror" 
                                    multiple data-placeholder="Pilih mata pelajaran" 
                                    style="width: 100%;" name="mapel_ids[]" required>
                                    @foreach($mapels as $mapel)
                                        <option value="{{ $mapel->id }}" 
                                            {{ in_array($mapel->id, old('mapel_ids', [])) ? 'selected' : '' }}>
                                            {{ $mapel->nama_mapel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @error('mapel_ids')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <a href="{{ route('grup_ujian.index') }}" class="btn btn-secondary">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
<script>
    $(function () {
        $('.select2').select2();
    });

    // Validation for waktu_selesai to be after waktu_mulai
    document.getElementById('waktu_mulai').addEventListener('change', function() {
        document.getElementById('waktu_selesai').min = this.value;
    });
</script>
@stop