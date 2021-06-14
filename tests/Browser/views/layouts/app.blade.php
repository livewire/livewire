<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js" defer></script> -->
    <script src="http://alpine.test/packages/alpinejs/dist/cdn.js" defer></script>
    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>
