In Livewire, properties are used to store and manage data in your components. They are defined as public properties on component classes and can be accessed and modified both on the server and client side.

## Initializing properties

You can set initial values for your properties within the `mount` method because it gets called when a Livewire component is created.

Consider the following example:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class TodoList extends Component
{
    public $todos = [];

	public $todo = '';

    public function mount()
    {
        $this->todos = auth()->user()->existingTodos();
    }

    // ...
}
```

In this example, we've defined an empty `todos` array and initialized it with existing todos from the authenticated user. This way, when the component renders for the first time, all the existing todos in the database display to the user.

## Bulk assignment

Sometimes initializing many properties in the `mount()` method can feel verbose. To help with this, Livewire provides a convenient method for assigning multiple properties at once called `fill()`. By passing an associative array of property names and their respective values you can set several properties simultaneously and cut down on repetitive lines of code in `mount`.

For example:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class UpdatePost extends Component
{
	public $post;

	public $title;

	public $description;
	
	public function mount(Post $post)
	{
		$this->post = $post;

		$this->fill(
			$post->only('title', 'description'),
		);
	}

	// ...
}
```

because `$post->only(...)` returns an associative array of model attributes and values based on the names you pass into it,  the `$title` and `$description` properties will be initially set to the `title` and `description` of the `$post` model from the database without having to set each one individually.

## Data binding

Livewire supports two-way data binding through an HTML attribute called `wire:model`. This allows you to easily synchronize data between component properties and HTML inputs, keeping your user interface and component state in sync. 

Here's a basic example of using `wire:model` to bind the `todo` property in an `TodoList` component to a basic input element. 

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

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

```html
<div>
	<input type="text" wire:model="todo" placeholder="Todo...">

	<button wire:click="add">Add Todo</button>

	<ul>
		@foreach($todos as $todo)
			<li>{{ $todo }}</li>
		@endoreach
	</ul>
</div>
```

Now, in the above example, the value of the text input will syncronize with the `$title` property in the browser, and will be synchronized with the server, when the "Add Todo" button is clicked.

