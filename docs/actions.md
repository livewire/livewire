---
Title: Actions
Order: 5
---

```toc
min_depth: 1
max_depth: 6
```



Actions in Livewire are methods that can be called from your components' Blade templates to perform tasks, such as updating component properties, validating input, or interacting with databases. This documentation page will cover everything you need to know about working with actions in Livewire, including action parameters, lifecycle hooks, and more.

### Creating Actions

Actions are defined as methods within your Livewire component class. To create an action, add a public method to your component:

```php
use Livewire\Component;

class Counter extends Component
{
    public $count = 0;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.counter');
    }
}
```

To call the `increment` action from your component's Blade template, use the `wire:click` directive:

```php
<!-- counter.blade.php -->
<div>
    <button wire:click="increment">+</button>
    <p>{{ $count }}</p>
</div>
```

### Calling Actions with JavaScript

You can also call Livewire actions from JavaScript using the `window.livewire` object. This is useful when you need to trigger an action in response to a JavaScript event or interaction:

```html
<!-- counter.blade.php -->
<div>
    <button onclick="incrementFromJs()">+</button>
    <p>{{ $count }}</p>
</div>

<script>
    function incrementFromJs() {
        window.livewire.find('component-id').call('increment');
    }
</script>
```

Replace `'component-id'` with the actual component ID, which can be accessed using `$this->id` in your Livewire component.

### Action Parameters

You can pass parameters to actions from your Blade templates. This is useful for providing additional context or data to the action:

```php
use Livewire\Component;

class Items extends Component
{
    public $items = [];

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    // ...
}
```

To pass a parameter to the `addItem` action, include it in the `wire:click` directive:

```html
<!-- items.blade.php -->
<div>
    <button wire:click="addItem('NewItem')">Add Item</button>
    <!-- ... -->
</div>
```


## Event Listeners in Livewire

Livewire provides several built-in event listeners that allow you to handle user interactions and trigger actions. You can use event listeners like `wire:click`, `wire:keydown`, and others to respond to events in your components' Blade templates. Furthermore, Livewire allows you to listen for any JavaScript event by prefixing the event name with `wire:`.

### Available Event Listeners

Here are some common event listeners you can use with Livewire:

-   `wire:click`: Triggered when an element is clicked.
-   `wire:keydown`: Triggered when a key is pressed down.
-   `wire:keyup`: Triggered when a key is released.
-   `wire:submit`: Triggered when a form is submitted.
-   `wire:change`: Triggered when the value of an input element changes.
-   `wire:mouseenter`: Triggered when the mouse pointer enters an element.
-   `wire:mouseleave`: Triggered when the mouse pointer leaves an element.

### Keydown Modifiers

When using `wire:keydown`, you can add modifiers to listen for specific keys. Here's a table of common keydown modifiers:

| Modifier    | Description                     |
|-------------|---------------------------------|
| `.enter`    | Triggered when the Enter key is pressed. |
| `.tab`      | Triggered when the Tab key is pressed. |
| `.delete`   | Triggered when the Delete key is pressed. |
| `.esc`      | Triggered when the Escape key is pressed. |
| `.space`    | Triggered when the Space key is pressed. |
| `.up`       | Triggered when the Up arrow key is pressed. |
| `.down`     | Triggered when the Down arrow key is pressed. |
| `.left`     | Triggered when the Left arrow key is pressed. |
| `.right`    | Triggered when the Right arrow key is pressed. |

To use a keydown modifier, add the modifier after the `wire:keydown` directive:

```html
<input type="text" wire:keydown.enter="submitForm">
```

This input will trigger the `submitForm` action only when the Enter key is pressed.

### Custom Events

In addition to the built-in event listeners, you can listen for any JavaScript event by prefixing the event name with `wire:`. For example, to listen for a custom `myEvent` event, you can use:

```html
<button wire:myEvent="handleMyEvent">Trigger My Event</button>
```

To trigger this custom event, you can use JavaScript to dispatch the event on the element:

```js
const button = document.querySelector('button');
const myEvent = new CustomEvent('myEvent', { detail: { message: 'Hello, world!' } });
button.dispatchEvent(myEvent);
```

In your Livewire component, you can define the `handleMyEvent` action to handle the custom event:

```php
use Livewire\Component;

class ExampleComponent extends Component
{
    public function handleMyEvent($eventDetail)
    {
        // Handle the custom event and access the event detail
        $message = $eventDetail['message'];
    }

    // ...
}
```

This approach allows you to integrate Livewire with custom JavaScript events and handle them within your Livewire components.

