Properties store and manage data inside your Livewire components. They are defined as public properties on component classes and can be accessed and modified on both the server and client-side.

## Initializing properties

You can set initial values for properties within your component's `mount()` method.

Consider the following example:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TodoList extends Component
{
    public $todos = [];

    public $todo = '';

    public function mount()
    {
        $this->todos = Auth::user()->todos; // [tl! highlight]
    }

    // ...
}
```

In this example, we've defined an empty `todos` array and initialized it with existing todos from the authenticated user. Now, when the component renders for the first time, all the existing todos in the database are shown to the user.

## Bulk assignment

Sometimes initializing many properties in the `mount()` method can feel verbose. To help with this, Livewire provides a convenient way to assign multiple properties at once via the `fill()` method. By passing an associative array of property names and their respective values, you can set several properties simultaneously and cut down on repetitive lines of code in `mount()`.

For example:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class UpdatePost extends Component
{
    public $post;

    public $title;

    public $description;

    public function mount(Post $post)
    {
        $this->post = $post;

        $this->fill( // [tl! highlight]
            $post->only('title', 'description'), // [tl! highlight]
        ); // [tl! highlight]
    }

    // ...
}
```

Because `$post->only(...)` returns an associative array of model attributes and values based on the names you pass into it, the `$title` and `$description` properties will be initially set to the `title` and `description` of the `$post` model from the database without having to set each one individually.

## Data binding

Livewire supports two-way data binding through the `wire:model` HTML attribute. This allows you to easily synchronize data between component properties and HTML inputs, keeping your user interface and component state in sync.

Let's use the `wire:model` directive to bind the `$todo` property in a `TodoList` component to a basic input element:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class TodoList extends Component
{
    public $todos = [];

    public $todo = '';

    public function add()
    {
        $this->todos[] = $this->todo;

        $this->todo = '';
    }

    // ...
}
```

```blade
<div>
    <input type="text" wire:model="todo" placeholder="Todo..."> <!-- [tl! highlight] -->

    <button wire:click="add">Add Todo</button>

    <ul>
        @foreach ($todos as $todo)
            <li>{{ $todo }}</li>
        @endforeach
    </ul>
</div>
```

In the above example, the text input's value will synchronize with the `$todo` property on the server when the "Add Todo" button is clicked.

This is just scratching the surface of `wire:model`. For deeper information on data binding, check out our [documentation on forms](/docs/forms).

## Resetting properties

Sometimes, you may need to reset your properties back to their initial state after an action is performed by the user. In these cases, Livewire provides a `reset()` method that accepts one or more property names and resets their values to their initial state.

In the example below, we can avoid code duplication by using `$this->reset()` to reset the `todo` field after the "Add Todo" button is clicked:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class ManageTodos extends Component
{
    public $todos = [];

    public $todo = '';

    public function addTodo()
    {
        $this->todos[] = $this->todo;

        $this->reset('todo'); // [tl! highlight]
    }

    // ...
}
```

In the above example, after a user clicks "Add Todo", the input field holding the todo that has just been added will clear, allowing the user to write a new todo.

> [!warning] `reset()` won't work on values set in `mount()`
> `reset()` will reset a property to its state before the `mount()` method was called. If you initialized the property in `mount()` to a different value, you will need to reset the property manually.

## Pulling properties

Alternatively, you can use the `pull()` method to both reset and retrieve the value in one operation.

Here's the same example from above, but simplified with `pull()`:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class ManageTodos extends Component
{
    public $todos = [];

    public $todo = '';

    public function addTodo()
    {
        $this->todos[] = $this->pull('todo'); // [tl! highlight]
    }

    // ...
}
```

The above example is pulling a single value, but `pull()` can also be used to reset and retrieve (as a key-value pair) all or some properties:

```php
// The same as $this->all() and $this->reset();
$this->pull();

// The same as $this->only(...) and $this->reset(...);
$this->pull(['title', 'content']);
```

## Supported property types

Livewire supports a limited set of property types because of its unique approach to managing component data between server requests.

Each property in a Livewire component is serialized or "dehydrated" into JSON between requests, then "hydrated" from JSON back into PHP for the next request.

This two-way conversion process has certain limitations, restricting the types of properties Livewire can work with.

### Primitive types

Livewire supports primitive types such as strings, integers, etc. These types can be easily converted to and from JSON, making them ideal for use as properties in Livewire components.

Livewire supports the following primitive property types: `Array`, `String`, `Integer`, `Float`, `Boolean`, and `Null`.

```php
class TodoList extends Component
{
    public $todos = []; // Array

