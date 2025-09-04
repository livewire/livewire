<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @if (app('livewire')->isCspSafe())
        <meta http-equiv="Content-Security-Policy" content="
            default-src 'self';
            script-src 'self';
            style-src 'self' 'unsafe-inline';
            object-src 'none';
        ">
    @endif
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>
