@extends('layouts.admin')

@section('title', 'Input Soal - Cepat & Efisien')

@section('content_header')
    <h1><i class="fas fa-edit"></i> Input Soal</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Form Input -->
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Input Soal</h4>
                    <div>
                        <button type="button" class="btn btn-light btn-sm" id="btn-preview" title="Preview Soal">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" id="btn-draft" title="Simpan Draft">
                            <i class="fas fa-save"></i> Draft
                        </button>
                    </div>
                </div>
                <div class="card-body">

                 @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Progress Indicator -->
                <div class="progress mb-3" style="height: 5px;">
                    <div class="progress-bar bg-success" id="form-progress" style="width: 0%"></div>
                </div>

                    <form action="{{ route('soal.store') }}" method="POST" enctype="multipart/form-data" id="soal-form">
                        @csrf
                        <input type="hidden" name="sumber" value="{{ $source ?? 'master' }}">
                        
                        <!-- Quick Settings -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="id_mapel" class="form-label fw-bold">Mata Pelajaran</label>
                                <select name="id_mapel" id="id_mapel" class="form-select form-select-lg" required>                        
                                    <option value="">Pilih Mapel</option>
                                        @foreach ($p_mapel as $mapel)
                                            <option value="{{ $mapel->id }}" {{ (old('id_mapel') == $mapel->id || request()->get('id_mapel') == $mapel->id) ? 'selected' : '' }}>
                                                {{ $mapel->nama }}
                                            </option>
                                        @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="jenis" class="form-label fw-bold">Tipe Soal</label>
                                <select name="jenis" id="jenis" class="form-select form-select-lg">
                                    <option value="teks" {{ old('jenis', $soal->jenis ?? '') == 'teks' ? 'selected' : '' }}>Teks (Pilihan Ganda)</option>
                                    <option value="listening" {{ old('jenis', $soal->jenis ?? '') == 'listening' ? 'selected' : '' }}>Listening (Audio)</option>
                                </select>
                            </div>
                        </div>

                        <!-- File Upload with Drag & Drop -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" id="label-file">Gambar / Audio Soal <small class="text-muted">(Opsional - Drag & drop atau klik)</small></label>
                            <div class="border border-2 border-dashed rounded p-3 text-center" id="drop-zone" style="cursor: pointer; background-color: #f8f9fa;">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-0 text-muted">Drop file here or click to browse</p>
                                <input type="file" name="file" class="form-control d-none" id="input-file" accept="image/*,audio/*">
                            </div>
                            <div id="preview-area" class="mt-2">
                                @if (!empty($soal->file))
                                    @if(($soal->jenis ?? 'teks') == 'listening')
                                        <audio controls src="{{ asset('upload/audio_soal/' . $soal->file) }}"></audio>
                                    @else
                                        <img src="{{ asset('upload/gambar_soal/' . $soal->file) }}" class="img-thumbnail mt-2" width="100%">
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Soal Text with Auto-resize -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Teks Soal <small class="text-muted">(Tekan Ctrl+Enter untuk lanjut ke opsi)</small></label>
                            <div class="alert alert-info small">
                                Tips: Anda bisa mengetik simbol matematika langsung (±, ×, ÷, π) atau menulis LaTeX inline dengan $...$ / \(...\) dan rumus block dengan $$...$$. Gunakan tombol <strong>Preview</strong> untuk melihat rendering KaTeX. Jika ingin memasukkan banyak soal sekaligus, gunakan fitur Import (CSV/XLSX) dan lihat template import.
                            </div>
                            <textarea class="form-control" id="editor_soal" name="soal" rows="3" placeholder="Ketik soal di sini...">{{ old('soal', $soal->soal ?? '') }}</textarea>
                        </div>

                        <!-- Opsi Jawaban with Quick Input -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Opsi Jawaban <small class="text-muted">(Tekan Enter untuk lanjut ke opsi berikutnya)</small></label>
                            @foreach ($huruf_opsi as $idx)
                                <div class="input-group mb-2">
                                    <span class="input-group-text fw-bold">{{ $idx }}</span>
                                    <input type="text"
                                        class="form-control opsi-input"
                                        name="opsi_{{ $idx }}"
                                        value="{{ old('opsi_' . $idx, $data_pc[$idx]['opsi'] ?? '') }}"
                                        placeholder="Jawaban {{ $idx }}"
                                        data-next="opsi_{{ $huruf_opsi[$loop->index + 1] ?? 'jawaban' }}">
                                </div>
                            @endforeach
                        </div>

                        <!-- Kunci Jawaban -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kunci Jawaban</label>
                                <select class="form-select form-select-lg" name="jawaban" id="jawaban" required>
                                        <option value="">Pilih Jawaban Benar</option>
                                        <option value="A" {{ old('jawaban', $soal->jawaban ?? '') == 'A' ? 'selected' : '' }}>
                                            A
                                        </option>
                                        <option value="B" {{ old('jawaban', $soal->jawaban ?? '') == 'B' ? 'selected' : '' }}>
                                            B
                                        </option>
                                        <option value="C" {{ old('jawaban', $soal->jawaban ?? '') == 'C' ? 'selected' : '' }}>
                                            C
                                        </option>
                                        <option value="D" {{ old('jawaban', $soal->jawaban ?? '') == 'D' ? 'selected' : '' }}>
                                            D
                                        </option>
                                        <option value="E" {{ old('jawaban', $soal->jawaban ?? '') == 'E' ? 'selected' : '' }}>
                                            E
                                        </option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('soal.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" id="btn-clear">
                                    <i class="fas fa-eraser"></i> Clear
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Simpan Soal
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-md-4">
            <div class="card shadow-lg" id="preview-card" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-eye"></i> Preview Soal</h5>
                </div>
                <div class="card-body" id="preview-content">
                    <!-- Preview content will be populated by JavaScript -->
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Tips Input Cepat</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li><kbd>Ctrl+Enter</kbd> di soal → lanjut ke opsi A</li>
                        <li><kbd>Enter</kbd> di opsi → lanjut ke opsi berikutnya</li>
                        <li><kbd>Ctrl+S</kbd> → Simpan draft</li>
                        <li><kbd>Ctrl+P</kbd> → Preview</li>
                        <li>Drag & drop file untuk upload cepat</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Preview Soal Lengkap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-preview-content">
                <!-- Full preview content -->
            </div>
        </div>
    </div>
</div>

<!-- KaTeX for rendering LaTeX math (auto-render) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js"></script>

<script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize CKEditor
    if (document.getElementById('editor_soal')) {
        CKEDITOR.replace('editor_soal', {
            height: 120,
            allowedContent: true, // keep LaTeX backslashes and custom spans
            entities: false,
            toolbar: [
                ['Bold', 'Italic', 'Underline'],
                ['NumberedList', 'BulletedList'],
                ['Link', 'Unlink'],
                ['Undo', 'Redo']
            ]
        });
    }

    // Variables
    let autoSaveTimer;
    const form = document.getElementById('soal-form');
    
    // Drag & Drop File Upload
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('input-file');
    
    dropZone.addEventListener('click', () => fileInput.click());
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#e3f2fd';
    });
    dropZone.addEventListener('dragleave', () => {
        dropZone.style.backgroundColor = '#f8f9fa';
    });
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.style.backgroundColor = '#f8f9fa';
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFilePreview(files[0]);
        }
    });
    
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            updateFilePreview(e.target.files[0]);
        }
    });
    
    function updateFilePreview(file) {
        const previewArea = document.getElementById('preview-area');
        previewArea.innerHTML = `<div class="alert alert-info"><i class="fas fa-file"></i> ${file.name}</div>`;
    }
    
    // Quick Navigation with Enter
    document.querySelectorAll('.opsi-input').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextField = this.getAttribute('data-next');
                if (nextField) {
                    const nextElement = document.getElementById(nextField) || document.querySelector(`[name="${nextField}"]`);
                    if (nextElement) {
                        nextElement.focus();
                    }
                }
            }
        });
    });
    
    // Ctrl+Enter in soal textarea
    document.getElementById('editor_soal').addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.key === 'Enter') {
            e.preventDefault();
            document.querySelector('.opsi-input').focus();
        }
    });
    
    // Keyboard Shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey) {
            switch(e.key) {
                case 's':
                    e.preventDefault();
                    saveDraft();
                    break;
                case 'p':
                    e.preventDefault();
                    showPreview();
                    break;
            }
        }
    });
    
    // Auto-save draft
    function saveDraft() {
        // Implement auto-save logic here
        console.log('Draft saved');
        // You can implement AJAX call to save draft
    }
    
    // Preview functionality
    document.getElementById('btn-preview').addEventListener('click', showPreview);
    document.getElementById('btn-draft').addEventListener('click', saveDraft);
    
    function showPreview() {
        const modal = new bootstrap.Modal(document.getElementById('previewModal'));
        const previewContent = generatePreview();
        const modalContentEl = document.getElementById('modal-preview-content');
        modalContentEl.innerHTML = previewContent;
        // render LaTeX if KaTeX auto-render is available
        renderMathIfReady(modalContentEl);
        modal.show();
    }
    
    function generatePreview() {
        const soal = CKEDITOR.instances.editor_soal.getData() || document.getElementById('editor_soal').value;
        const opsiA = document.querySelector('[name="opsi_A"]').value;
        const opsiB = document.querySelector('[name="opsi_B"]').value;
        const opsiC = document.querySelector('[name="opsi_C"]').value;
        const opsiD = document.querySelector('[name="opsi_D"]').value;
        const opsiE = document.querySelector('[name="opsi_E"]').value;
        
        let html = `<div class="soal-preview">
            <h5>Soal:</h5>
            <div>${soal}</div>
            <h6 class="mt-3">Opsi Jawaban:</h6>
            <ul class="list-group">
                <li class="list-group-item">A. ${opsiA}</li>
                <li class="list-group-item">B. ${opsiB}</li>
                <li class="list-group-item">C. ${opsiC}</li>
                <li class="list-group-item">D. ${opsiD}</li>
                <li class="list-group-item">E. ${opsiE}</li>
            </ul>
        </div>`;
        
        return html;
    }

    // Helper: attempt to render math inside an element using KaTeX auto-render
    function renderMathIfReady(element) {
        try {
            if (window.renderMathInElement) {
                renderMathInElement(element, {
                    // delimiters for inline and display math
                    delimiters: [
                        {left: '$$', right: '$$', display: true},
                        {left: '\\(', right: '\\)', display: false},
                        {left: '$', right: '$', display: false}
                    ],
                    throwOnError: false
                });
            } else {
                // try again shortly (scripts might be deferred)
                setTimeout(function(){ renderMathIfReady(element); }, 250);
            }
        } catch (e) {
            console.warn('KaTeX render error:', e.message || e);
        }
    }
    
    // Form progress tracking
    function updateProgress() {
        const requiredFields = form.querySelectorAll('[required]');
        let filled = 0;
        requiredFields.forEach(field => {
            if (field.value.trim() !== '') filled++;
        });
        const progress = (filled / requiredFields.length) * 100;
        document.getElementById('form-progress').style.width = progress + '%';
    }
    
    form.addEventListener('input', updateProgress);
    updateProgress();
    
    // Clear form
    document.getElementById('btn-clear').addEventListener('click', function() {
        if (confirm('Yakin ingin menghapus semua input?')) {
            form.reset();
            CKEDITOR.instances.editor_soal.setData('');
            updateProgress();
        }
    });
    
    // Auto-save timer (every 30 seconds)
    function startAutoSave() {
        autoSaveTimer = setInterval(() => {
            if (document.querySelector('[name="soal"]').value || document.querySelector('[name="opsi_A"]').value) {
                saveDraft();
            }
        }, 30000);
    }
    
    startAutoSave();
});
</script>

@endsection

