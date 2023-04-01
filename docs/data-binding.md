---
Title: Data Binding
Order: 6
---

```toc
min_depth: 1
max_depth: 6
```

# Introduction

Livewire allows you to easily bind your component's properties to form inputs using the `wire:model` directive. This enables seamless communication between your frontend and backend, ensuring your component's state stays in sync with the user input. Let's see a basic example of data binding using the `wire:model` directive in a `CreatePost` component:

```php
class CreatePost extends Component
{
    public $title;

    public function save()
    {
        // Save the post...
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<!-- create-post.blade.php -->
<form wire:submit.prevent="save">
    <input type="text" wire:model="title">
    <button type="submit">Save</button>
</form>
```

# Live updating

Livewire's `wire:model` directive offers a powerful way to bind form input data to your component's properties. However, by default, `wire:model` defers updating the component's properties until an action, such as submitting a form or clicking a button, is triggered. This approach reduces the number of server requests made while the user interacts with the form, but it may not be suitable for all scenarios.

In some cases, you may want your component's properties to be updated in real-time as the user types or interacts with the form input. Livewire provides the `wire:model.live` directive for such scenarios, which allows for immediate updating of the component's properties on the server-side as the user interacts with the input.

Consider a `SearchPosts` component, where you want to display search results instantly as the user types in a query. By using the `wire:model.live` directive, you can achieve live updating of the search results without waiting for the user to trigger an action:

```php
class SearchPosts extends Component
{
    public $query;

    public function render()
    {
        $posts = Post::where('title', 'like', '%' . $this->query . '%')->get();

        return view('livewire.search-posts', [
            'posts' => $posts,
        ]);
    }
}
```

```html
<div>
    <input type="text" wire:model.live="query" placeholder="Search posts...">

    <ul>
        @foreach($posts as $post)
            <li>{{ $post->title }}</li>
        @endforeach
    </ul>
</div>
```

By using `wire:model.live`, the `query` property is updated on the server in real-time, ensuring the search results are displayed instantly as the user types their search query.

# Lazy updating

While `wire:model` is a powerful way to bind form input data to your component's properties, it might not always be ideal for every situation. By default, `wire:model` updates the component's properties when an action is triggered, such as submitting a form or clicking a button. However, there are scenarios where you might want to update the server only when an input's "change" event happens, such as when a user tabs away from an input or deselects a dropdown option.

In these cases, you can use the `wire:model.lazy` directive, which ensures that data updates are sent to the server only when the input's "change" event occurs. This is particularly useful for real-time form validation scenarios, where you want to validate input data as the user interacts with the form but not send updates on every keystroke.

Here's a simple example using a `CreatePost` component with a title input field:

```php
<!-- create-post.blade.php -->
<input type="text" wire:model.lazy="title">
```

# Debouncing input

Debouncing an input means delaying the processing of the input's change event until a certain period of time has passed without any additional user input. You can debounce an input using the `wire:model.debounce` directive. This is mostly useful with `wire:model.live` to avoid sending too many network requests:

```php
<!-- search-posts.blade.php -->
<input type="text" wire:model.live.debounce.300ms="query">
<!-- Display search results... -->
```

# Throttling input

Similar to debouncing, you can also throttle an input to limit the number of times the input's change event is processed within a certain period of time. Use the `wire:model.throttle` directive for throttling:

```php
<!-- search-posts.blade.php -->
<input type="text" wire:model.live.throttle.300ms="query">
<!-- Display search results... -->
```

# Binding to arrays

Livewire makes it easy to bind inputs to nested values in an array using dot notation. This feature is particularly useful when you need to manage a list of items, like a todo list or a questionnaire, where each item has its own set of properties.

Let's say you have a `TodoList` component that allows users to manage a list of tasks, where each task has a title and a completion status. In this example, we'll represent the tasks as an array of associative arrays, with each task having a `title` and a `completed` property.

Here's the `TodoList` component:

```php
use Livewire\Component;

class TodoList extends Component
{
    public $tasks = [];

    public function addTask()
    {
        $this->tasks[] = ['title' => '', 'completed' => false];
    }

    public function save()
    {
        // Save tasks to the database or perform other actions...
    }

    public function render()
    {
        return view('livewire.todo-list');
    }
}

```

In the `todo-list` Blade template, you can bind each task's `title` and `completed` properties to the corresponding input fields using dot notation:

```html
<div>
    @foreach ($tasks as $index => $task)
        <div>
            <input type="text" wire:model="tasks.{{ $index }}.title" placeholder="Task title">
            <input type="checkbox" wire:model="tasks.{{ $index }}.completed">
        </div>
    @endforeach

    <button wire:click="addTask">Add Task</button>
    <button wire:click="save">Save</button>
</div>
```

By using dot notation, such as `tasks.{{ $index }}.title`, Livewire can bind the input fields to the nested values in the `$tasks` array. As the user updates the task title or toggles the completion status, the corresponding values in the `$tasks` array are updated accordingly.

When you're ready to save the tasks or perform other actions, you can use the `save` method in your component, which has access to the updated `$tasks` array. This approach allows you to easily manage lists of items with nested properties in your Livewire components.

# Binding to child components

Livewire allows you to two-way bind data between a parent and a child component using the `#[Modelable]` attribute. By adding this attribute to a child component's property, you can use `wire:model` from the parent's Blade template to bind data to the child component.

Consider a `CreatePost` component with a nested child component responsible for handling a specific field in the create post form:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public $title;

    public function render()
    {
        return view('livewire.create-post');
    }
}

// Child component
use Livewire\Component;

class ChildComponent extends Component
{
    #[Modelable]
    public $fieldValue;

    public function render()
    {
        return view('livewire.child-component');
    }
}
```

In the `create-post` Blade template, you can bind data to the child component like this:

```html
<div>
    <input type="text" wire:model="title">

    <livewire:select-author wire:model="author" />
</div>
```

# Intercepting data updates

Livewire provides "updating" and "updated" lifecycle hooks that you can use to validate or perform additional actions before a property is set on the component. For example, you can validate the `title` field of a `CreatePost` component within these hooks:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public $title;

    public function updatingTitle($value)
    {
        // Validate the title value before updating
        // ...
    }

    public function updatedTitle($value)
    {
        // Perform additional actions after the title has been updated
        // ...
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

# Security concerns

It's important to remember that all properties in a Livewire component can be updated from the client-side, even if they don't have a `wire:model` in the template. Therefore, you should treat Livewire properties as untrusted user-input and always validate and authorize data accordingly.

Consider a component that is vulnerable to unauthorized data updates:

```php
use Livewire\Component;

class VulnerableComponent extends Component
{
    public $secret;

    public function render()
    {
        return view('livewire.vulnerable-component');
    }
}
```

To fix the vulnerability, you can add validation and authorization checks to ensure that the `secret` property can only be updated by authorized users:

```php
use Livewire\Component;

class SecureComponent extends Component
{
    public $secret;

    public function updatingSecret($value)
    {
        // Add authorization and validation checks
        // ...
    }

    public function render()
    {
        return view('livewire.secure-component');
    }
}
```

By implementing these security measures, you can ensure that your Livewire components handle user-input securely and prevent unauthorized access to sensitive data.

