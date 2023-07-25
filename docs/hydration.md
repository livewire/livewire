
Using Livewire feels like attaching a server-side PHP class directly to a web browser. Things like calling server-side functions directly from button presses support this illusion. But in reality, it is just that: an illusion.

In the background, Livewire actually behaves much more like a standard web application. It renders static HTML to the browser, listens for browser events, then makes AJAX requests to invoke server-side code.

Because each AJAX request Livewire makes to the server is "stateless" (meaning there isn't a long running backend process keeping the state of a component alive), Livewire must re-create the last-known state of a component before making any updates.

It does this by taking "snapshots" of the PHP component after each server-side update so that the component can be re-created or _resumed_ on the next request.

Throughout this documentation, we will refer to the process of taking the snapshot as "dehydration" and the process of re-creating a component from a snapshot as "hydration".

## Dehydrating

When Livewire _dehydrates_ a server-side component, it does two things:

* Renders the component's template to HTML
* Creates a JSON snapshot of the component

### Rendering HTML

After a component is mounted or an update has been made, Livewire calls a component's `render()` method to convert the Blade template to raw HTML.

Take the following `Counter` component for example:

```php
class Counter extends Component
{
    public $count = 1;

    public function increment()
    {
        $this->count++;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            Count: {{ $count }}

            <button wire:click="increment">+</button>
        </div>
        HTML;
    }
}
```

After each mount or update, Livewire would render the above `Counter` component to the following HTML:

```html
<div>
    Count: 1

    <button wire:click="increment">+</button>
</div>
```

### The snapshot

In order to re-create the `Counter` component on the server during the next request, a JSON snapshot is created, attempting to capture as much of the state of the component as possible:

```js
{
    state: {
        count: 1,
    },

    memo: {
        name: 'counter',

        id: '1526456',
    },
}
```

Notice two different portions of the snapshot: `memo`, and `state`.

The `memo` portion is used to store the information needed to identify and re-create the component, while the `state` portion stores the values of all the component's public properties.

> [!info]
> The above snapshot is a condensed version of an actual snapshot in Livewire. In live applications, the snapshot contains much more information, such as validation errors, a list of child components, locales, and much more. For a more detailed look at a snapshot object you may reference the [snapshot schema documentation](/docs/javascript#the-snapshot-object).

### Embedding the snapshot in the HTML

When a component is first rendered, Livewire stores the snapshot as JSON inside an HTML attribute called `wire:snapshot`. This way, Livewire's JavaScript core can extract the JSON and turn it into a run-time object:

```html
<div wire:id="..." wire:snapshot="{ state: {...}, memo: {...} }">
    Count: 1

    <button wire:click="increment">+</button>
</div>
```

## Hydrating

When a component update is triggered, for example, the "+" button is pressed in the `Counter` component, a payload like the following is sent to the server:

```js
{
    calls: [
        { method: 'increment', params: [] },
    ],

    snapshot: {
        state: {
            count: 1,
        },

        memo: {
            name: 'counter',

            id: '1526456',
        },
    }
}
```

Before Livewire can call the `increment` method, it must first create a new `Counter` instance and seed it with the snapshot's state.

Here is some PHP pseudo-code that achieves this result:

```php
$state = request('snapshot.state');
$memo = request('snapshot.memo');

$instance = Livewire::new($memo['name'], $memo['id']);

foreach ($state as $property => $value) {
    $instance[$property] = $value;
}
```

If you follow the above script, you will see that after creating a `Counter` object, its public properties are set based on the state provided from the snapshot.

## Advanced hydration

The above `Counter` example works well to demonstrate the concept of hydration; however, it only demonstrates how Livewire handles hydrating simple values like integers (`1`).

As you may know, Livewire supports many more sophisticated property types beyond integers.

Let's take a look at a slightly more complex example - a `Todos` component:

```php
class Todos extends Component
{
    public $todos;

    public function mount() {
        $this->todos = collect([
            'first',
            'second',
            'third',
        ]);
    }
}
```

As you can see, we are setting the `$todos` property to a [Laravel collection](https://laravel.com/docs/collections#main-content) with three strings as its content.

JSON alone has no way of representing Laravel collections, so instead, Livewire has created its own pattern of associating metadata with pure data inside a snapshot.

Here is the snapshot's state object for this `Todos` component:

```js
state: {
    todos: [
        [ 'first', 'second', 'third' ],
        { s: 'clctn', class: 'Illuminate\\Support\\Collection' },
    ],
},
```

This may be confusing to you if you were expecting something more straightforward like:

```js
state: {
    todos: [ 'first', 'second', 'third' ],
},
```

However, if Livewire were hydrating a component based on this data, it would have no way of knowing it's a collection and not a plain array.

Therefore, Livewire supports an alternate state syntax in the form of a tuple (an array of two items):

```js
todos: [
    [ 'first', 'second', 'third' ],
    { s: 'clctn', class: 'Illuminate\\Support\\Collection' },
],
```

When Livewire encounters a tuple when hydrating a component's state, it uses information stored in the second element of the tuple to more intelligently hydrate the state stored in the first.

To demonstrate more clearly, here is simplified code showing how Livewire might re-create a collection property based on the above snapshot:

```php
[ $state, $metadata ] = request('snapshot.state.todos');

$collection = new $metadata['class']($state);
```

As you can see, Livewire uses the metadata associated with the state to derive the full collection class.

### Deeply nested tuples

One distinct advantage of this approach is the ability to dehydrate and hydrate deeply nested properties.

For example, consider the above `Todos` example, except now with a [Laravel Stringable](https://laravel.com/docs/helpers#method-str) instead of a plain string as the third item in the collection:

```php
class Todos extends Component
{
    public $todos;

    public function mount() {
        $this->todos = collect([
            'first',
            'second',
            str('third'),
        ]);
    }
}
```

The dehydrated snapshot for this component's state would now look like this:

```js
todos: [
    [
        'first',
        'second',
        [ 'third', { s: 'str' } ],
    ],
    { s: 'clctn', class: 'Illuminate\\Support\\Collection' },
],
```

As you can see, the third item in the collection has been dehydrated into a metadata tuple. The first element in the tuple being the plain string value, and the second being a flag denoting to Livewire that this string is a _stringable_.

### Supporting custom property types

Internally, Livewire has hydration support for the most common PHP and Laravel types. However, if you wish to support un-supported types, you can do so using [Synthesizers](/docs/synthesizers) — Livewire's internal mechanism for hydrating/dehydrating non-primitive property types.

