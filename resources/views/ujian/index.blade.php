@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
   
@stop

@section('content')
    @php $rp = $rp ?? ''; @endphp
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div id="admin-active-panel" style="display:none;">
                <div class="p-3 mb-3 rounded" style="background:#e6f7ff;border:1px solid #bfe9ff;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong id="admin-active-name">Ujian Aktif:</strong>
                            <div id="admin-active-start" class="small text-muted">Waktu mulai: -</div>
                        </div>
                        <div style="text-align:center;">
                            <div class="small text-muted">Waktu Mundur</div>
                            <div id="admin-active-timer" style="font-size:1.5rem;font-weight:600;color:#0d6efd">--:--:--</div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="p-2 text-white d-flex justify-content-between align-items-center" style="background-color:#254baab3">
                <h3 class="mb-0">Daftar Ujian @isset($modeLabel)<span class="badge bg-light text-dark">{{ $modeLabel }}</span>@endisset</h3>
                <div class="d-flex gap-2 ms-auto align-items-center">
                    @if(($rp ?? '') === 'cbt.')
                        <span class="badge bg-{{ ($sesiAktif->key ?? '') === 'DEMO' ? 'success' : 'secondary' }} me-1" title="Sesi aktif saat ini">
                            Sesi: {{ $sesiAktif->key ?? '-' }}
                        </span>
                        <form action="{{ route('cbt.demo.reset') }}" method="POST" class="d-inline" onsubmit="return confirm('Reset Demo?\n\n- Hapus semua attempt demo1/2/3 di ujian DEMO CBT\n- Aktifkan sesi DEMO (peserta real terkunci selama sesi DEMO aktif)')">
                            @csrf
                            <input type="hidden" name="aktifkan_sesi" value="1">
                            <button type="submit" class="btn btn-sm btn-warning"><i class="fas fa-redo"></i> Reset Demo</button>
                        </form>
                    @endif
                    <a href="{{route($rp.'ujian.create')}}" class="btn btn-sm btn-success">Tambah</a>
                </div>
            </div>
            

            <div class="card-body">
                <!-- Form Pencarian -->
                <form method="GET" action="" class="mb-3">

                    <div class="d-flex justify-content-end">
                        <div class="input-group w-25">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </div>
                    
                </form>

                <form id="bulk-delete-form" method="POST" action="{{ route($rp.'ujian.bulkDestroy') }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle">
                            <thead>
                                <tr>
                                    <th width="3%"><input type="checkbox" id="select-all"></th>
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Mode</th>
                                    <th>Mapel</th>
                                    <th>Jumlah Soal</th>
                                    <th>Waktu</th>
                                    <th>Mulai</th>
                                    <th>Terlambat</th>
                                    <th>Token</th>
                                    <th width="25%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ujians as $ujian)
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="{{ $ujian->id }}" class="row-checkbox"></td>
                                    <td>{{ $loop->iteration + ($ujians->firstItem() - 1) }}</td>
                                    <td>{{ $ujian->nama_ujian }}</td>
                                    <td><span class="badge bg-{{ ($ujian->mode ?? 'cbt') === 'cbt' ? 'primary' : 'success' }}">{{ strtoupper($ujian->mode ?? 'CBT') }}</span></td>
                                    <td>
                                        @php
                                            $ids = method_exists($ujian, 'getMapelIdsAttribute') ? $ujian->mapel_ids : ($ujian->id_mapel ? [$ujian->id_mapel] : []);
                                            $names = [];
                                            foreach ($ids as $mid) {
                                                if (isset($mapelLookup[$mid])) $names[] = $mapelLookup[$mid];
                                            }
                                        @endphp
                                        {{ implode(', ', $names) ?: '-' }}
                                    </td>
                                    <td>{{ $ujian->jumlah_soal }}</td>
                                    <td>{{ $ujian->waktu }} menit</td>
                                    <td>{{ $ujian->tgl_mulai }}</td>
                                    <td>{{ $ujian->terlambat }}</td>
                                    <td>{{ $ujian->token }}</td>
                                    <td>
                                        <form action="{{ route($rp.'ujian.generateToken', $ujian->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-success">Refresh Token</button>
                                        </form>
                                        <a href="{{ route($rp.'ujian.edit', $ujian->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                        <button type="button" class="btn btn-sm btn-danger btn-delete-single" data-id="{{ $ujian->id }}">Hapus</button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center">Data tidak ditemukan</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <div>
                            <button type="submit" id="bulk-delete-btn" class="btn btn-danger" onclick="return confirm('Hapus semua ujian yang dipilih?')">Hapus yang dipilih</button>
                        </div>
                        <div>
                            {{ $ujians->appends(request()->query())->links() }}
                        </div>
                    </div>
                </form>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $ujians->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = selectAll.checked);
                });
            }

            // Prevent nested form hidden _method fields (e.g. from row forms) from
            // overriding the method of the bulk-delete form. Some row actions
            // may include a hidden input named _method (PUT/DELETE) which will be
            // submitted as part of this form and cause Laravel to treat the
            // request as e.g. PUT. Remove any _method inputs before submitting.
            const bulkForm = document.getElementById('bulk-delete-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function (e) {
                    // Remove any nested _method inputs to ensure method remains POST
                    bulkForm.querySelectorAll('input[name="_method"]').forEach(n => n.remove());
                    return true;
                });
            }

            // Single delete buttons: build a temporary form and submit
            document.querySelectorAll('.btn-delete-single').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    if (!confirm('Hapus ujian ini?')) return;
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route($rp.'ujian.bulkDestroy') }}';
                    const token = document.querySelector('input[name="_token"]').value;
                    const inputToken = document.createElement('input');
                    inputToken.type = 'hidden';
                    inputToken.name = '_token';
                    inputToken.value = token;
                    const idsInput = document.createElement('input');
                    idsInput.type = 'hidden';
                    idsInput.name = 'ids[]';
                    idsInput.value = id;
                    form.appendChild(inputToken);
                    form.appendChild(idsInput);
                    document.body.appendChild(form);
                    form.submit();
                });
            });
        });
    </script>
    <script>
        // Admin active ujian polling and countdown
        async function fetchActiveUjian() {
            try {
                const res = await fetch('{{ route('ujian.active') }}');
                const data = await res.json();
                if (!data.active) {
                    document.getElementById('admin-active-panel').style.display = 'none';
                    return;
                }

                document.getElementById('admin-active-panel').style.display = 'block';
                document.getElementById('admin-active-name').textContent = data.nama_ujian || 'Ujian Aktif';
                document.getElementById('admin-active-start').textContent = 'Waktu mulai: ' + (data.tgl_mulai || '-');

                const serverTime = data.server_time;
                const examEndTs = data.terlambat_ts; // timestamp
                // compute remaining seconds and start countdown
                const clientLoad = Date.now() / 1000;
                function getCurrentServerTime() {
                    return Date.now() / 1000 + (serverTime - clientLoad);
                }

                let timerEl = document.getElementById('admin-active-timer');
                function updateAdminTimer() {
                    const now = getCurrentServerTime();
                    const remaining = Math.max(0, Math.floor(examEndTs - now));
                    const h = Math.floor(remaining / 3600);
                    const m = Math.floor((remaining % 3600) / 60);
                    const s = remaining % 60;
                    function pad(n){return String(n).padStart(2,'0')}
                    timerEl.textContent = `${pad(h)}:${pad(m)}:${pad(s)}`;
                    if (remaining <= 0) {
                        timerEl.textContent = '00:00:00';
                    }
                }

                updateAdminTimer();
                // clear previous interval if any
                if (window.__adminActiveInterval) clearInterval(window.__adminActiveInterval);
                window.__adminActiveInterval = setInterval(updateAdminTimer, 1000);
            } catch (e) {
                console.error('active ujian fetch error', e);
            }
        }

        // Poll every 15 seconds to pick up new active ujian
        fetchActiveUjian();
        setInterval(fetchActiveUjian, 15000);
    </script>

    @endsection