The `#[Reactive]` attribute makes a child component's property automatically update when the parent changes the value being passed in.

## Basic usage

Apply the `#[Reactive]` attribute to any property that should react to parent changes:

```php
<?php // resources/views/components/⚡todo-count.blade.php

use Livewire\Attributes\Reactive;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Reactive] // [tl! highlight]
    public $todos;

    #[Computed]
    public function count()
    {
        return $this->todos->count();
    }
};
?>

<div>
    Count: {{ $this->count }}
</div>
```

Now when the parent component adds or removes todos, the child component will automatically update to reflect the new count.

## Why props aren't reactive by default

By default, Livewire props are **not reactive**. When a parent component updates, only the parent's state is sent to the server—not the child's. This minimizes data transfer and improves performance.

Here's what happens without `#[Reactive]`:

```php
<?php // resources/views/components/⚡todos.blade.php

use Livewire\Component;

new class extends Component {
    public $todos = [];

    public function addTodo($text)
    {
        $this->todos[] = ['text' => $text];
        // Child components with $todos props won't automatically update
    }
};
?>

<div>
    <livewire:todo-count :$todos />

    <button wire:click="addTodo('New task')">Add Todo</button>
</div>
```

Without `#[Reactive]` on the child's `$todos` property, adding a todo in the parent won't update the child's count.

## How it works

When you add `#[Reactive]`:

1. Parent updates its `$todos` property
2. Parent sends new `$todos` value to the child during the response
3. Child component automatically re-renders with the new value

This creates a "reactive" relationship similar to frontend frameworks like Vue or React.

## Performance considerations

> [!warning] Use reactive properties sparingly
> Reactive properties require additional data to be sent between server and client on every parent update. Only use `#[Reactive]` when necessary for your use case.

**When to use:**
* Child component displays data that changes in the parent
* Child needs to stay in sync with parent state
* You're building a tightly coupled parent-child relationship

**When NOT to use:**
* Initial data is passed once and never changes
* Child manages its own independent state
* Performance is critical and updates aren't needed

## Example: Live search results

Here's a practical example of a search component with reactive results:

```php
<?php // resources/views/components/⚡search.blade.php

use Livewire\Component;
use App\Models\Post;

new class extends Component {
    public $query = '';

    public function posts()
    {
        return Post::where('title', 'like', "%{$this->query}%")->get();
    }
};
?>

<div>
    <input type="text" wire:model.live="query" placeholder="Search posts...">

    <livewire:search-results :posts="$this->posts()" /> <!-- [tl! highlight] -->
</div>
```

```php
<?php // resources/views/components/⚡search-results.blade.php

use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {
    #[Reactive] // [tl! highlight]
    public $posts;
};
?>

<div>
    @foreach($posts as $post)
        <div>{{ $post->title }}</div>
    @endforeach
</div>
```

As the user types, the parent's `$posts` changes and the child's results automatically update.

## Alternative: Events

For loosely coupled components, consider using events instead of reactive props:

```php
// Parent dispatches event
$this->dispatch('todos-updated', todos: $this->todos);

// Child listens for event
#[On('todos-updated')]
public function handleTodosUpdate($todos)
{
    $this->todos = $todos;
}
```

Events provide more flexibility but require explicit communication between components.

## Learn more

For more information about parent-child communication and component architecture, see the [Nesting Components documentation](/docs/4.x/nesting#reactive-props).
