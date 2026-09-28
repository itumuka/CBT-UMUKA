{{-- Partial form for soal create/edit. Can be loaded standalone or inside modal via AJAX --}}
@php
    $isEdit = isset($soal->id);
    $jenisNow = old('jenis', $soal->jenis ?? 'teks');
    $currentFile = $soal->file ?? null;
    $fileOnDisk = null;
    if (!empty($currentFile)) {
        if (file_exists(public_path('upload/gambar_soal/'.$currentFile))) {
            $fileOnDisk = asset('upload/gambar_soal/'.$currentFile);
        } elseif (file_exists(public_path('upload/audio_soal/'.$currentFile))) {
            $fileOnDisk = asset('upload/audio_soal/'.$currentFile);
        } elseif (file_exists(public_path('image/gambar_soal/'.$currentFile))) {
            $fileOnDisk = asset('image/gambar_soal/'.$currentFile);
        }
    }
    $kunci = old('jawaban', $soal->jawaban ?? '');
@endphp
<form action="{{ $isEdit ? route('soal.update', $soal->id) : route('soal.store') }}" method="POST" enctype="multipart/form-data" id="soal-edit-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <input type="hidden" name="sumber" value="{{ $source ?? ($soal->sumber ?? 'master') }}">
    <input type="hidden" name="id" value="{{ old('id', $soal->id ?? '') }}">

    <div class="row">
        {{-- ============ KOLOM FORM ============ --}}
        <div class="col-lg-7">
            <div class="mb-3">
                <label for="id_mapel" class="form-label fw-bold">Mata Pelajaran</label>
                <select name="id_mapel" id="id_mapel" class="form-control" required>
                    @foreach($mapels as $mapel)
                        <option value="{{ $mapel->id }}" {{ old('id_mapel', $soal->id_mapel ?? '') == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama }}
                        </option>
                    @endforeach
                </select>
                @error('id_mapel') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="jenis" class="form-label fw-bold">Tipe Soal</label>
                <select name="jenis" id="jenis" class="form-control">
                    <option value="teks" {{ $jenisNow == 'teks' ? 'selected' : '' }}>Teks (Pilihan Ganda)</option>
                    <option value="listening" {{ $jenisNow == 'listening' ? 'selected' : '' }}>Listening (Audio)</option>
                </select>
                @error('jenis') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Lampiran (Gambar / Audio)</label>
                @if(!empty($currentFile))
                    <div class="card mb-2">
                        <div class="card-body p-2 d-flex gap-3 align-items-center">
                            @if($fileOnDisk)
                                @if($jenisNow == 'listening')
                                    <audio controls src="{{ $fileOnDisk }}" style="max-width:100%"></audio>
                                @else
                                    <img src="{{ $fileOnDisk }}" alt="Lampiran soal" class="img-thumbnail" style="max-height:120px">
                                @endif
                                <div class="small text-muted"><code>{{ $currentFile }}</code><br>Upload baru untuk mengganti.</div>
                            @else
                                <div class="alert alert-danger small mb-0 w-100">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    File <code>{{ $currentFile }}</code> tidak ditemukan di server. Upload ulang untuk memperbaiki.
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="hapus_file" value="1" id="hapus_file">
                        <label class="form-check-label text-danger" for="hapus_file">Hapus lampiran ini</label>
                    </div>
                @endif
                <div id="dropzone" class="border rounded p-3 text-center text-muted" style="border-style:dashed !important; cursor:pointer">
                    <i class="fas fa-cloud-upload-alt fa-2x mb-1"></i>
                    <div>Klik atau seret file ke sini <small>(maks 5 MB)</small></div>
                    <input type="file" name="gambar_soal" id="gambar_soal" class="d-none" accept="image/*,audio/*">
                </div>
                <div class="mt-2 d-none" id="new-file-preview-wrap">
                    <span class="badge bg-info" id="new-file-name"></span>
                    <img id="new-file-preview" class="img-thumbnail mt-1 d-none" style="max-height:150px" alt="Pratinjau file baru">
                </div>
                @error('gambar_soal') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="soal" class="form-label fw-bold">Teks Soal <small class="text-muted">(mendukung HTML)</small></label>
                <textarea name="soal" id="soal" class="form-control" rows="5">{{ old('soal', $soal->soal ?? '') }}</textarea>
                @error('soal') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>

            <label class="form-label fw-bold">Opsi Jawaban <small class="text-muted">(klik lingkaran untuk kunci jawaban)</small></label>
            @foreach(['a','b','c','d','e'] as $huruf)
                @php $H = strtoupper($huruf); @endphp
                <div class="card mb-2 opsi-card {{ $kunci === $H ? 'border-success' : '' }}" id="opsi-card-{{ $huruf }}">
                    <div class="card-body p-2 d-flex gap-2 align-items-start">
                        <div class="form-check mt-1">
                            <input class="form-check-input kunci-radio" type="radio" name="jawaban" value="{{ $H }}" id="kunci_{{ $huruf }}" {{ $kunci === $H ? 'checked' : '' }} required>
                            <label class="form-check-label fw-bold" for="kunci_{{ $huruf }}">{{ $H }}</label>
                        </div>
                        <textarea name="opsi_{{ $huruf }}" id="opsi_{{ $huruf }}" class="form-control opsi-input" rows="2" data-huruf="{{ $H }}">{{ old("opsi_$huruf", $soal->{'opsi_' . $huruf} ?? '') }}</textarea>
                    </div>
                    @error("opsi_$huruf") <div class="text-danger small px-2 pb-1">{{ $message }}</div> @enderror
                </div>
            @endforeach
            @error('jawaban') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
        </div>

        {{-- ============ KOLOM PRATINJAU ============ --}}
        <div class="col-lg-5">
            <div class="card border-primary sticky-top" style="top:10px">
                <div class="card-header bg-primary text-white py-2"><i class="fas fa-eye"></i> Pratinjau Peserta</div>
                <div class="card-body">
                    <div id="pv-file-wrap" class="mb-2 text-center {{ $fileOnDisk ? '' : 'd-none' }}">
                        @if($fileOnDisk)
                            @if($jenisNow == 'listening')
                                <audio controls src="{{ $fileOnDisk }}" style="max-width:100%"></audio>
                            @else
                                <img src="{{ $fileOnDisk }}" class="img-fluid img-thumbnail" style="max-height:220px" alt="Lampiran">
                            @endif
                        @endif
                    </div>
                    <div id="pv-soal" class="mb-3"></div>
                    <div id="pv-opsi" class="list-group"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3 pt-2 border-top sticky-bottom bg-white pb-1">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" onclick="if(!this.closest('.modal.show')){window.location='{{ route('soal.index') }}'}">Batal</button>
        <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Simpan</button>
    </div>
