The `#[QueueOffline]` attribute marks an action so Livewire queues it when the browser is offline, then replays it automatically once the connection returns.

## Basic usage

Apply `#[QueueOffline]` to any action that should wait for reconnection instead of failing immediately:

```php
<?php // resources/views/components/post/⚡editor.blade.php

use Livewire\Attributes\QueueOffline;
use Livewire\Component;

new class extends Component {
    #[QueueOffline]
    public function saveDraft()
    {
        // Persist draft...
    }
};
```

```blade
<button wire:click="saveDraft">Save Draft</button>
```

When the user clicks this while offline, Livewire defers the request and sends it automatically after reconnecting.

## Modifier alternative

Instead of annotating the method, you can apply offline queueing at call-site with `.offline.queue`:

```blade
<button wire:click.offline.queue="saveDraft">Save Draft</button>
```

Use this when you want some calls to be offline-queued and others to run normally.

## When to use

Use `#[QueueOffline]` for actions where delayed execution is better than immediate failure:

- Draft autosave and form-progress saves
- Non-destructive preference updates
- User-intent actions that should survive brief connectivity drops

## Important notes

- Queued actions are held in browser memory and replayed when the page is still open
- Refreshing or closing the page before reconnecting clears queued actions
- Keep handlers idempotent so replayed actions are safe

## Learn more

See [Actions](/docs/4.x/actions#queueing-actions-while-offline) and [wire:offline](/docs/4.x/wire-offline).
