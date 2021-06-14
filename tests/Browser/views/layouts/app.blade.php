<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (config('use_alpine_v3'))
        <script src="http://alpine.test/packages/alpinejs/dist/cdn.js" defer></script>
    @else
        <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script>
    @endif

    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>