</form>

<script>
(function () {
    var soalEl = document.getElementById('soal');
    var pvSoal = document.getElementById('pv-soal');
    var pvOpsi = document.getElementById('pv-opsi');
    var inputs = document.querySelectorAll('.opsi-input');
    var radios = document.querySelectorAll('.kunci-radio');

    function esc(s) {
        return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function renderPreview() {
        if (pvSoal && soalEl) pvSoal.innerHTML = soalEl.value || '<span class="text-muted">— teks soal —</span>';
        if (!pvOpsi) return;
        pvOpsi.innerHTML = '';
        var kunci = (document.querySelector('.kunci-radio:checked') || {}).value || '';
        inputs.forEach(function (ta) {
            var h = ta.getAttribute('data-huruf');
            var item = document.createElement('div');
            item.className = 'list-group-item' + (h === kunci ? ' list-group-item-success fw-bold' : '');
            item.textContent = h + '. ' + (ta.value || '—');
            pvOpsi.appendChild(item);
        });
        document.querySelectorAll('.opsi-card').forEach(function (card) {
            var r = card.querySelector('.kunci-radio');
            card.classList.toggle('border-success', !!(r && r.checked));
        });
    }

    if (soalEl) soalEl.addEventListener('input', renderPreview);
    inputs.forEach(function (ta) { ta.addEventListener('input', renderPreview); });
    radios.forEach(function (r) { r.addEventListener('change', renderPreview); });
    renderPreview();

    // Dropzone + pratinjau file baru
    var dz = document.getElementById('dropzone');
    var fi = document.getElementById('gambar_soal');
    var wrap = document.getElementById('new-file-preview-wrap');
    var nm = document.getElementById('new-file-name');
    var img = document.getElementById('new-file-preview');

    function showFile(f) {
        if (!f) return;
        wrap.classList.remove('d-none');
        nm.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
        if (f.type.indexOf('image/') === 0 && img) {
            var rd = new FileReader();
            rd.onload = function (e) { img.src = e.target.result; img.classList.remove('d-none'); };
            rd.readAsDataURL(f);
        } else if (img) {
            img.classList.add('d-none');
        }
    }

    if (dz && fi) {
        dz.addEventListener('click', function () { fi.click(); });
        fi.addEventListener('change', function () { showFile(fi.files[0]); });
        ['dragover', 'dragenter'].forEach(function (ev) {
            dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.add('bg-light'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            dz.addEventListener(ev, function (e) { e.preventDefault(); dz.classList.remove('bg-light'); });
        });
        dz.addEventListener('drop', function (e) {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                fi.files = e.dataTransfer.files;
                showFile(fi.files[0]);
            }
        });
    }
})();
</script>
