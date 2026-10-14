@props(['name','label','help'=>null])
<div class="field"><label for="{{ $name }}">{{ $label }}</label>@if($help)<p id="{{ $name }}-help" class="help">{{ $help }}</p>@endif{{ $slot }}@error($name)<p id="{{ $name }}-error" class="field-error">Error: {{ $message }}</p>@enderror</div>
