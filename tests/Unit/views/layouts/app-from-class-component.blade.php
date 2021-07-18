@yield('content')

{{ $slot }}

{{ $foo }}

@isset($bar)
    {{ $bar }}
@endisset
