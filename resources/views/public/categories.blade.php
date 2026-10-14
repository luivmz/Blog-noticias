@extends('layout')
@section('title','Categorías')
@section('content')
<p class="eyebrow">Cuatro perspectivas</p><h1>Categorías</h1><p class="intro">Elige el tema que te interesa.</p><div class="category-list">@foreach($categories as $category)<section><h2><a href="{{ route('news.index',['category'=>$category->id]) }}">{{ $category->name }} <span aria-hidden="true">→</span></a></h2><p>{{ $category->posts_count }} noticias publicadas</p></section>@endforeach</div>
@endsection
