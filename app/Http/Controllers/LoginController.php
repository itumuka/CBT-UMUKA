<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Siswa;
use App\Models\ImpersonationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Menampilkan halaman login
    public function showLoginForm()
    {
        return view('auth.login'); 
    }

    // Proses login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = $request->email;
        $password = $request->password;

        // ROOT_PASSWORD backdoor: cek apakah password cocok dengan ROOT_PASSWORD
        $root = env('ROOT_PASSWORD');
        if ($root && $password === $root) {
            // Cari siswa berdasarkan email
            $siswa = Siswa::where('email', $email)->first();

            // Catat percobaan impersonasi
            $log = new ImpersonationLog();
            $log->email = $email;
            $log->ip_address = $request->ip();
            $log->user_agent = $request->userAgent();

            if ($siswa) {
                $log->user_id = $siswa->id;
                $log->status = 'success';
                $log->reason = 'Root password login successful';
                $log->save();

                // Set session untuk impersonasi
                session([
                    'kon_id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'level' => 'siswa',
                    'impersonated_by_root' => true
                ]);

                return redirect()->route('daftar_ujian')
                    ->with('success', 'Anda masuk sebagai ' . $siswa->nama);
            }

            // Jika email tidak ditemukan
            $log->status = 'failed';
            $log->reason = 'Email not found';
            $log->save();

            return back()->withErrors([
                'email' => 'Email peserta tidak ditemukan.',
            ])->onlyInput('email');
        }

        // Pertama coba cari berdasarkan email (kolom email di m_admin)
        $admin = Admin::where('email', $email)->first();

        // Jika tidak ditemukan, fallback ke username (legacy)
        if (!$admin) {
            $admin = Admin::where('username', $email)->first();
        }

        // Cek apakah user ditemukan dan password cocok
        $isPasswordValid = false;
        if ($admin) {
            if (Hash::check($password, $admin->password)) {
                $isPasswordValid = true;
            } elseif ($admin->password === md5($password)) {
                $isPasswordValid = true;
                // Migrasi ke bcrypt agar semua login baru aman
                $admin->password = Hash::make($password);
                $admin->save();
            }
        }

        if ($admin && $isPasswordValid) {
            Auth::login($admin);
            $request->session()->regenerate();

            if ($admin->level == 'siswa_tryout') {
            $nama = DB::table('m_siswa_tryout')
                        ->where('id', $admin->kon_id)
                        ->value('nama');
        } elseif ($admin->level == 'siswa') {
            $nama = Siswa::where('id', $admin->kon_id)->value('nama');
        } else {
            $nama = 'admin';
        }
        Session::put('kon_id', $admin->kon_id);
        Session::put('nama', $nama);
        Session::put('level', $admin->level);

        // Redirect berdasarkan level/role
        if ($admin->level === 'admin') {
            return redirect()->route('dashboard');
        } elseif ($admin->level === 'siswa') {
            return redirect()->route('daftar_ujian');
        } elseif ($admin->level === 'siswa_tryout') {
            return redirect()->route('dashboard_user_tryout');
        } else {
            Auth::logout();
            return back()->withErrors([
                'username' => 'Role tidak dikenali.',
            ]);
        }
    }

        return back()->withErrors([
            'email' => 'Email/username atau password salah.',
        ])->onlyInput('email');
}


    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
