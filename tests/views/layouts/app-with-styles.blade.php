<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .show {
            display: block;
        }
    </style>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


