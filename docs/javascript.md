
## Using JavaScript in Livewire components

Livewire and Alpine provide plenty of utilities for building dynamic components directly in your HTML, however, there are times when it's helpful to break out of the HTML and execute plain JavaScript for your component. Livewire's `@script` and `@assets` directive allow you to do this in a predictable, maintainable way.

### Executing scripts

To execute bespoke JavaScript in your Livewire component, simply wrap a `<script>` element with `@script` and `@endscript`. This will tell Livewire to handle the execution of this JavaScript.

Because scripts inside `@script` are handled by Livewire, they are executed at the perfect time after the page has loaded, but before the Livewire component has rendered. This means you no longer need to wrap your scripts in `document.addEventListener('...')` to load them properly.

This also means that lazily or conditionally loaded Livewire components are still able to execute JavaScript after the page has initialized.

```blade
<div>
    ...
</div>

@script
<script>
    // This Javascript will get executed every time this component is loaded onto the page...
</script>
@endscript
```

Here's a more full example where you can do something like register a one-off Alpine component that is used in your Livewire component.

```blade
<div>
    Counter component in Alpine:

    <div x-data="counter">
        <h1 x-text="count"></h1>
        <button x-on:click="increment">+</button>
    </div>
</div>

@script
<script>
    Alpine.data('counter', () => {
        return {
            count: 0,
            increment() {
                this.count++
            },
        }
    })
</script>
@endscript
```

### Using `$wire` from scripts

Another helpful feature of using `@script` for your JavaScript is that you automatically have access to your Livewire component's `$wire` object.

Here's an example of using a simple `setInterval` to refresh the component every 2 seconds (You could easily do this with [`wire:poll`](/docs/wire-poll), but it's a simple way to demonstrate the point):

You can learn more about `$wire` on the [`$wire` documentation](#the-wire-object).

```blade
@script
<script>
    setInterval(() => {
        $wire.$refresh()
    }, 2000)
</script>
@endscript
```

### Loading assets

The `@script` directive is useful for executing a bit of JavaScript every time a Livewire component loads, however, there are times you might want to load entire script and style assets on the page along with the component.

Here is an example of using `@assets` to load a date picker library called [Pikaday](https://github.com/Pikaday/Pikaday) and initialize it inside your component using `@script`:

```blade
<div>
    <input type="text" data-picker>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js" defer></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endassets

@script
<script>
    new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
</script>
@endscript
```

When this component loads, Livewire will make sure any `@assets` are loaded on that page before evaluating `@script`s. In addition, it will ensure the provided `@assets` are only loaded once per page no matter how many instances of this component there are, unlike `@script`, which will evaluate for every component instance on the page.

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

In addition to dispatching and listening for events from individual components in PHP, the global `Livewire` object allows you to interact with [Livewire's event system](/docs/events) from anywhere in your application:

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

## Object schemas

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

    // Call the method
    // Usage: $wire.$call('increment')
    $call(method, ...params) { ... },

    // Entangle the value of a Livewire property with a different,
    // arbitrary, Alpine property...
    // Usage: <div x-data="{ count: $wire.$entangle('count') }">
    $entangle(name, live = false) { ... },

    // Watch the value of a property for changes...
    // Usage: Alpine.$watch('count', (value, old) => { ... })
    $watch(name, callback) { ... },

    // Refresh a component by sending a commit to the server
    // to re-render the HTML and swap it into the page...
    $refresh() { ... },

    // Identical to the above `$refresh`. Just a more technical name...
    $commit() { ... },

    // Listen for a an event dispatched from this component or its children...
    // Usage: $wire.$on('post-created', () => { ... })
    $on(event, callback) { ... },

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

    // Retrieve the underlying "component" object...
    __instance() { ... },
}
```

You can learn more about `$wire` in [Livewire's documentation on accessing properties in JavaScript](/docs/properties#accessing-properties-from-javascript).

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
        // to subsequent component update requests (commits)...
        path: '/',
        method: 'GET',
        locale: 'en',

        // A list of any nested "child" components. Keyed by
        // internal template ID with the component ID as the values...
        children: [],

        // Weather or not this component was "lazy loaded"...
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

### The `commit` payload

When an action is performed on a Livewire component in the browser, a network request is triggered. That network request contains one or many components and various instructions for the server. Internally, these component network payloads are called "commits".

The term "commit" was chosen as a helpful way to think about Livewire's relationship between frontend and backend. A component is rendered and manipulated on the frontend until an action is performed that requires it to "commit" its state and updates to the backend.

You will recognize this schema from the payload in the network tab of your browser's DevTools, or [Livewire's JavaScript hooks](#javascript-hooks):

```js
let commit = {
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

### Commit hooks

Because Livewire requests contain multiple components, _request_ is too broad of a term to refer to an individual component's request and response payload. Instead, internally, Livewire refers to component updates as _commits_ — in reference to _committing_ component state to the server.

These hooks expose `commit` objects. You can learn more about their schema by reading [the commit object documentation](#the-commit-payload).

#### Preparing commits

The `commit.prepare` hook will be triggered immediately before a request is sent to the server. This gives you a chance to add any last minute updates or actions to the outgoing request:

```js
Livewire.hook('commit.prepare', ({ component }) => {
    // Runs before commit payloads are collected and sent to the server...
})
```

#### Intercepting commits

Every time a Livewire component is sent to the server, a _commit_ is made. To hook into the lifecycle and contents of an individual commit, Livewire exposes a `commit` hook.

This hook is extremely powerful as it provides methods for hooking into both the request and response of a Livewire commit:

```js
Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
    // Runs immediately before a commit's payload is sent to the server...

    respond(() => {
        // Runs after a response is received but before it's processed...
    })

    succeed(({ snapshot, effect }) => {
        // Runs after a successful response is received and processed
        // with a new snapshot and list of effects...
    })

    fail(() => {
        // Runs if some part of the request failed...
    })
})
```

## Request hooks

If you would like to instead hook into the entire HTTP request going and returning from the server, you can do so using the `request` hook:

```js
Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
    // Runs after commit payloads are compiled, but before a network request is sent...

    respond(({ status, response }) => {
        // Runs when the response is received...
        // "response" is the raw HTTP response object
        // before await response.text() is run...
    })

    succeed(({ status, json }) => {
        // Runs when the response is received...
        // "json" is the JSON response object...
    })

    fail(({ status, content, preventDefault }) => {
        // Runs when the response has an error status code...
        // "preventDefault" allows you to disable Livewire's
        // default error handling...
        // "content" is the raw response content...
    })
})
```

### Customizing page expiration behavior

If the default page expired dialog isn't suitable for your application, you can implement a custom solution using the `request` hook:

```html
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.hook('request', ({ fail }) => {
            fail(({ status, preventDefault }) => {
                if (status === 419) {
                    confirm('Your custom page expiration behavior...')

                    preventDefault()
                }
            })
        })
    })
</script>
```

With the above code in your application, users will receive a custom dialog when their session has expired.
