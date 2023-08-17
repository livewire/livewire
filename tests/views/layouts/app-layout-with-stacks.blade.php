<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>