This is just scratching the surface of the capabilities and usages of `wire:model`.
For deeper information on `wire:model` and data binding, please refer to the [Livewire Data Binding documentation](https://laravel-livewire.com/docs/data-binding).

## Resetting properties

Sometimes, you may need to reset your properties back to their initial state after an action is performed by the user, for example. In these cases, Livewire provides a `reset()` method that accepts one or more property names and resets their values to their initial state.

> Note: `->reset()` will reset a value to it's original state BEFORE the `mount` method was called. If you initialized the value in `mount()` to something different, it won't be reset to that value, and you will have to manually reset the value instead.

Below is an example where we can avoid code duplication using `$this->reset()` to reset the `todo` field after the "Add Todo" button is clicked:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class ManageTodos extends Controller
{
	public $todos = [];

	public $todo = '';

	public function addTodo()
	{
		$this->todos[] = $this->todo;
	
		$this->reset('todo');
	}

	// ...
}
```

In the above example, after a user clicks "Add Todo", the input field holding the todo that has just been added will clear, allowing them to write a new one.

As an added convenience, the `reset()` method returns the property value before reset so you can use it directly inline:

```php
public function addTodo()
{
    $this->todos[] = $this->reset('todo');
}
```

## Supported property types

Livewire supports a limited set of property types because of its unique way of managing component data, which involves hydration and dehydration.

In simple terms, dehydration is the process where Livewire takes your PHP property values and turns them into a JSON format that can be easily passed between the front-end and back-end.

Conversely, hydration is when Livewire takes the JSON-formatted data and turns it back into PHP property values. This two-way conversion process has certain limitations, which restricts the types of properties Livewire can work with.

### Primitives types

Livewire supports primitive types such as strings, integers, etc. These types can be easily converted to and from JSON, making them ideal for use as properties in Livewire components.

Livewire supports the following primitive property types:

* Array
* String
* Integer
* Float
* Boolean
* Null

```php
class TodoList extends Component
{
    public $todos = []; // Array

	public $todo = ''; // String

	public $maxTodos = 10; // Integer

    public $prioritize = false; // Boolean

    public $searchFilter; // null
}
```

### Common PHP types

In addition to primitive types, Livewire also supports some PHP object types commonly used in Laravel applications. However, it's important to note that these types will be dehydrated into JSON friendly primitive types and re-hydrated on each request. This means that the property may not preserve run-time values such as closures. Also information about the object such as class name may be exposed to JavaScript.

Support types:
| Type | Full Class Name |
|------|-----------------|
| Collection | `Illuminate\Support\Collection` |
| Eloquent Collection | `Illuminate\Database\Eloquent\Collection` |
| Model | `Illuminate\Database\Model` |
| DateTime | `DateTime` |
| Carbon | `Carbon\Carbon` |
| Stringable | `Illuminate\Support\Stringable` |

Here's a quick example of setting properties as these various types:

```php
public function mount()
{
	$this->todos = collect([]); // Collection

	$this->todos = Todos::all(); // Eloquent Collection

	$this->todo = Todos::first(); // Model

	$this->todo = str(''); // Stringable

	$this->date = new DateTime('now'); // DateTime

	$this->date = new Carbon('now'); // Carbon

}
```

As you see above, you can set component properties to objects of these types like any normal PHP class. From inside the component class you shouldn't know the difference, but behind the scenes, when Livewire dehydrates this property, it will convert them into a JSON string. Similarly, when hydrating the property, Livewire will convert the string back into an object of that type for use on the next request.

### Supporting Custom Types

Livewire allows you to support custom types in your application through two powerful mechanisms: Wireables and Synthesizers. For most applications, Wireables deliver simplicity and ease of use, which we'll explore in this guide. If you're an advanced user or package author wanting more flexibility, Synthesizers are the way to go: [Read more about Synthesizers here.]

#### Wireables

Wireables are any class in your application that implements the `Wireable` interface.

For example let's say you have a `Customer` object in your application that represents basic data about a customer:

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

Now if you set it as a property inside a Livewire component, an error will be thrown telling your that property type isn't supported:

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

However, we can add support for `Customer` by implementing the `Wireable` interface and adding a `toLivewire()` and `fromLivewire()` method, which tells Livewire how to turn this property into JSON friendly data, and back again:

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
        ]
    }

    public static function fromLivewire($data)
    {
		$name = $data['name'];
		$age = $data['age'];

        return new static($name, $age); 
    }
}
```

Now you can freely set `Customer` objects on your Livewire components and Livewire will know how to convert these objects to JSON for the browser and back again.

As mentioned earlier, if you want to support types more globally and more powerfully, Livewire exposes it's internal mechanism called Synthesizers for handling different property types so that you can add support for your own. [Read more about Synthesizers here.]

## Accessing from JS using `$wire`

Because Livewire properties are also available in the Browser via JavaScript, you can access and manipulate their JavaScript representations from AlpineJS.

AlpineJS is a lightweight JavaScript library that comes bundled with Livewire. It provides a way to build lightweight interactions into your Livewire components without needing to make full server roundtrips.

AlpineJS is useful in lots of contexts, but it was created to specifically pair well with Livewire. In fact, much of Livewire's front-end is a layer on top of Alpine. This means every Livewire component is actually also an Alpine component and you can use Alpine within your component without needing to declare `x-data` on the root element.

The rest of this page assumes a basic familiarity with Alpine. If you're unfamiliar, [take a look at the AlpineJS documentation to get up to speed.]

### Accessing properties

Livewire exposes a magic property inside Alpine called `$wire`, which you can access from any Alpine expression inside your Livewire component.

The `$wire` object can be treated like a JavaScript version of your component. It has all the same properties and methods as the PHP version of your component, but also contains a few dedicated methods to perform specific functions.

Here's an example of using `$wire` to show a live character count of the `todo` input field:

```html
<div>
	<input type="text" wire:model="todo">

	Todo character length: <h2 x-text="$wire.todo.length"></h2>
</div>
```

As the user types in the field, the character length of the current todo being written will be shown and live-updated on the page. All without sending a network request to the server.

If you prefer, you can use the more explicit `.get()` method to accomplish the same thing:

```html
<div>
	<input type="text" wire:model="todo">

	Todo character length: <h2 x-text="$wire.get('todo').length"></h2>
</div>
```

### Manipulating properties

Similarly, you can manipulate your Livewire component properties in JavaScript using `$wire`.

Here's an example of adding a "Clear" button to the `TodoList` component to allow the user to reset the input field to empty using only JavaScript:

```html
<div>
	<input type="text" wire:model="todo">

	<button x-on:click="$wire.todo = ''">Clear</button>
</div>
```

Now after the user clicks "Clear", the input will be reset to empty without sending a network request.

On the next request, the server-side value of `$todo` will be updated and it will completely syncronized.

If you prefer, you can also use the more explicit `.set()` method for setting properties client side. However, you should note that using `.set()` immediately triggers a network request and synchronizes the state with the server. If that is desired, then this is a great API for it, if not, you should instead stick with setting the property directly.

Here's an example of the same example as above but with `.set()`:

```html
<button x-on:click="$wire.todo.set('todo', '')">Clear</button>
```

## Security concerns

While Livewire properties are a powerful feature, there are a few security considerations that you should be aware of before using them.

In short, always treat public properties as user input, as if they were request input coming from a traditional endpoint. Because of this, it's important to validate and authorize properties before persisting to a database just like you would do when working with request input in a controller.

### Don't trust property values

To demonstrate how neglecting to authorize and validate properties can introduce security holes in your application, the following `UpdatePost` component is vulnerable to attack:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

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

        $post->update(
			'title' => $this->title,
			'content' => $this->content,
        );

        session()->flash('message', 'Post updated successfully!');
    }

    public function render()
    {
        return view('livewire.update-post');
    }
}
```

```html
<form wire:submit="update">
	<input type="text" wire:model="title">
	<input type="text" wire:model="content">

	<button type="submit">Update</button>
