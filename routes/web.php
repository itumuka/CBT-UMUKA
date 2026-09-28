<?php

use App\Http\Controllers\Auth\RegisterController as AuthRegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SoalController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\SiswaController;   
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HasilUjianController;
use App\Http\Controllers\IkutUjianController;
use App\Http\Controllers\MapelTryoutController;
use App\Http\Controllers\RegisterTryoutController;
use App\Http\Controllers\SiswaTryoutController;
use App\Http\Controllers\UjianController;
use App\Models\Siswa;

// Menampilkan halaman login
Route::get('/', function(){
    return redirect()->route('login');
});
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::get('/register-tryout', [RegisterTryoutController::class, 'showRegisterForm'])->name('register');

// Proses login
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/create-register-tryout', [RegisterTryoutController::class, 'register'])->name('register.post');

// Dashboard hanya bisa diakses setelah login
Route::middleware(['authadmin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/data-camaba', [SiswaController::class, 'index'])->name('siswa.index');
    Route::get('/siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
    Route::delete('/siswa/bulk-delete', [SiswaController::class, 'bulkDestroy'])->name('siswa.bulkDestroy');
    Route::post('/siswa/set-mapel-cbt', [SiswaController::class, 'setMapelCbt'])->name('siswa.setMapelCbt');
    Route::resource('Guru', GuruController::class);
    
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::post('/siswa/{id}/update', [SiswaController::class, 'update'])->name('siswa.update');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
    Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');


    Route::get('/data-mapel', [MapelController::class, 'index'])->name('mapel.index');
    Route::get('/data-mapel-create', [MapelController::class, 'create'])->name('mapel.create');
    Route::post('/data-mapel-store', [MapelController::class, 'store'])->name('mapel.store');
    Route::get('/mapel/{id}/edit', [MapelController::class, 'edit'])->name('mapel.edit');
    Route::delete('/mapel/{id}', [MapelController::class, 'destroy'])->name('mapel.destroy');
    Route::put('/mapel/{id}', [MapelController::class, 'update'])->name('mapel.update');

    Route::get('/data-soal', [SoalController::class, 'index'])->name('soal.index');       // Menampilkan daftar soal
    Route::get('/soal/create', [SoalController::class, 'create'])->name('soal.create'); // Form tambah soal
    Route::post('/tambah-soal', [SoalController::class, 'store'])->name('soal.store');       // Simpan soal baru
    Route::get('/soal/{id}/edit', [SoalController::class, 'edit'])->name('soal.edit'); // Form edit soal
    Route::put('/soal/{id}', [SoalController::class, 'update'])->name('soal.update');  // Update soal
    Route::delete('/soal/{id}', [SoalController::class, 'destroy'])->name('soal.destroy'); // Hapus soal
    // Import soal (CSV/TSV) form and processing
    Route::get('/soal/import', [SoalController::class, 'importForm'])->name('soal.importForm');
    Route::post('/soal/import', [SoalController::class, 'import'])->name('soal.import');
    // Laporan soal bermasalah (gambar hilang / tanpa lampiran)
    Route::get('/soal/bermasalah', [SoalController::class, 'bermasalah'])->name('soal.bermasalah');
    // Admin utility: merge soal sumber tryout/import into master
    Route::post('/soal/merge-sources', [SoalController::class, 'mergeSources'])->name('soal.mergeSources');
    // Bulk actions for soal
    Route::post('/soal/bulk-delete', [SoalController::class, 'bulkDestroy'])->name('soal.bulkDestroy');
    Route::post('/soal/bulk-move', [SoalController::class, 'bulkMove'])->name('soal.bulkMove');
    Route::post('/soal/bulk-duplicate', [SoalController::class, 'bulkDuplicate'])->name('soal.bulkDuplicate');

    Route::get('/data-ujian', [UjianController::class, 'index'])->name('ujian.index');
    // Export rankings (CSV)
    Route::get('/hasil-ujian/export', [App\Http\Controllers\HasilUjianController::class, 'export'])->name('hasil_ujian.export');
    Route::get('/hasil-ujian/student/{id}/breakdown', [App\Http\Controllers\HasilUjianController::class, 'studentMapelBreakdown'])->name('hasil_ujian.studentBreakdown');
    Route::get('/data-ujian/active', [UjianController::class, 'activeUjian'])->name('ujian.active');
    Route::get('/hasil-ujian/mapel/{id}', [App\Http\Controllers\HasilUjianController::class, 'mapelRanking'])->name('hasil_ujian.mapelRanking');
    // Reset siswa password (admin) - generate a new random password and return it once
    Route::post('/siswa/{id}/reset-password', [\App\Http\Controllers\SiswaController::class, 'resetPassword'])->name('siswa.resetPassword');
    Route::get('/hasil-ujian/mapel/{id}/export', [App\Http\Controllers\HasilUjianController::class, 'exportMapel'])->name('hasil_ujian.exportMapel');
    Route::get('/data-ujian/create', [UjianController::class, 'create'])->name('ujian.create');
    Route::post('/data-ujian', [UjianController::class, 'store'])->name('ujian.store');
    Route::post('/data-ujian/bulk-delete', [UjianController::class, 'bulkDestroy'])->name('ujian.bulkDestroy');
    // root impersonation (admin only)
    Route::get('/admin/root-login', [App\Http\Controllers\AdminController::class, 'showRootLogin'])->name('admin.rootLogin');
    Route::post('/admin/root-login', [App\Http\Controllers\AdminController::class, 'doRootLogin'])->name('admin.root.login');
    Route::post('/admin/revert-impersonation', [App\Http\Controllers\AdminController::class, 'revertImpersonation'])->name('admin.revertImpersonation');
    Route::get('/edit-data-ujian/{id}', [UjianController::class, 'edit'])->name('ujian.edit');
    Route::put('/update-data-ujian/{id}', [UjianController::class, 'update'])->name('ujian.update');
    Route::put('/data-ujian/generateToken/{id}', [UjianController::class, 'generateToken'])->name('ujian.generateToken');

    Route::get('/hasil-ujian', [HasilUjianController::class, 'index'])->name('hasil-ujian.index');
    Route::get('/hasil-ujian', [HasilUjianController::class, 'index'])->name('hasil-ujian.index');
    Route::get('/hasil-ujian/{id}', [HasilUjianController::class, 'detail'])->name('hasil-ujian.detail');
    Route::get('/hasil-ujian/data/{id}', [HasilUjianController::class, 'dataDetail'])->name('hasil-ujian.data');
    Route::get('/hasil-ujian/batalkan/{id}/{tes_id}', [HasilUjianController::class, 'batalkan'])->name('hasil-ujian.batalkan');

    Route::get('/mapel-tryout', [MapelTryoutController::class, 'index'])->name('mapel_tryout.index');
    Route::get('/mapel-tryout-create', [MapelTryoutController::class, 'create'])->name('mapel_tryout.create');
    Route::post('/mapel-tryout-store', [MapelTryoutController::class, 'store'])->name('mapel_tryout.store');
    Route::get('/mapel-tryout/{id}/edit', [MapelTryoutController::class, 'edit'])->name('mapel_tryout.edit');
    Route::delete('/mapel-tryout/{id}', [MapelTryoutController::class, 'destroy'])->name('mapel_tryout.destroy');
    Route::put('/mapel-tryout/{id}', [MapelTryoutController::class, 'update'])->name('mapel_tryout.update');

    // CBT Sessions management
    Route::get('/cbt-sessions', [App\Http\Controllers\CbtSessionController::class, 'index'])->name('cbt_sessions.index');
    Route::get('/cbt-sessions/create', [App\Http\Controllers\CbtSessionController::class, 'create'])->name('cbt_sessions.create');
    Route::post('/cbt-sessions', [App\Http\Controllers\CbtSessionController::class, 'store'])->name('cbt_sessions.store');
    Route::delete('/cbt-sessions/{id}', [App\Http\Controllers\CbtSessionController::class, 'destroy'])->name('cbt_sessions.destroy');
    Route::post('/cbt-sessions/{id}/toggle', [App\Http\Controllers\CbtSessionController::class, 'toggleActive'])->name('cbt_sessions.toggle');
    Route::post('/cbt-sessions/{id}/toggle-token', [App\Http\Controllers\CbtSessionController::class, 'toggleToken'])->name('cbt_sessions.toggle_token');

    Route::get('/data-siswa-tryout', [SiswaTryoutController::class, 'index'])->name('siswa_tryout.index');
    Route::get('/data-siswa-tryout/{id}/edit', [SiswaTryoutController::class, 'edit'])->name('siswa_tryout.edit');
    Route::put('/data-siswa-tryout/{id}', [SiswaTryoutController::class, 'update'])->name('siswa_tryout.update');
    Route::delete('/data-siswa-tryout/{id}', [SiswaTryoutController::class, 'destroy'])->name('siswa_tryout.destroy');

    Route::prefix('hasil_ujian')->group(function () {
        Route::get('/', [HasilUjianController::class, 'index'])->name('hasil_ujian.index'); // Menampilkan semua hasil ujian
        Route::get('/create', [HasilUjianController::class, 'create'])->name('hasil_ujian.create'); // Form tambah hasil ujian
        Route::post('/store', [HasilUjianController::class, 'store'])->name('hasil_ujian.store'); // Simpan hasil ujian baru
        Route::get('/{id}', [HasilUjianController::class, 'show'])->name('hasil_ujian.show'); // Menampilkan detail hasil ujian
        Route::get('/edit/{id}', [HasilUjianController::class, 'edit'])->name('hasil_ujian.edit'); // Form edit hasil ujian
        Route::put('/update/{id}', [HasilUjianController::class, 'update'])->name('hasil_ujian.update'); // Update hasil ujian
        Route::delete('/delete/{id}', [HasilUjianController::class, 'destroy'])->name('hasil_ujian.destroy'); // Hapus hasil ujian
    });

    // Halaman terpisah CBT vs Tryout: URL dan nama route beda
    // (cbt.* / tryout.*) tapi controller sama — query di-scope per mode.
    // Route lama di atas tetap jalan sebagai alias (default mode cbt).
    foreach (['cbt', 'tryout'] as $seg) {
        Route::prefix($seg)->name($seg . '.')->group(function () {
            Route::get('/data-camaba', [SiswaController::class, 'index'])->name('siswa.index');
            Route::get('/siswa/export', [SiswaController::class, 'export'])->name('siswa.export');
            Route::delete('/siswa/bulk-delete', [SiswaController::class, 'bulkDestroy'])->name('siswa.bulkDestroy');
            Route::post('/siswa/set-mapel-cbt', [SiswaController::class, 'setMapelCbt'])->name('siswa.setMapelCbt');
            Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
            Route::post('/siswa/{id}/update', [SiswaController::class, 'update'])->name('siswa.update');
            Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
            Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
            Route::post('/siswa', [SiswaController::class, 'store'])->name('siswa.store');
            Route::post('/siswa/{id}/reset-password', [SiswaController::class, 'resetPassword'])->name('siswa.resetPassword');

            Route::get('/data-ujian', [UjianController::class, 'index'])->name('ujian.index');
            Route::get('/data-ujian/create', [UjianController::class, 'create'])->name('ujian.create');
            Route::post('/data-ujian', [UjianController::class, 'store'])->name('ujian.store');
            Route::post('/data-ujian/bulk-delete', [UjianController::class, 'bulkDestroy'])->name('ujian.bulkDestroy');
            Route::post('/demo/reset', [UjianController::class, 'resetDemo'])->name('demo.reset');
            Route::get('/edit-data-ujian/{id}', [UjianController::class, 'edit'])->name('ujian.edit');
            Route::put('/update-data-ujian/{id}', [UjianController::class, 'update'])->name('ujian.update');
            Route::put('/data-ujian/generateToken/{id}', [UjianController::class, 'generateToken'])->name('ujian.generateToken');

            Route::get('/hasil-ujian', [HasilUjianController::class, 'index'])->name('hasil-ujian.index');
            Route::get('/hasil-ujian/export', [HasilUjianController::class, 'export'])->name('hasil_ujian.export');
            Route::get('/hasil-ujian/{id}', [HasilUjianController::class, 'show'])->name('hasil_ujian.show');
        });
    }




});

