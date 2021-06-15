@extends('layout.app', ['class' => 'error-page'])

@section('content')
    <div class="wrapper">
        <div class="page-header page-500">
            <div
                class="page-header-image"
                style="background-image: url('{{asset('argon')}}/img/500.svg');"
            ></div>
            <div class="container low">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <h4 class="display-4">Error :(</h4>
                        <p class="lead">We are sorry. This was unexpected.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection