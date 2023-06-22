
Nesting components is one of Livewire's most powerful features because it allows you to re-use and encapsulate specific behavior within your components. However, because Livewire components exist across the stack (both frontend and backend), nesting them is more nuanced than nesting a Vue, React, or Blade component.

> [!warning] You might not need a Livewire component
> Before you extract a portion of your template into a nested Livewire component, ask yourself: Does this need to be "live"? If the answer is no, it's recommended that you create a simple [Blade component](https://laravel.com/docs/10.x/blade#components) instead. Only nest a Livewire component if the component benefits from Livewire's dynamic nature or if there is a direct performance benefit.

For more information on the performance, usage implications, and constraints of nesting Livewire components: [Read this in-depth article on the subject](/docs/understanding-nesting)

## Nesting a component

To nest a Livewire component, include it in the parent component's Blade view. Here's an example of a `Dashboard` parent component with a nested `TodoList` component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard');
    }
}
```

```html
<div>
    <h1>Dashboard</h1>

    <livewire:todo-list /> <!-- [tl! highlight] -->
</div>
```

Simple enough. On the first render, `Dashboard` will encounter `<livewire:todo-list />` and render it in place. On a subsequent network request to `Dashboard`, the nested `todo-list` component will be skipped because it is now its own independent component on the page because [nested components are islands](/docs/understanding-nesting).

## Passing props to children

Passing data from a parent component into a child is straightforward. It's much the same as passing props into a standard [Blade component](https://laravel.com/docs/10.x/blade#components).

Here's an example of a `TodoList` component that passes a collection of `$todos` into a child component called `TodoCount`:

```php
<?php

namespace App\Http\Livewire;

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

```html
<div>
    <livewire:todo-count :todos="$todos" />

    <!-- ... -->
</div>
```

As you can see, we are passing `$todos` into `todo-count` with the syntax: `:todos= "$todos"`.

Now that `$todos` has been passed in, you can receive that data through the child component's `mount()` method:

```php
<?php

namespace App\Http\Livewire;

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
            'count' => $this->todos->count();
        ]);
    }
}
```

