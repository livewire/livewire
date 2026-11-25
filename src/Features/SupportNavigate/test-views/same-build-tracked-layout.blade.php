<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/test-navigate-asset-xyz789.js" data-navigate-track="build-1"></script>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


