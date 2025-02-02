<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .active {
            background-color: #DDF;
        }
    </style>
</head>

<body>
    <div>
        @persist('nav')
            <ul x-data>
                <li><a href="/navbar/one" wire:navigate dusk="link.one" x-bind:class="$current($el.href) ? 'active' : null">Link one</a></li>
                <li><a href="/navbar/two" wire:navigate dusk="link.two" x-bind:class="$current($el.href) ? 'active' : null">Link two</a></li>
                <li><a href="/navbar/three" wire:navigate dusk="link.three" x-bind:class="$current($el.href) ? 'active' : null">Link three</a></li>
                <li><a href="/navbar/four" wire:navigate dusk="link.four" x-bind:class="$current($el.href) ? 'active' : null">Link four</a></li>
                <li><a href="/navbar/five" wire:navigate dusk="link.five" x-bind:class="$current($el.href) ? 'active' : null">Link five</a></li>
                <li><a href="/navbar/six" wire:navigate dusk="link.six" x-bind:class="$current($el.href) ? 'active' : null">Link six</a></li>
                <li><a href="/navbar/seven" wire:navigate dusk="link.seven" x-bind:class="$current($el.href) ? 'active' : null">Link seven</a></li>
                <li><a href="/navbar/eight" wire:navigate dusk="link.eight" x-bind:class="$current($el.href) ? 'active' : null">Link eight</a></li>
                <li><a href="/navbar/nine" wire:navigate dusk="link.nine" x-bind:class="$current($el.href) ? 'active' : null">Link nine</a></li>
                <li><a href="/navbar/ten" wire:navigate dusk="link.ten" x-bind:class="$current($el.href) ? 'active' : null">Link ten</a></li>
                <li><a href="/navbar/eleven" wire:navigate dusk="link.eleven" x-bind:class="$current($el.href) ? 'active' : null">Link eleven</a></li>
                <li><a href="/navbar/twelve" wire:navigate dusk="link.twelve" x-bind:class="$current($el.href) ? 'active' : null">Link twelve</a></li>
                <li><a href="/navbar/thirteen" wire:navigate dusk="link.thirteen" x-bind:class="$current($el.href) ? 'active' : null">Link thirteen</a></li>
                <li><a href="/navbar/fourteen" wire:navigate dusk="link.fourteen" x-bind:class="$current($el.href) ? 'active' : null">Link fourteen</a></li>
                <li><a href="/navbar/fifteen" wire:navigate dusk="link.fifteen" x-bind:class="$current($el.href) ? 'active' : null">Link fifteen</a></li>
            </ul>
        @endpersist
    </div>

    <main>
        {{ $slot }}
    </main>

    <script data-navigate-once>
        document.addEventListener('alpine:init', () => {
            let state = Alpine.reactive({
                href: window.location.href
            })

            document.addEventListener('livewire:navigated', () => {
                queueMicrotask(() => {
                    state.href = window.location.href
                })
            })

            Alpine.magic('current', (el) => (expected = '') => {
                let strip = (subject) => subject.replace(/^\/|\/$/g, '')

                return strip(state.href) === strip(expected)
            })
        })
    </script>
</body>

</html>
