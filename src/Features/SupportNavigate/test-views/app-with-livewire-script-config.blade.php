<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @livewireStyles
    <style>
        [data-x-cloak] { display: none !important; }
    </style>
    <link rel="modulepreload" href="/compiled-custom-livewire-init.js?v=234" />
    <script type="module" src="/compiled-custom-livewire-init.js?v=456" data-navigate-track="reload"></script>
</head>
<body>
    {{ $slot }}

    @livewireScriptConfig
</body>
</html>


