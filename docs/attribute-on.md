The `#[On]` attribute allows a component to listen for events and execute a method when those events are dispatched.

## Basic usage

Apply the `#[On]` attribute to any method that should be called when an event is dispatched:

```php
<?php // resources/views/components/⚡dashboard.blade.php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    #[On('post-created')] // [tl! highlight]
    public function updatePostList($title)
    {
        session()->flash('status', "New post created: {$title}");
    }
};
```

When another component dispatches the `post-created` event, the `updatePostList()` method will be called automatically.

## Dispatching events

To dispatch an event that triggers listeners, use the `dispatch()` method:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public $title = '';

    public function save()
    {
        $post = Post::create(['title' => $this->title]);

        $this->dispatch('post-created', title: $post->title); // [tl! highlight]

        return redirect('/posts');
    }
};
```

The `post-created` event will trigger any methods decorated with `#[On('post-created')]`.

## Passing data to listeners

Events can pass data as named parameters:

```php
// Dispatching with multiple parameters
$this->dispatch('post-updated', id: $post->id, title: $post->title);
```

```php
// Listening and receiving parameters
#[On('post-updated')]
public function handlePostUpdate($id, $title)
{
    // Use $id and $title...
}
```

## Dynamic event names

You can use component properties in event names for scoped listening:

```php
<?php // resources/views/components/post/⚡show.blade.php

use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public Post $post;

    #[On('post-updated.{post.id}')] // [tl! highlight]
    public function refreshPost()
    {
        $this->post->refresh();
    }
};
```

If `$post->id` is `3`, this will only listen for `post-updated.3` events, ignoring updates to other posts.

## Multiple event listeners

A single method can listen for multiple events:

```php
#[On('post-created')]
#[On('post-updated')]
#[On('post-deleted')]
public function refreshStats()
{
    // Refresh statistics when any post changes
}
```

## Listening to browser events

You can also listen for browser events dispatched from JavaScript:

```php
#[On('user-logged-in')]
public function handleUserLogin()
{
    // Handle login...
}
```

```javascript
// From JavaScript
window.dispatchEvent(new CustomEvent('user-logged-in'));
```

## Alternative: Listening in the template

Instead of using the attribute, you can listen for events directly on child components in your Blade template:

```blade
<livewire:post.edit @saved="$refresh" />
```

This listens for the `saved` event from the `post.edit` child component and refreshes the parent when it's dispatched.

You can also call specific methods:

```blade
<livewire:post.edit @saved="handleSave($event.id)" />
```

## When to use

Use `#[On]` when:

* One component needs to react to actions in another component
* Implementing real-time notifications or updates
* Building loosely coupled components that communicate via events
* Listening for browser or Laravel Echo events
* Refreshing data when external changes occur

## Example: Real-time notifications

Here's a practical example of a notification bell that listens for new notifications:

```php
<?php // resources/views/components/⚡notification-bell.blade.php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public $unreadCount = 0;

    public function mount()
    {
        $this->unreadCount = auth()->user()->unreadNotifications()->count();
    }

    #[On('notification-sent')] // [tl! highlight]
    public function incrementCount()
    {
        $this->unreadCount++;
    }

    #[On('notifications-read')] // [tl! highlight]
    public function resetCount()
    {
        $this->unreadCount = 0;
    }
};
?>

<button class="relative">
    <svg><!-- Bell icon --></svg>
    @if($unreadCount > 0)
        <span class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full px-2 py-1 text-xs">
            {{ $unreadCount }}
        </span>
    @endif
</button>
```

Other components can dispatch events to update the notification count:

```php
// From anywhere in your app
$this->dispatch('notification-sent');
```

## Learn more

For more information about events, dispatching to specific components, and Laravel Echo integration, see the [Events documentation](/docs/4.x/events).

## Reference

```php
#[On(
    string $event,
)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$event` | `string` | *required* | The name of the event to listen for |
