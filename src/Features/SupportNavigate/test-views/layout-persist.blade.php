<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


