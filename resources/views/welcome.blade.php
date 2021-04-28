@extends('layout.app', ['class' => 'register-page'])

@section('content')
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
ƒ
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-email-83"></i></span>
                                    </div>
                                    <input class="form-control" name="2fa_code_email" placeholder="Email 2FA Code" type="number" value="{{old('2fa_code_email')}}">
                                </div>
                                @error('2fa_code_email')
                                    <small class="text-danger">
                                        {{$message}}
                                    </small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-lock-circle-open"></i></span>
                                    </div>
                                    <input class="form-control" name="anti_fishing_secret" placeholder="Secret" type="password" value="{{old('anti_fishing_secret')}}">
                                </div>
                            </div>

                            @error('anti_fishing_secret')
                                <small class="text-danger">
                                    {{$message}}
                                </small>
                            @enderror

                            <div class="form-group">
                                <div class="input-group input-group-alternative">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="ni ni-mobile-button"></i></span>
                                    </div>
                                    <input class="form-control" name="2fa_code" placeholder="2FA Code" type="number" value="{{old('2fa_code')}}">
                                </div>

                                @error('2fa_code')
                                    <small class="text-danger">
                                        {{$message}}
                                    </small>
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
                                <a href="https://pasunashi.com">
                                    <button type="button" class="btn btn-outline-info">Go back to PasuNashi</button>
                                </a>

                                @if (Session::has('error'))
                                    <div class="alert alert-danger mt-5" role="alert">
                                        <strong>Error!</strong> {{Session::get('error')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                {{-- here's a div that has been closed before --}}
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