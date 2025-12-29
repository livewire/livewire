
## Using JavaScript in Livewire components

Livewire and Alpine provide plenty of utilities for building dynamic components directly in your HTML, however, there are times when it's helpful to break out of the HTML and execute plain JavaScript for your component.

> [!warning] Class-based components need the @@script directive
> The examples on this page use bare `<script>` tags, which work for **single-file** and **multi-file** components. If you're using **class-based components** (where the Blade view is in a separate file from the PHP class), you must wrap your script tags with the `@@script` directive:
>
> ```blade
> @@script
> <script>
>     // Your JavaScript here...
> </script>
> @@endscript
> ```
>
> This tells Livewire to handle the execution timing properly for class-based components.

### Executing scripts

You can add `<script>` tags directly inside your component template to execute JavaScript when the component loads.

Because these scripts are handled by Livewire, they execute at the perfect time—after the page has loaded, but before the Livewire component renders. This means you no longer need to wrap your scripts in `document.addEventListener('...')` to load them properly.

This also means that lazily or conditionally loaded Livewire components are still able to execute JavaScript after the page has initialized.

```blade
<div>
    ...
</div>

<script>
    // This Javascript will get executed every time this component is loaded onto the page...
</script>
```

Here's a more full example where you can do something like register a JavaScript action that is used in your Livewire component.

```blade
<div>
    <button wire:click="$js.increment">+</button>
</div>

<script>
    this.$js.increment = () => {
        console.log('increment')
    }
</script>
```

To learn more about JavaScript actions, [visit the actions documentation](/docs/4.x/actions#javascript-actions).

### Using `$wire` from scripts

When you add `<script>` tags inside your component, you automatically have access to your Livewire component's `$wire` object.

Here's an example of using a simple `setInterval` to refresh the component every 2 seconds (You could easily do this with [`wire:poll`](/docs/4.x/wire-poll), but it's a simple way to demonstrate the point):

```blade
<script>
    setInterval(() => {
        $wire.$refresh()
    }, 2000)
</script>
```

## The `$wire` object

The `$wire` object is your JavaScript interface to your Livewire component. It provides access to component properties, methods, and utilities for interacting with the server.

Inside component scripts, you can use `$wire` directly. Here are the most essential methods you'll use:

```js
// Access and modify properties
$wire.count
$wire.count = 5
$wire.$set('count', 5)

// Call component methods
$wire.save()
$wire.delete(postId)

// Refresh the component
$wire.$refresh()

// Dispatch events
$wire.$dispatch('post-created', { postId: 2 })

// Listen for events
$wire.$on('post-created', (event) => {
    console.log(event.postId)
})

// Access the root element
$wire.$el.querySelector('.modal')
```

