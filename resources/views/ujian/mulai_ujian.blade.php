@extends('layouts.app')

@section('content')
<style>
    .custom-radio {
    accent-color: #0d6efd !important;
    transform: scale(1.3);
    display: inline-block;
    border: solid 1px black;
    }
    /* Critical timer visual */
    .timer-critical {
        color: #dc3545 !important; /* bootstrap danger */
        animation: pulse 1s infinite;
        font-weight: 800;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.06); }
        100% { transform: scale(1); }
    }

    /* Floating student timer panel */
    #student-time-panel.floating {
        position: fixed;
        top: 12px;
        right: 12px;
        z-index: 10500;
        background: rgba(230, 247, 255, 0.95);
        border: 1px solid #bfe9ff;
        padding: 12px 16px;
        border-radius: 8px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        min-width: 200px;
        text-align: center;
    }

    /* Mobile: keep panel at top-right but smaller so it doesn't dominate screen */
    @media (max-width: 576px) {
        #student-time-panel.floating {
            top: 8px;
            right: 8px;
            left: auto;
            bottom: auto;
            transform: none;
            padding: 8px 10px;
            min-width: 120px;
            font-size: 0.85rem;
        }

        /* make the timer number smaller on mobile */
        #student-time-panel #timer {
            font-size: 1.1rem !important;
        }
        #student-time-panel .small.text-muted {
            display: none; /* hide start time text on small screens to save space */
        }
    }

    /* Prevent text selection in question areas to reduce copy attempts */
    .card-header, .card-body {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    /* Modern peserta theme (display only - JS hooks untouched) */
    .exam-container { max-width: 880px; }
    #antiCheatBanner { border-radius: 14px; border: 1px solid #f5c66b; background: #fff8e6; color: #7a4b00; box-shadow: 0 4px 14px rgba(122,75,0,.08); }
    .mapel-pills .badge { font-size: .85rem; padding: 7px 14px; border-radius: 999px; }
    #student-time-panel { border-radius: 14px !important; border: 1px solid #c9dbff !important; background: linear-gradient(180deg, #f2f6ff, #e7efff) !important; box-shadow: 0 6px 18px rgba(37,75,170,.12); padding: 12px 18px !important; }
    #student-time-panel #timer { font-weight: 800; letter-spacing: .04em; }
    .soal-card { border: 0 !important; border-radius: 16px !important; box-shadow: 0 10px 28px rgba(15,23,42,.08); overflow: hidden; }
    .soal-card .card-header { background: linear-gradient(120deg, #254baa, #3b63c9); color: #fff; border: 0; padding: 12px 18px; }
    .soal-card .card-body { padding: 20px 22px; font-size: 1.02rem; }
    .soal-card .form-check { border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 10px 14px 10px 40px; margin-bottom: 10px; transition: border-color .15s, background .15s; }
    .soal-card .form-check:hover { border-color: #93b4f5; background: #f6f9ff; }
    .soal-card .form-check:has(input:checked) { border-color: #254baa; background: #eef3ff; box-shadow: 0 0 0 1px #254baa; }
    .soal-card .form-check-input { margin-left: -26px; margin-top: 4px; }
    .submit-bar { position: sticky; bottom: 12px; z-index: 1030; background: rgba(255,255,255,.96); border: 1px solid #dbe5fa; border-radius: 14px; box-shadow: 0 -6px 24px rgba(15,23,42,.10); padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 18px; }
    .submit-bar .btn { border-radius: 12px; padding: 10px 28px; font-weight: 700; }
    @media (max-width: 576px) {
        .soal-card .card-body { padding: 14px; }
        .submit-bar { flex-direction: column; align-items: stretch; text-align: center; }
    }
</style>

<!-- Logout Menu -->
<div style="position: fixed; top: 10px; right: 10px; z-index: 10000;">
    <button id="logout-btn" class="btn btn-sm text-white shadow" title="Logout (ujian dihentikan)" style="background:linear-gradient(135deg,#4b5563,#1f2937);border:0;border-radius:999px;padding:8px 16px;font-weight:600;">
        <i class="fas fa-door-open"></i> <span class="d-none d-sm-inline ms-1">Logout</span>
    </button>
</div>

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Inline Script for Logout -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Logout button clicked');
                console.log('Swal available:', typeof Swal);
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Konfirmasi Logout',
                        text: 'Apakah Anda yakin ingin logout? Ujian akan dihentikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        console.log('Swal result:', result);
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                } else {
                    console.log('Swal not available, using native confirm');
                    if (confirm('Apakah Anda yakin ingin logout? Ujian akan dihentikan.')) {
                        document.getElementById('logout-form').submit();
                    }
                }
            });
        } else {
            console.log('Logout button not found');
        }
    });
