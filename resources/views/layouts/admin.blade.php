{{-- Layout dasar panel admin: AdminLTE + tema global + toast notifikasi.
     Semua halaman admin sebaiknya extend layouts.admin, bukan adminlte::page langsung. --}}
@extends('adminlte::page')

@section('adminlte_css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/admin-modern.css') }}">
@endsection

@section('adminlte_js')
    @parent
    @include('ujian._toast')
    <script>
        // Konfirmasi global sebelum logout (satu pintu keluar).
        // Dipanggil dari onclick tombol logout navbar (submit() programatik
        // tidak memicu event submit, jadi konfirmasi dipasang di sini).
        window.adminConfirmLogout = function (ev) {
            ev.preventDefault();
            var f = document.getElementById('logout-form');
            if (typeof Swal === 'undefined') {
                if (f) f.submit();
                return false;
            }
            Swal.fire({
                title: 'Keluar aplikasi?',
                text: 'Anda harus login kembali untuk masuk.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#254baa',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then(function (r) {
                if (r.isConfirmed && f) f.submit();
            });

            return false;
        };
    </script>
@endsection
