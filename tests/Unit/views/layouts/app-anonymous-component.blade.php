@props([
    'foo' => 'bar',
])

<div {{$attributes}}>
    {{ $foo }}
</div>

{{ $slot }}

@isset($bar)
    {{ $bar }}
@endisset
