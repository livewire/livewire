<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (config('use_alpine_v3'))
        <script src="https://unpkg.com/alpinejs@3.11.0/dist/cdn.min.js" defer></script>
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
