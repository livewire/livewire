<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/test-navigate-asset.js?v=456" data-navigate-track></script>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


