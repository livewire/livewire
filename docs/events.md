Livewire offers a powerful event system that you can use to communicate between different components on the page. Because it uses browser events under the hood, you can also use it to communicate with Alpine components or even plain, vanilla JavaScript itself.

To trigger an event, you can use the `dispatch()` method from anywhere inside your component and listen for that event from any other component on the page.

# Dispatching events

To dispatch an event from a Livewire component, you can call the `dispatch()` method, passing it the event name and any additional data you want to send along with the event.

Here's an example of dispatching a "post-created" event from a `CreatePost` component:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function save()
    {
		// ...

		$this->dispatch('post-created');
    }
}
```

In this example, when the `dispatch()` method is called, the `post-created` event will be dispatched, and every other component listening on the page will be notified.

You can also pass additional data along with the event, by passing it as the second parameter:

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

Now when the `post-created` event is dispatched from `CreatePost`, this component will pick it up and a network request will be triggered and the `something` will be run.

Notice, additional data sent along with the event will be passed through as the first parameter to the `doSomething` method.

# Events in Alpine

Because Livewire events are actually plain browser events under the hood, you can use Alpine to listen for them, or even dispatch them as well.

## Listening for Livewire events in Alpine

To listen for a `post-created` event from Alpine, you would do something like the following:

```html
<div x-on:post-created=".."></div>
```

The above snippet would listen for a Livewire component that dispatched the `post-created` event.

It's important to note that it will respond to this event if the Livewire component is a child of this element.

To listen for any Livewire component on the page dispatching `post-created`, you can add `.window` to the listener to listen globally:

```html
<div x-on:post-created.window=".."></div>
```

If you want to access additional data sent along with the event, you can do so using `$event.detail`:

```html
<div x-on:post-created="$event.detail"></div>
```

You can read more about [listening for events in Alpine here.](https://alpinejs.dev/directives/on)

## Dispatching Livewire events from Alpine

Any event dispatched from Alpine is capable of being picked up by a Livewire component.

Let's look at what it would look like to dispatch the `post-created` event from Alpine itself:

```html
<button @click="$dispatch('post-created')">...</button>
```

Just like the Livewire method, you can pass additional data along with the event by passing it as the second paramter:

```html
<button @click="$dispatch('post-created', 'Post Title')">...</button>
```

You can read more about [dispatching events in Alpine here.](https://alpinejs.dev/magics/dispatch)

## Listening for events from children only

By default, when you register a Livewire event listener using `#[On]`, it will listen for that event to be dispatched anywhere on the page. (It does this by listening for the event on the `window` object)

Sometimes you may want to scope an event listener to only listen for event dispatches from child components rendered somewhere within the listening component.

To listen for children dispatches only, you can pass a second argument to `#[On]` called `fromChildren` and set it to true:

```php
use Livewire\Component;

class AnotherComponent extends Component
{
	#[On('post-created', fromChildren: true)]
    public function doSomething($title)
    {
		//
    }
}
```

Now, the `doSomething` action will only be triggered when a child component dispatches `post-created`.

> [!tip] You might not need events
> If you are using events to call behavior on a parent from a child directly, you can instead call the action directly from the child using `$parent` in your Blade template. [Read more about this technique here.](todo)

## Dispatching directly to another component

If you want to use events for communicating directly between two components on the page you can use the `dispatch()->to()` modifier.

Below is an example of the `CreatePost` component dispatching the `post-created` event directly to the `Foo` component, skipping any other components listening for that specific event:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function save()
    {
		// ...

		$this->dispatch('post-created')->to(Foo::class);
    }
}
```

## Dispatching a component event to itself

You can restrict an event to only dispatching on the component it was triggered from like so:

```php
use Livewire\Component;

class CreatePost extends Component
{
    public function save()
    {
		// ...

		$this->dispatch('post-created')->self();
    }
}
```

Now, the above component is both dispatching and listening for `post-created` on itself.

## Dispatching events from Blade Templates

You can dispatch events directly from your Blade templates using the `$dispatch` JavaScript function. This is useful when you want to trigger an event from a user interaction, such as a button click:

```html
<div>
    <button wire:click="$dispatch('dataSent', 'Data from Blade')">Send Data</button>
</div>

```

In this example, when the button is clicked, the `dataSent` event will be emitted with the specified data.

## Testing dispatched events

To test events emitted by your component, use the `assertDispatched()` method in your Livewire test. This method checks that a specific event has been dispatched during the component's lifecycle:

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
        Livewire::test(CreatePost::class)
            ->call('save')
            ->assertDispatched('post-created');
    }
}
```

In this example, the test ensures that the `post-created` event is dispatched with the specified data when the `save()` method is called on the `CreatePost`.

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
            ->dispatch('post-created')
            ->assertSee('Received data');
    }
}
```

In this example, the test emits the `dataSent` event with the specified data and checks that the `AnotherComponent` properly handles the event and displays the received data.

## Security Concerns

When working with events in Livewire, it is essential to be aware of potential security risks. Since any listener can be called from the front-end and passed any parameters, it's crucial to validate and authorize input data to protect your application from potential security threats.

### Authorizing input data

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

## Real-time events w/ Laravel Echo

@todo