> [!tip] Use the #[Prop] attribute as a shorter alternative
> If the `mount()` method in above example feels like redundant boilerplate code to you, you can instead use [Livewire's #[Prop] attribute](/docs/nesting#the-prop-attribute) as a shorthand:
> ```php
> #[Prop] // [tl! highlight]
> public $todos;
> ```

### Passing static props

In the last example, we passed a `dynamic` prop expression, meaning a PHP expression like so:

```html
<livewire:todo-count :todos="$todos" />
```

Sometimes, you may want to pass a static value such as a string; in those cases, you would leave off the colon at the beginning of the statement:

```html
<livewire:todo-count :todos="$todos" label="Todo Count:" />
```

You can also pass in boolean values by only including the key:

```html
<livewire:todo-count :todos="$todos" inline />
```

Now an `$inline` variable will be passed to `mount()` with the value: "true".

> [!tip] 
> If the name of the the property and variable you are passing into the child component match, you can use the following shorter, alternative syntax:
> 
> ```html
> <livewire:todo-count :todos="$todos" /> <!-- [tl! remove] -->
> 
> <livewire:todo-count :$todos /> <!-- [tl! add] -->
> ```

## Rendering children in a loop

When rendering a child component inside a loop, including a unique "key" for each iteration is necessary.

These keys are how Livewire keeps track of each component on subsequent renders; particularly if a component has already been rendered or if multiple components have been re-arranged.

You can specify the key by declaring a `:key` prop on the child component:

```html
<div>
    <h1>Todos</h1>

    @foreach ($todos as $todo)
        <livewire:todo-item :todo="$todo" :key="$todo->id" />
    @endforeach
</div>
```

As you can see, each child component in the above iteration will have a unique key set to the ID of each `$todo`. This ensures the key will be unique and tracked if the todos are re-ordered.

> [!warning] Keys aren't optional
> If you have used frontend frameworks like Vue or Alpine, you are familiar with adding a key to a nested element in a loop. However, in those frameworks, a key isn't _mandatory_, meaning the items will render, but a re-order might not be tracked properly. Livewire, however, relies more heavily on this key and, as such, will behave errantly without them.

## Shorthand syntaxes

Livewire provides a few helpful shorthand syntaxes to help cut down on repetitive code related to passing in props.

### The `#[Prop]` attribute

The first is a `#[Prop]` attribute that allows you to skip the `mount()` method and signals Livewire to assign the property automatically.

For example, if the following `todo-item` component is rendered on the page and `$todo` is passed in:

```html
<livewire:todo-item :todo="$todo" :key="$todo->id" />
```

You can add the `#[Prop]` attribute above the `$todo` property and Livewire will automatically set it to the `$todo` prop being passed in.

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoItem extends Component
{
    #[Prop]
    public Todo $todo;

    // ...
}
```

Often, this removes the need for `mount()` entirely, helping to reduce repetitive boilerplate in your application.

### Shortened attribute syntax

When passing PHP variables into a component, the variable name and the prop name are often the same. To avoid writing the name twice, once for the key and once for the value, Livewire allows you to prefix the variable with a colon and reference it directly instead:

```html
<livewire:todo-item :todo="$todo" /> <!-- [tl! remove] -->

<livewire:todo-item :$todo /> <!-- [tl! add] -->
```

## Reactive props

Developers new to Livewire expect that props are "reactive" by default. Meaning that when a parent changes the value of a prop being passed into a child component, the child component will automatically be updated.

However, this is not the case. By default, [every component is an island](/docs/understanding-nesting), meaning when an update is triggered on the parent and a network request is sent, only the parent component's state is sent to the server to re-render, not the child component's. The reason is to only send the minimal amount of data back and forth between the server and client, making updates as performant as possible.

If you want or need a prop to be reactive, you can easily opt into this behavior using the `#[Prop(reactive: true)]` attribute parameter.

For example, below is the template of a parent `TodoList` component. Inside, it's rendering a `TodoCount` component and passing in the current list of todos:

```html
<div>
    <h1>Todos:</h1>

    <livewire:todo-count :$todos />

    <!-- ... -->
</div>
```

Now, if you add `#[Prop(reactive: true)]` to the `$todos` prop in `TodoCount` like below, when a todo is added or removed inside the parent component, `TodoCount` will update automatically:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoCount extends Component
{
    #[Prop(reactive: true)]
    public $todos;

    public function render()
    {
        return view('livewire.todo-count', [
            'count' => $this->todos->count();
        ]);
    }
}
```

Reactive properties are an incredibly powerful feature, making Livewire more similar to working with a frontend component library like Vue or React. Again, it is important to understand the performance implications and only add `reactive: true` when it makes sense for your scenario.

## Binding to child data using `wire:model`

Another powerful pattern for sharing state between parent and child components is being able to use `wire:model` directly on a child component.

A common example of this need is wrapping an input element into a dedicated Livewire component, but still accessing its state in the parent component.

Here's an example of a parent `TodoList` component, with a `$todo` property to track the current todo about to be added by a user:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoList extends Component
{
    public $todo = '';

    public function add()
    {
        Todo::create([
            'content' => $this->reset('todo'),
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

As you can see, in the `TodoList` template, `wire:model` is being used to bind the `$todo` property directly to a nested `TodoInput` component:

```html
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

Livewire provides a `#[Modelable]` attribute you can add to any property to make it _modelable_ from a parent component.

Below is the `TodoInput` component with `#[Modelable]` being added above to the `$value` property to signal to Livewire that if `wire:model` is declared on it by a parent, it should use this property to bind to:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class TodoInput extends Component
{
    #[Modelable]
    public $value = '';

    public function render()
    {
        return view('livewire.todo-input');
    }
}
```

```html
<div>
    <input type="text" wire:model="value" >
</div>
```

The parent `TodoList` component can treat `TodoInput` like any other input element and bind directly to its value using `wire:model`.

## Listening for events from children

Another powerful parent-child component communication technique is Livewire's event system.

Livewire allows you to dispatch an event on the server or client that can be listened to from other components.

You can [read the complete documentation on Livewire's event system here](/docs/events), but below is a simple example of using an event to trigger an update in a parent component.

Consider a `TodoList` component with functionality to show and remove todos:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoList extends Component
{
    public function remove($todoId)
    {
        $todo = Todo::find($todoId);

        if (! Auth::user()->can('update', $todo)) {
            abort(403);
        } 

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

```html
<div>
    @foreach ($todos as $todo)
        <livewire:todo-item :$todo :key="$todo->id" />
    @endforeach
</div>
```

To be able to call `remove()` from inside the child `TodoItem` components, you can add an event listener to `TodoList`  by using the `#[On]` attribute like so:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoList extends Component
{
    #[On('remove-todo')] // [tl! highlight]
    public function remove($todoId)
    {
        $todo = Todo::find($todoId);

        if (! Auth::user()->can('update', $todo)) {
            abort(403);
        } 

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

Now, you can dispatch the "remove-todo" event from the `TodoList` child component like so:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoItem extends Component
{
    #[Prop]
    public Todo $todo;

    public function remove()
    {
        $this->dispatch('remove-todo', $this->todo->id); // [tl! highlight]
    }

    public function render()
    {
        return view('livewire.todo-item');
    }
}
```

```html
<div>
    <span>{{ $todo->content }}</span>

    <button wire:click="remove">Remove</button>
</div>
```

When the "Remove" button is clicked inside a `TodoItem`, the parent `TodoList` component will pick it up and perform the todo removal.

After the todo is removed in the parent, the list of todos will change, the list will be re-rendered, and the child that dispatched the "remove-todo" event will be removed from the page.

### Improving performance by dispatching client-side

Though the above example works, it takes two network requests to perform a single action:

1. The first is a network request from the `TodoItem` component to trigger the `remove` action, dispatching the "remove-todo" event.
2. The second is after the actual "remove-todo" event dispatches client-side and is picked up by `TodoList` to call its `remove` action.

You can avoid the first request entirely by dispatching the "remove-todo" event directly client-side.

Here's the updated `TodoItem` component without the server-side dispatch:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Todo;

class TodoItem extends Component
{
    #[Prop]
    public Todo $todo;

    public function render()
    {
        return view('livewire.todo-item');
    }
}
```

```html
<div>
    <span>{{ $todo->content }}</span>

    <button wire:click="$dispatch('remove-todo', {{ $todo->id }})">Remove</button>
</div>
```

As a rule of thumb, always prefer dispatching client-side if you can.

## Directly accessing the parent from the child

Event communication adds a layer of indirection. A parent can listen for an event that never gets dispatched from a child, and a child can dispatch an event that is never picked up.

This is a quality that is sometimes desired and sometimes not.

If you are using events (like in the above scenario) to communicate directly between parents and children, you might prefer to call a parent action directly from the child.

Livewire allows you to do this by providing a magic `$parent` variable in your Blade template that you can use to access actions and properties directly from the child. Here's the above `TodoItem`  template rewritten to call the `remove()` action directly on the parent using `$parent`:

```html
<div>
    <span>{{ $todo->content }}</span>

    <button wire:click="$parent.remove({{ $todo->id }})">Remove</button>
</div>
```

These are a few of the ways to communicate back and forth between parent and child components. Understanding their tradeoffs enables you to make more informed decisions about which to use and when.

## Dynamic child components

Livewire allows you to choose which child component to render at run-time by offering a `<livewire:dynamic-component` syntax:

```html
<livewire:dynamic-component :is="$current" />
```

This feature is useful for lots of different applications, but here's a specific example of rendering different steps in a multi-step form using a dynamic component:

```php
<?php

namespace App\Http\Livewire;

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
        $currentIndex = array_search($this->steps, $this->current);

        $this->current = $this->steps[$currentIndex + 1];
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}
```

```html
<div>
    <livewire:dynamic-component :is="$current" />

    <button wire:click="next">Next</button>
</div>
```

Now, if the `Steps` component has `$current` set to "step-1", Livewire will look for and render a component called "step-one" like so:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class StepOne extends Component
{
    public function render()
    {
        return view('livewire.step-one');
    }
}
```

## Recursive components

Another lesser-utilized feature worth noting is the ability to nest components recursively. Meaning a parent component renders itself as its child.

Here's an example of a `Question` component that might be inside a survey of some sort that can have sub-questions attached to itself:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Question;

class Question extends Component
{
    public Question $question;

    public function render()
    {
        return view('livewire.question', [
            'subQuestions' => $this->question->subQuestions,
        ]);
    }
}
```

```html
<div>
    Question: {{ $question->content }}

    @foreach ($subQuestions as $subQuestion)
        <livewire:question :question="$subQuestion" />
    @endforeaach
</div>
```

> [!warning]
> Of course, the standard rules of recursion apply here. Most importantly, you have control logic in your template to ensure the template doesn't recurse indefinitely. In our case above, if a `$subQuestion` contained the original question as its own `$subQuestion`, there would be an infinite loop.

## Forcing a child component to re-render

Behind the scenes, Livewire generates a key for each nested Livewire component in its template.

For example, take the following nested `todo-count` component:

```html
<div>
    <livewire:todo-count :todos="$todos" />
</div>
```

Livewire internally attaches a random string key like so:

```html
<div>
    <livewire:todo-count :todos="$todos" key="lska" />
</div>
```

When the parent is rendering and encounters a child component like the above, it stores the key in a list of children attached to the parent like so:

```php
'children' => ['lska'],
```

This list is used for reference on subsequent renders to detect if a child component has already been rendered in a previous request. If it HAS already been rendered, the component is skipped; remember, [nested components are islands](). If, however, the child key ISN'T in the list, meaning it HASN'T been rendered already, Livewire will create a new instance of the component and render it in place.

This is all behind-the-scenes behavior that most users don't need to be aware of; however, the concept of setting a key on a child is a powerful tool for controlling child rendering.

Using this knowledge, if you want to force a component to re-render at some point, you can simply change its key.

Here's an example where we might want to destroy and re-initialize the `todo-count` component if the `$todos` being passed in change:

```html
<div>
    <livewire:todo-count :todos="$todos" :key="$todos->pluck('id')->join('-')" />
</div>
```

As you can see above, we are generating a dynamic `:key` string based on `$todos` contents. This way, the `todo-count` component will render and exist as normal until the `$todos` themselves change. At that point, the component will be re-initialized entirely from scratch, and the old one will be thrown away.

Although this is a lesser-known technique, and at first glance, may feel "hacky", it isn't. Consider that you use this same mechanism to help Livewire keep track of child components in a loop and make sure they aren't re-initialized unintentionally. For example:

```html
<div>
    @foreach ($todos as $todo)
        <livewire:todo-item :todo="$todo" :key="$todo->id" />
    @endforeach
</div>
```

In our case, we are using the same mechanism, just reversing the intent. Instead of using a dynamic `:key` to ensure that components aren't re-rendered, we're using it to CAUSE them to re-render.
