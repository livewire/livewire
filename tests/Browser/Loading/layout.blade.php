<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @livewireStyles
</head>
<body>
    @livewire('component')

    @livewireScripts
</body>
</html>
