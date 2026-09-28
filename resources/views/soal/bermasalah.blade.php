@extends('layouts.admin')

@section('title', 'Soal Bermasalah')

@section('content_header')
    <h1>Soal Bermasalah <small class="text-muted">(gambar)</small></h1>
@stop

@section('content')
<div class="container mt-3">
    <div class="alert alert-info">
        Halaman ini mendeteksi otomatis soal yang gambarnya bermasalah. Perbaiki dengan menekan <strong>Edit</strong> lalu upload ulang gambarnya.
    </div>

    <div class="card shadow-lg mb-4">
        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
            <strong>A. File gambar hilang dari server ({{ count($hilang) }})</strong>
            <small>Dirujuk database tapi file tidak ada di disk</small>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover table-striped align-middle" mb-0>
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th>Mapel</th>
                        <th>Soal</th>
                        <th>File yang dicari</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($hilang as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->mapel->nama ?? ('Mapel #'.$s->id_mapel) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($s->soal), 120) }}</td>
                        <td><code>{{ $s->file }}</code></td>
                        <td><a href="{{ route('soal.edit', $s->id) }}" class="btn btn-primary btn-sm">Edit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-success">Tidak ada — semua file gambar ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-lg">
        <div class="card-header bg-warning d-flex justify-content-between align-items-center">
            <strong>B. Menyebut gambar tapi tanpa lampiran ({{ count($tanpaFile) }})</strong>
            <small>Teks soal merujuk gambar yang tidak dilampirkan</small>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover table-striped align-middle" mb-0>
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th>Mapel</th>
                        <th>Soal</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tanpaFile as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->mapel->nama ?? ('Mapel #'.$s->id_mapel) }}</td>
                        <td>{{ \Illuminate\Support\Str::limit(strip_tags($s->soal), 150) }}</td>
                        <td><a href="{{ route('soal.edit', $s->id) }}" class="btn btn-primary btn-sm">Edit</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-success">Tidak ada — semua soal bergambar sudah berlampiran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop
