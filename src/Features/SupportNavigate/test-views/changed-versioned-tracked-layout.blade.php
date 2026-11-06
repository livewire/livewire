<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/test-navigate-asset-def456.js" data-navigate-track="build-2"></script>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


