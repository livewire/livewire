<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
