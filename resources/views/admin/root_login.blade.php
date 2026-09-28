@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Root Impersonation</div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('admin.root.login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Root Password</label>
                            <input type="password" name="root_password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">User ID (Siswa)</label>
                            <input type="number" name="user_id" class="form-control" required>
                            <div class="form-text">Masukkan ID siswa yang ingin di-impersonate (lihat tabel m_siswa)</div>
                        </div>

                        <button class="btn btn-primary">Masuk sebagai User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
