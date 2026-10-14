@extends('layout')
@section('title','Página no encontrada')
@section('content')
<p class="eyebrow">Respuesta 404</p><h1>Página no encontrada</h1><p class="intro">La noticia o página que buscas no está disponible. Puedes volver al listado para encontrar otra historia.</p><div class="actions"><a class="button" href="{{ route('news.index') }}">Volver a noticias</a><a class="button secondary" href="{{ route('login') }}">Ir al login</a></div>
@endsection
