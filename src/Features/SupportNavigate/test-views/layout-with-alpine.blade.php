<html x-data x-bind:class="{ dark : true }">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="/test-navigate-asset.js?v=123"></script>
</head>
<body>
    {{ $slot }}

    @stack('scripts')
</body>
</html>


