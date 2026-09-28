{{-- Notifikasi toast SweetAlert2 untuk sisi peserta.
     Otomatis menampilkan session success/error + validation errors.
     Menyediakan window.pesertaToast(icon, title) untuk JS halaman. --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    function toast(icon, title) {
        if (typeof Swal === 'undefined') return;
        Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: function (t) {
                t.addEventListener('mouseenter', Swal.stopTimer);
                t.addEventListener('mouseleave', Swal.resumeTimer);
            }
        }).fire({ icon: icon, title: title });
    }
    window.pesertaToast = toast;

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('success'))
            toast('success', @json(session('success')));
        @endif
        @if(session('error'))
            toast('error', @json(session('error')));
        @endif
        @if($errors->any())
            @foreach($errors->all() as $msg)
                toast('error', @json($msg));
            @endforeach
        @endif
    });
})();
</script>
