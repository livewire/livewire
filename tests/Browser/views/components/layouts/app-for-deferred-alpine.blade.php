<html>
<head>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @livewireStyles
</head>
<body>
    {{ $slot }}

    <script id="alpine-scripts">
        let alpineLoaded = false;

        // Add alpine to the document after liverwire has loaded
        document.addEventListener('livewire:load', () => {
            if (! alpineLoaded) {
                alpineLoaded = true;
                const s = document.getElementById('alpine-scripts')
                const el = document.createElement('script')
                el.type = 'text/javascript'

                @if (config('use_alpine_v3'))
                    el.src = 'https://unpkg.com/alpinejs@3.11.0/dist/cdn.min.js'
                @else
                    el.src = 'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.2/dist/alpine.min.js'
                    window.deferLoadingAlpine = startAlpine => { startAlpine() }
                @endif

                s.parentNode.insertBefore(el, s);
            }
        })
    </script>

    @livewireScripts
    @stack('scripts')
</body>
</html>