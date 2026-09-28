<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $title = config('app.name', 'CBT');
        if (trim($__env->yieldContent('title')) !== '') {
            $t = trim($__env->yieldContent('title'));
            // If the title is exactly 'Login' or 'Register', show only that
            if (in_array($t, ['Login', 'Register'])) {
                $title = $t;
            } else {
                $title = $t . ' - ' . config('app.name', 'CBT');
            }
        }
    @endphp
    <title>{{ $title }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/image/umuka.png">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js']);

    <!-- Font Awesome CDN (versi 5.x) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
    <div id="app">
        {{-- <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">

                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="background-color: black">
                                    <!-- Tombol Logout -->
                                    <a class="dropdown-item text-white" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                                    </a>
                                
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav> --}}

        {{-- Persistent logout button for authenticated users --}}
        @auth
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        @endauth

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    {{-- SweetAlert2 CDN (fallback jika belum ada) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Sidebar logout handler: when AdminLTE renders the menu item with class 'menu-logout', intercept click
        document.addEventListener('DOMContentLoaded', function () {
                document.body.addEventListener('click', function (e) {
                    // element with explicit class
                    var t = e.target.closest && e.target.closest('.menu-logout');
                    // anchor exactly '#logout'
                    var aExact = e.target.closest && e.target.closest('a[href="#logout"]');
                    // anchor whose href contains the fragment '#logout' (handles '/ujian#logout' etc.)
                    var aAny = (function () {
                        var a = e.target.closest && e.target.closest('a');
                        if (!a) return null;
                        var h = a.getAttribute('href') || '';
                        return h.indexOf('#logout') !== -1 ? a : null;
                    })();

                    if (t || aExact || aAny) {
                        console.debug('[logout] intercepted click', { foundClass: !!t, exact: !!aExact, any: !!aAny });
                        e.preventDefault();
                        var f = document.getElementById('logout-form');
                        console.debug('[logout] logout-form present:', !!f);
                        if (f) f.submit();
                    }
                });

                // Also proactively attach an inline onclick to any logout anchors so they submit POST directly
                try {
                    var anchors = document.querySelectorAll('a[href="#logout"], a[href*="#logout"]');
                    console.debug('[logout] anchors found:', anchors.length);
                    anchors.forEach(function (a) {
                        a.setAttribute('onclick', "event.preventDefault(); document.getElementById('logout-form').submit();");
                    });
                    var f = document.getElementById('logout-form');
                    console.debug('[logout] logout-form initial presence:', !!f);
                } catch (err) {
                    console.error('[logout] error attaching onclick to anchors', err);
                }
        });
    </script>

    {{-- Show generic success after registration; don't reveal token in the UI. --}}
    @if(session()->has('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    title: 'Registrasi berhasil',
                    text: '{{ session("success") }} Silakan masuk menggunakan akun Anda.',
                    icon: 'success',
                    confirmButtonText: 'Menuju Login'
                }).then(() => { window.location.href = '{{ route("login") }}'; });
            });
        </script>
    @endif

</body>
</html>
