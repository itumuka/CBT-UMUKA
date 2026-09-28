@extends('layouts.admin')

@section('title', 'Dashboard')



@section('content_header')
    <h1>Dashboard</h1>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

@stop

@section('content')

<div class="container">
    <!-- Card Utama -->
    <div class="card shadow-lg p-4">
        <div class="card-body">
            <p class="alert alert-primary">Selamat datang di sistem ujian online <strong> {{ session('level') }} </strong> <strong> {{ session('nama') }} </strong> </p>

            
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End of Card Utama -->
</div> <!-- End of Container -->

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