> [!tip] Complete $wire reference
> For a comprehensive list of all `$wire` methods and properties, see the [$wire reference](#the-wire-object) at the bottom of this page.

## Loading assets

Component `<script>` tags are useful for executing a bit of JavaScript every time a Livewire component loads, however, there are times you might want to load entire script and style assets on the page along with the component.

Here is an example of using `@assets` to load a date picker library called [Pikaday](https://github.com/Pikaday/Pikaday) and initialize it inside your component:

```blade
<div>
    <input type="text" data-picker>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js" defer></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endassets

<script>
    new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
</script>
```

When this component loads, Livewire will make sure any `@assets` are loaded on that page before evaluating scripts. In addition, it will ensure the provided `@assets` are only loaded once per page no matter how many instances of this component there are, unlike component scripts, which will evaluate for every component instance on the page.

## Interceptors

Intercept Livewire requests at three levels: **action** (most granular), **message** (per-component), and **request** (HTTP level).

```js
// Action interceptors - fire for each action call
$wire.intercept(callback)                     // All actions on this component
$wire.intercept('save', callback)             // Only 'save' action
Livewire.interceptAction(callback)            // Global (all components)

// Message interceptors - fire for each component message
$wire.interceptMessage(callback)              // Messages from this component
$wire.interceptMessage('save', callback)      // Only when message contains 'save'
Livewire.interceptMessage(callback)           // Global (all components)

// Request interceptors - fire for each HTTP request
$wire.interceptRequest(callback)              // Requests involving this component
$wire.interceptRequest('save', callback)      // Only when request contains 'save'
Livewire.interceptRequest(callback)           // Global (all requests)
```

All interceptors return an unsubscribe function:

```js
let unsubscribe = $wire.intercept(callback)
unsubscribe() // Remove the interceptor
```

### Action interceptors

Action interceptors are the most granular. They fire for each method call on a component.

```js
$wire.intercept(({ action, onSend, onCancel, onSuccess, onError, onFailure, onFinish }) => {
    // action.name        - Method name ('save', '$refresh', etc.)
    // action.params      - Method parameters
    // action.component   - Component instance
    // action.cancel()    - Cancel this action

    onSend(({ call }) => {
        // call: { method, params, metadata }
    })

    onCancel(() => {})

    onSuccess((result) => {
        // result: Return value from PHP method
    })

    onError(({ response, body, preventDefault }) => {
        preventDefault() // Prevent error modal
    })

    onFailure(({ error }) => {
        // error: Network error
    })

    onFinish(() => {
        // Always runs (success, error, failure, or cancel)
    })
})
```

### Message interceptors

Message interceptors fire for each component update. A message contains one or more actions.

```js
$wire.interceptMessage(({ message, cancel, onSend, onCancel, onSuccess, onError, onFailure, onStream, onFinish }) => {
    // message.component  - Component instance
    // message.actions    - Set of actions in this message
    // cancel()           - Cancel this message

    onSend(({ payload }) => {
        // payload: { snapshot, updates, calls }
    })

    onCancel(() => {})

    onSuccess(({ payload, onSync, onEffect, onMorph, onRender }) => {
        // payload: { snapshot, effects }

        onSync(() => {})    // After state synced
        onEffect(() => {})  // After effects processed
        onMorph(() => {})   // After DOM morphed
        onRender(() => {})  // After render complete
    })

    onError(({ response, body, preventDefault }) => {
        preventDefault() // Prevent error modal
    })

    onFailure(({ error }) => {})

    onStream(({ json }) => {
        // json: Parsed stream chunk
    })

    onFinish(() => {})
})
```

### Request interceptors

Request interceptors fire for each HTTP request. A request may contain messages from multiple components.

```js
$wire.interceptRequest(({ request, onSend, onCancel, onSuccess, onError, onFailure, onResponse, onParsed, onStream, onRedirect, onDump, onFinish }) => {
    // request.messages   - Set of messages in this request
    // request.cancel()   - Cancel this request

    onSend(({ responsePromise }) => {})

    onCancel(() => {})

    onResponse(({ response }) => {
        // response: Fetch Response (before body read)
    })

    onParsed(({ response, body }) => {
        // body: Response body as string
    })

    onSuccess(({ response, body, json }) => {})

    onError(({ response, body, preventDefault }) => {
        preventDefault() // Prevent error modal
    })

    onFailure(({ error }) => {})

    onStream(({ response }) => {})

    onRedirect(({ url, preventDefault }) => {
        preventDefault() // Prevent redirect
    })

    onDump(({ html, preventDefault }) => {
        preventDefault() // Prevent dump modal
    })

    onFinish(() => {})
})
```

### Examples

**Loading state for a component:**

```blade
<script>
    $wire.intercept(({ onSend, onFinish }) => {
        onSend(() => $wire.$el.classList.add('opacity-50'))
        onFinish(() => $wire.$el.classList.remove('opacity-50'))
    })
</script>
```

**Confirm before delete:**

```blade
<script>
    $wire.intercept('delete', ({ action }) => {
        if (!confirm('Are you sure?')) {
            action.cancel()
        }
    })
</script>
```

**Global session expiration handling:**

```js
Livewire.interceptRequest(({ onError }) => {
    onError(({ response, preventDefault }) => {
        if (response.status === 419) {
            preventDefault()
            if (confirm('Session expired. Refresh?')) {
                window.location.reload()
            }
        }
    })
})
```

**Action-specific success notification:**

```blade
<script>
    $wire.intercept('save', ({ onSuccess, onError }) => {
        onSuccess(() => showToast('Saved!'))
        onError(() => showToast('Failed to save', 'error'))
    })
</script>
```

## Global Livewire events

Livewire dispatches two helpful browser events for you to register any custom extension points from outside scripts:

```html
<script>
    document.addEventListener('livewire:init', () => {
        // Runs after Livewire is loaded but before it's initialized
        // on the page...
    })

    document.addEventListener('livewire:initialized', () => {
        // Runs immediately after Livewire has finished initializing
        // on the page...
    })
</script>
```

> [!info]
> It is often beneficial to register any [custom directives](#registering-custom-directives) or [lifecycle hooks](#javascript-hooks) inside of `livewire:init` so that they are available before Livewire begins initializing on the page.

## The `Livewire` global object

Livewire's global object is the best starting point for interacting with Livewire from external scripts.

You can access the global `Livewire` JavaScript object on `window` from anywhere inside your client-side code.

It is often helpful to use `window.Livewire` inside a `livewire:init` event listener

### Accessing components

You can use the following methods to access specific Livewire components loaded on the current page:

```js
// Retrieve the $wire object for the first component on the page...
let component = Livewire.first()

// Retrieve a given component's `$wire` object by its ID...
let component = Livewire.find(id)

// Retrieve an array of component `$wire` objects by name...
let components = Livewire.getByName(name)

// Retrieve $wire objects for every component on the page...
let components = Livewire.all()
```

> [!info]
> Each of these methods returns a `$wire` object representing the component's state in Livewire.
> <br><br>
> You can learn more about these objects in [the `$wire` documentation](#the-wire-object).

### Interacting with events

In addition to dispatching and listening for events from individual components in PHP, the global `Livewire` object allows you to interact with [Livewire's event system](/docs/4.x/events) from anywhere in your application:

```js
// Dispatch an event to any Livewire components listening...
Livewire.dispatch('post-created', { postId: 2 })

// Dispatch an event to a given Livewire component by name...
Livewire.dispatchTo('dashboard', 'post-created', { postId: 2 })

// Listen for events dispatched from Livewire components...
Livewire.on('post-created', ({ postId }) => {
    // ...
})
```

In certain scenarios, you might need to unregister global Livewire events. For instance, when working with Alpine components and `wire:navigate`, multiple listeners may be registered as `init` is called when navigating between pages. To address this, utilize the `destroy` function, automatically invoked by Alpine. Loop through all your listeners within this function to unregister them and prevent any unwanted accumulation.

```js
Alpine.data('MyComponent', () => ({
    listeners: [],
    init() {
        this.listeners.push(
            Livewire.on('post-created', (options) => {
                // Do something...
            })
        );
    },
    destroy() {
        this.listeners.forEach((listener) => {
            listener();
        });
    }
}));
```
### Using lifecycle hooks

Livewire allows you to hook into various parts of its global lifecycle using `Livewire.hook()`:

```js
// Register a callback to execute on a given internal Livewire hook...
Livewire.hook('component.init', ({ component, cleanup }) => {
    // ...
})
```

More information about Livewire's JavaScript hooks can be [found below](#javascript-hooks).

### Registering custom directives

Livewire allows you to register custom directives using `Livewire.directive()`.

Below is an example of a custom `wire:confirm` directive that uses JavaScript's `confirm()` dialog to confirm or cancel an action before it is sent to the server:

```html
<button wire:confirm="Are you sure?" wire:click="delete">Delete post</button>
```

Here is the implementation of `wire:confirm` using `Livewire.directive()`:

```js
Livewire.directive('confirm', ({ el, directive, component, cleanup }) => {
    let content =  directive.expression

    // The "directive" object gives you access to the parsed directive.
    // For example, here are its values for: wire:click.prevent="deletePost(1)"
    //
    // directive.raw = wire:click.prevent
    // directive.value = "click"
    // directive.modifiers = ['prevent']
    // directive.expression = "deletePost(1)"

    let onClick = e => {
        if (! confirm(content)) {
            e.preventDefault()
            e.stopImmediatePropagation()
        }
    }

    el.addEventListener('click', onClick, { capture: true })

    // Register any cleanup code inside `cleanup()` in the case
    // where a Livewire component is removed from the DOM while
    // the page is still active.
    cleanup(() => {
        el.removeEventListener('click', onClick)
    })
})
```

## JavaScript hooks

For advanced users, Livewire exposes its internal client-side "hook" system. You can use the following hooks to extend Livewire's functionality or gain more information about your Livewire application.

### Component initialization

Every time a new component is discovered by Livewire — whether on the initial page load or later on — the `component.init` event is triggered. You can hook into `component.init` to intercept or initialize anything related to the new component:

```js
Livewire.hook('component.init', ({ component, cleanup }) => {
    //
})
```

For more information, please consult the [documentation on the component object](#the-component-object).

### DOM element initialization

In addition to triggering an event when new components are initialized, Livewire triggers an event for each DOM element within a given Livewire component.

This can be used to provide custom Livewire HTML attributes within your application:

```js
Livewire.hook('element.init', ({ component, el }) => {
    //
})
```

### DOM Morph hooks

During the DOM morphing phase—which occurs after Livewire completes a network roundtrip—Livewire triggers a series of events for every element that is mutated.

```js
Livewire.hook('morph.updating',  ({ el, component, toEl, skip, childrenOnly }) => {
	//
})

Livewire.hook('morph.updated', ({ el, component }) => {
	//
})

Livewire.hook('morph.removing', ({ el, component, skip }) => {
	//
})

Livewire.hook('morph.removed', ({ el, component }) => {
	//
})

Livewire.hook('morph.adding',  ({ el, component }) => {
	//
})

Livewire.hook('morph.added',  ({ el }) => {
	//
})
```

In addition to the events fired per element, a `morph` and `morphed` event is fired for each Livewire component:

```js
Livewire.hook('morph',  ({ el, component }) => {
	// Runs just before the child elements in `component` are morphed (exluding partial morphing)
})

Livewire.hook('morphed',  ({ el, component }) => {
    // Runs after all child elements in `component` are morphed (excluding partial morphing)
})
```

## Server-side JavaScript evaluation

In addition to executing JavaScript directly in your components, you can use the `js()` method to evaluate JavaScript expressions from your server-side PHP code.

This is generally useful for performing some kind of client-side follow-up after a server-side action is performed.

For example, here is a `post.create` component that triggers a client-side alert dialog after the post is saved to the database:

```php
<?php // resources/views/components/post/⚡create.blade.php

use Livewire\Component;

new class extends Component {
    public $title = '';

    public function save()
    {
        // Save post to database...

        $this->js("alert('Post saved!')");
    }
};
```

The JavaScript expression `alert('Post saved!')` will be executed on the client after the post has been saved to the database on the server.

You can access the current component's `$wire` object inside the expression:

```php
$this->js('$wire.$refresh()');
$this->js('$wire.$dispatch("post-created", { id: ' . $post->id . ' })');
```

## Common patterns

Here are some common patterns for using JavaScript with Livewire in real-world applications.

### Integrating third-party libraries

Many JavaScript libraries need to be initialized when elements are added to the page. Use component scripts to initialize libraries when your component loads:

```blade
<div>
    <div id="map" style="height: 400px;"></div>
</div>

@assets
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY"></script>
@endassets

<script>
    new google.maps.Map($wire.$el.querySelector('#map'), {
        center: { lat: {{ $latitude }}, lng: {{ $longitude }} },
        zoom: 12
    });
</script>
```

### Syncing with localStorage

You can sync component state with localStorage using `$watch`:

```blade
<script>
    // Load from localStorage on init
    if (localStorage.getItem('draft')) {
        $wire.content = localStorage.getItem('draft');
    }

    // Save to localStorage when it changes
    $wire.$watch('content', (value) => {
        localStorage.setItem('draft', value);
    });
</script>
```

## Best practices

### Component scripts vs global scripts

**Use component scripts when:**
- The JavaScript is specific to that component's functionality
- You need access to `$wire` or component-specific data
- The code should run every time the component loads

**Use global scripts when:**
- Registering custom directives or hooks
- Setting up global event listeners
- Initializing app-wide JavaScript

### Avoiding memory leaks

When adding event listeners in component scripts, Livewire automatically cleans them up when the component is removed. However, if you're using global interceptors or hooks, make sure to clean up when appropriate:

```js
// Component-level - automatically cleaned up ✓
$wire.intercept(({ onSend }) => {
    onSend(() => console.log('Sending...'));
});

// Global-level - lives for the entire page lifecycle
Livewire.interceptMessage(({ onSend }) => {
    onSend(() => console.log('Sending...'));
});
```

### Debugging tips

**Access component from browser console:**
```js
// Get first component on page
let $wire = Livewire.first()

// Inspect component state
console.log($wire.count)

// Call methods
$wire.increment()
```

**Monitor all requests:**
```js
Livewire.interceptRequest(({ onSend }) => {
    onSend(() => {
        console.log('Request sent:', Date.now());
    });
});
```

**View component snapshots:**
```js
let component = Livewire.first().__instance()
console.log(component.snapshot)
```

### Performance considerations

- Use `wire:ignore` on elements that shouldn't be touched by Livewire's DOM morphing
- Debounce expensive operations using `wire:model.debounce` or JavaScript debouncing
- Use lazy loading (`lazy` parameter) for components that aren't immediately visible
- Consider using islands for isolated regions that update independently

## See also

- **[Alpine](/docs/4.x/alpine)** — Use Alpine for client-side interactivity
- **[Actions](/docs/4.x/actions)** — Create JavaScript actions in components
- **[Properties](/docs/4.x/properties)** — Access properties from JavaScript with $wire
- **[Events](/docs/4.x/events)** — Dispatch and listen for events in JavaScript

## Reference

When extending Livewire's JavaScript system, it's important to understand the different objects you might encounter.

Here is an exhaustive reference of each of Livewire's relevant internal properties.

As a reminder, the average Livewire user may never interact with these. Most of these objects are available for Livewire's internal system or advanced users.

### The `$wire` object

Given the following generic `Counter` component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public $count = 1;

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

Livewire exposes a JavaScript representation of the server-side component in the form of an object that is commonly referred to as `$wire`:

```js
let $wire = {
    // All component public properties are directly accessible on $wire...
    count: 0,

    // All public methods are exposed and callable on $wire...
    increment() { ... },

    // Access the `$wire` object of the parent component if one exists...
    $parent,

    // Access the root DOM element of the Livewire component...
    $el,

    // Access the ID of the current Livewire component...
    $id,

    // Get the value of a property by name...
    // Usage: $wire.$get('count')
    $get(name) { ... },

    // Set a property on the component by name...
    // Usage: $wire.$set('count', 5)
    $set(name, value, live = true) { ... },

    // Toggle the value of a boolean property...
    $toggle(name, live = true) { ... },

    // Call the method...
    // Usage: $wire.$call('increment')
    $call(method, ...params) { ... },

    // Define a JavaScript action...
    // Usage: $wire.$js('increment', () => { ... })
    // Usage: $wire.$js.increment = () => { ... }
    $js(name, callback) { ... },

    // [DEPRECATED] Entangle - You probably don't need this.
    // Use $wire directly to access properties instead.
    // Usage: <div x-data="{ count: $wire.$entangle('count') }">
    $entangle(name, live = false) { ... },

    // Watch the value of a property for changes...
    // Usage: Alpine.$watch('count', (value, old) => { ... })
    $watch(name, callback) { ... },

    // Refresh a component by sending a message to the server
    // to re-render the HTML and swap it into the page...
    $refresh() { ... },

    // Identical to the above `$refresh`. Just a more technical name...
    $commit() { ... }, // Alias for $refresh()

    // Listen for a an event dispatched from this component or its children...
    // Usage: $wire.$on('post-created', () => { ... })
    $on(event, callback) { ... },

    // Listen for a lifecycle hook triggered from this component or the request...
    // Usage: $wire.$hook('message.sent', () => { ... })
    $hook(name, callback) { ... },

    // Dispatch an event from this component...
    // Usage: $wire.$dispatch('post-created', { postId: 2 })
    $dispatch(event, params = {}) { ... },

    // Dispatch an event onto another component...
    // Usage: $wire.$dispatchTo('dashboard', 'post-created', { postId: 2 })
    $dispatchTo(otherComponentName, event, params = {}) { ... },

    // Dispatch an event onto this component and no others...
    $dispatchSelf(event, params = {}) { ... },

    // A JS API to upload a file directly to component
    // rather than through `wire:model`...
    $upload(
        name, // The property name
        file, // The File JavaScript object
        finish = () => { ... }, // Runs when the upload is finished...
        error = () => { ... }, // Runs if an error is triggered mid-upload...
        progress = (event) => { // Runs as the upload progresses...
            event.detail.progress // An integer from 1-100...
        },
    ) { ... },

    // API to upload multiple files at the same time...
    $uploadMultiple(name, files, finish, error, progress) { },

    // Remove an upload after it's been temporarily uploaded but not saved...
    $removeUpload(name, tmpFilename, finish, error) { ... },

    // Register an action interceptor for this component instance
    // Usage: $wire.intercept(({ action, onSend, onCancel, onSuccess, onError, onFailure, onFinish }) => { ... })
    // Or scope to specific action: $wire.intercept('save', ({ action, onSuccess }) => { ... })
    intercept(actionOrCallback, callback) { ... },

    // Alias for intercept
    interceptAction(actionOrCallback, callback) { ... },

    // Register a message interceptor for this component instance
    // Usage: $wire.interceptMessage(({ message, cancel, onSend, onCancel, onSuccess, onError, onFailure, onFinish }) => { ... })
    // Or scope to specific action: $wire.interceptMessage('save', callback)
    interceptMessage(actionOrCallback, callback) { ... },

    // Register a request interceptor for this component instance
    // Usage: $wire.interceptRequest(({ request, onSend, onCancel, onSuccess, onError, onFailure, onFinish }) => { ... })
    // Or scope to specific action: $wire.interceptRequest('save', callback)
    interceptRequest(actionOrCallback, callback) { ... },

    // Retrieve the underlying "component" object...
    __instance() { ... },
}
```

You can learn more about `$wire` in [Livewire's documentation on accessing properties in JavaScript](/docs/4.x/properties#accessing-properties-from-javascript).

### The `snapshot` object

Between each network request, Livewire serializes the PHP component into an object that can be consumed in JavaScript. This snapshot is used to unserialize the component back into a PHP object and therefore has mechanisms built in to prevent tampering:

```js
let snapshot = {
    // The serialized state of the component (public properties)...
    data: { count: 0 },

    // Long-standing information about the component...
    memo: {
        // The component's unique ID...
        id: '0qCY3ri9pzSSMIXPGg8F',

        // The component's name. Ex. <livewire:[name] />
        name: 'counter',

        // The URI, method, and locale of the web page that the
        // component was originally loaded on. This is used
        // to re-apply any middleware from the original request
        // to subsequent component update requests (messages)...
        path: '/',
        method: 'GET',
        locale: 'en',

        // A list of any nested "child" components. Keyed by
        // internal template ID with the component ID as the values...
        children: [],

        // Whether or not this component was "lazy loaded"...
        lazyLoaded: false,

        // A list of any validation errors thrown during the
        // last request...
        errors: [],
    },

    // A securely encrypted hash of this snapshot. This way,
    // if a malicious user tampers with the snapshot with
    // the goal of accessing un-owned resources on the server,
    // the checksum validation will fail and an error will
    // be thrown...
    checksum: '1bc274eea17a434e33d26bcaba4a247a4a7768bd286456a83ea6e9be2d18c1e7',
}
```

### The `component` object

Every component on a page has a corresponding component object behind the scenes keeping track of its state and exposing its underlying functionality. This is one layer deeper than `$wire`. It is only meant for advanced usage.

Here's an actual component object for the above `Counter` component with descriptions of relevant properties in JS comments:

```js
let component = {
    // The root HTML element of the component...
    el: HTMLElement,

    // The unique ID of the component...
    id: '0qCY3ri9pzSSMIXPGg8F',

    // The component's "name" (<livewire:[name] />)...
    name: 'counter',

    // The latest "effects" object. Effects are "side-effects" from server
    // round-trips. These include redirects, file downloads, etc...
    effects: {},

    // The component's last-known server-side state...
    canonical: { count: 0 },

    // The component's mutable data object representing its
    // live client-side state...
    ephemeral: { count: 0 },

    // A reactive version of `this.ephemeral`. Changes to
    // this object will be picked up by AlpineJS expressions...
    reactive: Proxy,

    // A Proxy object that is typically used inside Alpine
    // expressions as `$wire`. This is meant to provide a
    // friendly JS object interface for Livewire components...
    $wire: Proxy,

    // A list of any nested "child" components. Keyed by
    // internal template ID with the component ID as the values...
    children: [],

    // The last-known "snapshot" representation of this component.
    // Snapshots are taken from the server-side component and used
    // to re-create the PHP object on the backend...
    snapshot: {...},

    // The un-parsed version of the above snapshot. This is used to send back to the
    // server on the next roundtrip because JS parsing messes with PHP encoding
    // which often results in checksum mis-matches.
    snapshotEncoded: '{"data":{"count":0},"memo":{"id":"0qCY3ri9pzSSMIXPGg8F","name":"counter","path":"\/","method":"GET","children":[],"lazyLoaded":true,"errors":[],"locale":"en"},"checksum":"1bc274eea17a434e33d26bcaba4a247a4a7768bd286456a83ea6e9be2d18c1e7"}',
}
```

### The `message` payload

When an action is performed on a Livewire component in the browser, a network request is triggered. That network request contains one or many components and various instructions for the server. Internally, these component network payloads are called "messages".

A "message" represents the data sent from the frontend to the backend when a component needs to update. A component is rendered and manipulated on the frontend until an action is performed that requires it to send a message with its state and updates to the backend.

You will recognize this schema from the payload in the network tab of your browser's DevTools, or [Livewire's JavaScript hooks](#javascript-hooks):

```js
let message = {
    // Snapshot object...
    snapshot: { ... },

    // A key-value pair list of properties
    // to update on the server...
    updates: {},

    // An array of methods (with parameters) to call server-side...
    calls: [
        { method: 'increment', params: [] },
    ],
}
```
