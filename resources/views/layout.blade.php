<!doctype html>
<html lang="es" class="text-normal theme-normal">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>@yield('title') · Entrelineas</title><meta name="description" content="Entrelineas, un blog universitario de noticias ficticias para aprender y compartir."><link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml"><link rel="stylesheet" href="{{ asset('css/app.css') }}"><script src="{{ asset('js/app.js') }}" defer></script><script src="{{ asset('js/accessibility.js') }}"></script></head>
<body>
<a class="skip-link" href="#contenido-principal">Saltar al contenido principal</a>
<header class="site-header wrapper">
 <div class="masthead"><a class="brand" href="{{ route('home') }}">Entre<span>lineas</span><span class="brand-dot" aria-hidden="true">.</span></a><p>Una mirada al campus. <br>Ideas que nos conectan.</p></div>
 <nav aria-label="Navegación principal" class="navigation">
 @foreach(['home'=>'Inicio','news.index'=>'Noticias','categories'=>'Categorías','admin.dashboard'=>'Administración'] as $route=>$label)
 @php($active = $route === 'news.index' ? request()->routeIs('news.*') : ($route === 'admin.dashboard' ? request()->routeIs('admin.*','login') : request()->routeIs($route)))
 <a href="{{ route($route) }}" @if($active) aria-current="page" @endif>{{ $label }}</a>
 @endforeach
 @auth <form action="{{ route('logout') }}" method="post">@csrf<button class="text-button" type="submit">Cerrar sesión</button></form> @endauth
 </nav>
 <div class="accessibility-bar"><button type="button" id="accessibility-toggle" class="secondary" aria-expanded="false" aria-controls="accessibility-panel" disabled>Accesibilidad</button></div>
 <section id="accessibility-panel" class="accessibility-panel" aria-labelledby="accessibility-title" hidden>
  <div class="row-heading"><p id="accessibility-title"><strong>Accesibilidad</strong></p><button type="button" id="accessibility-close" class="secondary">Cerrar panel</button></div>
  <p>Adapta la lectura. Tus preferencias se conservan en este navegador.</p>
  <fieldset><legend>Tamaño del texto</legend><div class="preference-options">
   <button type="button" class="secondary" id="text-decrease" aria-label="Disminuir texto" aria-describedby="text-size-status">A−</button>
   <button type="button" class="secondary" data-text="normal" aria-pressed="true">Normal</button>
   <button type="button" class="secondary" id="text-increase" aria-label="Aumentar texto" aria-describedby="text-size-status">A+</button>
  </div><p id="text-size-status" class="help" role="status">Tamaño actual: Normal (100 %).</p></fieldset>
  <fieldset><legend>Apariencia</legend><div class="preference-options">
   @foreach(['normal'=>'Normal','contrast'=>'Alto contraste','dark'=>'Oscuro','comfortable'=>'Lectura cómoda'] as $theme=>$label)
   <button type="button" class="secondary" data-theme="{{ $theme }}" aria-pressed="{{ $theme === 'normal' ? 'true' : 'false' }}">{{ $label }}</button>
   @endforeach
  </div></fieldset>
  <fieldset><legend>Lectura asistida</legend><button type="button" id="voice-help" class="secondary" aria-pressed="false">Activar ayuda por voz</button><p id="voice-help-status" class="help" role="status">Ayuda por voz desactivada. Todos los mensajes también aparecen en texto.</p></fieldset>
  <button type="button" id="accessibility-reset" class="secondary">Restablecer preferencias</button>
  <p id="accessibility-status" class="help" role="status"></p>
 </section>
 <noscript><p class="help">Las preferencias y la lectura por voz requieren JavaScript. Puedes navegar y usar los formularios normalmente.</p></noscript>
</header>
<main id="contenido-principal" class="wrapper main" tabindex="-1">
 @if(session('status'))<p class="message success" role="status" data-voice-message>Éxito: {{ session('status') }}</p>@endif
 @if($errors->any())
 <section id="error-summary" class="message error" role="alert" aria-labelledby="errores-titulo" tabindex="-1">
  <p id="errores-titulo" data-voice-message><strong>Error: Hay {{ count($errors->keys()) }} {{ count($errors->keys()) === 1 ? 'campo que necesita' : 'campos que necesitan' }} revisión.</strong> Revisa los mensajes del formulario.</p>
  <ul>@foreach($errors->keys() as $field)<li><a href="#{{ $field }}">{{ $errors->first($field) }}</a></li>@endforeach</ul>
 </section>
 @endif
 @yield('content')
</main>
<footer class="site-footer wrapper"><p><strong>Entrelineas.</strong> Noticias para una comunidad curiosa.</p><p>Proyecto académico · Contenido ficticio y original.<br>Pruebas y Calidad de Software · PA02</p><a href="{{ route('news.index') }}">Explorar noticias</a></footer>
</body></html>
