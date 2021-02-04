@extends('layout.app', ['class' => 'register-page'])

@section('content')
    {{-- <div class="pt-8 bg-gradient-warning" style="height: 100vh !important;">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">

                        @if (Session::has('error'))
                            <div class="card-header">
                                <div class="alert alert-danger" role="alert">
                                    <strong>Error!</strong> {{Session::get('error')}}
                                </div>
                            </div>
                        @endif
        
                        <div class="card-body">
                            <form method="POST" action="{{route('login')}}">
                                @csrf

                                <div class="form-group row">
                                    <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail 2FA:</label>
        
                                    <div class="col-md-6">
                                        <div class="input-group mb-3">
                                            <input 
                                                type="number" 
                                                class="form-control" 
                                                aria-describedby="send-code"
                                                name="2fa_code_email"
                                            >

                                            <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="send-code">Send Code</button>
                                            </div>
                                        </div>
        
                                        @error('email')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="anti_fishing_secret" class="col-md-4 col-form-label text-md-right">Secret:</label>
        
                                    <div class="col-md-6">
                                        <input 
                                            type="password" 
                                            class="form-control @error('anti_fishing_secret') is-invalid @enderror" 
                                            name="anti_fishing_secret" 
                                            required
                                        >
        
                                        @error('anti_fishing_secret')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
        
                                <div class="form-group row">
                                    <label for="2fa_code" class="col-md-4 col-form-label text-md-right">6 Digit Code:</label>
        
                                    <div class="col-md-6">
                                        <input 
                                            type="number" 
                                            class="form-control @error('2fa_code') is-invalid @enderror" 
                                            name="2fa_code" 
                                            required
                                        >
        
                                        @error('2fa_code')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
        
                                <div class="form-group row mb-0">
                                    <div class="col-md-8 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            Login
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const emailButton = document.getElementById('send-code')

        window.addEventListener('load', () => 
        {
            emailButton.addEventListener('click', e => 
            {
                e.preventDefault()

                sendCodeByEmail()
            })
        })

        async function sendCodeByEmail()
        {
            emailButton.innerHTML = `
                <div class="spinner-border" role="status" style="width: 1rem; height: 1rem;">
                    <span class="sr-only">Loading...</span>
                </div>
            `

            emailButton.setAttribute('disabled', '')

            await fetch("/api/send-code-by-email", {
                method: 'POST',
                headers: new Headers({
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Accept-Language': 'es',
                }),
                body: JSON.stringify({
                    email: "mr.corvy@gmail.com",
                    isSecondary: false
                })
            })
            .then(res => res.json())
            .then(response => {
                console.log(response)

                emailButton.innerHTML = 'Send Code'

                emailButton.removeAttribute('disabled')
            })
        }
    </script> --}}
    <div class="wrapper">
        <div class="page-header bg-default">
            <div class="page-header-image" style="background-image: url('{{asset('argon')}}/img/register_bg.png');"></div>
                <div class="container" id="container">
                    <div class="form-container sign-in-container">
                        <form method="POST" action="{{route('login')}}" role="form">
                            @csrf
                            <h2>Sign in</h2>
                            <div class="social-container mb-5">
                                <button class="btn btn-primary" id="send-code">Send Code</button>
                            </div>

                            <div class="form-group mb-3">
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                                    </div>
                                    <input class="form-control" name="2fa_code_email" placeholder="Email 2FA Code" type="number">
                                </div>
                                @error('2fa_code_email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                                    </div>
                                    <input class="form-control" name="anti_fishing_secret" placeholder="Secret" type="password">
                                </div>
                            </div>

                            @error('anti_fishing_secret')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

                            <div class="form-group">
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-mobile-button"></i></span>
                                    </div>
                                    <input class="form-control" name="2fa_code" placeholder="2FA Code" type="number">
                                </div>

                                @error('2fa_code')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

                            </div>

                            <button class="btn btn-outline-success btn-block mt-3" type="submit">Sign In</button>
                        </form>
                    </div>
                    <div class="overlay-container">
                        <div class="overlay">
                            <div class="overlay-panel overlay-right">
                                <h1 class="text-white">Hi!</h1>
                                <p>
                                    Only the admin has access beyond this point.
                                </p>
                                <a href="https://pasusewa.com">
                                    <button type="button" class="btn btn-outline-info">Go back to PasuSewa</button>
                                </a>

                                @if (Session::has('error'))
                                    <div class="alert alert-danger mt-5" role="alert">
                                        <strong>Error!</strong> {{Session::get('error')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                {{-- aca hay un div que está cerrado mas arriba --}}
            </div>
        </div>
    </div>

    <script>
        const emailButton = document.getElementById('send-code')

        window.addEventListener('load', () => 
        {
            emailButton.addEventListener('click', e => 
            {
                e.preventDefault()

                sendCodeByEmail()
            })
        })

        async function sendCodeByEmail()
        {
            emailButton.innerHTML = `
                <div class="spinner-border" role="status" style="width: 1rem; height: 1rem;">
                    <span class="sr-only">Loading...</span>
                </div>
            `

            emailButton.setAttribute('disabled', '')

            await fetch("/api/send-code-by-email", {
                method: 'POST',
                headers: new Headers({
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Accept-Language': 'es',
                }),
                body: JSON.stringify({
                    email: "mr.corvy@gmail.com",
                    isSecondary: false
                })
            })
            .then(res => res.json())
            .then(response => {
                console.log(response)

                emailButton.innerHTML = 'Send Code'

                emailButton.removeAttribute('disabled')
            })
        }
    </script>
@endsection