</script>

<div class="container exam-container">

    <!-- Anti-cheat banner -->
    <div class="alert alert-warning text-center" id="antiCheatBanner">
        <strong><i class="fas fa-shield-halved me-1"></i> Ujian diawasi:</strong> jangan membuka tab/jendela lain, meminimize, atau membagi layar. Pelanggaran dicatat dan dapat berakibat diskualifikasi.
        <div class="small mt-1">Pastikan fokus tetap pada layar ujian. Jika perangkat kehilangan fokus lebih dari 3 kali, ujian akan diberhentikan.</div>
    </div>

    @if(!empty($selectedMapels) && count($selectedMapels) > 0)
        <div class="mb-3 mapel-pills">
            <div class="small text-muted mb-1">Mata pelajaran yang diujikan:</div>
            <div class="d-flex flex-wrap gap-1">
                @foreach($selectedMapels as $m)
                    <span class="badge bg-primary">{{ $m->nama }}</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="alert alert-info text-center">
    <div id="student-time-panel" style="display:block;">
        <div class="small text-muted">Waktu Mulai: <span id="student-start">{{ $waktu_mulai_value ?? $waktu_mulai }}</span></div>
        <div style="margin-top:6px; font-weight:600;">Sisa waktu:</div>
        <div id="timer" style="font-size:1.6rem; color:#0d6efd;">--:--:--</div>
        <div id="autosaveIndicator" class="small mt-1 text-success" style="font-weight:600;">Autosave: <span id="autosaveStatus">OK</span></div>
    </div>
    <div id="warningCount" class="small text-danger mt-1" style="display:none">Pelanggaran terdeteksi: <span id="count">0</span></div>
