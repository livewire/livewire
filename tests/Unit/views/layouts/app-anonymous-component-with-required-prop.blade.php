@props([
    'foo' => 'bar',
    'bar',
])

<div {{$attributes}}>
    {{ $foo }}
</div>

{{ $slot }}

{{ $bar }}
