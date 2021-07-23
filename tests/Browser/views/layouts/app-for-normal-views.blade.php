<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
</head>
<body>
    @yield('content')

    @livewireScripts
    @stack('scripts')
</body>
</html>
