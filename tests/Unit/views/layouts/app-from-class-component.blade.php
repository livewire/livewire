@yield('content')

{{ $slot }}

<div {{ $attributes->merge(['id' => 'foo']) }}>
    {{ $foo }}
</div>

@isset($bar)
    {{ $bar }}
@endisset
