@extends('layouts.admin')

@section('title', 'Daftar Soal')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Daftar Soal</h1>
        <div>
            <a href="{{ route('soal.create') }}?source=master" class="btn btn-success btn-sm ms-2"><i class="fas fa-plus"></i> Tambah</a>
            @if(!empty($nonMasterCount) && $nonMasterCount > 0)
                <form action="{{ route('soal.mergeSources') }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Merge semua soal dari sumber tryout/import menjadi master? (Tidak bisa dibatalkan)');">
                    @csrf
                    <input type="hidden" name="confirm" value="yes">
                    <button type="submit" class="btn btn-outline-warning btn-sm">Merge sources ({{ $nonMasterCount }})</button>
                </form>
            @endif
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(isset($mapels) && $mapels)
                    <div class="row">
                        @foreach($mapels as $mp)
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title">{{ $mp->nama }}</h5>
                                        <p class="card-text text-muted">Jumlah soal: <strong>{{ $mp->soal_count }}</strong></p>
                                        <div class="mt-auto">
                                            <a href="{{ route('soal.index') }}?mapel={{ $mp->id }}" class="btn btn-primary btn-sm">Lihat Soal</a>
                                            <a href="{{ route('soal.create') }}?source=master&id_mapel={{ $mp->id }}" class="btn btn-outline-success btn-sm ms-2">Tambah Soal</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    @if(isset($soals) && $soals)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped align-middle" id="soal-table">
                                <thead>
                                    <tr>
                                        <th style="width:40px"><input type="checkbox" id="select-all"></th>
                                        <th style="width:60px">#</th>
                                        <th>Soal</th>
                                        <th style="width:220px">Mapel</th>
                                        <th style="width:180px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($soals as $soal)
                                    @php $rowSumber = $soal->sumber ?? ($source ?? 'master'); @endphp
                                    <tr>
                                        <td><input type="checkbox" class="select-row" value="{{ $soal->id }}"></td>
                                        <td class="text-muted">{{ ($soals->currentpage()-1) * $soals->perpage() + $loop->index + 1 }}</td>
                                        <td style="max-width:700px">
                                            <div class="small text-muted mb-1">Tipe: {{ $soal->jenis ?? 'teks' }}</div>
                                            <div class="text-truncate" style="max-width:700px">{!! $soal->soal ?? ($soal->soal_teks ?? '-') !!}</div>
                                        </td>
                                        <td>
                                            <div>{{ $soal->mapel->nama ?? '-' }}</div>
                                            <div class="mt-1">
                                                <span class="badge bg-secondary">MASTER</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-outline-primary btn-edit" data-id="{{ $soal->id }}" data-source="{{ $source ?? 'master' }}" title="Edit"><i class="fas fa-edit"></i></button>
                                                <a href="{{ route('soal.edit', $soal->id) }}?source={{ ($source ?? 'master') }}" class="btn btn-sm btn-primary d-none" title="Fallback Edit"><i class="fas fa-pencil-alt"></i></a>
                                                <form action="{{ route('soal.destroy', $soal->id) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Bulk action bar -->
                        <div class="my-3 d-flex align-items-center gap-2">
                            <button id="btn-delete-selected" class="btn btn-danger btn-sm">Hapus Terpilih</button>
                            <div class="d-flex align-items-center">
                                <label class="me-2 mb-0">Pindahkan ke:</label>
                                <select id="bulk-target-mapel" class="form-select form-select-sm">
                                    <option value="">-- Pilih Mapel --</option>
                                    @if(isset($mapelsList))
                                        @foreach($mapelsList as $mp)
                                            <option value="{{ $mp->id }}">{{ $mp->nama }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <button id="btn-move-selected" class="btn btn-outline-primary btn-sm ms-2">Pindahkan</button>
                                <button id="btn-duplicate-selected" class="btn btn-outline-success btn-sm ms-2">Duplikat</button>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-end">
                            {{ $soals->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">Tidak ada data soal untuk ditampilkan.</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="modalEditSoal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Soal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalEditContent" class="p-2">
                    <div class="text-center text-muted">Memuat form...</div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // handle edit button click -> load form via AJAX into modal
    document.querySelectorAll('.btn-edit').forEach(function(btn){
        btn.addEventListener('click', function(e){
            var id = this.dataset.id;
            var source = this.dataset.source || 'master';
            var url = '/soal/' + id + '/edit?source=' + encodeURIComponent(source);

            var modal = new bootstrap.Modal(document.getElementById('modalEditSoal'));
            var content = document.getElementById('modalEditContent');
            content.innerHTML = '<div class="text-center p-4">Memuat form...</div>';
            modal.show();

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                .then(function(r){ return r.text(); })
                .then(function(html){
                    // The server returns full page for normal requests; try to extract form partial
                    // If the response contains <form id="soal-edit-form"> we'll use it, else use whole html
                    var tmp = document.createElement('div'); tmp.innerHTML = html;
                    var form = tmp.querySelector('#soal-edit-form');
                    if(form) {
                        content.innerHTML = '';
                        content.appendChild(form);
                        // re-hook modal dismiss attributes to buttons inside partial
                    } else {
                        // fallback: display returned HTML
                        content.innerHTML = html;
                    }
                }).catch(function(err){
                    content.innerHTML = '<div class="text-danger">Gagal memuat form.</div>';
                });
        });
    });

    // confirm delete
    document.querySelectorAll('.delete-form').forEach(function(f){
        f.addEventListener('submit', function(e){
            if(!confirm('Yakin ingin menghapus soal ini?')){
                e.preventDefault();
            }
        });
    });

    // Select all / per-row selection
    var selectAll = document.getElementById('select-all');
    if(selectAll){
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.select-row').forEach(function(cb){ cb.checked = selectAll.checked; });
        });
    }

    function getSelectedIds(){
        var ids = [];
        document.querySelectorAll('.select-row:checked').forEach(function(cb){ ids.push(cb.value); });
        return ids;
    }

    // Bulk delete
    var btnDelete = document.getElementById('btn-delete-selected');
    if(btnDelete){
        btnDelete.addEventListener('click', function(){
            var ids = getSelectedIds();
            if(ids.length === 0){ alert('Pilih minimal satu soal.'); return; }
            if(!confirm('Yakin ingin menghapus ' + ids.length + ' soal terpilih?')) return;

            // create a form and submit (to preserve CSRF)
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{!! route("soal.bulkDestroy") !!}';
            var token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}'; form.appendChild(token);
            ids.forEach(function(id){ var i = document.createElement('input'); i.type='hidden'; i.name='selected[]'; i.value = id; form.appendChild(i); });
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Bulk move
    var btnMove = document.getElementById('btn-move-selected');
    if(btnMove){
        btnMove.addEventListener('click', function(){
            var ids = getSelectedIds();
            var target = document.getElementById('bulk-target-mapel').value;
            if(ids.length === 0){ alert('Pilih minimal satu soal.'); return; }
            if(!target){ alert('Pilih mapel tujuan.'); return; }
            if(!confirm('Pindahkan ' + ids.length + ' soal ke mapel terpilih?')) return;

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{!! route("soal.bulkMove") !!}';
            var token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}'; form.appendChild(token);
            var t = document.createElement('input'); t.type='hidden'; t.name='target_mapel'; t.value = target; form.appendChild(t);
            ids.forEach(function(id){ var i = document.createElement('input'); i.type='hidden'; i.name='selected[]'; i.value = id; form.appendChild(i); });
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Bulk duplicate handler
    var btnDuplicate = document.getElementById('btn-duplicate-selected');
    if(btnDuplicate){
        btnDuplicate.addEventListener('click', function(){
            var ids = getSelectedIds();
            var target = document.getElementById('bulk-target-mapel').value;
            if(ids.length === 0){ alert('Pilih minimal satu soal.'); return; }
            if(!target){ alert('Pilih mapel tujuan.'); return; }
            if(!confirm('Duplikat ' + ids.length + ' soal ke mapel terpilih?')) return;

            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{!! route("soal.bulkDuplicate") !!}';
            var token = document.createElement('input'); token.type = 'hidden'; token.name = '_token'; token.value = '{{ csrf_token() }}'; form.appendChild(token);
            var t = document.createElement('input'); t.type='hidden'; t.name='target_mapel'; t.value = target; form.appendChild(t);
            ids.forEach(function(id){ var i = document.createElement('input'); i.type='hidden'; i.name='selected[]'; i.value = id; form.appendChild(i); });
            document.body.appendChild(form);
            form.submit();
        });
    }
});
</script>
@endsection
