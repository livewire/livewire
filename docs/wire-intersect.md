Livewire's `wire:intersect` directive allows you to execute an action when an element enters or leaves the viewport. This is useful for lazy loading content, triggering analytics, or creating scroll-based interactions.

## Basic usage

The simplest form runs an action when an element becomes visible:

```blade
<div wire:intersect="loadMore">
    <!-- Content loads when scrolled into view -->
</div>
```

When the element enters the viewport, the `loadMore` action will be called on your component.

## Enter and leave events

You can specify whether to run the action on enter, leave, or both:

```blade
<!-- Runs when entering viewport (default) -->
<div wire:intersect="trackView">...</div>

<!-- Runs when entering viewport (explicit) -->
<div wire:intersect:enter="trackView">...</div>

<!-- Runs when leaving viewport -->
<div wire:intersect:leave="pauseVideo">...</div>
```

## Visibility modifiers

Control how much of the element needs to be visible before triggering:

```blade
<!-- Trigger when any part is visible (default) -->
<div wire:intersect="load">...</div>

<!-- Trigger when half is visible -->
<div wire:intersect.half="load">...</div>

<!-- Trigger when fully visible -->
<div wire:intersect.full="load">...</div>

<!-- Trigger at custom threshold (0-100) -->
<div wire:intersect.threshold.25="load">...</div>
```

## Margin

Add a margin around the viewport to trigger the action before/after the element enters:

```blade
<!-- Trigger 200px before entering viewport -->
<div wire:intersect.margin.200px="loadMore">...</div>

<!-- Use percentage-based margin -->
<div wire:intersect.margin.10%="loadMore">...</div>

<!-- Different margins for each side (top, right, bottom, left) -->
<div wire:intersect.margin.10%.25px.25px.25px="loadMore">...</div>
```

## Fire once

Use the `.once` modifier to ensure the action only fires on the first intersection:

```blade
<div wire:intersect.once="trackImpression">
    <!-- Action only fires once, even if scrolled past multiple times -->
</div>
```

This is particularly useful for analytics or tracking when you only want to record the first time a user sees something.

## Combining modifiers

You can combine multiple modifiers to create precise behaviors:

```blade
<!-- Load when half visible, only once, with 100px margin -->
<div wire:intersect.once.half.margin.100px="loadSection">
    <!-- ... -->
</div>
```

## Common use cases

### Infinite scroll

```blade
<?php

use Livewire\Component;

new class extends Component {
    public $page = 1;
    public $posts = [];

    public function mount()
    {
        $this->loadPosts();
    }

    public function loadPosts()
    {
        $newPosts = Post::latest()
            ->skip(($this->page - 1) * 10)
            ->take(10)
            ->get();

        $this->posts = array_merge($this->posts, $newPosts->toArray());
        $this->page++;
    }
};
?>

<div>
    @foreach ($posts as $post)
        <div>{{ $post['title'] }}</div>
    @endforeach

    <div wire:intersect="loadPosts">
        Loading more posts...
    </div>
</div>
```

### Lazy loading images

```blade
<?php

use Livewire\Component;

new class extends Component {
    public $imageLoaded = false;

    public function loadImage()
    {
        $this->imageLoaded = true;
    }
};
?>

<div>
    @if ($imageLoaded)
        <img src="/path/to/image.jpg" alt="Product">
    @else
        <div wire:intersect.once="loadImage" class="bg-gray-200 h-64">
            <!-- Placeholder -->
        </div>
    @endif
</div>
```

### Tracking visibility

```blade
<div wire:intersect:enter.once="trackView" wire:intersect:leave="trackLeave">
    <!-- Track when users view and leave this content -->
</div>
```

## Comparison with Alpine's x-intersect

If you're familiar with Alpine.js, `wire:intersect` works similarly to `x-intersect` but triggers Livewire actions instead of Alpine expressions. The modifiers and behavior are designed to feel familiar to Alpine users.

## Reference

```blade
wire:intersect="action"
wire:intersect:enter="action"
wire:intersect:leave="action"
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.once` | Only fire the action on the first intersection |
| `.half` | Trigger when half of the element is visible |
| `.full` | Trigger when the entire element is visible |
| `.threshold.[0-100]` | Trigger at a custom visibility threshold percentage |
| `.margin.[value]` | Add margin around the viewport (e.g., `.margin.200px`, `.margin.10%`) |
