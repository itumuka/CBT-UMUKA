@extends('layouts.admin')

@section('title', 'Tambah Ujian')

@section('content')
@php $rp = $rp ?? ''; @endphp
<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">Tambah Ujian @isset($modeLabel)<span class="badge bg-light text-dark">{{ $modeLabel }}</span>@endisset</div>
        <div class="card-body">
            <form action="{{ route($rp.'ujian.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama Ujian</label>
                    <input type="text" name="nama_ujian" class="form-control" value="{{ old('nama_ujian') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mode (CBT / Tryout)</label>
                    <select name="mode" class="form-select">
                        <option value="cbt" {{ old('mode', $mode ?? 'cbt') === 'cbt' ? 'selected' : '' }}>CBT</option>
                        <option value="tryout" {{ old('mode', $mode ?? 'cbt') === 'tryout' ? 'selected' : '' }}>Tryout</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mapel (centang satu atau beberapa)</label>
                    <div class="row">
                        @php $oldSelected = old('id_mapels', []); @endphp
                        @foreach($mapels as $m)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="id_mapels[]" value="{{ $m->id }}" id="mapel_{{ $m->id }}" {{ in_array($m->id, (array)$oldSelected) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mapel_{{ $m->id }}">{{ $m->nama }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Soal</label>
                    <input type="number" name="jumlah_soal" class="form-control" value="{{ old('jumlah_soal', 0) }}" min="0">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu (menit) - keterangan</label>
                        <input type="number" name="waktu" class="form-control" value="{{ old('waktu', '') }}" min="0" placeholder="(opsional - hanya keterangan)">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Waktu Mulai</label>
                    <input type="datetime-local" name="tgl_mulai" id="tgl_mulai" class="form-control" value="{{ old('tgl_mulai') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Waktu Selesai</label>
                    <input type="datetime-local" name="terlambat" id="terlambat" class="form-control" value="{{ old('terlambat') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nilai Minimal (opsional)</label>
                    <input type="number" name="nilai_minimal" class="form-control" value="{{ old('nilai_minimal', 0) }}" min="0">
                </div>

                <div class="mb-3">
                    <label class="form-label">Izinkan Submit Setelah (menit)</label>
                    <input type="number" name="allow_submit_after" id="allow_submit_after" class="form-control" value="{{ old('allow_submit_after', '') }}" min="0" placeholder="(opsional)">
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Simpan</button>
                    <a href="{{ route($rp.'ujian.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h5>PREVIEW</h5>
            <div id="preview" class="p-3 border rounded">
                <div><strong>Waktu Mulai:</strong> <span id="preview_mulai">-</span></div>
                <div><strong>Waktu Selesai:</strong> <span id="preview_selesai">-</span></div>
                <div><strong>Durasi Ujian:</strong> <span id="preview_durasi">-</span></div>
                <div><strong>Ijinkan Submit Setelah:</strong> <span id="preview_allow">-</span></div>
            </div>
        </div>
    </div>
    
@stop

@section('js')
<script>
function fmtHuman(dt) {
    if(!dt) return '-';
    try {
        const d = new Date(dt);
        return new Intl.DateTimeFormat('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit' }).format(d);
    } catch(e) {
        return dt;
    }
}

function calcDuration(start, end) {
    if(!start || !end) return '-';
    const s = new Date(start);
    const e = new Date(end);
    if(isNaN(s) || isNaN(e)) return '-';
    const diff = Math.max(0, Math.floor((e - s) / 1000));
    const minutes = Math.floor(diff / 60);
    const seconds = diff % 60;
    return `${minutes}:${String(seconds).padStart(2,'0')} (${minutes} Menit)`;
}

function updatePreview(){
    const start = document.getElementById('tgl_mulai')?.value;
    const end = document.getElementById('terlambat')?.value;
    const allow = document.getElementById('allow_submit_after')?.value;

    document.getElementById('preview_mulai').textContent = fmtHuman(start);
    document.getElementById('preview_selesai').textContent = fmtHuman(end);
    document.getElementById('preview_durasi').textContent = calcDuration(start, end);
    document.getElementById('preview_allow').textContent = allow ? `${allow} Menit` : '(0 Menit)';
}

['tgl_mulai','terlambat','allow_submit_after'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){
        el.addEventListener('input', updatePreview);
    }
});

updatePreview();
</script>
@endsection
            