## Using `$wire.call()` in Livewire

Livewire provides a convenient way to call actions in your components from JavaScript using the `$wire.call()` method. This method is particularly useful when you need to trigger a Livewire action in response to a JavaScript event or interaction.

### Calling Actions from JavaScript

To call a Livewire action from JavaScript, you can use the `$wire.call()` method on the `window.livewire` object. First, ensure that you have the `@livewireScripts` directive in your layout:

///

Next, in your component's Blade template, add an element with an onclick event that calls a JavaScript function:

```html
<!-- example-component.blade.php -->
<div>
    <button onclick="callActionFromJs()">Call Action</button>
</div>
```

Using `$wire.call()`, you can easily integrate Livewire actions with JavaScript events and interactions, providing a seamless way to work with both server-side and client-side logic in your applications.

## Security Concerns in Livewire Components

When working with Livewire components, it's important to be aware of potential security concerns. One key aspect to consider is that any public method on your component can be called from the client-side with any parameters a user wants. This makes it crucial to properly authorize input and mark private methods as protected.

### Authorizing Input

Since public methods in your component can be called from the client-side, you should always validate and sanitize any input that these methods receive. Livewire provides validation helpers that make it easy to ensure that the input you receive meets your requirements. You can use the `validate()` or `validateOnly()` methods in your component's actions to validate input data:

```php
use Livewire\Component;
use Illuminate\Validation\Rule;

class ExampleComponent extends Component
{
    public $email;

    public function submit()
    {
        $validatedData = $this->validate([
            'email' => ['required', 'email', Rule::unique('users')],
        ]);

        // Process the validated data
    }

    // ...
}
```

By validating the input data, you can ensure that only authorized and properly formatted data is processed by your component.

### Marking Private Methods as Protected

To prevent unauthorized access to private methods in your component, you should mark them as protected. By doing so, you restrict their visibility to the component and its subclasses, ensuring they cannot be called from the client-side:

```php
use Livewire\Component;

class ExampleComponent extends Component
{
    public function publicMethod()
    {
        // This method can be called from the client-side
    }

    protected function privateMethod()
    {
        // This method cannot be called from the client-side
    }

    // ...
}
```

By marking private methods as protected, you can safeguard sensitive operations and limit the actions that can be triggered from the client-side.

In conclusion, always be cautious when dealing with public methods in your Livewire components. Validate and authorize input to prevent unwanted behavior and mark private methods as protected to ensure they are not accessible from the client-side. Following these best practices will help you maintain a secure and reliable application.


## Magic Actions in Livewire Components

Livewire provides a set of "magic" actions that allow you to perform common tasks in your components without needing to define custom methods. These magic actions include `$set`, `$refresh`, and others, which can be used directly in your Blade templates or from JavaScript.

### `$set`

The `$set` magic action allows you to update a property in your Livewire component directly from the Blade template. To use `$set`, specify the property you want to update and the new value as arguments.

```html
<!-- example-component.blade.php -->
<div>
    <button wire:click="$set('count', 0)">Reset Count</button>
</div>
```

In this example, when the button is clicked, the `count` property in the component will be set to `0`.

### `$refresh`

The `$refresh` magic action forces a re-render of your Livewire component. This can be useful when you want to update the component's view without changing any property values.

```html
<!-- example-component.blade.php -->
<div>
    <button wire:click="$refresh">Refresh Component</button>
</div>
```

When the button is clicked, the component will re-render, allowing you to see the latest changes in the view.

### `$toggle`

The `$toggle` magic action is used to toggle the value of a boolean property in your Livewire component. It is particularly useful for handling show/hide behavior in your components.

```html
<!-- example-component.blade.php -->
<div>
    <button wire:click="$toggle('isVisible')">Toggle Visibility</button>
</div>
```

In this example, when the button is clicked, the `isVisible` property in the component will toggle between `true` and `false`.

### Using Magic Actions from JavaScript

You can also call magic actions from JavaScript using the `$wire` object. For example, to call the `$set` magic action from JavaScript:

```html
<script>
    function updateCountFromJs() {
        window.livewire.find('component-id').call('$set', 'count', 0);
    }
</script>

```

Replace `'component-id'` with the actual component ID, which can be accessed using `$this->id` in your Livewire component.

Magic actions in Livewire provide a convenient way to perform common tasks without the need to define custom methods. By using these actions, you can streamline your components and keep your code clean and efficient.

# Magic actions
There are a few magic actions you can use:
* $refresh
* $set
* $emit()
* $parent