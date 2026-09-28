@extends('layouts.admin')

@section('title', 'Tambah Grup Ujian')

@section('content_header')
    <h1>Tambah Grup Ujian Baru</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.grup-ujian.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="nama_grup">Nama Grup <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_grup') is-invalid @enderror" 
                           id="nama_grup" name="nama_grup" value="{{ old('nama_grup') }}" required>
                    @error('nama_grup')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                            id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="waktu_mulai">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('waktu_mulai') is-invalid @enderror" 
                                   id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai') }}" required>
                            @error('waktu_mulai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="waktu_selesai">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control @error('waktu_selesai') is-invalid @enderror" 
                                   id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai') }}" required>
                            @error('waktu_selesai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="durasi_menit">Durasi Ujian (Menit) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('durasi_menit') is-invalid @enderror" 
                           id="durasi_menit" name="durasi_menit" value="{{ old('durasi_menit') }}" required min="1">
                    @error('durasi_menit')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Mata Pelajaran <span class="text-danger">*</span></label>
                    <div class="select2-purple">
                        <select class="select2" multiple="multiple" name="mapel_ids[]" 
                                data-placeholder="Pilih mata pelajaran" style="width: 100%;" required>
                            @foreach($mapels as $mapel)
                                <option value="{{ $mapel->id }}" {{ in_array($mapel->id, old('mapel_ids', [])) ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @error('mapel_ids')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('admin.grup-ujian.index') }}" class="btn btn-default">Batal</a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
            });
        });
    </script>
@stop