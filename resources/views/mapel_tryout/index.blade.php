@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content_header')
   
@stop

@section('content')
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="p-2 text-white d-flex justify-content-between align-items-center" style="background-color:#254baab3">
                <h3 class="mb-0">Daftar Mapel Tryout</h3>
                <div class="d-flex gap-2 ms-auto"> 
                    <a href="{{route('mapel_tryout.create')}}" class="btn btn-sm" style="background-color:#40de42d6; color:white">Tambah</a>
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

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped align-middle">
                        <thead>
                            <tr>
                                <th width="5%">No</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th width="30%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mapels as $mapel)
                            <tr>
                                <td>{{ $loop->iteration + ($mapels->firstItem() - 1) }}</td>
                                <td>{{ $mapel->nama }}</td>
                                <td>{{ $mapel->kategori }}</td>
                                <td>
                                    <a href="{{ url('/mapel-tryout/' . $mapel->id . '/edit') }}" class="btn btn-primary btn-sm">Edit</a>
                        
                                    <form action="{{ route('mapel_tryout.destroy', $mapel->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
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
                    {{ $mapels->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @endsection