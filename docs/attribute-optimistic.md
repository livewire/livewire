The `#[Optimistic]` attribute marks an action as optimistic. If that action sends local Livewire updates and the request fails, Livewire automatically rolls those optimistic updates back to the last known server state.

## Basic usage

Apply `#[Optimistic]` to actions where users should see immediate UI feedback:

```php
<?php // resources/views/components/post/⚡likes.blade.php

use Livewire\Attributes\Optimistic;
use Livewire\Component;

new class extends Component {
    public int $likes = 0;

    #[Optimistic] // [tl! highlight]
    public function saveLike()
    {
        // Persist likes...
    }
};
```

```blade
<button
    type="button"
    wire:click.optimistic="$set('likes', likes + 1)"
>
    👍 <span wire:text="likes"></span>
</button>
```

If the request succeeds, the optimistic value stays.  
If it fails, Livewire restores the previous value.

## Modifier alternative

Instead of an attribute, you can mark a specific call optimistic with `.optimistic`:

```blade
<button wire:click.optimistic="saveLike">Like</button>
```

You can also opt in when using JavaScript `$set`:

```js
$wire.$set('likes', $wire.$get('likes') + 1, true, { optimistic: true })
```

## When to use

Use optimistic actions when latency is noticeable and users benefit from instant feedback:

* **Counters and reactions** - likes, votes, favorites
* **Toggle state** - enable/disable flags, pin/unpin
* **Reordering and small edits** - quick interactions where rollback is acceptable

## See also

- **[Actions](/docs/4.x/actions)** — Action lifecycle and modifiers
- **[wire:click](/docs/4.x/wire-click)** — Event-driven actions
- **[Async Attribute](/docs/4.x/attribute-async)** — Parallel execution (different tradeoff)
