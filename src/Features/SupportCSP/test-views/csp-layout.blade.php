<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    {{ $slot }}
</body>
</html>
