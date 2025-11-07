The `@persist` directive preserves elements across page navigations when using `wire:navigate`, maintaining their state and avoiding re-initialization.

## Basic usage

Wrap an element with `@persist` and provide a unique name to preserve it across page visits:

```blade
@persist('player')
    <audio src="{{ $episode->file }}" controls></audio>
@endpersist
```

When navigating to a new page that also contains a persisted element with the same name, Livewire reuses the existing DOM element instead of creating a new one. For an audio player, this means playback continues uninterrupted.

> [!tip] Requires wire:navigate
> The `@@persist` directive only works when navigation is handled by Livewire's `wire:navigate` feature. Standard page loads will not preserve elements.

## Common use cases

**Audio/video players**
```blade
@persist('podcast-player')
    <audio src="{{ $episode->audio_url }}" controls></audio>
@endpersist
```

**Chat widgets**
```blade
@persist('support-chat')
    <div id="chat-widget">
        <!-- Chat interface... -->
    </div>
@endpersist
```

**Third-party widgets**
```blade
@persist('analytics-widget')
    <div id="analytics-dashboard">
        <!-- Complex widget that's expensive to initialize... -->
    </div>
@endpersist
```

## Placement in layouts

Persisted elements should typically be placed outside Livewire components, commonly in your main layout:

```blade
<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html>
    <head>
        <title>{{ $title ?? 'App' }}</title>
        @livewireStyles
    </head>
    <body>
        <main>
            {{ $slot }}
        </main>

        @persist('player')
            <audio src="{{ $episode->file }}" controls></audio>
        @endpersist

        @livewireScripts
    </body>
</html>
```

## Preserving scroll position

For scrollable persisted elements, add `wire:scroll` to maintain scroll position:

```blade
@persist('scrollable-list')
    <div class="overflow-y-scroll" wire:scroll>
        <!-- Scrollable content... -->
    </div>
@endpersist
```

## Active link highlighting

Inside persisted elements, use `wire:current` instead of server-side conditionals to highlight active links:

```blade
@persist('navigation')
    <nav>
        <a href="/dashboard" wire:navigate wire:current="font-bold">Dashboard</a>
        <a href="/posts" wire:navigate wire:current="font-bold">Posts</a>
        <a href="/users" wire:navigate wire:current="font-bold">Users</a>
    </nav>
@endpersist
```

[Learn more about wire:current →](/docs/4.x/wire-current)

## How it works

When navigating with `wire:navigate`:
1. Livewire looks for elements with matching `@persist` names on both pages
2. If found, the existing element is moved to the new page's DOM
3. The element's state, event listeners, and Alpine data are preserved

[Learn more about navigation →](/docs/4.x/navigate)
