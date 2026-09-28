@extends('layouts.admin')

@section('title', 'Dashboard')



@section('content_header')
    <h1>Dashboard</h1>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

@stop

@section('content')

<div class="container py-4">
    <!-- Card Utama -->
    <div class="card shadow-lg p-0 overflow-hidden" style="border:0;border-radius:18px;">
        <div class="px-4 py-4 text-white" style="background:linear-gradient(120deg,#254baa 0%,#06b6d4 100%);">
            <h4 class="mb-1"><i class="fas fa-graduation-cap me-2"></i>Dashboard Tryout</h4>
            <div class="opacity-75 small">Selamat datang di sistem ujian online</div>
        </div>
        <div class="card-body p-4">
            <p class="alert alert-primary border-0">Halo, <strong>{{ session('nama') }}</strong>! Pilih menu <strong>Ujian</strong> untuk melihat daftar tryout yang tersedia.</p>
        </div>
    </div>
</div>

@stop

@section('css')
    <style>
        .shadow-lg {
            border-radius: 10px;
            background-color: #fff;
        }
        .shadow-sm {
            border-radius: 8px;
        }
    </style>
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop
