@extends('layouts.admin')

@section('title', 'Edit Mapel')

@section('content_header')
    <h1>Edit Data Ujian</h1>
@stop

@section('content')
@php $rp = $rp ?? ''; @endphp
<div class="container mt-4 d-flex justify-content-center">
    <div class="card w-50">
        <div class="card-header text-white bg-primary">
            Edit Data Ujian @isset($modeLabel)<span class="badge bg-light text-dark">{{ $modeLabel }}</span>@endisset
        </div>
        <div class="card-body">
            <form action="{{ route($rp.'ujian.update', $ujian->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- Metode untuk update data --}}

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Ujian</label>
                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" 
                           value="{{ old('nama', $ujian->nama_ujian) }}" required>
                    @error('nama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="mode" class="form-label">Mode (CBT / Tryout)</label>
                    <select name="mode" id="mode" class="form-select @error('mode') is-invalid @enderror">
                        <option value="cbt" {{ old('mode', $ujian->mode ?? 'cbt') === 'cbt' ? 'selected' : '' }}>CBT</option>
                        <option value="tryout" {{ old('mode', $ujian->mode ?? 'cbt') === 'tryout' ? 'selected' : '' }}>Tryout</option>
                    </select>
                    @error('mode')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="mapel" class="form-label">Mapel Ujian (centang satu atau beberapa)</label>
                    @php $selected = old('id_mapels', $ujian->mapel_ids ?? []); @endphp
                    <div class="row">
                        @foreach($mapels as $mapel)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="id_mapels[]" value="{{ $mapel->id }}" id="mapel_{{ $mapel->id }}" {{ in_array($mapel->id, (array)$selected) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="mapel_{{ $mapel->id }}">{{ $mapel->nama }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('id_mapel')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror


                    <label for="waktu" class="form-label">Waktu Ujian</label>
                    <input type="text" name="waktu" id="waktu" class="form-control @error('waktu') is-invalid @enderror" 
                           value="{{ old('waktu', $ujian->waktu) }}" required>
                    @error('waktu')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="tgl_mulai" class="form-label">Tanggal Mulai Ujian</label>
                    <input type="datetime-local" name="tgl_mulai" id="tgl_mulai"
                        class="form-control @error('tgl_mulai') is-invalid @enderror"
                        value="{{ old('tgl_mulai', \Carbon\Carbon::parse($ujian->tgl_mulai)->format('Y-m-d\TH:i')) }}" required>
                    @error('tgl_mulai')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="tgl_terlambat" class="form-label">Tanggal Terlambat Ujian</label>
                    <input type="datetime-local" name="tgl_terlambat" id="tgl_terlambat"
                        class="form-control @error('tgl_terlambat') is-invalid @enderror"
                        value="{{ old('tgl_terlambat', \Carbon\Carbon::parse($ujian->terlambat)->format('Y-m-d\TH:i')) }}" required>
                    @error('tgl_terlambat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label for="allow_submit_after" class="form-label">Izinkan Submit Setelah (menit)</label>
                    <input type="number" name="allow_submit_after" id="allow_submit_after" class="form-control @error('allow_submit_after') is-invalid @enderror" 
                           value="{{ old('allow_submit_after', $ujian->allow_submit_after) }}" min="0" placeholder="(opsional)">
                    @error('allow_submit_after')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <button type="submit" class="btn text-white bg-success">Simpan Perubahan</button>
                <a href="{{ route($rp.'ujian.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<div class="container mt-3">
    <div class="card">
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
</div>

@endsection

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
    const end = document.getElementById('tgl_terlambat')?.value;
    const allow = document.getElementById('allow_submit_after')?.value;

    document.getElementById('preview_mulai').textContent = fmtHuman(start);
    document.getElementById('preview_selesai').textContent = fmtHuman(end);
    document.getElementById('preview_durasi').textContent = calcDuration(start, end);
    document.getElementById('preview_allow').textContent = allow ? `${allow} Menit` : '(0 Menit)';
}

['tgl_mulai','tgl_terlambat','allow_submit_after'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){
        el.addEventListener('input', updatePreview);
    }
});

updatePreview();
</script>
@endsection
