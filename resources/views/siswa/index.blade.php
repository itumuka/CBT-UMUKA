@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    
@stop

@section('content')
    @php $rp = $rp ?? ''; @endphp
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="p-2 text-white d-flex justify-content-between align-items-center" style="background-color:#254baab3">
                <h3 class="mb-0">Daftar Peserta @isset($modeLabel)<span class="badge bg-{{ $modeBadge ?? 'secondary' }}">{{ $modeLabel }}</span>@endisset</h3>
                <div>
                    <a href="{{route($rp.'siswa.create')}}" class="btn btn-sm" style="background-color:#18fb20; color:white">Tambah</a>
                    {{-- <button class="btn btn-sm" style="background-color:#5CB338; color:white">Download format import</button>
                    <button class="btn btn-primary btn-sm">Import</button> --}}
                </div>
            </div>


            <div class="card-body">
                <!-- Tab/Filter Status Akun -->
                <div class="mb-3">
                    <a href="{{ route($rp.'siswa.index', array_merge(request()->except('page'), ['status' => 'real'])) }}" class="btn btn-sm {{ ($status ?? 'real') === 'real' ? 'btn-primary' : 'btn-outline-primary' }} me-1">Peserta (Real)</a>
                    <a href="{{ route($rp.'siswa.index', array_merge(request()->except('page'), ['status' => 'test'])) }}" class="btn btn-sm {{ ($status ?? '') === 'test' ? 'btn-warning' : 'btn-outline-warning' }}">Tester (Test)</a>
                    @if(($mode ?? 'cbt') === 'cbt')
                    <a href="{{ route($rp.'siswa.index', array_merge(request()->except('page'), ['status' => 'cbt'])) }}" class="btn btn-sm {{ ($status ?? '') === 'cbt' ? 'btn-info' : 'btn-outline-info' }}">CBT</a>
                    @endif
                    <a href="{{ route($rp.'siswa.index', array_merge(request()->except('page'), ['status' => null])) }}" class="btn btn-sm {{ empty($status) ? 'btn-secondary' : 'btn-outline-secondary' }} ms-1">Semua</a>

                    @if(($status ?? '') === 'cbt')
                        <form action="{{ route($rp.'siswa.setMapelCbt') }}" method="POST" style="display:inline;" onsubmit="return confirm('Set 2 mapel acak (IPA & IPS) untuk semua siswa CBT yang belum memilih mapel?')">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success ms-3"><i class="fas fa-random"></i> Set Mapel</button>
                        </form>
                    @endif
                </div>

                <!-- Form Pencarian -->
                <form method="GET" action="" class="mb-3">
                    <div class="d-flex justify-content-end">
                        <div class="input-group w-25">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, No Register, atau jurusan..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </div>
                    @if(!empty($status))
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                </form>

                <!-- Export & Spotlight Controls -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <a href="{{ route($rp.'siswa.export', array_merge(request()->except('page'), ['status' => $status ?? null])) }}" class="btn btn-sm btn-outline-success">Export Semua</a>
                        <a href="{{ route($rp.'siswa.export', array_merge(request()->except('page'), ['status' => $status ?? null, 'only_anomalies' => 1])) }}" class="btn btn-sm btn-outline-danger">Export Anomali</a>
                    </div>
                    <div>
                        <small class="text-muted">Spotlight: nama yang mengandung email, duplikat nama, dan sekolah anomali</small>
                    </div>
                </div>

                <!-- Spotlight panels -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="card p-2">
                            <h6>Nama berformat Email (spotlight)</h6>
                            <ul class="mb-0">
                                @forelse($nameEmails ?? [] as $ne)
                                    <li>{{ $ne->id }} — {{ $ne->nama }} @if($ne->email) ({{ $ne->email }}) @endif</li>
                                @empty
                                    <li><em>Tidak ada</em></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-2">
                            <h6>Duplikat Nama</h6>
                            <ul class="mb-0">
                                @if(!empty($duplicateNames))
                                    @foreach($duplicateNames as $name => $count)
                                        <li>{{ $name }} — {{ $count }} kali</li>
                                    @endforeach
                                @else
                                    <li><em>Tidak ada</em></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-2">
                            <h6>Sekolah Anomali</h6>
                            <ul class="mb-0">
                                @forelse($schoolAnomalies ?? [] as $scha)
                                    <li>{{ $scha->id }} — {{ $scha->sekolah }}</li>
                                @empty
                                    <li><em>Tidak ada</em></li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sorting Controls -->
                <div class="mb-3 d-flex justify-content-end align-items-center gap-2">
                    @php
                        $currentSortBy = request('sort_by', 'nama');
                        $currentSortDir = request('sort_dir', 'asc');
                        // helper to build query with toggled sort dir
                        $buildSortUrl = function($by) use ($currentSortBy, $currentSortDir) {
                            $dir = 'asc';
                            if ($currentSortBy === $by) {
                                $dir = $currentSortDir === 'asc' ? 'desc' : 'asc';
                            }
                            return request()->fullUrlWithQuery(['sort_by' => $by, 'sort_dir' => $dir, 'page' => 1]);
                        };
                    @endphp

                    <div class="btn-group" role="group">
                        <a href="{{ $buildSortUrl('nama') }}" class="btn btn-sm {{ $currentSortBy === 'nama' ? 'btn-primary' : 'btn-outline-primary' }}">Nama @if($currentSortBy === 'nama') ({{ strtoupper($currentSortDir) }}) @endif</a>
                        <a href="{{ $buildSortUrl('id') }}" class="btn btn-sm {{ $currentSortBy === 'id' ? 'btn-primary' : 'btn-outline-primary' }}">ID @if($currentSortBy === 'id') ({{ strtoupper($currentSortDir) }}) @endif</a>
                    </div>
                </div>

                <form id="bulk-delete-form" action="{{ route($rp.'siswa.bulkDestroy') }}" method="POST" onsubmit="return confirmBulkDelete();">
                    @csrf
                    @method('DELETE')
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>No</th>
                                <th>Nama</th>
                                <th>NO REGISTER</th>
                                <th>Jurusan</th>
                                <th>Sekolah</th>
                                <th>No. HP</th>
                                <th>Email</th>
                                <th>Token</th>
                                <th>Mapel Wajib</th>
                                <th>Mapel Pilihan</th>
                                <th>Session</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $nameEmailIds = collect($nameEmails ?? [])->pluck('id')->all();
                                $schoolAnomIds = collect($schoolAnomalies ?? [])->pluck('id')->all();
                            @endphp
                            @forelse ($siswas as $siswa)
                            @php
                                $isAnom = in_array($siswa->id, $nameEmailIds) || in_array($siswa->id, $schoolAnomIds);
                            @endphp
                            <tr class="{{ $isAnom ? 'table-danger' : '' }}">
                                <td><input type="checkbox" name="ids[]" value="{{ $siswa->id }}" class="row-checkbox"></td>
                                <td>{{ $loop->iteration + ($siswas->firstItem() - 1) }}</td>
                                <td>
                                    {{ $siswa->nama }}
                                    @if(in_array($siswa->id, $nameEmailIds))
                                        <span class="badge bg-danger ms-1">Nama berformat email</span>
                                    @endif
                                </td>
                                <td>{{ $siswa->nim }}</td>
                                <td>{{ $siswa->jurusan }}</td>
                                <td>{{ $siswa->sekolah ?? '-' }}</td>
                                <td>{{ $siswa->no_hp ?? '-' }}</td>
                                <td>{{ $siswa->email ?? '-' }}</td>
                                <td>
                                    @if($siswa->token)
                                        <code>{{ $siswa->token }}</code>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $wajibNames = [];
                                        if (!empty($siswa->mapel_wajib)) {
                                            $ids = array_filter(explode(',', $siswa->mapel_wajib));
                                            foreach ($ids as $id) {
                                                if (isset($mapelLookup[$id])) $wajibNames[] = $mapelLookup[$id];
                                            }
                                        }
                                    @endphp
                                    {{ implode(', ', $wajibNames) ?: '-' }}
                                </td>
                                <td>
                                    @php
                                        $pilihanNames = [];
                                        if (!empty($siswa->mapel_pilihan)) {
                                            $ids = array_filter(explode(',', $siswa->mapel_pilihan));
                                            foreach ($ids as $id) {
                                                if (isset($mapelLookup[$id])) $pilihanNames[] = $mapelLookup[$id];
                                            }
                                        }
                                    @endphp
                                    {{ implode(', ', $pilihanNames) ?: '-' }}
                                </td>
                                <td>{{ $siswa->session_key ?? '-' }}</td>
                                <td>
                                    <a href="{{ route($rp.'siswa.edit', $siswa->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                    <button type="button" class="btn btn-sm btn-warning btn-reset-password" data-id="{{ $siswa->id }}">Reset Password</button>
                                    
                                    <form action="{{ route($rp.'siswa.destroy', $siswa->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Data tidak ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                        
                    </table>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button type="submit" class="btn btn-sm btn-danger" id="delete-selected">Hapus Terpilih</button>
                        <a href="{{ route($rp.'siswa.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                    </div>
                </form>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $siswas->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Select / deselect all checkboxes (guarded: only attach if element exists)
        (function(){
            const selectAllEl = document.getElementById('select-all');
            if (!selectAllEl) return; // safe-guard for pages without the checkbox
            selectAllEl.addEventListener('change', function(e) {
                // e.target may be null in some edge cases; fallback to this
                const checked = (e && e.target && typeof e.target.checked !== 'undefined') ? e.target.checked : !!this.checked;
                document.querySelectorAll('.row-checkbox').forEach(cb => { if(cb) cb.checked = checked; });
            });
        })();

        // Reset password handler
        document.querySelectorAll('.btn-reset-password').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.dataset.id;
                // Show SweetAlert2 input to allow custom password or auto-generate
                Swal.fire({
                    title: 'Reset Password Peserta',
                    html: `
                        <p class="text-start">Masukkan password baru (opsional). Kosongkan untuk meng-generate password acak.</p>
                        <input id="swal-password" class="swal2-input" placeholder="Password baru (min 6 karakter)">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" value="1" id="swal-show-copy">
                            <label class="form-check-label small" for="swal-show-copy">Tampilkan password di layar setelah reset</label>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Reset',
                    focusConfirm: false,
                    preConfirm: () => {
                        const val = document.getElementById('swal-password').value || '';
                        return val;
                    }
                }).then(async (result) => {
                    if (!result.isConfirmed) return;
                    const custom = (result.value || '').trim();
                    // validate if provided
                    if (custom !== '' && custom.length < 6) {
                        Swal.fire({ icon: 'error', title: 'Password minimal 6 karakter' });
                        return;
                    }

                    // CSRF
                    const tokenInput = document.querySelector('input[name="_token"]');
                    const meta = document.querySelector('meta[name="csrf-token"]');
                    const csrf = tokenInput ? tokenInput.value : (meta ? meta.getAttribute('content') : '');

                    // Disable button to prevent duplicates
                    btn.disabled = true;

                    try {
                        const res = await fetch(`/siswa/${id}/reset-password`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                            body: JSON.stringify({ password: custom })
                        });
                        let data;
                        const ct = res.headers.get('content-type') || '';
                        if (ct.indexOf('application/json') !== -1) {
                            data = await res.json();
                        } else {
                            // non-json (redirect/html) - read text for better error
                            const text = await res.text();
                            throw new Error('Unexpected response: ' + (text.substring(0,300) || text));
                        }

                        if (res.ok && data.success) {
                                const showCopyEl = document.getElementById('swal-show-copy');
                                const showCopy = showCopyEl ? !!showCopyEl.checked : false;
                            const html = showCopy ? `Password baru: <strong>${data.password}</strong><br><small>Salin dan berikan ke peserta. Password hanya ditampilkan sekali.</small>` : `<small>Password di-set. Pilih 'Tampilkan password' jika ingin melihatnya sekarang.</small>`;
                            Swal.fire({ title: 'Berhasil', html: html, icon: 'success' });
                        } else {
                            const msg = (data && data.error) ? data.error : 'Terjadi kesalahan saat mereset password';
                            Swal.fire({ title: 'Error', text: msg, icon: 'error' });
                        }
                    } catch (e) {
                        console.error(e);
                        Swal.fire({ title: 'Error', text: 'Gagal mereset password', icon: 'error' });
                    } finally {
                        btn.disabled = false;
                    }
                });
            });
        });

        function confirmBulkDelete() {
            const checked = document.querySelectorAll('.row-checkbox:checked').length;
            if (!checked) {
                alert('Pilih data terlebih dahulu.');
                return false;
            }
            return confirm('Apakah Anda yakin ingin menghapus ' + checked + ' data terpilih?');
        }
    </script>

@endsection