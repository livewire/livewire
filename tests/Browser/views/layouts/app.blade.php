<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.1/dist/alpine.min.js" defer></script>
    <!-- <script src="http://alpine.test/dist/alpine.js" defer></script> -->
    @livewireStyles
</head>
<body>
    {{ $slot }}

    <div dusk="stack">
        @stack('page_bottom')
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
