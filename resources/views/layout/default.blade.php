@extends('layout.index')

@section('title', 'Default Layout')

@section('content')
    <div class="container">
        @include('portal.extra.header')
        @include('portal.extra.nav')
        
    </div>
@endsection