</form>
```

At first glance, this component may look completely fine to you. Now, let me walk you through how an attacker could use it to do unauthrozed things in your system.

Because we are storing the `id` of the post as a public property on the component, it can be manipulated on the client just the same as the `title` and `content` properties.

It doesn't matter that we didn't write an input with `wire:model="id"`. A maliscous user can easily change the view to the following using their browser DevTools:

```html
<form wire:submit="update">
	<input type="text" wire:model="id">
	<input type="text" wire:model="title">
	<input type="text" wire:model="content">

	<button type="submit">Update</button>
</form>
```

Now they can update the `id` input to the ID of a post model they don't own, and when they submit the form and `update()` is called, `Post::findOrFail()` will return and update a post that they are not the owner of.

To prevent this kind of attack, we can use one or both of these strategies:

A) Authorize the input
B) Lock the property from updates

#### A) Authorizing the input

Because `$id` can be manipulated client-side with something like `wire:model`, just like in a controller, we can use [Laravel authorization] to make sure the current user can update the post:

```php
public function update()
{
	$post = Post::findOrFail($this->id);

	auth()->user()->can('update', $post);

	$post->update(...);
}
```

Now, if a maliscous user mutates the `$id` property, the added authorization will catch it and throw an error.

#### B) Locking the property

Livewire provides a "locked" property feature that allows you to prevent properties from being modified on the client side. You can "lock" a property from client-side manipulation using the `#[Locked]` attribute:

```php
use Livewire\Use\Locked;

class UpdatePost extends Component
{
	#[Locked]
    public $id;

	// ...
}
```

Now, if a user tries to modify `$id` on the front-end using something like `wire:model` an error will be thrown.

By using `#[Locked]` you are safe to assume this property has not been manipulated anywhere outside your component's class.

As an added note, if instead of storing the `$id` as string property, you stored the entire `Post` model to a property called `$post`, Livewire will automatically lock the property and ensure the ID isn't changed so that you are safe from these kinds of attacks:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class UpdatePost extends Component
{
    public Post $post;
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
        $this->post->update(
			'title' => $this->title,
			'content' => $this->content,
        );

        session()->flash('message', 'Post updated successfully!');
    }

    public function render()
    {
        return view('livewire.update-post');
    }
}
```

### Properties expose system information to the browser

Another important thing to keep in mind is that Livewire properties are serialized or "dehydrated" before they are sent to the browser, which means that their values are converted to a format that can be sent over the wire and understood by JavaScript. This format can expose information about your application to the browser, including the names and class names of your properties.

For example, if you have a Livewire component that defines a public property called `$post`, that contains an instance of a `Post` model from your database, the dehydrated value of this property sent over the wire might look something like this:

```json
{
	"data": {
		"post": {
			"type": "model",
			"class": "App\Models\Post",
			"key": 1,
			"relationships": [].
		}
	}
}
```

As you can see, the dehydrated value of the `$post` property includes the class name of the model (`App\Models\Post\`) as well as the ID and any relationships that have been eager loaded.

If you don't want to expose the classname of the model, you can use Laravel's "morphMap" functionality from a service provider to assign an alias to a model class name:

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

Now, when the Eloquent model is "dehydrated" (serialized), the original classname won't be exposed, only the above "post" alias.

## Getter properties

In addition to regular properties, Livewire components also offer Getters. Getters are methods on your component marked with the `#[Getter]` attribute. They can be accessed as a dynamic property that isn't stored as part of the component's state, but is instead evaluated on-the-fly.

Getters are useful for Eloquent queries with constraints that won't be persisted between requests.

For example, if we wanted to get all the todos for a user, but only select the "title" field from the database, that select conststraint won't be re-applied between requests if we set the result to a property called `$todos`. Instead, we can return the query results from a getter and ensure the constraint is applied on each subsequent request:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class ShowTodos extends Component
{
	#[Getter]
	public function todos()
	{
		return Auth::user()
			->todos()
			->select('content')
			->get();
	}

    public function render()
    {
        return view('livewire.show-todos');
    }
}
```

```html
<ul>
	@foreach ($this->todos as $todo)
		<li>{{ $todo }}</li>
	@endforeach
</ul>
```

> Note, if you want to access getters from your component's Blade view, you have to access them on the `$this` object like so: `$this->todos`.

You can also access `$todos` from inside your class for example if you had a "markAllAsComplete" action:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class ShowTodos extends Component
{
	#[Getter]
	public function todos()
	{
		return Auth::user()
			->todos()
			->select('content')
			->get();
	}

	public function markAllComplete()
	{
		$this->todos->each->complete();
	}

    public function render()
    {
        return view('livewire.show-todos');
    }
}
```

You might be wondering, why not just call `$this->todos()` as a method directly where you need to? Why use `#[Getter]` in the first place? 

The reason is: getters have a performance advantage: they are automatically cached after their first usage during a single request. This means you can freely access `$this->todos` within your component and be assured that the actual method will only be called once so that you don't run an expensive query multiple times in the same request.
