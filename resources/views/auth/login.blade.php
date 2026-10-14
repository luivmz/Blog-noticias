@extends('layout')
@section('title','Iniciar sesión')
@section('content')
<section class="form-panel" aria-labelledby="login-title"><p class="eyebrow">Acceso a la redacción</p><h1 id="login-title">Iniciar sesión</h1><p>Ingresa para crear y administrar las noticias.</p><form action="{{ route('login.store') }}" method="post">@csrf
<x-field name="email" label="Correo electrónico"><input id="email" name="email" type="email" autocomplete="username" required value="{{ old('email') }}" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror></x-field>
<x-field name="password" label="Contraseña"><input id="password" name="password" type="password" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="password-error" @enderror></x-field>
<button type="submit">Iniciar sesión</button></form><p class="help">Acceso exclusivo para la administración. No hay registro público.</p></section>
@endsection
