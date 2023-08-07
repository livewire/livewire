Polling is a technique used in web applications to "poll" the server (send regular requests) for updates. It's a simple way to keep a page up-to-date without the need for a more sophisticated technology like [WebSockets](/docs/events#real-time-events-using-laravel-echo).

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
<div wire:poll>
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
<div wire:poll.15s> <!-- In seconds... -->

<div wire:poll.15000ms> <!-- In milliseconds... -->
```

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
