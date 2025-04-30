Every component update in Livewire triggers a network request. By default, when multiple components trigger updates at the same time, they are bundled into a single request.

This results in fewer network connections to the server and can drastically reduce server load.

In addition to the performance gains, this also unlocks features internally that require collaboration between multiple components ([Reactive Properties](/docs/nesting#reactive-props), [Modelable Properties](/docs/nesting#binding-to-child-data-using-wiremodel), etc.)

However, there are times when disabling this bundling is desired for performance reasons. The following page outlines various ways to customize this behavior in Livewire.

## Isolating component requests

By using Livewire's `#[Isolate]` class attribute, you can mark a component as "isolated". This means that whenever that component makes a server roundtrip, it will attempt to isolate itself from other component requests.

This is useful if the update is expensive and you'd rather execute this component's update in parallel with others. For example, if multiple components are using `wire:poll` or listening for an event on the page, you may want to isolate specific component whose updates are expensive and would otherwise hold up the entire request.

```php
use Livewire\Attributes\Isolate;
use Livewire\Component;

#[Isolate] // [tl! highlight]
class ShowPost extends Component
{
    // ...
}
```

By adding the `#[Isolate]` attribute, this component's requests will no longer be bundled with other component updates.

## Lazy components are isolated by default

When many components on a single page are "lazy" loaded (using the `#[Lazy]` attribute), it is often desired that their requests are isolated and sent in parallel. Therefore, Livewire isolates lazy updates by default.

If you wish to disable this behavior, you can pass an `isolate: false` parameter into the `#[Lazy]` attribute like so:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Lazy;

#[Lazy(isolate: false)] // [tl! highlight]
class Revenue extends Component
{
    // ...
}
```

Now, if there are multiple `Revenue` components on the same page, all ten updates will be bundled and sent the server as single, lazy-load, network request.