    public $todo = ''; // String

    public $maxTodos = 10; // Integer

    public $showTodos = false; // Boolean

    public $todoFilter; // Null
}
```

### Common PHP types

In addition to primitive types, Livewire supports common PHP object types used in Laravel applications. However, it's important to note that these types will be _dehydrated_ into JSON and _hydrated_ back to PHP on each request. This means that the property may not preserve run-time values such as closures. Also, information about the object such as class names may be exposed to JavaScript.

Supported PHP types:
| Type | Full Class Name |
|------|-----------------|
| BackedEnum | `BackedEnum` |
| Collection | `Illuminate\Support\Collection` |
| Eloquent Collection | `Illuminate\Database\Eloquent\Collection` |
| Model | `Illuminate\Database\Model` |
| DateTime | `DateTime` |
| Carbon | `Carbon\Carbon` |
| Stringable | `Illuminate\Support\Stringable` |

> [!warning] Eloquent Collections and Models
> When storing Eloquent Collections and Models in Livewire properties, additional query constraints like select(...) will not be re-applied on subsequent requests.
>
> See [Eloquent constraints aren't preserved between requests](#eloquent-constraints-arent-preserved-between-requests) for more details

Here's a quick example of setting properties as these various types:

```php
public function mount()
{
    $this->todos = collect([]); // Collection

    $this->todos = Todos::all(); // Eloquent Collection

    $this->todo = Todos::first(); // Model

    $this->date = new DateTime('now'); // DateTime

    $this->date = new Carbon('now'); // Carbon

    $this->todo = str(''); // Stringable
}
```

### Supporting custom types

Livewire allows your application to support custom types through two powerful mechanisms:

* Wireables
* Synthesizers

Wireables are simple and easy to use for most applications, so we'll explore them below. If you're an advanced user or package author wanting more flexibility, [Synthesizers are the way to go](/docs/synthesizers).

#### Wireables

Wireables are any class in your application that implements the `Wireable` interface.

For example, let's imagine you have a `Customer` object in your application that contains the primary data about a customer:

```php
class Customer
{
    protected $name;
    protected $age;

    public function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }
}
```

Attempting to set an instance of this class to a Livewire component property will result in an error telling you that the `Customer` property type isn't supported:

```php
class ShowCustomer extends Component
{
    public Customer $customer;

    public function mount()
    {
        $this->customer = new Customer('Caleb', 29);
    }
}
```

However, you can solve this by implementing the `Wireable` interface and adding a `toLivewire()` and `fromLivewire()` method to your class. These methods tell Livewire how to turn properties of this type into JSON and back again:

```php
use Livewire\Wireable;

class Customer implements Wireable
{
    protected $name;
    protected $age;

    public function __construct($name, $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function toLivewire()
    {
        return [
            'name' => $this->name,
            'age' => $this->age,
        ];
    }

    public static function fromLivewire($value)
    {
        $name = $value['name'];
        $age = $value['age'];

        return new static($name, $age);
    }
}
```

Now you can freely set `Customer` objects on your Livewire components and Livewire will know how to convert these objects into JSON and back into PHP.

As mentioned earlier, if you want to support types more globally and powerfully, Livewire offers Synthesizers, its advanced internal mechanism for handling different property types. [Learn more about Synthesizers](/docs/synthesizers).

## Accessing properties from JavaScript

Because Livewire properties are also available in the browser via JavaScript, you can access and manipulate their JavaScript representations from [AlpineJS](https://alpinejs.dev/).

Alpine is a lightweight JavaScript library that is included with Livewire. Alpine provides a way to build lightweight interactions into your Livewire components without making full server roundtrips.

Internally, Livewire's frontend is built on top of Alpine. In fact, every Livewire component is actually an Alpine component under-the-hood. This means that you can freely utilize Alpine inside your Livewire components.

The rest of this page assumes a basic familiarity with Alpine. If you're unfamiliar with Alpine, [take a look at the Alpine documentation](https://alpinejs.dev/docs).

### Accessing properties

Livewire exposes a magic `$wire` object to Alpine. You can access the `$wire` object from any Alpine expression inside your Livewire component.

The `$wire` object can be treated like a JavaScript version of your Livewire component. It has all the same properties and methods as the PHP version of your component, but also contains a few dedicated methods to perform specific functions in your template.

For example, we can use `$wire` to show a live character count of the `todo` input field:

```blade
<div>
    <input type="text" wire:model="todo">

