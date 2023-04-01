---
Title: Events
Order: 6
---

```toc
min_depth: 1
max_depth: 6
```

# Introduction

Livewire offers a powerful event system that you can use to communicate between different components on the page. Because it uses browser events under the hood, you can also use it to communicate with Alpine components or even plain, vanilla JavaScript itself.

To trigger an event, you can use the `dispatch()` method from anywhere inside your component and listen for that event from any other component on the page.

# Dispatching events

To dispatch an event from a Livewire component, you can call the `dispatch()` method, passing it the event name and any additional data you want to send along with the event.

Here's an example of dispatching a "post-created" event from a `CreatePost` component:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function sendData()
    {
		$this->dispatch('post-created');
    }
}
```

In this example, when the `dispatch()` method is called, the `post-created` event will be dispatched, and every other component listening on the page will be notified.

You can also pass additional data along with the event like so:

```php
$this->dispatch('post-created', $post->title);
```


# Listening for events

To listen for an event in a Livewire component, add the `#[On]` attribute with the event name above the method you want to be called with the event is dispatched.

```php
use Livewire\Component;

class AnotherComponent extends Component
{
	#[On('post-created')]
    public function doSomething($title)
    {
		//
    }
}
```

Now when the `post-created` event is dispatched from another component on the page, this component will pick it up and a network request will be triggered and the `something` action will be run.

Notice, any additional data sent along with the event will be passed through as the first parameter to the `doSomething` method.

# Events in Alpine

Because Livewire events are actually plain browser events under the hood, you can use Alpine to listen for them, or even dispatch them itself.

## Listening for Livewire events in Alpine

To listen for a `post-created` event from Alpine, you would do something like the following:

```html
<div x-on:post-created=".."></div>
```

You can access data passed to Alpine using `$event.detail`:

```html
<div x-on:post-created="$event.detail"></div>
```

## Dispatching Livewire events from Alpine

Livewire's events system dovtails with Alpine's. So if you're familiar with dispatching custom events from Alpine, this should be completely familiar to you:

```html
<button @click="$dispatch('post-created')">...</button>
```

```html
<button @click="$dispatch('post-created', 'something')">...</button>
```

## Listening only for child events

Sometimes you may want to constrain a listener to only events dispatched by a child of the Livewire component. In these cases you can use:


## Event Modifiers

### `emitUp()`

The `emitUp()` method can be used to emit an event only to parent components in a nested structure. This can be helpful when you want to target a specific parent component without notifying other unrelated components:

```php
use Livewire\Component;

class ChildComponent extends Component
{
    public function sendDataToParent()
    {
        $data = 'Data for parent component';
        $this->emitUp('dataSentToParent', $data);
    }

    // ...
}
```

### `emitTo()`

The `emitTo()` method allows you to emit an event directly to a specific Livewire component by its class name or alias. This can be useful when you want to send data or trigger actions in a particular component without broadcasting the event to other components:

```php
use Livewire\Component;

class SenderComponent extends Component
{
    public function sendDataToReceiver()
    {
        $data = 'Data for ReceiverComponent';
        $this->emitTo('receiver-component', 'dataSentToReceiver', $data);
    }

    // ...
}
```

## Global Listeners

Sometimes, you may want to listen for events in JavaScript, outside of a Livewire component. To do this, you can use the `window.livewire.on()` method to create a global listener:

```js
<script>
    window.livewire.on('dataSent', (receivedData) => {
        console.log('Data received:', receivedData);
    });
</script>
```

Events in Livewire provide a flexible way to communicate between components, making it easy to create interactive and dynamic user interfaces. By emitting events and listening for them in other components, you can create a seamless flow of data and actions throughout your application.

## Dispatching Browser Events

Livewire also allows you to dispatch browser events. This can be useful when you want to trigger JavaScript actions based on Livewire events. To dispatch a browser event, use the `dispatchBrowserEvent()` method:

```php
use Livewire\Component;

class ExampleComponent extends Component
{
    public function triggerBrowserEvent()
    {
        $this->dispatchBrowserEvent('custom-event', ['message' => 'Hello, world!']);
    }

    // ...
}
```

In your JavaScript code, you can listen for the dispatched browser event:

```php
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('#example-component').addEventListener('custom-event', (event) => {
            console.log('Received message:', event.detail.message);
        });
    });
</script>
```

In this example, when the `triggerBrowserEvent()` method is called, a `custom-event` browser event will be dispatched, and the JavaScript event listener will receive the data sent with the event.

## Emitting Events from Blade Templates

### `$emit`

You can emit events directly from your Blade templates using the `$emit` JavaScript function. This is useful when you want to trigger an event from a user interaction, such as a button click:

```php
<!-- example-component.blade.php -->
<div>
    <button x-on:click="$wire.emit('dataSent', 'Data from Blade')">Send Data</button>
</div>

```

