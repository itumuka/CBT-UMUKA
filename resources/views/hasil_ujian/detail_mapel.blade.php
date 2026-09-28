@extends('layouts.admin')

@section('title', 'Perankingan Mapel')

@section('content')
<div class="container mt-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Perankingan: {{ $mapel->nama ?? 'Mapel' }}</h3>
        <div>
            <a href="{{ route('hasil_ujian.exportMapel', ['id' => $mapel->id] + request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>
    <div class="mb-3 d-flex justify-content-end">
        <form method="GET" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari nama" value="{{ request('search') }}">
            <button class="btn btn-primary">Cari</button>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Rata-rata Nilai</th>
                    <th>Jumlah Tes</th>
                    <th>Total Benar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rankings as $row)
                <tr>
                    @php $rankNo = $loop->iteration + ($rankings->firstItem() - 1); @endphp
                    <td>{{ $rankNo }}</td>
                    <td>
                        {{ $row->nama }}
                        @if($rankNo === 1)
                            <span class="badge bg-warning text-dark ms-2"><i class="fas fa-trophy"></i> 1</span>
                        @elseif($rankNo === 2)
                            <span class="badge bg-secondary ms-2"><i class="fas fa-medal"></i> 2</span>
                        @elseif($rankNo === 3)
                            <span class="badge bg-info text-dark ms-2"><i class="fas fa-award"></i> 3</span>
                        @endif
                    </td>
                    <td>{{ number_format($row->avg_nilai,2) }}</td>
                    <td>{{ $row->tests_count }}</td>
                    <td>{{ $row->total_benar }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $rankings->appends(request()->query())->links() }}
    </div>
</div>

@endsection
