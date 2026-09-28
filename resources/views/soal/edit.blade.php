@extends('layouts.admin')

@section('title', 'Input Soal')

@section('content')
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="card-title">Edit Soal</h3>
    </div>
    <div class="card-body">
        @include('soal._form')
    </div>
</div>
@endsection
