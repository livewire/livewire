<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="data:text/plain,noop">
    <script src="/test-navigate-asset.js?v=789"></script>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>
