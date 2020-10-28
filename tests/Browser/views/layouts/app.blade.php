<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.7.3/dist/alpine.min.js" defer></script>
    <!-- <script src="http://alpine.test/dist/alpine.js" defer></script> -->
    @livewireStyles
</head>
<body>
    {{ $slot }}

    @livewireScripts
    @stack('scripts')
</body>
</html>
