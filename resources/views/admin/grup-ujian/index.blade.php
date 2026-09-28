@extends('layouts.admin')

@section('title', 'Manajemen Grup Ujian')

@section('content_header')
    <h1>Manajemen Grup Ujian</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Grup Ujian</h3>
            <div class="card-tools">
                <a href="{{ route('admin.grup-ujian.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Grup Ujian
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Grup</th>
                            <th>Deskripsi</th>
                            <th>Waktu Mulai</th>
                            <th>Waktu Selesai</th>
                            <th>Durasi (Menit)</th>
                            <th>Status</th>
                            <th>Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grupUjians as $grup)
                            <tr>
                                <td>{{ $grup->id }}</td>
                                <td>{{ $grup->nama_grup }}</td>
                                <td>{{ $grup->deskripsi }}</td>
                                <td>{{ $grup->waktu_mulai->format('d/m/Y H:i') }}</td>
                                <td>{{ $grup->waktu_selesai->format('d/m/Y H:i') }}</td>
                                <td>{{ $grup->durasi_menit }}</td>
                                <td>
                                    <form action="{{ route('admin.grup-ujian.toggle', $grup) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm {{ $grup->is_active ? 'btn-success' : 'btn-secondary' }}">
                                            {{ $grup->is_active ? 'Aktif' : 'Non-aktif' }}
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <ul class="list-unstyled">
                                        @foreach($grup->mapels as $mapel)
                                            <li>{{ $mapel->nama_mapel }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <a href="{{ route('admin.grup-ujian.edit', $grup) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.grup-ujian.destroy', $grup) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus grup ujian ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('table').DataTable();
        });
    </script>
@stop