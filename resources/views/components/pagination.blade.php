@if($paginator->hasPages())
<nav aria-label="Paginación de noticias" class="pagination"><p>Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</p><ul>
@if($paginator->onFirstPage())<li><span>Anterior</span></li>@else<li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a></li>@endif
@foreach($elements as $element)
@if(is_string($element))<li><span>{{ $element }}</span></li>@endif
@if(is_array($element))@foreach($element as $page=>$url)<li>@if($page==$paginator->currentPage())<span aria-current="page" aria-label="Página {{ $page }}, actual">{{ $page }}</span>@else<a href="{{ $url }}" aria-label="Ir a página {{ $page }}">{{ $page }}</a>@endif</li>@endforeach @endif
@endforeach
@if($paginator->hasMorePages())<li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a></li>@else<li><span>Siguiente</span></li>@endif
</ul></nav>@endif
