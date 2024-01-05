<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>This is a custom layout</h1>

    {{ $slot }}

    @stack('scripts')
</body>
</html>


