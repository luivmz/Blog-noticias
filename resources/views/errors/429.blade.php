@extends('layout')
@section('title','Espera un momento antes de intentar otra vez')
@section('content')
<p class="eyebrow">Respuesta 429</p><h1>Espera un momento antes de intentar otra vez</h1><p class="intro">Se recibieron varios intentos seguidos. Espera un minuto y vuelve a iniciar sesión.</p><div class="actions"><a class="button" href="{{ route('news.index') }}">Volver a noticias</a><a class="button secondary" href="{{ route('login') }}">Ir al login</a></div>
@endsection
