@extends('layout')
@section('title',$post->exists ? 'Editar noticia' : 'Crear noticia')
@section('content')
<a class="back-link" href="{{ route('admin.noticias.index') }}">← Volver a administrar noticias</a><section class="editor-form" aria-labelledby="form-title"><p class="eyebrow">Mesa de edición</p><h1 id="form-title">{{ $post->exists ? 'Editar noticia' : 'Crear noticia' }}</h1><p>Todos los campos son obligatorios. El enlace de la noticia se genera a partir del título.</p><form method="post" action="{{ $post->exists ? route('admin.noticias.update',$post) : route('admin.noticias.store') }}">@csrf @if($post->exists) @method('PUT') @endif
@foreach(['title'=>['Título','Entre 5 y 150 caracteres.'],'summary'=>['Resumen','Entre 10 y 300 caracteres.'],'content'=>['Contenido','Al menos 30 caracteres. Separa los párrafos con una línea en blanco.']] as $name=>[$label,$help])
<x-field :name="$name" :label="$label" :help="$help">
@if($name==='title')<input id="{{ $name }}" name="{{ $name }}" required maxlength="150" value="{{ old($name,$post->$name) }}" aria-describedby="{{ $name }}-help{{ $errors->has($name) ? ' '.$name.'-error' : '' }}" @if($errors->has($name)) aria-invalid="true" @endif>
@else<textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $name==='content' ? 10 : 3 }}" required @if($name==='summary') maxlength="300" @endif aria-describedby="{{ $name }}-help{{ $errors->has($name) ? ' '.$name.'-error' : '' }}" @if($errors->has($name)) aria-invalid="true" @endif>{{ old($name,$post->$name) }}</textarea>@endif
</x-field>@endforeach
<div class="form-columns"><x-field name="category_id" label="Categoría"><select id="category_id" name="category_id" required @error('category_id') aria-invalid="true" aria-describedby="category_id-error" @enderror><option value="">Selecciona una categoría</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$post->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></x-field>
<x-field name="status" label="Estado" help="El borrador no aparece en el blog público."><select id="status" name="status" required aria-describedby="status-help{{ $errors->has('status') ? ' status-error' : '' }}" @error('status') aria-invalid="true" @enderror><option value="draft" @selected(old('status',$post->status ?? 'draft')==='draft')>Borrador</option><option value="published" @selected(old('status',$post->status)==='published')>Publicado</option></select></x-field></div>
<div class="actions"><button type="submit">{{ $post->exists ? 'Guardar cambios' : 'Crear noticia' }}</button><a class="button secondary" href="{{ route('admin.noticias.index') }}">Cancelar</a></div>
</form></section>
@endsection
