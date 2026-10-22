Polling is a technique used in web applications to "poll" the server (send requests on a regular interval) for updates. It's a simple way to keep a page up-to-date without the need for a more sophisticated technology like [WebSockets](/docs/4.x/events#real-time-events-using-laravel-echo).

## Basic usage

Using polling inside Livewire is as simple as adding `wire:poll` to an element.

Below is an example of a `SubscriberCount` component that shows a user's subscriber count:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SubscriberCount extends Component
{
    public function render()
    {
        return view('livewire.subscriber-count', [
            'count' => Auth::user()->subscribers->count(),
        ]);
    }
}
```

```blade
<div wire:poll> <!-- [tl! highlight] -->
    Subscribers: {{ $count }}
</div>
```

Normally, this component would show the subscriber count for the user and never update until the page was refreshed. However, because of `wire:poll` on the component's template, this component will now refresh itself every `2.5` seconds, keeping the subscriber count up-to-date.

You can also specify an action to fire on the polling interval by passing a value to `wire:poll`:

```blade
<div wire:poll="refreshSubscribers">
    Subscribers: {{ $count }}
</div>
```

Now, the `refreshSubscribers()` method on the component will be called every `2.5` seconds.

## Timing control

The primary drawback of polling is that it can be resource intensive. If you have a thousand visitors on a page that uses polling, one thousand network requests will be triggered every `2.5` seconds.

The best way to reduce requests in this scenario is simply to make the polling interval longer.

You can manually control how often the component will poll by appending the desired duration to `wire:poll` like so:

```blade
<div wire:poll.1m> <!-- In minutes... -->

<div wire:poll.15s> <!-- In seconds... -->

<div wire:poll.15000ms> <!-- In milliseconds... -->
```

## Backing off

Polling at a fixed interval keeps sending requests even when nothing has changed. Add the `.backoff` modifier and Livewire will wait twice as long after each poll that comes back unchanged:

```blade
<div wire:poll.5s.backoff> <!-- [tl! highlight] -->
    Subscribers: {{ $count }}
</div>
```

While the subscriber count stays the same, this component polls at 5, 10, 20, and 40 seconds, then every 40 seconds. As soon as a poll brings back something new, or the user interacts with the component, it goes back to polling every 5 seconds.

A poll comes back unchanged when the server responds with the same data and the same HTML as the poll before it. Failed polls back off the same way, so a struggling server isn't hit at full speed while it recovers.

By default, Livewire slows a poll down to 8 times its interval. You can set the slowest pace yourself by adding a duration after `.backoff`:

```blade
<div wire:poll.5s.backoff.30s>
```

Now the component never waits more than 30 seconds between polls.

> [!tip] Keep values that change on every render out of the template
> A value like the current time makes every response different, so the poll never backs off. Render it with Alpine instead, or move it outside the polling component.

## Background throttling

To further cut down on server requests, Livewire automatically throttles polling when a page is in the background. For example, if a user keeps a page open in a different browser tab, Livewire will reduce the number of polling requests by 95% until the user revisits the tab.

If you want to opt-out of this behavior and keep polling continuously, even when a tab is in the background, you can add the `.keep-alive` modifier to `wire:poll`:

```blade
<div wire:poll.keep-alive>
```

##  Viewport throttling

Another measure you can take to only poll when necessary, is to add the `.visible` modifier to `wire:poll`. The `.visible` modifier instructs Livewire to only poll the component when it is visible on the page:

```blade
<div wire:poll.visible>
```

If a component using `wire:visible` is at the bottom of a long page, it won't start polling until the user scrolls it into the viewport. When the user scrolls away, it will stop polling again.

## Reference

```blade
wire:poll
wire:poll="action"
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.[number]m` | Poll interval in minutes (e.g., `.1m`) |
| `.[number]s` | Poll interval in seconds (e.g., `.15s`) |
| `.[number]ms` | Poll interval in milliseconds (e.g., `.15000ms`) |
| `.backoff` | Double the interval after each poll that comes back unchanged |
| `.backoff.[duration]` | Back off, but never wait longer than the given duration (e.g., `.backoff.30s`) |
| `.keep-alive` | Continue polling even when the tab is in the background |
| `.visible` | Only poll when the element is visible in the viewport |
