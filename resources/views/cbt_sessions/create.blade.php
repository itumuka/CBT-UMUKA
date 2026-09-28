@extends('layouts.admin')

@section('title','Tambah CBT Session')

@section('content_header')
    <h1>Tambah Sesi</h1>
@endsection

@section('content')
<div class="container-fluid">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('cbt_sessions.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Key (unik, tanpa spasi)</label>
            <input type="text" name="key" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-check mb-3">
            <input type="checkbox" name="active" value="1" class="form-check-input" id="activeCheck">
            <label class="form-check-label" for="activeCheck">Jadikan aktif (jika dicentang, semua sesi lain akan dinonaktifkan)</label>
        </div>
        <div>
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('cbt_sessions.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection
