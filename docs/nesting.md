
Livewire allows you to nest additional Livewire components inside of a parent component. This feature is immensely powerful, as it allows you to re-use and encapsulate behavior within Livewire components that are shared across your application.

> [!warning] You might not need a Livewire component
> Before you extract a portion of your template into a nested Livewire component, ask yourself: Does this content in this component need to be "live"? If not, we recommend that you create a simple [Blade component](https://laravel.com/docs/blade#components) instead. Only create a Livewire component if the component benefits from Livewire's dynamic nature or if there is a direct performance benefit.

Consult our [in-depth, technical examination of Livewire component nesting](/docs/understanding-nesting) for more information on the performance, usage implications, and constraints of nested Livewire components.

## Nesting a component

To nest a Livewire component within a parent component, simply include it in the parent component's Blade view. Below is an example of a `Dashboard` parent component that contains a nested `TodoList` component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
}
```

```blade
<div>
    <h1>Dashboard</h1>

    <livewire:todo-list /> <!-- [tl! highlight] -->
</div>
```

On this page's initial render, the `Dashboard` component will encounter `<livewire:todo-list />` and render it in place. On a subsequent network request to `Dashboard`, the nested `todo-list` component will skip rendering because it is now its own independent component on the page. For more information on the technical concepts behind nesting and rendering, consult our documentation on why [nested components are "islands"](/docs/understanding-nesting#every-component-is-an-island).

For more information about the syntax for rendering components, consult our documentation on [Rendering Components](/docs/components#rendering-components).

## Passing props to children

Passing data from a parent component to a child component is straightforward. In fact, it's very much like passing props to a typical [Blade component](https://laravel.com/docs/blade#components).

For example, let's check out a `TodoList` component that passes a collection of `$todos` to a child component called `TodoCount`:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TodoList extends Component
{
    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Auth::user()->todos,
        ]);
    }
}
```

```blade
<div>
    <livewire:todo-count :todos="$todos" />

    <!-- ... -->
</div>
```

As you can see, we are passing `$todos` into `todo-count` with the syntax: `:todos="$todos"`.

Now that `$todos` has been passed to the child component, you can receive that data through the child component's `mount()` method:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoCount extends Component
{
    public $todos;

    public function mount($todos)
    {
        $this->todos = $todos;
    }

    public function render()
    {
        return view('livewire.todo-count', [
            'count' => $this->todos->count(),
        ]);
    }
}
```

> [!tip] Omit `mount()` as a shorter alternative
> If the `mount()` method in above example feels like redundant boilerplate code to you, it can be omitted as long as the property and parameter names match:
> ```php
> public $todos; // [tl! highlight]
> ```

### Passing static props

In the previous example, we passed props to our child component using Livewire's dynamic prop syntax, which supports PHP expressions like so:

```blade
<livewire:todo-count :todos="$todos" />
```

However, sometimes you may want to pass a component a simple static value such as a string. In these cases, you may omit the colon from the beginning of the statement:

```blade
<livewire:todo-count :todos="$todos" label="Todo Count:" />
```

Boolean values may be provided to components by only specifying the key. For example, to pass an `$inline` variable with a value of `true` to a component, we may simply place `inline` on the component tag:

```blade
<livewire:todo-count :todos="$todos" inline />
```

### Shortened attribute syntax

When passing PHP variables into a component, the variable name and the prop name are often the same. To avoid writing the name twice, Livewire allows you to simply prefix the variable with a colon:

```blade
<livewire:todo-count :todos="$todos" /> <!-- [tl! remove] -->

<livewire:todo-count :$todos /> <!-- [tl! add] -->
```

## Rendering children in a loop

When rendering a child component within a loop, you should include a unique `key` value for each iteration.

Component keys are how Livewire tracks each component on subsequent renders, particularly if a component has already been rendered or if multiple components have been re-arranged on the page.

You can specify the component's key by specifying a `:key` prop on the child component:

```blade
<div>
    <h1>Todos</h1>

    @foreach ($todos as $todo)
        <livewire:todo-item :$todo :key="$todo->id" />
    @endforeach
</div>
```

As you can see, each child component will have a unique key set to the ID of each `$todo`. This ensures the key will be unique and tracked if the todos are re-ordered.

> [!warning] Keys aren't optional
> If you have used frontend frameworks like Vue or Alpine, you are familiar with adding a key to a nested element in a loop. However, in those frameworks, a key isn't _mandatory_, meaning the items will render, but a re-order might not be tracked properly. However, Livewire relies more heavily on keys and will behave incorrectly without them.

## Reactive props

Developers new to Livewire expect that props are "reactive" by default. In other words, they expect that when a parent changes the value of a prop being passed into a child component, the child component will automatically be updated. However, by default, Livewire props are not reactive.

When using Livewire, [every component is an island](/docs/understanding-nesting#every-component-is-an-island). This means that when an update is triggered on the parent and a network request is dispatched, only the parent component's state is sent to the server to re-render - not the child component's. The intention behind this behavior is to only send the minimal amount of data back and forth between the server and client, making updates as performant as possible.

But, if you want or need a prop to be reactive, you can easily enable this behavior using the `#[Reactive]` attribute parameter.

For example, below is the template of a parent `TodoList` component. Inside, it is rendering a `TodoCount` component and passing in the current list of todos:

```blade
<div>
    <h1>Todos:</h1>

    <livewire:todo-count :$todos />

    <!-- ... -->
</div>
```

Now let's add `#[Reactive]` to the `$todos` prop in the `TodoCount` component. Once we have done so, any todos that are added or removed inside the parent component will automatically trigger an update within the `TodoCount` component:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Reactive;
use Livewire\Component;
use App\Models\Todo;

class TodoCount extends Component
{
    #[Reactive] // [tl! highlight]
    public $todos;

    public function render()
    {
        return view('livewire.todo-count', [
            'count' => $this->todos->count(),
        ]);
    }
}
```

Reactive properties are an incredibly powerful feature, making Livewire more similar to frontend component libraries like Vue and React. But, it is important to understand the performance implications of this feature and only add `#[Reactive]` when it makes sense for your particular scenario.

## Binding to child data using `wire:model`

Another powerful pattern for sharing state between parent and child components is using `wire:model` directly on a child component via Livewire's `Modelable` feature.

This behavior is very commonly needed when extracting an input element into a dedicated Livewire component while still accessing its state in the parent component.

Below is an example of a parent `TodoList` component that contains a `$todo` property which tracks the current todo about to be added by a user:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Todo;

class TodoList extends Component
{
    public $todo = '';

    public function add()
    {
        Todo::create([
            'content' => $this->pull('todo'),
        ]);
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Auth::user()->todos,
        ]);
    }
}
```

As you can see in the `TodoList` template, `wire:model` is being used to bind the `$todo` property directly to a nested `TodoInput` component:

```blade
<div>
    <h1>Todos</h1>

    <livewire:todo-input wire:model="todo" /> <!-- [tl! highlight] -->

    <button wire:click="add">Add Todo</button>

    <div>
        @foreach ($todos as $todo)
            <livewire:todo-item :$todo :key="$todo->id" />
        @endforeach
    </div>
</div>
```

Livewire provides a `#[Modelable]` attribute you can add to any child component property to make it _modelable_ from a parent component.

Below is the `TodoInput` component with the `#[Modelable]` attribute added above the `$value` property to signal to Livewire that if `wire:model` is declared on the component by a parent it should bind to this property:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Modelable;

class TodoInput extends Component
{
    #[Modelable] // [tl! highlight]
    public $value = '';

    public function render()
    {
        return view('livewire.todo-input');
    }
}
```

```blade
<div>
    <input type="text" wire:model="value" >
</div>
```

Now the parent `TodoList` component can treat `TodoInput` like any other input element and bind directly to its value using `wire:model`.

> [!warning]
> Currently Livewire only supports a single `#[Modelable]` attribute, so only the first one will be bound.


## Listening for events from children

Another powerful parent-child component communication technique is Livewire's event system, which allows you to dispatch an event on the server or client that can be intercepted by other components.

Our [complete documentation on Livewire's event system](/docs/events) provides more detailed information on events, but below we'll discuss a simple example of using an event to trigger an update in a parent component.

Consider a `TodoList` component with functionality to show and remove todos:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Todo;

class TodoList extends Component
{
    public function remove($todoId)
    {
        $todo = Todo::find($todoId);

        $this->authorize('delete', $todo);

        $todo->delete();
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Auth::user()->todos,
        ]);
    }
}
```

```blade
<div>
    @foreach ($todos as $todo)
        <livewire:todo-item :$todo :key="$todo->id" />
    @endforeach
</div>
```

To call `remove()` from inside the child `TodoItem` components, you can add an event listener to `TodoList` via the `#[On]` attribute:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Todo;
use Livewire\Attributes\On;

class TodoList extends Component
{
    #[On('remove-todo')] // [tl! highlight]
    public function remove($todoId)
    {
        $todo = Todo::find($todoId);

        $this->authorize('delete', $todo);

        $todo->delete();
    }

    public function render()
    {
        return view('livewire.todo-list', [
            'todos' => Auth::user()->todos,
        ]);
    }
}
```

Once the attribute has been added to the action, you can dispatch the `remove-todo` event from the `TodoList` child component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoItem extends Component
{
    public Todo $todo;

    public function remove()
    {
        $this->dispatch('remove-todo', todoId: $this->todo->id); // [tl! highlight]
    }

    public function render()
    {
        return view('livewire.todo-item');
    }
}
```

```blade
<div>
    <span>{{ $todo->content }}</span>

    <button wire:click="remove">Remove</button>
</div>
```

Now when the "Remove" button is clicked inside a `TodoItem`, the parent `TodoList` component will intercept the dispatched event and perform the todo removal.

After the todo is removed in the parent, the list will be re-rendered and the child that dispatched the `remove-todo` event will be removed from the page.

### Improving performance by dispatching client-side

Though the above example works, it takes two network requests to perform a single action:

1. The first network request from the `TodoItem` component triggers the `remove` action, dispatching the `remove-todo` event.
2. The second network request is after the `remove-todo` event is dispatched client-side and is intercepted by `TodoList` to call its `remove` action.

You can avoid the first request entirely by dispatching the `remove-todo` event directly on the client-side. Below is an updated `TodoItem` component that does not trigger a network request when dispatching the `remove-todo` event:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoItem extends Component
{
    public Todo $todo;

    public function render()
    {
        return view('livewire.todo-item');
    }
}
```

```blade
<div>
    <span>{{ $todo->content }}</span>

    <button wire:click="$dispatch('remove-todo', { todoId: {{ $todo->id }} })">Remove</button>
</div>
```

As a rule of thumb, always prefer dispatching client-side when possible.

## Directly accessing the parent from the child

Event communication adds a layer of indirection. A parent can listen for an event that never gets dispatched from a child, and a child can dispatch an event that is never intercepted by a parent.

This indirection is sometimes desirable; however, in other cases you may prefer to access a parent component directly from the child component.

Livewire allows you to accomplish this by providing a magic `$parent` variable to your Blade template that you can use to access actions and properties directly from the child. Here's the above `TodoItem` template rewritten to call the `remove()` action directly on the parent via the magic `$parent` variable:

```blade
<div>
    <span>{{ $todo->content }}</span>

    <button wire:click="$parent.remove({{ $todo->id }})">Remove</button>
</div>
```

Events and direct parent communication are a few of the ways to communicate back and forth between parent and child components. Understanding their tradeoffs enables you to make more informed decisions about which pattern to use in a particular scenario.

## Dynamic child components

Sometimes, you may not know which child component should be rendered on a page until run-time. Therefore, Livewire allows you to choose a child component at run-time via `<livewire:dynamic-component ...>`, which receives an `:is` prop:

```blade
<livewire:dynamic-component :is="$current" />
```

Dynamic child components are useful in a variety of different scenarios, but below is an example of rendering different steps in a multi-step form using a dynamic component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Steps extends Component
{
    public $current = 'step-one';

    protected $steps = [
        'step-one',
        'step-two',
        'step-three',
    ];

    public function next()
    {
        $currentIndex = array_search($this->current, $this->steps);

        $this->current = $this->steps[$currentIndex + 1];
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}
```

```blade
<div>
    <livewire:dynamic-component :is="$current" :key="$current" />

    <button wire:click="next">Next</button>
</div>
```

Now, if the `Steps` component's `$current` prop is set to "step-one", Livewire will render a component named "step-one" like so:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class StepOne extends Component
{
    public function render()
    {
        return view('livewire.step-one');
    }
}
```

If you prefer, you can use the alternative syntax:

```blade
<livewire:is :component="$current" :key="$current" />
```

> [!warning]
> Don't forget to assign each child component a unique key. Although Livewire automatically generates a key for `<livewire:dynamic-child />` and `<livewire:is />`, that same key will apply to _all_ your child components, meaning subsequent renders will be skipped.
> 
> See [forcing a child component to re-render](#forcing-a-child-component-to-re-render) for a deeper understanding of how keys affect component rendering.

## Recursive components

Although rarely needed by most applications, Livewire components may be nested recursively, meaning a parent component renders itself as its child.

Imagine a survey which contains a `SurveyQuestion` component that can have sub-questions attached to itself:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Question;

class SurveyQuestion extends Component
{
    public Question $question;

    public function render()
    {
        return view('livewire.survey-question', [
            'subQuestions' => $this->question->subQuestions,
        ]);
    }
}
```

```blade
<div>
    Question: {{ $question->content }}

    @foreach ($subQuestions as $subQuestion)
        <livewire:survey-question :question="$subQuestion" :key="$subQuestion->id" />
    @endforeach
</div>
```

> [!warning]
> Of course, the standard rules of recursion apply to recursive components. Most importantly, you should have logic in your template to ensure the template doesn't recurse indefinitely. In the example above, if a `$subQuestion` contained the original question as its own `$subQuestion`, an infinite loop would occur.

## Forcing a child component to re-render

Behind the scenes, Livewire generates a key for each nested Livewire component in its template.

For example, consider the following nested `todo-count` component:

```blade
<div>
    <livewire:todo-count :$todos />
</div>
```

Livewire internally attaches a random string key to the component like so:

```blade
<div>
    <livewire:todo-count :$todos key="lska" />
</div>
```

When the parent component is rendering and encounters a child component like the above, it stores the key in a list of children attached to the parent:

```php
'children' => ['lska'],
```

Livewire uses this list for reference on subsequent renders in order to detect if a child component has already been rendered in a previous request. If it has already been rendered, the component is skipped. Remember, [nested components are islands](/docs/understanding-nesting#every-component-is-an-island). However, if the child key is not in the list, meaning it hasn't been rendered already, Livewire will create a new instance of the component and render it in place.

These nuances are all behind-the-scenes behavior that most users don't need to be aware of; however, the concept of setting a key on a child is a powerful tool for controlling child rendering.

Using this knowledge, if you want to force a component to re-render, you can simply change its key.

Below is an example where we might want to destroy and re-initialize the `todo-count` component if the `$todos` being passed to the component are changed:

```blade
<div>
    <livewire:todo-count :todos="$todos" :key="$todos->pluck('id')->join('-')" />
</div>
```

As you can see above, we are generating a dynamic `:key` string based on the content of `$todos`. This way, the `todo-count` component will render and exist as normal until the `$todos` themselves change. At that point, the component will be re-initialized entirely from scratch, and the old component will be discarded.
