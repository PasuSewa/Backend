@extends('layout.app', ['class' => 'register-page'])

@section('content')
    {{dd(Auth::user())}}
@endsection