Route::middleware(['authuser'])->group(function () {
    // Halaman profil pengguna (siswa)
    Route::get('/profile', [SiswaController::class, 'profile'])->name('siswa.profile');
    Route::post('/profile/reveal-token', [SiswaController::class, 'revealToken'])->name('siswa.revealToken');
    Route::get('/ujian',[UjianController::class, 'daftar_ujian'])->name('daftar_ujian');
    Route::get('/ikut_ujian',[IkutUjianController::class, 'ikuti_ujian'])->name('ikuti-ujian');
    Route::get('/konfirmasi-ujian', [UjianController::class, 'konfirmasi_ujian'])->name('konfirmasi_ujian');
    Route::get('/mulai-ujian', [UjianController::class, 'mulai_ujian'])->name('mulai_ujian');
    Route::post('/cek-token', [UjianController::class, 'cekToken'])->name('cek.token');
    
    Route::post('/ujian/submit', [UjianController::class, 'submitJawaban'])->name('submit_jawaban');
    Route::post('/ujian/report-cheat', [UjianController::class, 'reportCheat'])->name('ujian.reportCheat');
    Route::post('/ujian/register-tab', [UjianController::class, 'registerTab'])->name('ujian.registerTab');
    Route::post('/ujian/ping-tab', [UjianController::class, 'pingTab'])->name('ujian.pingTab');
    Route::post('/ujian/release-tab', [UjianController::class, 'releaseTab'])->name('ujian.releaseTab');
    // Server time endpoint for client sync
    Route::get('/server-time', [UjianController::class, 'serverTime'])->name('server.time');
    // Active-ujian endpoint reachable by students (for timer resync)
    Route::get('/server/active-ujian', [UjianController::class, 'activeUjian'])->name('server.ujianActive');
}); 

Route::middleware(['authusertryout'])->group(function () {
    Route::get('/dashboard-user-tryout', [DashboardController::class, 'dashboard_user_tryout'])->name('dashboard_user_tryout');  
}); 

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
