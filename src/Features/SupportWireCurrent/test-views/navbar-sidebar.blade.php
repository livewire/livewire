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
                <li><a href="/first" wire:navigate dusk="link.first" wire:current="active">Link first</a></li>
                <li><a href="/first/sub" wire:navigate dusk="link.sub" wire:current="active">Link first/sub</a></li>
                <li><a href="/second" wire:navigate dusk="link.second" wire:current="active">Link second</a></li>
                <li><a href="/first" wire:navigate dusk="link.first.exact" wire:current.exact="active">exact: Link first</a></li>
                <li><a href="/first/sub" wire:navigate dusk="link.sub.exact" wire:current.exact="active">exact: Link first/sub</a></li>
                <li><a href="/second" wire:navigate dusk="link.second.exact" wire:current.exact="active">exact: Link second</a></li>
                <li><a href="/first/" wire:navigate dusk="link.first.slash" wire:current="active">Link first (trailing slash without .strict)</a></li>
                <li><a href="/first/" wire:navigate dusk="link.first.slash.strict" wire:current.strict="active">Link first (trailing slash with .strict)</a></li>

                <li style="padding-top: 1rem">Route helper:</li>
                <li><a href="{{ route('first') }}" wire:navigate dusk="route.link.first" wire:current="active">Link first</a></li>
                <li><a href="{{ route('first.sub') }}" wire:navigate dusk="route.link.sub" wire:current="active">Link first/sub</a></li>
                <li><a href="{{ route('second') }}" wire:navigate dusk="route.link.second" wire:current="active">Link second</a></li>
                <li><a href="{{ route('first') }}" wire:navigate dusk="route.link.first.exact" wire:current.exact="active">exact: Link first</a></li>
                <li><a href="{{ route('first.sub') }}" wire:navigate dusk="route.link.sub.exact" wire:current.exact="active">exact: Link first/sub</a></li>
                <li><a href="{{ route('second') }}" wire:navigate dusk="route.link.second.exact" wire:current.exact="active">exact: Link second</a></li>

                <li style="padding-top: 1rem">Non wire:navigate:</li>
                <li><a href="{{ route('first') }}" dusk="native.link.first" wire:current="active">Link first</a></li>
                <li><a href="{{ route('first.sub') }}" dusk="native.link.sub" wire:current="active">Link first/sub</a></li>
                <li><a href="{{ route('second') }}" dusk="native.link.second" wire:current="active">Link second</a></li>
                <li><a href="{{ route('first') }}" dusk="native.link.first.exact" wire:current.exact="active">exact: Link first</a></li>
                <li><a href="{{ route('first.sub') }}" dusk="native.link.sub.exact" wire:current.exact="active">exact: Link first/sub</a></li>
                <li><a href="{{ route('second') }}" dusk="native.link.second.exact" wire:current.exact="active">exact: Link second</a></li>
            </ul>
        @endpersist
    </div>

    <main>
        {{ $slot }}
    </main>
</body>
</html>
