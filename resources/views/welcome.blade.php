@include('layout.head')

<div class="pt-8 bg-gradient-warning" style="height: 100vh !important;">
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
    
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
        })
    }
</script>

@include('layout.foot')