@extends('layouts.admin')

@section('title', 'CBT Sessions')

@section('content_header')
    <h1>CBT Sessions</h1>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Daftar Sesi</h4>
        <a href="{{ route('cbt_sessions.create') }}" class="btn btn-primary">Tambah Sesi</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Key</th>
                        <th>Name</th>
                        <th>Active</th>
                        <th>Token</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>{{ $s->key }}</td>
                        <td>{{ $s->name }}</td>
                        <td>
                            @if($s->active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            @php $ts = $s->token_status ?? 0; @endphp
                            <div class="mb-1">
                                @if($ts == 0)
                                    <span class="badge bg-secondary">Off</span>
                                @elseif($ts == 1)
                                    <span class="badge bg-success">Real</span>
                                @else
                                    <span class="badge bg-info text-dark">Tester</span>
                                @endif
                            </div>

                            @if($s->active)
                                <form action="{{ route('cbt_sessions.toggle_token', $s->id) }}" method="POST" class="d-flex gap-1 align-items-center">
                                    @csrf
                                    <input type="hidden" name="status" value="" />
                                    <select name="status" class="form-select form-select-sm" style="width:120px">
                                        <option value="0" {{ $ts==0 ? 'selected' : '' }}>Off</option>
                                        <option value="1" {{ $ts==1 ? 'selected' : '' }}>Real</option>
                                        <option value="2" {{ $ts==2 ? 'selected' : '' }}>Tester</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Set</button>
                                </form>
                            @else
                                <div class="small text-muted">Aktifkan sesi untuk mengubah token</div>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('cbt_sessions.toggle', $s->id) }}" method="POST" style="display:inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" type="submit">
                                    @if($s->active) Nonaktifkan @else Aktifkan @endif
                                </button>
                            </form>

                            @if($s->active)
                                @php
                                    // Determine legacy boolean token_active for backward compatibility in the single-click button
                                    $legacyTokenActive = false;
                                    if (isset($s->token_status)) {
                                        $legacyTokenActive = ((int) $s->token_status) > 0;
                                    } elseif (isset($s->token_active)) {
                                        $legacyTokenActive = (bool) $s->token_active;
                                    }
                                @endphp
                                <form action="{{ route('cbt_sessions.toggle_token', $s->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary" type="submit">
                                        @if($legacyTokenActive) Nonaktifkan Token @else Aktifkan Token @endif
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('cbt_sessions.destroy', $s->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus sesi ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
</div>
@endsection

