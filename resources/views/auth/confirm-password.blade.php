@extends('layouts.guest')
@section('title','Confirmar contraseña') @section('subtitle','Confirme su contraseña antes de continuar')
@section('content')<form method="POST" action="{{ route('password.confirm') }}">@csrf<div class="login-form-group"><label class="login-form-label" for="password">Contraseña</label><input class="login-input" id="password" name="password" type="password" autocomplete="current-password" autofocus required></div><button class="btn-login" type="submit">Confirmar</button></form>@endsection
