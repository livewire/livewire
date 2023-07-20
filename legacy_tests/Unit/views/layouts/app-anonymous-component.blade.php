@props([
    'foo' => 'bar',
])

<div {{ $attributes->merge(['id' => 'foo']) }}>
    {{ $foo }}
</div>

{{ $slot }}

@isset($bar)
    {{ $bar }}
@endisset
