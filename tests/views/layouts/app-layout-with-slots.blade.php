<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    {{ $header ?? 'No Header' }}

    {{ $slot }}

    {{ $footer ?? 'No Footer' }}
</body>
</html>