In this example, when the button is clicked, the `dataSent` event will be emitted with the specified data.

### `$emitUp` and `$emitSelf`

Similarly, you can use the `$emitUp` and `$emitSelf` JavaScript functions to emit events that target specific components:

-   `$emitUp`: Emits the event only to parent components in a nested structure.
-   `$emitSelf`: Emits the event only to the current component.

```html
<!-- child-component.blade.php -->
<div>
    <button x-on:click="$wire.emitUp('dataSentToParent', 'Data for parent component')">Send Data to Parent</button>
    <button x-on:click="$wire.emitSelf('internalEvent', 'Internal data')">Trigger Internal Event</button>
</div>
```

In this example, the `dataSentToParent` event will be emitted to parent components when the first button is clicked, and the `internalEvent` will be emitted only to the current component when the second button is clicked.

Events in Livewire provide a flexible way to communicate between components, making it easy to create interactive and dynamic user interfaces. By emitting events and listening for them in other components, as well as dispatching browser events and emitting events from Blade templates, you can create a seamless flow of data and actions throughout your application.

### Testing Emitted Events

To test events emitted by your component, use the `assertEmitted()` method in your Livewire test. This method checks that a specific event has been emitted during the component's lifecycle:

```php
use Livewire\Livewire;
use App\Http\Livewire\ExampleComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_emits_data_sent_event()
    {
        Livewire::test(ExampleComponent::class)
            ->call('sendData')
            ->assertEmitted('dataSent', 'Some data to send');
    }
}
```

In this example, the test ensures that the `dataSent` event is emitted with the specified data when the `sendData()` method is called on the `ExampleComponent`.

### Testing Event Listeners

To test event listeners, you can emit events from the test environment and assert that the expected actions are performed in response to the event:

```php
use Livewire\Livewire;
use App\Http\Livewire\AnotherComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnotherComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_handles_data_sent_event()
    {
        Livewire::test(AnotherComponent::class)
            ->emit('dataSent', 'Received data')
            ->assertSee('Received data');
    }
}
```

In this example, the test emits the `dataSent` event with the specified data and checks that the `AnotherComponent` properly handles the event and displays the received data.

### Testing Dispatched Browser Events

To test dispatched browser events, use the `assertDispatchedBrowserEvent()` method in your Livewire test. This method checks that a specific browser event has been dispatched during the component's lifecycle:

```php
use Livewire\Livewire;
use App\Http\Livewire\ExampleComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_dispatches_custom_browser_event()
    {
        Livewire::test(ExampleComponent::class)
            ->call('triggerBrowserEvent')
            ->assertDispatchedBrowserEvent('custom-event', ['message' => 'Hello, world!']);
    }
}
```

In this example, the test ensures that the `custom-event` browser event is dispatched with the specified data when the `triggerBrowserEvent()` method is called on the `ExampleComponent`.

Testing events is a crucial aspect of ensuring that your Livewire components behave as expected. By using the provided testing methods, you can easily verify that events are emitted, listeners are functioning properly, and browser events are dispatched as intended.

## Security Concerns

When working with events in Livewire, it is essential to be aware of potential security risks. Since any listener can be called from the front-end and passed any parameters, it's crucial to validate and authorize input data to protect your application from potential security threats.

### Validating Input Data

To validate the input data received in your event listeners, you can use the same validation techniques as you would in a typical Laravel application. For example, you can use the `validate()` method to ensure that the received data matches the expected structure:

```php
use Livewire\Component;

class AnotherComponent extends Component
{
    protected $listeners = ['dataSent' => 'handleData'];

    public function handleData($receivedData)
    {
        $validatedData = $this->validate([
            'receivedData' => 'required|string|max:255',
        ], ['receivedData' => $receivedData]);

        // Process the validated data received from the event
    }

    // ...
}
```

In this example, the `handleData()` method validates the received data using the `validate()` method, ensuring that it is a string with a maximum length of 255 characters.

### Authorizing Events

It's also crucial to authorize events to ensure that only users with the appropriate permissions can perform specific actions. You can use Laravel's built-in authorization features, such as gates and policies, to protect your event listeners:

```php
use Livewire\Component;
use Illuminate\Support\Facades\Gate;

class AnotherComponent extends Component
{
    protected $listeners = ['dataSent' => 'handleData'];

    public function handleData($receivedData)
    {
        // Check if the user is authorized to handle the data
        if (Gate::denies('handle-data', $receivedData)) {
            abort(403, 'You are not authorized to handle this data.');
        }

        // Process the data received from the event
    }

    // ...
}
```

In this example, the `handleData()` method checks if the user is authorized to handle the received data using the `Gate::denies()` method. If the user is not authorized, a 403 Forbidden response is returned.

By validating and authorizing input data, you can mitigate potential security risks and ensure that your Livewire components remain secure and reliable.
