<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
{{ $slot }}

<script data-navigate-once>
    document.addEventListener('alpine:init', () => {
        document.addEventListener('livewire:navigate-swapping', (ev) => {
            // Use "ev.ev.detail.document" because we are in the swapping phase
            // and the "ev.ev.detail.document" is the one that will be swapped in
            ev.detail.document.documentElement.classList.add('swapping');
        })
    })
</script>
</body>
</html>