    Todo character length: <h2 x-text="$wire.todo.length"></h2>
</div>
```

As the user types in the field, the character length of the current todo being written will be shown and live-updated on the page, all without sending a network request to the server.

If you prefer, you can use the more explicit `.get()` method to accomplish the same thing:

```blade
<div>
    <input type="text" wire:model="todo">

    Todo character length: <h2 x-text="$wire.get('todo').length"></h2>
</div>
```

### Manipulating properties

Similarly, you can manipulate your Livewire component properties in JavaScript using `$wire`.

For example, let's add a "Clear" button to the `TodoList` component to allow the user to reset the input field using only JavaScript:

```blade
<div>
    <input type="text" wire:model="todo">

    <button x-on:click="$wire.todo = ''">Clear</button>
</div>
```

After the user clicks "Clear", the input will be reset to an empty string, without sending a network request to the server.

On the subsequent request, the server-side value of `$todo` will be updated and synchronized.

If you prefer, you can also use the more explicit `.set()` method for setting properties client-side. However, you should note that using `.set()` by default immediately triggers a network request and synchronizes the state with the server. If that is desired, then this is an excellent API:

```blade
<button x-on:click="$wire.set('todo', '')">Clear</button>
```

In order to update the property without sending a network request to the server, you can pass a third bool parameter. This will defer the network request and on a subsequent request, the state will be synchronized on the server-side:
```blade
<button x-on:click="$wire.set('todo', '', false)">Clear</button>
```

## Security concerns

While Livewire properties are a powerful feature, there are a few security considerations that you should be aware of before using them.

In short, always treat public properties as user input — as if they were request input from a traditional endpoint. In light of this, it's essential to validate and authorize properties before persisting them to a database — just like you would do when working with request input in a controller.

### Don't trust property values

To demonstrate how neglecting to authorize and validate properties can introduce security holes in your application, the following `UpdatePost` component is vulnerable to attack:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class UpdatePost extends Component
{
    public $id;
    public $title;
    public $content;

    public function mount(Post $post)
    {
        $this->id = $post->id;
        $this->title = $post->title;
        $this->content = $post->content;
    }

    public function update()
    {
        $post = Post::findOrFail($this->id);

        $post->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        session()->flash('message', 'Post updated successfully!');
    }

    public function render()
    {
        return view('livewire.update-post');
    }
}
```

```blade
<form wire:submit="update">
    <input type="text" wire:model="title">
    <input type="text" wire:model="content">

    <button type="submit">Update</button>
</form>
```

At first glance, this component may look completely fine. But, let's walk through how an attacker could use the component to do unauthorized things in your application.

Because we are storing the `id` of the post as a public property on the component, it can be manipulated on the client just the same as the `title` and `content` properties.

It doesn't matter that we didn't write an input with `wire:model="id"`. A malicious user can easily change the view to the following using their browser DevTools:

```blade
<form wire:submit="update">
    <input type="text" wire:model="id"> <!-- [tl! highlight] -->
    <input type="text" wire:model="title">
    <input type="text" wire:model="content">

    <button type="submit">Update</button>
</form>
```

Now the malicious user can update the `id` input to the ID of a different post model. When the form is submitted and `update()` is called, `Post::findOrFail()` will return and update a post the user is not the owner of.

To prevent this kind of attack, we can use one or both of the following strategies:

* Authorize the input
* Lock the property from updates

#### Authorizing the input

Because `$id` can be manipulated client-side with something like `wire:model`, just like in a controller, we can use [Laravel's authorization](https://laravel.com/docs/authorization) to make sure the current user can update the post:

```php
public function update()
{
    $post = Post::findOrFail($this->id);

    $this->authorize('update', $post); // [tl! highlight]

    $post->update(...);
}
```

If a malicious user mutates the `$id` property, the added authorization will catch it and throw an error.

#### Locking the property

Livewire also allows you to "lock" properties in order to prevent properties from being modified on the client-side. You can "lock" a property from client-side manipulation using the `#[Locked]` attribute:

```php
use Livewire\Attributes\Locked;
use Livewire\Component;

class UpdatePost extends Component
{
    #[Locked] // [tl! highlight]
    public $id;

    // ...
}
```

Now, if a user tries to modify `$id` on the front end, an error will be thrown.

By using `#[Locked]`, you can assume this property has not been manipulated anywhere outside your component's class.

For more information on locking properties, [consult the Locked properties documentation](/docs/locked).

#### Eloquent models and locking

When an Eloquent model is assigned to a Livewire component property, Livewire will automatically lock the property and ensure the ID isn't changed, so that you are safe from these kinds of attacks:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class UpdatePost extends Component
{
    public Post $post; // [tl! highlight]
    public $title;
    public $content;

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->content = $post->content;
    }

    public function update()
    {
        $this->post->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        session()->flash('message', 'Post updated successfully!');
    }

    public function render()
    {
        return view('livewire.update-post');
    }
}
```

### Properties expose system information to the browser

Another essential thing to remember is that Livewire properties are serialized or "dehydrated" before they are sent to the browser. This means that their values are converted to a format that can be sent over the wire and understood by JavaScript. This format can expose information about your application to the browser, including the names and class names of your properties.

For example, suppose you have a Livewire component that defines a public property named `$post`. This property contains an instance of a `Post` model from your database. In this case, the dehydrated value of this property sent over the wire might look something like this:

```json
{
    "type": "model",
    "class": "App\Models\Post",
    "key": 1,
    "relationships": []
}
```

As you can see, the dehydrated value of the `$post` property includes the class name of the model (`App\Models\Post`) as well as the ID and any relationships that have been eager-loaded.

If you don't want to expose the class name of the model, you can use Laravel's "morphMap" functionality from a service provider to assign an alias to a model class name:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Relation::morphMap([
            'post' => 'App\Models\Post',
        ]);
    }
}
```

Now, when the Eloquent model is "dehydrated" (serialized), the original class name won't be exposed, only the "post" alias:

```json
{
    "type": "model",
    "class": "App\Models\Post", // [tl! remove]
    "class": "post", // [tl! add]
    "key": 1,
    "relationships": []
}
```

### Eloquent constraints aren't preserved between requests

Typically, Livewire is able to preserve and recreate server-side properties between requests; however, there are certain scenarios where preserving values are impossible between requests.

For example, when storing Eloquent collections as Livewire properties, additional query constraints like `select(...)` will not be re-applied on subsequent requests.

To demonstrate, consider the following `ShowTodos` component with a `select()` constraint applied to the `Todos` Eloquent collection:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ShowTodos extends Component
{
    public $todos;

    public function mount()
    {
        $this->todos = Auth::user()
            ->todos()
            ->select(['title', 'content']) // [tl! highlight]
            ->get();
    }

    public function render()
    {
        return view('livewire.show-todos');
    }
}
```

When this component is initially loaded, the `$todos` property will be set to an Eloquent collection of the user's todos; however, only the `title` and `content` fields of each row in the database will have been queried and loaded into each of the models.

When Livewire _hydrates_ the JSON of this property back into PHP on a subsequent request, the select constraint will have been lost.

To ensure the integrity of Eloquent queries, we recommend that you use [computed properties](/docs/computed-properties) instead of properties.

Computed properties are methods in your component marked with the `#[Computed]` attribute. They can be accessed as a dynamic property that isn't stored as part of the component's state but is instead evaluated on-the-fly.

Here's the above example re-written using a computed property:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowTodos extends Component
{
    #[Computed] // [tl! highlight]
    public function todos()
    {
        return Auth::user()
            ->todos()
            ->select(['title', 'content'])
            ->get();
    }

    public function render()
    {
        return view('livewire.show-todos');
    }
}
```

Here's how you would access these _todos_ from the Blade view:

```blade
<ul>
    @foreach ($this->todos as $todo)
        <li>{{ $todo }}</li>
    @endforeach
</ul>
```

Notice, inside your views, you can only access computed properties on the `$this` object like so: `$this->todos`.

You can also access `$todos` from inside your class. For example, if you had a `markAllAsComplete()` action:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowTodos extends Component
{
    #[Computed]
    public function todos()
    {
        return Auth::user()
            ->todos()
            ->select(['title', 'content'])
            ->get();
    }

    public function markAllComplete() // [tl! highlight:3]
    {
        $this->todos->each->complete();
    }

    public function render()
    {
        return view('livewire.show-todos');
    }
}
```

You might wonder why not just call `$this->todos()` as a method directly where you need to? Why use `#[Computed]` in the first place?

The reason is that computed properties have a performance advantage, since they are automatically cached after their first usage during a single request. This means you can freely access `$this->todos` within your component and be assured that the actual method will only be called once, so that you don't run an expensive query multiple times in the same request.

For more information, [visit the computed properties documentation](/docs/computed-properties).