</div>
    <form action="{{ route('submit_jawaban') }}" method="POST" id="ujianForm">
    @csrf

    @foreach ($soals as $index => $soal)
        <div class="card mb-3 soal-card">
            <div class="card-header">
                <strong>Soal {{ $soals->firstItem() + $index }}</strong>
            </div>
            <div class="card-body">
                {!! $soal->soal !!}

                @if ($soal->file)
                    @php
                        $filePathUpload = public_path('upload/gambar_soal/' . $soal->file);
                        $filePathLegacy = public_path('image/gambar_soal/' . $soal->file);
                        $servePath = null;
                        if (file_exists($filePathUpload)) {
                            $servePath = asset('upload/gambar_soal/' . $soal->file);
                        } elseif (file_exists($filePathLegacy)) {
                            $servePath = asset('image/gambar_soal/' . $soal->file);
                        }
                        $ext = strtolower(pathinfo($soal->file, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['png','jpg','jpeg','gif','webp','bmp','svg']);
                        $isVideo = in_array($ext, ['mp4','webm','ogg']);
                        $isAudio = in_array($ext, ['mp3','wav','m4a','ogg']);
                    @endphp

                    @if($servePath)
                        @if($isImage)
                            {{-- lazy load images and constrain height to avoid heavy rendering --}}
                            <img src="{{ $servePath }}" alt="Gambar Soal" class="img-fluid mb-3 soal-image" loading="lazy" decoding="async" style="max-height:360px; width:auto; object-fit:contain; display:block; margin:0 auto;">
                        @elseif($isVideo)
                            {{-- show video with metadata preload only --}}
                            <video controls preload="metadata" class="img-fluid mb-3" style="max-height:360px; width:100%; display:block; margin:0 auto;">
                                <source src="{{ $servePath }}" type="{{ $soal->tipe_file ?? 'video/mp4' }}">
                                Browser Anda tidak mendukung video.
                            </video>
                        @elseif($isAudio)
                            <audio controls preload="metadata" src="{{ $servePath }}" class="w-100 mb-3"></audio>
                        @else
                            {{-- unknown file type - render as link --}}
                            <div class="mb-2"><a href="{{ $servePath }}" target="_blank">Buka file soal</a></div>
                        @endif
                    @else
                        {{-- missing file: show placeholder to aid debugging --}}
                        <div class="alert alert-danger small">Gambar soal tidak ditemukan: {{ $soal->file }}</div>
                    @endif
                @endif

                @foreach (['A', 'B', 'C', 'D', 'E'] as $opsi)
                    @php
                        $content = trim(strip_tags($soal->{'opsi_' . strtolower($opsi)} ?? ''));
                    @endphp
                    @if (!empty($content))
                        <div class="form-check">
                            <input class="form-check-input custom-radio" type="radio" name="jawaban[{{ $soal->id }}]" value="{{ $opsi }}" required>
                            <label class="form-check-label">
                                {!! $opsi !!}. {!! str_replace(['#####', '<p>', '</p>'], '', $soal->{'opsi_' . strtolower($opsi)}) !!}
                            </label>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach  
    
    {{ $soals->links('pagination::bootstrap-5') }}

    <input type="hidden" name="waktu_mulai" value="{{$waktu_mulai_value ?? $waktu_mulai}}">
    <div class="submit-bar">
        <div class="small text-muted"><i class="fas fa-circle-info me-1"></i> Periksa kembali jawaban sebelum dikirim. Pengiriman hanya sekali.</div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim Jawaban</button>
    </div>
</form>

</div>
    @if(isset($currentAttemptSubmitted) && $currentAttemptSubmitted)
    <script>
        // If this attempt is already submitted, inform the user and redirect to dashboard
        document.addEventListener('DOMContentLoaded', function () {
            // Disable form controls
            const form = document.getElementById('ujianForm');
            if (form) form.querySelectorAll('input,select,textarea,button').forEach(el => el.disabled = true);

            // Prevent back button from returning to a usable state (best-effort)
            try {
                history.pushState(null, document.title, location.href);
                window.addEventListener('popstate', function () {
                    history.pushState(null, document.title, location.href);
                });
            } catch (e) {}

            Swal.fire({
                title: 'Terima kasih telah mengikuti tes',
                html: 'Jawaban Anda telah disimpan. Hasil akan diumumkan oleh panitia setelah proses penilaian selesai.<br><br>Anda sudah menyelesaikan ujian dan akan diarahkan dalam 3 detik.',
                icon: 'success',
                allowOutsideClick: false,
                showConfirmButton: false,
                timer: 3000
            });

            setTimeout(() => { window.location.href = '{{ route("daftar_ujian") }}'; }, 3100);
        });
    </script>
    @endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    // remaining_seconds is remaining seconds at server_time
    let initialRemaining = Number({{ isset($remaining_seconds) ? $remaining_seconds : 0 }} || 0);
    let serverTime = {{ $server_time ?? 'null' }}; // timestamp server saat view dibuat
    // allowedSubmitUntil: timestamp until submit allowed (nullable)
    let allowedSubmitUntil = {{ $allowed_submit_until ?? 'null' }};
    // For resync: endpoint to get current server time and exam end (if any)
    // NB: pakai route siswa (server.ujianActive), bukan route admin
    const serverActiveUrl = '{{ route('server.ujianActive') }}';
    // attempt start timestamp (optional)
    let startTime = {{ $start_time ?? 'null' }};
    let clientLoadTime = Date.now() / 1000; // waktu client saat load

        let timerDisplay = document.getElementById('timer');
        let form = document.getElementById('ujianForm');
        // Make the timer panel floating so user always sees it
        try {
            const st = document.getElementById('student-time-panel');
            if (st) st.classList.add('floating');
        } catch (e) {}

        function getCurrentServerTime() {
            if (serverTime) {
                let timeOffset = serverTime - clientLoadTime;
                return Date.now() / 1000 + timeOffset;
            }
            return Date.now() / 1000;
        }

        function updateTimer() {
            let remaining;
            if (serverTime) {
                // remaining was measured at serverTime; subtract elapsed since then
                let currentTime = getCurrentServerTime();
                let elapsedSinceServer = Math.floor(currentTime - serverTime);
                remaining = Math.max(0, initialRemaining - elapsedSinceServer);
            } else {
                remaining = Math.max(0, initialRemaining);
                initialRemaining = Math.max(0, initialRemaining - 1);
            }

            // format remaining as HH:MM:SS to match 'Waktu Mundur'
            const hours = Math.floor(remaining / 3600);
            const minutes = Math.floor((remaining % 3600) / 60);
            const seconds = remaining % 60;
            function pad(n){ return String(n).padStart(2,'0'); }
            timerDisplay.textContent = `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;

            // visual notification when remaining <= 5 minutes
            const CRITICAL_SECONDS = 300; // 5 minutes
            if (remaining > 0 && remaining <= CRITICAL_SECONDS) {
                timerDisplay.classList.add('timer-critical');
            } else {
                timerDisplay.classList.remove('timer-critical');
            }

            // Check submission window
            const now = getCurrentServerTime();
            if (remaining <= 0) {
                // Determine whether we are still within allowed submit window
                if (allowedSubmitUntil && now <= allowedSubmitUntil) {
                    // still within extra submit window: show different label
                    const extra = Math.max(0, Math.floor(allowedSubmitUntil - now));
                    const em = Math.floor(extra / 60);
                    const es = extra % 60;
                    timerDisplay.textContent = `${pad(0)}:${pad(0)}:${pad(0)} (Waktu utama habis, sisa submit tambahan ${pad(em)}:${pad(es)})`;
                    // allow form to be submitted during extra window
                    window.examExpired = false;
                } else {
                    // fully expired: disable form and prevent submission
                    timerDisplay.textContent = 'WAKTU HABIS';
                    window.examExpired = true;
                    // disable all inputs and submit button to prevent sending
                    try {
                        form.querySelectorAll('input,button,select,textarea').forEach(el => el.disabled = true);
                    } catch (e) {}
                    clearInterval(timerInterval);
                }
            }
        }

        updateTimer();
        let timerInterval = setInterval(updateTimer, 1000);

        // Periodic server resync to correct drift every 30s.
        // Backoff: setelah gagal beruntun, lewati beberapa tick agar console tidak spam.
        let resyncFails = 0;
        async function resyncServer() {
            try {
                const res = await fetch(serverActiveUrl);
                const ctype = (res.headers.get('content-type') || '').toLowerCase();
                if (!res.ok || ctype.indexOf('application/json') === -1) throw new Error('bad response');
                const data = await res.json();
                resyncFails = 0;
                if (data.active && data.terlambat_ts) {
                    // recalc serverTime and remaining from server authoritative values
                    serverTime = data.server_time;
                    // compute remaining as (terlambat_ts - server_time)
                    const rem = Math.max(0, data.terlambat_ts - data.server_time);
                    initialRemaining = rem;
                }
            } catch (e) {
                resyncFails++;
                // Lewati 3 tick berikutnya setelah 2x gagal beruntun
                if (resyncFails > 2) {
                    if (typeof console.debug === 'function') console.debug('resync skipped (backoff)', resyncFails);
                    resyncFails = 0;
                    skipTicks = 3;
                } else {
                    console.error('resync error', e);
                }
            }
        }
        let skipTicks = 0;
        setInterval(function () {
            if (skipTicks > 0) { skipTicks--; return; }
            resyncServer();
        }, 30000);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('ujianForm');

        // Restore jawaban dari localStorage
        Object.keys(localStorage).forEach(function(key) {
            if (key.startsWith('jawaban_')) {
                const soalId = key.split('_')[1];
                const value = localStorage.getItem(key);
                const input = document.querySelector(`input[name="jawaban[${soalId}]"][value="${value}"]`);
                if (input) {
                    input.checked = true;
                }
            }
        });

    // Simpan jawaban ke localStorage setiap kali dipilih, then try autosave to server
    const radios = form.querySelectorAll('input[type="radio"]');
    // The autosave route may have been removed; guard the route() call to avoid exceptions.
    const autosaveUrl = '{{ \Illuminate\Support\Facades\Route::has("ujian.autosave") ? route("ujian.autosave") : "" }}';
    const AUTOSAVE_ENABLED = (autosaveUrl && autosaveUrl.length > 0);

        function setAutosaveStatus(text, cls) {
            try {
                const el = document.getElementById('autosaveStatus');
                if (!el) return;
                el.textContent = text;
                el.parentElement.className = 'small mt-1 ' + (cls || 'text-secondary');
            } catch (e) { console.error(e); }
        }

        // queue failed autosaves in localStorage as autosave_queue (JSON array)
        function pushAutosaveQueue(item) {
            try {
                const key = 'autosave_queue';
                let q = [];
                const raw = localStorage.getItem(key);
                if (raw) q = JSON.parse(raw);
                q.push(item);
                localStorage.setItem(key, JSON.stringify(q));
            } catch (e) { console.error('pushAutosaveQueue error', e); }
        }

        async function flushAutosaveQueue() {
            try {
                if (!AUTOSAVE_ENABLED) return; // server autosave disabled: nothing to flush
                if (!navigator.onLine) return;
                const key = 'autosave_queue';
                const raw = localStorage.getItem(key);
                if (!raw) return;
                let q = JSON.parse(raw);
                if (!Array.isArray(q) || q.length === 0) {
                    localStorage.removeItem(key);
                    return;
                }
                for (let i = 0; i < q.length; i++) {
                    const item = q[i];
                    try {
                        const ok = await sendAutosave(item.soal_id, item.jawaban);
                        if (ok) {
                            // remove first item
                            q.shift();
                            localStorage.setItem(key, JSON.stringify(q));
                            i = -1; // reset loop to start
                        } else {
                            // stop trying for now
                            break;
                        }
                    } catch (e) { break; }
                }
                if (!q || q.length === 0) localStorage.removeItem(key);
            } catch (e) { console.error('flushAutosaveQueue error', e); }
        }

        async function sendAutosave(soalId, value) {
            // If server autosave is disabled, treat autosave as local-only success
            if (!AUTOSAVE_ENABLED) {
                try { setAutosaveStatus('Saved (local)', 'text-secondary'); } catch (e) {}
                return Promise.resolve(true);
            }

            try {
                const token = document.querySelector('input[name="_token"]').value;
                const res = await fetch(autosaveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ soal_id: soalId, jawaban: value })
                });
                if (!res.ok) throw new Error('network');
                const data = await res.json();
                if (data && data.ok) {
                    setAutosaveStatus('OK', 'text-success');
                    return true;
                }
                throw new Error('server');
            } catch (e) {
                console.error('autosave send failed', e);
                setAutosaveStatus('Offline', 'text-danger');
                return false;
            }
        }

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                const name = this.name; // example: jawaban[3]
                const soalId = name.match(/\d+/)[0]; // ambil angka
                const value = this.value;
                // save locally first
                localStorage.setItem('jawaban_' + soalId, value);
                // attempt autosave
                setAutosaveStatus('Saving...', 'text-warning');
                sendAutosave(soalId, value).then(ok => {
                    if (!ok) {
                        // queue for retry
                        pushAutosaveQueue({ soal_id: soalId, jawaban: value });
                    } else {
                        // on success, attempt flush any queued items
                        flushAutosaveQueue();
                    }
                });
            });
        });

        // Periodically try to flush the queue
        setInterval(flushAutosaveQueue, 5000);

        // Update indicator on online/offline
        window.addEventListener('online', function () { setAutosaveStatus('Online', 'text-success'); flushAutosaveQueue(); });
        window.addEventListener('offline', function () { setAutosaveStatus('Offline', 'text-danger'); });

        // helper: append saved answers from localStorage as hidden inputs and optionally submit
        window.appendSavedAnswersAndSubmit = function(options = {}) {
            try {
                // add hidden inputs for saved answers not present in DOM
                Object.keys(localStorage).forEach(function(key) {
                    if (!key.startsWith('jawaban_')) return;
                    const soalId = key.split('_')[1];
                    const value = localStorage.getItem(key);
                    if (!soalId || !value) return;
                    if (form.querySelector(`input[name="jawaban[${soalId}]"]`)) return;
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = `jawaban[${soalId}]`;
                    hidden.value = value;
                    hidden.className = 'injected-jawaban';
                    form.appendChild(hidden);
                });
            } catch (e) { console.error('appendSavedAnswers error', e); }

            // clear localStorage entries after copying
            try {
                Object.keys(localStorage).forEach(function(key) {
                    if (key.startsWith('jawaban_')) localStorage.removeItem(key);
                });
            } catch (e) { console.error('localStorage cleanup error', e); }

            // mark blocked so that reopened tabs don't continue
            try { localStorage.setItem('cbt_blocked', '1'); } catch (e) {}

            // optional immediate submit
            if (options.submit !== false) {
                // show loading then submit
                try {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ title: options.title || 'Mengirim jawaban...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    }
                } catch (e) {}

                if (typeof form.requestSubmit === 'function') form.requestSubmit(); else form.submit();
            }
        };

        // Ensure form submit cleans any injected inputs if needed (server side will ignore duplicates)
        form.addEventListener('submit', function () {
            // leave injected inputs for server to process; they will be removed on page reload
        });
    });
</script>

@endpush

@push('scripts')
<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ujianForm');
    if (!form) return;
    const submitBtn = form.querySelector('button[type="submit"]');
    if (!submitBtn) return;

    // Full list of soal IDs provided by server for cross-page completeness checks
    const ALL_SOAL_IDS = @json(isset($allSoalIds) ? $allSoalIds : []);
    submitBtn.addEventListener('click', function (e) {
        e.preventDefault();
        // Don't allow manual submit if exam is fully expired
        if (typeof window.examExpired !== 'undefined' && window.examExpired) {
            Swal.fire({
                title: 'Waktu Habis',
                text: 'Waktu untuk mengirim jawaban sudah habis. Anda tidak dapat mengirim ujian.',
                icon: 'error'
            });
            return;
        }
        // Check completeness across ALL pages: use server-provided ALL_SOAL_IDS and localStorage
        const missing = [];
        try {
            if (!Array.isArray(ALL_SOAL_IDS) || ALL_SOAL_IDS.length === 0) {
                // Fallback: check current page only
                const questionCards = Array.from(document.querySelectorAll('.card')).filter(c => c.querySelector('strong') && c.querySelector('strong').textContent.trim().startsWith('Soal'));
                questionCards.forEach((card, idx) => {
                    const soalLabel = card.querySelector('strong') ? card.querySelector('strong').textContent.trim() : ('Soal ' + (idx+1));
                    const radios = card.querySelectorAll('input[type="radio"]');
                    let answered = false;
                    radios.forEach(r => { if (r.checked) answered = true; });
                    if (!answered) {
                        const m = soalLabel.match(/(\d+)/);
                        missing.push(m ? m[0] : (idx+1));
                    }
                });
            } else {
                // iterate full list of soal IDs and check if answered either in DOM (checked radio) or in localStorage
                ALL_SOAL_IDS.forEach(function(soalId, idx) {
                    // prefer checked input in DOM
                    const anyChecked = form.querySelector(`input[name="jawaban[${soalId}]"]:checked`);
                    if (anyChecked) return; // answered on current page
                    // or saved in localStorage from other pages
                    const saved = localStorage.getItem('jawaban_' + soalId);
                    if (saved !== null && saved !== undefined && saved !== '') return;
                    // missing -> push display number (1-based)
                    missing.push(idx + 1);
                });
            }
        } catch (e) {
            console.error('completeness check error', e);
        }

        if (missing.length > 0) {
            const list = missing.join(', ');
            Swal.fire({
                icon: 'warning',
                title: 'Jawaban belum lengkap',
                html: `Soal nomor <strong>${list}</strong> belum dijawab. Silakan jawab semua soal sebelum mengirim.`
            });
            return;
        }

        Swal.fire({
            title: 'Kirim Jawaban?',
            text: 'Setelah dikirim, jawaban Anda tidak bisa diubah. Pastikan semua jawaban sudah benar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, kirim jawaban',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Before submitting, append saved answers from localStorage for pages not currently visible
                (function appendSavedAnswersAndSubmit() {
                    try {
                        // Iterate keys and add hidden inputs for each saved answer
                        Object.keys(localStorage).forEach(function(key) {
                            if (!key.startsWith('jawaban_')) return;
                            const soalId = key.split('_')[1];
                            const value = localStorage.getItem(key);
                            if (!soalId || !value) return;
                            // if there's already an input for this soal in the DOM (current page), skip
                            if (form.querySelector(`input[name="jawaban[${soalId}]"]`)) return;
                            // create hidden input
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = `jawaban[${soalId}]`;
                            hidden.value = value;
                            form.appendChild(hidden);
                        });
                    } catch (e) {
                        console.error('appendSavedAnswers error', e);
                    }

                    // Remove saved answers from localStorage (cleanup)
                    try {
                        Object.keys(localStorage).forEach(function(key) {
                            if (key.startsWith('jawaban_')) {
                                localStorage.removeItem(key);
                            }
                        });
                    } catch (e) { console.error('localStorage cleanup error', e); }

                    // Show a loading dialog while submitting
                    Swal.fire({
                        title: 'Mengirim jawaban...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    // Use requestSubmit to trigger submit event handlers when supported
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                })();
            }
        });
    });
});
</script>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const THRESHOLD = 2; // bila cheat_count > 2 -> flag
    let reported = false;
    let localWarnings = 0;

        // helper to post JSON with CSRF
        async function postReport(reason = 'visibilitychange') {
            try {
                const token = document.querySelector('input[name="_token"]').value;
                const res = await fetch("{{ route('ujian.reportCheat') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ event: reason })
                });
                // Abaikan respons non-JSON (mis. redirect login) agar tidak error
                const ctype = (res.headers.get('content-type') || '').toLowerCase();
                if (!res.ok || ctype.indexOf('application/json') === -1) return null;
                const data = await res.json();
                localWarnings = data.cheat_count || localWarnings;
                document.getElementById('warningCount').style.display = 'block';
                document.getElementById('count').textContent = localWarnings;
                if (data.cheat_count && data.cheat_count > THRESHOLD) {
                    // jika melebihi threshold, submit form dan redirect ke thank you
                    alert('Terdeteksi pelanggaran berulang. Ujian akan dihentikan.');
                    document.getElementById('ujianForm').submit();
                }
                return data;
            } catch (e) {
                // Abort saat pindah halaman / jaringan goyang: jangan berisik
                if (e && (e.name === 'AbortError' || (e.message && e.message.indexOf('NetworkError') !== -1))) {
                    if (typeof console.debug === 'function') console.debug('report skipped (offline/navigasi)', reason);
                } else {
                    console.error('report error', e);
                }
            }
        }

        // Request fullscreen to reduce switching
        async function enterFullscreen() {
            const el = document.documentElement;
            if (el.requestFullscreen) {
                try { await el.requestFullscreen(); } catch (e) {}
            }
        }

        // Attach event listeners
        // Stronger detection: visibilitychange, blur, beforeunload, key shortcuts (ctrl+t, ctrl+n), contextmenu
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                // Report the event but DO NOT force-clear localStorage or auto-submit.
                // Instead, try to flush any queued autosave entries to server.
                postReport('visibilitychange');
                try { if (typeof flushAutosaveQueue === 'function') flushAutosaveQueue(); } catch(e) { console.error(e); }
            }
        });

        window.addEventListener('blur', function () { 
            // On blur, report and try to flush autosave queue. Do not clear localStorage —
            // users navigating between pagination would otherwise lose stored answers.
            postReport('blur');
            try { if (typeof flushAutosaveQueue === 'function') flushAutosaveQueue(); } catch(e) { console.error(e); }
        });
        window.addEventListener('focus', function () { /* resume */ });

        // warn/attempt to prevent opening new windows via common shortcuts
        window.addEventListener('keydown', function (e) {
            // Block Ctrl+T, Ctrl+N, Ctrl+W, Ctrl+Tab in browser (best-effort)
            if ((e.ctrlKey || e.metaKey) && ['t','n','w','Tab'].includes(e.key)) {
                e.preventDefault();
                postReport('shortcut');
            }
        });

        // Block context menu (right-click) to make cheating harder
        window.addEventListener('contextmenu', function (e) { e.preventDefault(); postReport('contextmenu'); });

        // Disable copy/cut/paste globally on exam form
        document.addEventListener('copy', function (e) { e.preventDefault(); postReport('copy'); });
        document.addEventListener('cut', function (e) { e.preventDefault(); postReport('cut'); });
        document.addEventListener('paste', function (e) { e.preventDefault(); postReport('paste'); });

        // Prevent text selection via mouse drag / double click on question content
        document.addEventListener('selectstart', function (e) {
            // allow selection for inputs only (e.target bisa text node -> guard tagName)
            var tag = (e.target && e.target.tagName ? e.target.tagName : '').toString().toLowerCase();
            if (['input','textarea','select'].includes(tag)) return;
            e.preventDefault();
            postReport('select');
        });

        // Detect PrintScreen key (common on Windows: 'PrintScreen') and some screenshot combos
        window.addEventListener('keyup', function (e) {
            if (e.key === 'PrintScreen' || e.key === 'PrintScr' || e.key === 'Print') {
                postReport('screenshot');
                showBlockedOverlay('Tindakan screenshot terdeteksi. Ujian akan dihentikan.');
            }
        });

        // Block common developer tools / copy shortcuts and screenshot shortcuts
        window.addEventListener('keydown', function (e) {
            // Block Ctrl+C/V/X, Ctrl+S, Ctrl+Shift+I, F12, Ctrl+Shift+C
            const keyLower = (e.key || '').toString();
            const blockedCombos = (
                (e.ctrlKey || e.metaKey) && ['c','v','x','s'].includes(keyLower.toLowerCase())
            );
            const devtools = (e.key === 'F12') || (e.ctrlKey && e.shiftKey && ['I','i','C','c'].includes(e.key));
            // Windows+Shift+S often uses the Meta key on some browsers (metaKey) + Shift + S
            const winShiftS = (e.metaKey && e.shiftKey && keyLower.toLowerCase() === 's');
            const ctrlShiftS = (e.ctrlKey && e.shiftKey && keyLower.toLowerCase() === 's');

            if (blockedCombos || devtools || winShiftS || ctrlShiftS) {
                try { e.preventDefault(); } catch (ex) {}
                // Specific event names for later analysis
                const evName = winShiftS ? 'win_shift_s' : (ctrlShiftS ? 'ctrl_shift_s' : ('shortcut_blocked_' + keyLower));
                postReport(evName);
                try { showBlockedOverlay('Tindakan terlarang terdeteksi (' + evName + '). Ujian dihentikan.'); } catch(e) {}
                // minor UX: briefly flash banner
                const b = document.getElementById('antiCheatBanner');
                if (b) b.classList.add('bg-danger');
                setTimeout(()=>{ if (b) b.classList.remove('bg-danger'); }, 1000);
            }
        });

        // If user attempts to leave the page, notify server but do NOT return a string
        // Returning a string here causes browsers to show the default confirmation dialog
        // (e.g. 'Changes you made may not be saved.'). We avoid that by not setting returnValue.
        window.addEventListener('beforeunload', function (e) {
            // record intent to leave (fire-and-forget)
            navigator.sendBeacon = navigator.sendBeacon || function(){};
            try { postReport('beforeunload'); } catch(e){}
            // intentionally do NOT set e.returnValue or return a string to prevent browser dialog
        });

        // mobile/tablet: attempt to lock orientation and enter fullscreen
        try {
            if (screen.orientation && screen.orientation.lock) {
                screen.orientation.lock('portrait').catch(()=>{});
            }
        } catch(e) {}
        enterFullscreen();

            // Tab-lock logic
            const TAB_PING_INTERVAL = 3000; // ms
            const tabId = (localStorage.getItem('cbt_tab_id') || ('tab_' + Math.random().toString(36).substr(2,9)));
            localStorage.setItem('cbt_tab_id', tabId);

            async function registerTab() {
                try {
                    const token = document.querySelector('input[name="_token"]').value;
                    const res = await fetch("{{ route('ujian.registerTab') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ tab_id: tabId })
                    });
                    const data = await res.json();
                    if (data.blocked) {
                        showBlockedOverlay('Tab lain sedang aktif. Buka tab utama untuk melanjutkan.');
                    }
                } catch (e) { console.error(e); }
            }

            async function pingTab() {
                try {
                    const token = document.querySelector('input[name="_token"]').value;
                    const res = await fetch("{{ route('ujian.pingTab') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ tab_id: tabId })
                    });
                    const data = await res.json();
                    if (!data.ok) {
                        showBlockedOverlay('Tab lain mengambil alih ujian.');
                    }
                } catch (e) { console.error(e); }
            }

            async function releaseTab() {
                try {
                    const token = document.querySelector('input[name="_token"]').value;
                    await fetch("{{ route('ujian.releaseTab') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                        body: JSON.stringify({ tab_id: tabId })
                    });
                } catch (e) { console.error(e); }
            }

            function showBlockedOverlay(message) {
                if (document.getElementById('blockedOverlay')) return;
                const overlay = document.createElement('div');
                overlay.id = 'blockedOverlay';
                overlay.style.position = 'fixed';
                overlay.style.top = 0;
                overlay.style.left = 0;
                overlay.style.right = 0;
                overlay.style.bottom = 0;
                overlay.style.background = 'rgba(0,0,0,0.7)';
                overlay.style.zIndex = 99999;
                overlay.style.display = 'flex';
                overlay.style.alignItems = 'center';
                overlay.style.justifyContent = 'center';
                overlay.style.color = '#fff';
                overlay.style.fontSize = '1.2rem';
                overlay.innerText = message;
                document.body.appendChild(overlay);
                // disable form
                const form = document.getElementById('ujianForm');
                if (form) form.querySelectorAll('input,button,select,textarea').forEach(el => el.disabled = true);

                // After a short delay, redirect back to daftar ujian to avoid stuck state
                setTimeout(function() {
                    window.location.href = '{{ route("daftar_ujian") }}';
                }, 1500);
            }

            // register on load
            registerTab();
            // ping periodically
            const pingInterval = setInterval(pingTab, TAB_PING_INTERVAL);
            // release on unload
            window.addEventListener('beforeunload', function () { releaseTab(); });
    });
</script>
@endpush


@endsection
