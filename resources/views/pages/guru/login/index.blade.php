@extends('layouts.auth')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card card-primary">
              <div class="card-header"><h4>Guru Login</h4></div>

              <div class="card-body">
                <form method="POST" action="{{ route('guru.login.submit') }}">
                    @csrf

                    <!--baru label yang diubah dari email jadi email/NIP-->
                    <div class="form-group">
                            <label for="login">Email atau NIP</label>
                            <input id="login" type="text" class="form-control @error('login') is-invalid @enderror" 
                                name="login" value="{{ old('login') }}" required autofocus
                                placeholder="Masukkan email atau NIP Anda">
                                @error('login')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                    </div>
                    
                  <div class="form-group">
                    <div class="d-block">
                    	<label for="password" class="control-label">Password</label>
                        <div class="float-right">
                        @if (Route::has('password.request'))
                            <a class="text-small"href="{{ route('password.request') }}">
                                {{ __('Lupa Password?') }}
                            </a>
                        @endif
                      </div>
                    </div>

                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" tabindex="2" required autocomplete="current-password">
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                  </div>

                  <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="remember" class="custom-control-input" 
                                    id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="remember">Ingat Saya</label>
                            </div>
                        </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                      Login
                    </button>
                  </div>
                </form>
                </div>
              </div>
            </div>
        </div>
    </div>
@endsection
