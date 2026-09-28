@extends('layouts.admin')

@section('title', 'Pilih Ujian')

@section('content_header')
    <h1 class="m-0">Pilih Ujian</h1>
@stop

@section('content')
<div class="container py-3" style="max-width:860px;">
    <div class="alert alert-info">
        Halo <strong>{{ $siswa->nama ?? 'Siswa' }}</strong>, ada <strong>{{ $candidates->count() }} ujian</strong> yang sedang berlangsung. Silakan pilih ujian yang Anda maksud.
    </div>

    <div class="row g-3">
        @foreach($candidates as $u)
        <div class="col-md-6">
            <div class="card h-100 shadow-sm" style="border-radius:14px;">
                <div class="card-body">
                    <h5 class="card-title fw-bold">{{ $u->nama_ujian }}</h5>
                    <div class="small text-muted mb-2">
                        <i class="fas fa-clock me-1"></i>
                        {{ \Carbon\Carbon::parse($u->tgl_mulai)->format('d M Y H:i') }}
                        &ndash; {{ \Carbon\Carbon::parse($u->terlambat)->format('d M Y H:i') }}
                    </div>
                    <a href="{{ route('konfirmasi_ujian', ['token' => $token, 'ujian_id' => $u->id]) }}" class="btn btn-primary w-100">
                        <i class="fas fa-arrow-right me-1"></i> Pilih ujian ini
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <a href="{{ route('daftar_ujian') }}" class="btn btn-secondary mt-3">Kembali</a>
</div>

@include('ujian._toast')
@stop
