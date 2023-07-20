<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/test-navigate-asset.js?v=123"></script>
</head>
<body>
    <a href="/second" dusk="outside.link.to.second" wire:navigate>Go to second page (outside)</a>

    {{ $slot }}

    @stack('scripts')
</body>
</html>


