@extends('layout')
@section('title',$post->title)
@section('content')
<a class="back-link" href="{{ route('news.index') }}">← Volver a noticias</a><article class="article-body"><header><p class="meta">{{ $post->category->name }} · <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->published_at->translatedFormat('d M Y') }}</time></p><h1 data-news-title>{{ $post->title }}</h1><p class="article-summary" data-news-summary>{{ $post->summary }}</p></header><div class="article-content" data-news-content>@foreach(preg_split('/\R\s*\R/', $post->content) as $paragraph)<p>{{ $paragraph }}</p>@endforeach</div><footer><p class="meta">Redacción Entrelineas · Contenido ficticio para fines académicos.</p></footer></article>
<section class="news-reader article-body" aria-labelledby="reader-title">
 <h2 id="reader-title">Escuchar esta noticia</h2>
 <p>Lectura opcional del título, el resumen y el contenido. Puedes leer toda la noticia sin activar el audio.</p>
 <div class="preference-options">
  <button type="button" id="read-start" disabled>Escuchar</button>
  <button type="button" id="read-pause" class="secondary" disabled>Pausar</button>
  <button type="button" id="read-resume" class="secondary" disabled>Continuar</button>
  <button type="button" id="read-stop" class="secondary" disabled>Detener</button>
 </div>
 <p id="reader-status" role="status">Activa JavaScript para utilizar la lectura por voz. El contenido sigue disponible en texto.</p>
</section>
@endsection
