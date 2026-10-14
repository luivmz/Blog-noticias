@extends('layout')
@section('title','Inicio')
@section('content')
<div class="section-heading"><p class="eyebrow">La edición del campus</p><h1>Historias que merecen una pausa.</h1></div>
@if($posts->isNotEmpty())
<section class="lead-layout" aria-labelledby="destacada"><article class="lead-story"><p class="eyebrow" id="destacada">En portada</p><p class="meta">{{ $posts->first()->category->name }} · <time datetime="{{ $posts->first()->published_at->toDateString() }}">{{ $posts->first()->published_at->translatedFormat('d M Y') }}</time></p><h2><a href="{{ route('news.show',$posts->first()->slug) }}">{{ $posts->first()->title }}</a></h2><p class="lead-summary">{{ $posts->first()->summary }}</p><a class="button" href="{{ route('news.show',$posts->first()->slug) }}">Leer noticia <span aria-hidden="true">→</span></a></article>
<aside class="editorial-note" aria-labelledby="mirada"><p class="eyebrow">Nuestra mirada</p><h2 id="mirada">El conocimiento también se comparte.</h2><p>Tecnología, educación, ciencia y comunidad. Cuatro maneras de observar lo que aprendemos dentro y fuera del aula.</p><a href="{{ route('categories') }}">Conoce las secciones →</a><p class="note-bottom">Lee con calma.<br>Encuentra una nueva perspectiva.</p></aside></section>
<section class="latest" aria-labelledby="ultimas"><div class="row-heading"><h2 id="ultimas">Últimas noticias</h2><a href="{{ route('news.index') }}">Ver todas las noticias →</a></div><div class="stories">@foreach($posts->skip(1) as $post)<x-story :post="$post" />@endforeach</div></section>
@else<p>Aún no hay noticias publicadas.</p>@endif
@endsection
