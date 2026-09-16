@extends('layouts.guest')
@section('title','Iniciar sesión') @section('subtitle','Ingrese sus datos para acceder al sistema')
@section('content')<form method="POST" action="{{ route('login') }}" class="needs-validation">@csrf
<div class="login-form-group"><label class="login-form-label" for="email">Correo electrónico</label><div class="login-input-group"><i class="bi bi-envelope input-icon"></i><input class="login-input" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div></div>
<div class="login-form-group"><label class="login-form-label" for="password">Contraseña</label><div class="login-input-group"><i class="bi bi-shield-lock input-icon"></i><input class="login-input" id="password" name="password" type="password" autocomplete="current-password" required></div></div>
<div class="login-options"><label><input type="checkbox" name="remember" value="1"> Recordarme</label><a href="{{ route('password.request') }}" class="forgot-password-link">¿Olvidó su contraseña?</a></div><button class="btn-login" type="submit"><span>Ingresar</span><i class="bi bi-arrow-right"></i></button></form>@endsection
