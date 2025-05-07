<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
{{ $slot }}

<script data-navigate-once>
    document.addEventListener('alpine:init', () => {
        document.addEventListener('livewire:navigate-swapped', () => {
            document.documentElement.classList.add('swapped');
        })
    })
</script>
</body>
</html>
