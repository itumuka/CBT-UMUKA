@extends('layouts.app')

@section('content')
<div class="container text-center mt-5">
    <div class="d-inline-flex align-items-center justify-content-center rounded-circle shadow-sm mb-3" style="width:84px;height:84px;background:#fff3cd;">
        <i class="fas fa-info-circle fa-3x text-warning"></i>
    </div>
    <h3>Sudah Terkumpul</h3>
    <p class="text-muted">Memproses status... mohon tunggu.</p>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            title: 'Tidak diperbolehkan',
            html: 'Anda tidak diperbolehkan melakukan submit ulang karena sudah menyelesaikan ujian sebelumnya. Anda akan diarahkan ke dashboard dalam 3 detik.',
            icon: 'warning',
            showConfirmButton: false,
            allowOutsideClick: false,
            timer: 3000
        });
    setTimeout(function(){ window.location.href = '{{ route("daftar_ujian") }}'; }, 3100);
    });
</script>
@endpush

@endsection
