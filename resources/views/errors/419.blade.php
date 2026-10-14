@extends('layout')
@section('title','El formulario necesita actualizarse')
@section('content')
<p class="eyebrow">Respuesta 419</p><h1>El formulario necesita actualizarse</h1><p class="intro">La sesión del formulario venció. Vuelve a abrir la página y completa el formulario nuevamente.</p><div class="actions"><a class="button" href="{{ route('news.index') }}">Volver a noticias</a><a class="button secondary" href="{{ route('login') }}">Ir al login</a></div>
@endsection
