<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\CbtSession;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterTryoutController extends Controller
{
    public function showRegisterForm()
    {
        // Ambil mapel dari master `m_mapel` sesuai permintaan
        $wajib = Mapel::where('kategori', 'wajib')->get();
        $pilihan = Mapel::where('kategori', 'pilihan')->get();

        // Ambil sesi aktif (cbt atau tryout)
        $activeSession = CbtSession::where('active', true)->first();

        // Allow disabling registration via environment variable REGISTRATION_OPEN (true/false)
        $registrationOpen = env('REGISTRATION_OPEN', true);

        return view('auth.register', compact('wajib','pilihan','activeSession','registrationOpen'));
    }

    public function register(Request $request)
    {
        // Prevent registration when disabled via env config
        if (! env('REGISTRATION_OPEN', true)) {
            return redirect()->back()->withErrors(['registration' => 'Pendaftaran saat ini ditutup oleh administrator.']);
        }
        // Validasi Input
        $request->validate([
            'nama' => 'required|string|max:255|unique:m_admin,username',
            'sekolah' => 'required|max:200',
            // nomor HP harus dimulai dengan 08 dan total 10-13 digit (mengakomodasi pascabayar/prabayar)
            'no_hp' => [
                'required', 
                'regex:/^08[0-9]{8,11}$/',
                'unique:m_siswa,no_hp'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:m_siswa,email',
                'unique:m_admin,email'
            ],
            'mapel_wajib' => 'required|array',
            'mapel_pilihan' => 'required|array|size:2',
            'status_akun' => 'required|in:real,test',
            'password' => 'required|string|min:6'
        ], [
            'no_hp.regex' => 'Format nomor HP harus dimulai dengan 08 dan panjang antara 10 hingga 13 digit. (Pascabayar: 10-12 digit; Prabayar: 11-13 digit)',
            'no_hp.unique' => 'Nomor HP ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.'
        ]);

        // Gabungkan array id wajib menjadi string dipisah koma
        $mapelWajibString = implode(',', $request->mapel_wajib);
        $mapelPilihanString = implode(',', $request->mapel_pilihan);

        // Ambil session aktif (jika ada)
        $activeSession = CbtSession::where('active', true)->first();
        $sessionKey = $activeSession ? $activeSession->key : 'cbt';

        // Simpan Data ke Database
        // Simpan ke tabel m_siswa (camaba) sebagai canonical registrant
        // Generate token 5 karakter alfanumerik unik
        $token = null;
        $tries = 0;
        do {
            $tokenCandidate = strtoupper(Str::random(5)); // huruf besar untuk konsistensi
            $exists = DB::table('m_siswa')->where('token', $tokenCandidate)->exists();
            $tries++;
        } while ($exists && $tries < 10);

        if (empty($tokenCandidate)) {
            // fallback random
            $tokenCandidate = strtoupper(bin2hex(random_bytes(3)));
        }

        $idSiswa = DB::table('m_siswa')->insertGetId([
            'nama' => $request->nama,
            'sekolah' => strtoupper($request->sekolah), // convert to uppercase
            'no_hp' => $request->no_hp,
            'email' => strtolower($request->email), // convert to lowercase
            'mapel_wajib' => $mapelWajibString,
            'mapel_pilihan' => $mapelPilihanString,
            'session_key' => $sessionKey,
            'token' => $tokenCandidate,
            'status_akun' => $request->status_akun,
        ]);

        // Buat akun di tabel m_admin yang menunjuk ke m_siswa (level 'siswa')
        Admin::create([
            'username' => $request->nama,
            'password' => Hash::make($request->password),
            'email' => strtolower($request->email), // ensure email consistency
            'level' => 'siswa',
            'kon_id' => $idSiswa
        ]);

        // Redirect ke halaman utama dengan pesan sukses + token
        return redirect('/')->with(['success' => 'Berhasil registrasi!', 'user_token' => $tokenCandidate]);
    }
}