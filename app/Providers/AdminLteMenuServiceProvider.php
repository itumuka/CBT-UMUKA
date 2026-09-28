<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AdminLteMenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        View::composer('*', function ($view) {
            $level = session('level');

            $menu = [
                ['header' => 'DAFTAR MENU'],
            ];

            if ($level === 'admin') {
                $menu[] = [
                    'text' => 'Dashboard',
                    'url' => 'dashboard',
                    'icon' => 'fas fa-home nav-icon',
                ];
                $menu[] = ['header' => 'MODE CBT'];
                $menu[] = [
                    'text' => 'Peserta CBT',
                    'url' => 'cbt/data-camaba',
                    'icon' => 'fas fa-fw fa-user nav-icon',
                ];
                $menu[] = [
                    'text' => 'Ujian CBT',
                    'url' => 'cbt/data-ujian',
                    'icon' => 'fas fa-file-alt nav-icon',
                ];
                $menu[] = [
                    'text' => 'Hasil CBT',
                    'url' => 'cbt/hasil-ujian',
                    'icon' => 'fas fa-chart-line nav-icon',
                ];
                $menu[] = ['header' => 'MODE TRYOUT'];
                $menu[] = [
                    'text' => 'Peserta Tryout',
                    'url' => 'tryout/data-camaba',
                    'icon' => 'fas fa-fw fa-users nav-icon',
                ];
                $menu[] = [
                    'text' => 'Ujian Tryout',
                    'url' => 'tryout/data-ujian',
                    'icon' => 'fas fa-file-alt nav-icon',
                ];
                $menu[] = [
                    'text' => 'Hasil Tryout',
                    'url' => 'tryout/hasil-ujian',
                    'icon' => 'fas fa-chart-line nav-icon',
                ];
                $menu[] = ['header' => 'DATA & PENGATURAN'];
                $menu[] = [
                    'text' => 'Data camaba (Semua)',
                    'url' => 'data-camaba',
                    'icon' => 'fas fa-fw fa-user nav-icon',
                ];
                $menu[] = [
                    'text' => 'Data Soal',
                    'icon' => 'fas fa-question-circle nav-icon',
                    'submenu' => [
                        [
                            'text' => 'Daftar Soal',
                            'url' => 'data-soal',
                            'icon' => 'fas fa-list',
                        ],
                        [
                            'text' => 'Import Soal',
                            'url' => 'soal/import',
                            'icon' => 'fas fa-file-import',
                        ],
                        [
                            'text' => 'Soal Bermasalah',
                            'url' => 'soal/bermasalah',
                            'icon' => 'fas fa-exclamation-triangle',
                            'label' => (function () {
                                try {
                                    $n = \App\Http\Controllers\SoalController::countBermasalah();
                                } catch (\Throwable $e) {
                                    $n = 0;
                                }

                                return $n > 0 ? $n : null;
                            })(),
                            'label_color' => 'danger',
                        ],
                    ],
                ];
                $menu[] = [
                    'text' => 'Data Mapel',
                    'url' => 'data-mapel',
                    'icon' => 'fas fa-graduation-cap nav-icon',
                ];                
                $menu[] = [
                    'text' => 'Ujian',
                    'url' => 'data-ujian',
                    'icon' => 'fas fa-file-alt nav-icon',
                ];
                $menu[] = [
                    'text' => 'Hasil Ujian (Semua)',
                    'url' => 'hasil-ujian',
                    'icon' => 'fas fa-chart-line nav-icon',
                ]; 
                $menu[] = [
                    'text' => 'CBT Sessions',
                    'url' => 'cbt-sessions',
                    'icon' => 'fas fa-calendar-alt nav-icon',
                ];
            } elseif ($level === 'siswa_tryout') {
                $menu[] = [
                    'text' => 'Dashboard',
                    'url' => 'dashboard-user-tryout',
                    'icon' => 'fas fa-home nav-icon',
                ];
            } else {
                // For regular siswa users we don't show the dashboard link — show Ujian and Profil saja
                $menu[] = [
                    'text' => 'Ujian',
                    'url' => 'ujian',
                    'icon' => 'fas fa-book nav-icon',
                ];
                // Tambahkan link Profil untuk siswa
                $menu[] = [
                    'text' => 'Profil Saya',
                    'url' => 'profile',
                    'icon' => 'fas fa-user-circle nav-icon',
                ];
            }

            // Set ke konfigurasi AdminLTE
            // Note: Logout menu removed here to avoid duplication with header/user menu.
            config(['adminlte.menu' => $menu]);
        });
    }
}
