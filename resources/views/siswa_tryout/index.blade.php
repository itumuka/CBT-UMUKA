@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
    
@stop

@section('content')
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="p-2 text-white d-flex justify-content-between align-items-center" style="background-color:#254baab3">
                <h3 class="mb-0">Daftar Siswa Tryout</h3>
            </div>

            <div class="card-body">
                <!-- Form Pencarian -->
                <form method="GET" action="" class="mb-3">

                    <div class="d-flex justify-content-end">
                        <div class="input-group w-25">
                            <input type="text" name="search" class="form-control" placeholder="Cari nama, sekolah" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </div>
                    </div>

                    
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Sekolah</th>
                                <th>No Hp</th>
                                 <th>Mapel Wajib</th>
                                <th>Mapel Pilihan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($siswas as $siswa)
                            <tr>
                                <td>{{ $loop->iteration + ($siswas->firstItem() - 1) }}</td>
                                <td>{{ $siswa->nama }}</td>
                                <td>{{ $siswa->sekolah }}</td>
                                <td>{{ $siswa->no_hp }}</td>
                                <td>{{ implode(', ', $siswa->mapel_wajib_nama) }}</td>
                                <td>{{ implode(', ', $siswa->mapel_pilihan_nama) }}</td>
                                <td>
                                    <a href="{{ url('/data-siswa-tryout/' . $siswa->id . '/edit') }}" class="btn btn-primary btn-sm">Edit</a>
                        
                                    <form action="{{ route('siswa_tryout.destroy', $siswa->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Data tidak ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                        
                    </table>
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

@endsection