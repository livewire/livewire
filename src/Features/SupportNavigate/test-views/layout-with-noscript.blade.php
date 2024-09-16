<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/test-navigate-asset.js?v=123"></script>
    <noscript> <meta http-equiv="refresh"  content="0;url={{ route('no-javascript') }}"> </noscript>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


