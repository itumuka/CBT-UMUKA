@extends('layouts.admin')

@section('title', 'Perankingan Peserta')

@section('content_header')
@stop

@section('content')
@php $rp = $rp ?? ''; @endphp
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Perankingan Peserta @isset($modeLabel)<span class="badge bg-{{ $modeBadge ?? 'secondary' }}">{{ $modeLabel }}</span>@endisset <small class="text-muted fs-6">(Gabungan Semua Mapel)</small></h2>
        <div>
            <a href="{{ route($rp.'hasil_ujian.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-csv"></i> Export CSV
            </a>
        </div>
    </div>

    <form method="GET" class="mb-3 d-flex justify-content-end">
        <div class="input-group" style="width:320px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama peserta" class="form-control">
            <button class="btn btn-primary">Cari</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle">
            <thead>
                <tr>
                    <th style="width:60px;">No</th>
                    <th>Nama Peserta</th>
                    <th>Sekolah</th>
                    <th>Mapel (klik untuk detail)</th>
                    <th style="width:140px;">Total Nilai (Total Benar)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rankings as $row)
                <tr>
                    @php $rankNo = $loop->iteration + ($rankings->firstItem() - 1); @endphp
                    <td style="width:70px">{{ $rankNo }}</td>
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
                    <td>{{ $row->sekolah }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-mapel-detail" data-siswa-id="{{ $row->siswa_id }}">Lihat Mapel</button>
                    </td>
                    @php $ct = $computedTotals[$row->siswa_id] ?? null; @endphp
                    <td class="text-center"><strong>{{ $ct['total_benar_questions'] ?? $row->total_benar }}</strong></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $rankings->appends(request()->query())->links() }}
    </div>

    <!-- Modal for per-student mapel breakdown -->
    <div class="modal fade" id="mapelDetailModal" tabindex="-1" aria-labelledby="mapelDetailLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="mapelDetailLabel">Rincian Mapel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="mapelDetailContent">Memuat...</div>
          </div>
        </div>
      </div>
    </div>

@section('js')
<script>
    (function(){
        // delegated click handler so it works even if DOM changes
        var mapelModalEl = document.getElementById('mapelDetailModal');
        var mapelModal = mapelModalEl ? new bootstrap.Modal(mapelModalEl) : null;

        document.addEventListener('click', function(e){
            var btn = e.target.closest('.btn-mapel-detail');
            if (!btn) return;
            try {
                var siswaId = btn.getAttribute('data-siswa-id');
                console.log('[hasil_ujian] btn-mapel-detail clicked, siswaId=', siswaId);
                var content = document.getElementById('mapelDetailContent');
                if (content) content.innerHTML = 'Memuat...';

                fetch('/hasil-ujian/student/' + siswaId + '/breakdown', { credentials: 'same-origin' })
                    .then(function(r){
                        if (!r.ok) throw new Error('Network response not ok: ' + r.status);
                        return r.json();
                    })
                    .then(function(json){
                        var rows = json.data || [];
                        if (!rows.length) {
                            if (content) content.innerHTML = '<div class="alert alert-info">Belum ada mapel untuk siswa ini</div>';
                            if (mapelModal) mapelModal.show();
                            return;
                        }
                        var html = '<table class="table table-sm table-bordered table-hover"><thead><tr><th>Mapel</th><th>Total Benar</th><th>Total Salah</th><th>Jumlah Tes</th></tr></thead><tbody>';
                        rows.forEach(function(r){
                            html += '<tr><td>' + (r.mapel_nama||r.mapel_id) + '</td><td>' + (r.total_benar||0) + '</td><td>' + (r.total_salah!=null?r.total_salah:'-') + '</td><td>' + (r.attempts||0) + '</td></tr>';
                        });
                        html += '</tbody></table>';
                        if (content) content.innerHTML = html;
                        if (mapelModal) mapelModal.show();
                    }).catch(function(err){
                        console.error('[hasil_ujian] breakdown fetch error', err);
                        if (content) content.innerHTML = '<div class="alert alert-danger">Gagal memuat data</div>';
                        if (mapelModal) mapelModal.show();
                    });
            } catch (err) {
                console.error('[hasil_ujian] click handler error', err);
            }
        }, false);
    })();
</script>
@endsection

@endsection
