@extends('layouts.admin')

@section('title', 'Grup Ujian')

@section('content_header')
    <h1>Grup Ujian</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Grup Ujian</h3>
                    <div class="card-tools">
                        <a href="{{ route('grup_ujian.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Grup Ujian
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Grup</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Waktu Mulai</th>
                                    <th>Waktu Selesai</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($grupUjians as $grup)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $grup->nama_grup }}</td>
                                    <td>
                                        <ul class="list-unstyled m-0">
                                            @foreach($grup->mapels as $mapel)
                                                <li>- {{ $mapel->nama_mapel }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>{{ $grup->waktu_mulai->format('d/m/Y H:i') }}</td>
                                    <td>{{ $grup->waktu_selesai->format('d/m/Y H:i') }}</td>
                                    <td>{{ $grup->durasi_menit }} menit</td>
                                    <td>
                                        <span class="badge badge-{{ $grup->is_active ? 'success' : 'danger' }}">
                                            {{ $grup->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('grup_ujian.toggle', $grup) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-{{ $grup->is_active ? 'warning' : 'success' }}">
                                                <i class="fas fa-{{ $grup->is_active ? 'pause' : 'play' }}"></i>
                                                {{ $grup->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data grup ujian</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        // Flash message
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}'
            });
        @endif
    </script>
@stop