@extends('layouts.admin')

@section('title', 'Import Soal')

@section('content')
<div class="container">
    <h2>Import Soal</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('import_errors'))
        <div class="alert alert-warning">
            <strong>Beberapa baris gagal diproses:</strong>
            <ul>
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('soal.import') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                                <label for="file" class="form-label">Unggah file CSV/TSV atau XLSX (kolom: no, soal, opsi a, opsi b, opsi c, opsi d, opsi e, jawaban, id_mapel)</label>
                    <input type="file" name="file" id="file" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="paste" class="form-label">Atau tempel data (setiap baris: dipisah tab atau koma)</label>
                    <textarea name="paste" id="paste" rows="8" class="form-control" placeholder="Contoh:\n1\tSiapa presiden pertama Indonesia?\tSoekarno\tSoeharto\tGus Dur\tHabibie\tMegawati\tA\t3"></textarea>
                </div>

                <button class="btn btn-primary">Import</button>
            </form>
        </div>
    </div>

    <h5>Daftar Mapel (ID)</h5>
    <p>Gunakan kolom <code>id_mapel</code> untuk mengaitkan soal pada mapel. Berikut daftar mapel saat ini:</p>
    <table class="table table-sm table-bordered">
        <thead><tr><th>ID</th><th>Nama Mapel</th></tr></thead>
        <tbody>
        @foreach($mapels as $m)
            <tr><td>{{ $m->id }}</td><td>{{ $m->nama }}</td></tr>
        @endforeach
        </tbody>
    </table>

    <h5>Format contoh</h5>
    <p>Baris pertama boleh berisi header. Contoh format (tab-separated):</p>
    <pre>
no	soal	opsi a	opsi b	opsi c	opsi d	opsi e	jawaban	id_mapel
1	Apa ibu kota Indonesia?	Jakarta	Bandung	Surabaya	Medan	Makassar	A	26
2	Berapakah 2+2?	3	4	5	6	7	B	18
    </pre>

</div>
@endsection
