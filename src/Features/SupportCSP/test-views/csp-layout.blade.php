<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    {{ $slot }}

    {{-- An inline script of the page's own, like a theme bootstrap or analytics snippet... --}}
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        window.__cspInlineScriptRuns = (window.__cspInlineScriptRuns ?? 0) + 1
    </script>
</body>
</html>
