---
Title: Properties
Order: 3
---

# Introduction

In Livewire, properties are used to store data that needs to be persisted between requests. They are defined as public properties on Livewire components and can be accessed and modified both on the server and the client side.

To define a property on a Livewire component, you simply declare a public property on the component class. For example:

```php
class MyComponent extends Component
{
    public $name = 'John Doe';
}
```

In this example, we've defined a public property called `$name` and given it an initial value of `'John Doe'`. This property can be accessed and modified from within the component's methods, as well as from the component's Blade view.

In the component's Blade view, you can use the `wire:model` directive to bind the value of a property to a form input or other HTML element. For example:

```html
<div>
    <label for="name">Name:</label>
    <input type="text" id="name" wire:model="name">
    <p>Hello, {{ $name }}!</p>
</div>
```

In this example, we've used the `wire:model` directive to bind the value of the `name` property to an input field with an `id` of `name`. Whenever the user types into this input field, Livewire will automatically update the value of the `name` property on the server and re-render the component's HTML, including the `{{ $name }}` placeholder.

You can also access and modify Livewire properties from JavaScript by using the `$wire` global variable. For example, to update the `name` property from JavaScript, you could do:

```js
$wire.set('name', 'Jane Doe');
```

This would update the `name` property to `'Jane Doe'` on the server and re-render the component's HTML.

Properties are how components track state in Livewire. They are often used for storing input field data in a form, but can be used for many things.

```
[Maybe use this as an introduction?]
Properties are the stateful variables in Livewire components, allowing you to store data and manage state throughout the lifecycle of a component. They can be easily bound to input elements or displayed on the page, and they automatically trigger component re-rendering when their values change.
```

# Security concerns

While Livewire properties are a powerful feature for building dynamic, reactive interfaces, there are a few security considerations that you should keep in mind when using them.

### Properties are mutable, don't trust their values

One important thing to keep in mind is that Livewire properties are mutable, which means that their values can be changed by the user or by the server at any time. As a result, you should always validate and authorize the values of Livewire properties before using them in your application logic.

For example, consider the following component:

```php
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

    public function updatePost()
    {
        $post = Post::findOrFail($this->id);

		auth()->user()->can('update:post', $post);

        $post->update(...);

        session()->flash('message', 'Post updated successfully!');
    }

    public function render()
    {
        return view('livewire.update-post');
    }
}
```

The `$id` property in the `UpdatePost` component is used to store the ID of the post that we want to update. However, because Livewire properties can be modified on the client side, we need to be careful to validate and authorize the `$id` value before using it in our application logic.

If we don't take appropriate steps to secure the `$id` property, a malicious user could potentially modify the value of `$id` on the client side and submit the form to update a different post than the one they are authorized to modify.

To prevent this kind of attack, we can use one of two strategies:

A) Authorize the input
B) Lock the property from updates

### A) Authorizing the input

Because `$id` can be manipulated client-side with something like `wire:model`, we can authorize that the post is one that the user owns. This is a good idea in general.
```php
public function updatePost()
{
	$post = Post::findOrFail($this->id);

	auth()->user()->can('update:post', $post);

	$post->update(...);
}
```

### B) Locking the property

Livewire provides a "locked" property feature that allows you to prevent properties from being modified on the client side. To use locked properties, you simply need to define a public `protected` array property called `$locked` on your component and list the names of any properties that should be locked:

```php
class MyComponent extends Component
{
    protected $locked = ['name'];

    public $name = 'John Doe';
}

```

In this example, we've defined a public property called `$name` and marked it as locked using the `$locked` array. This means that the value of `$name` cannot be modified on the client side using Livewire's magic, and can only be modified on the server side.

By taking appropriate steps to secure the `$id` property, we can ensure that our `UpdatePost` component is not vulnerable to attacks that could compromise the security of our application.

### Properties are "dehydrated" and expose info to the browser

Another important thing to keep in mind is that Livewire properties are "dehydrated" before they are sent to the browser, which means that their values are converted to a format that can be sent over the wire. This format can expose information about your application to the browser, including the names and class names of your Livewire components and properties.

For example, if you have a Livewire component that defines a public property called `$user`, the dehydrated value of this property might look something like this:

```json
{
    "type": "model",
    "value": {
        "class": "App\\Http\\Livewire\\UserComponent",
        "id": "H9ag4y5Q5e5x5z8w",
        "data": {
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "johndoe@example.com"
            }
        }
    }
}
```

As you can see, the dehydrated value of the `$user` property includes the class name of the component (`App\Http\Livewire\UserComponent`) as well as the name and value of the property (`user`, `{id: 1, name: "John Doe", email: "johndoe@example.com"}`).

To minimize the risk of exposing sensitive information to the browser, Livewire provides a few options for customizing the dehydrated values of properties. For example, you can use the `toBase` method on model instances to exclude certain attributes from the dehydrated value:

```php
public function mount(User $user)
{
    $this->user = $user->makeHidden(['password']);
}
```

In this example, we've used the `makeHidden` method on the `$user` model to exclude the `password` attribute from the dehydrated value. This means that even if the dehydrated value of the `$user` property is exposed to the browser, it will not include the user's password.

Similarly, if you have a Livewire component that uses a custom PHP class or object as a property value, you can define a `serialize` method on the class to customize the dehydrated value:

```php
class CustomObject
{
    public $publicData;
    protected $secretData;

    public function __construct($publicData, $secretData)
    {
        $this->publicData = $publicData;
        $this->secretData = $secretData;
    }

    public function serialize()
    {
        return [
            'publicData' => $this->publicData,
        ];
    }
}

class MyComponent extends Component
{
    public $customObject;

    public function mount()
    {
        $this->customObject = new CustomObject('public', 'secret');
    }
}
```

In this example, we've defined a custom `CustomObject` class with both public and private data, and a `serialize` method that returns only the public data when the object is dehydrated. We've also defined a `$customObject` property on the `MyComponent` component and set it to an instance of the `CustomObject` class in the `mount` method.

When the `$customObject` property is dehydrated and sent to the browser, it will only include the public data of the object (`{publicData: "public"}`), and not the secret data.

In summary, while Livewire properties are a powerful tool for building dynamic and reactive interfaces, it's important to be aware of their security implications and take appropriate steps to validate, authorize, and customize their values as needed. By following best practices for secure programming, you can minimize the risk of exposing sensitive information to the browser and keep your application secure.

# Supported property types

Livewire supports various property types, including primitive types and more complex PHP-centric types.

## Primitive Types

Primitive types like strings, integers, floats, and booleans are supported out of the box. You can use them as properties in your Livewire components:

```php
use Livewire\Component;

class MyComponent extends Component
{
    public string $name;
    public int $age;
    public float $score;
    public bool $is_active;

    // ...
}

```

## Complex PHP-centric Types

Livewire intelligently handles more complex PHP-centric types, including:

-   Collections
-   DateTime objects
-   Stringable
-   StdClass

These types are automatically serialized and deserialized when passed between Livewire components and the front-end. Here are examples of using complex PHP-centric types as properties:

```php
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use DateTime;
use Livewire\Component;

class MyComponent extends Component
{
    public Collection $tasks;
    public DateTime $dueDate;
    public Stringable $description;
    public stdClass $metadata;

    public function mount()
    {
        $this->tasks = new Collection(['Task 1', 'Task 2']);
        $this->dueDate = new DateTime('tomorrow');
        $this->description = new Stringable('Sample description');
        $this->metadata = new stdClass;
        $this->metadata->priority = 'high';
    }

    // ...
}

```

## Custom Types with Wireables

To add support for custom types, you can implement the `Wireable` interface in your class. This interface requires you to define two methods, `toLivewire` and `fromLivewire`, which are responsible for serializing and deserializing your custom object.

# Initializing properties

Livewire properties can be initialized in a couple of different ways. The two most common ways are by passing them into the component as attributes, or by setting them in the `mount` method.

## Initializing properties via attributes

One way to initialize Livewire properties is by passing them into the component as attributes. For example, let's say we have a `HelloWorld` component with a public `$name` property that we want to initialize with a value passed in from the parent component:

```php
class HelloWorld extends Component
{
    public $name;

    public function render()
    {
        return view('livewire.hello-world');
    }
}
```

In the parent component's Blade view, we can pass in the value of `$name` as an attribute:

```php
<livewire:hello-world name="John"></livewire:hello-world>
```

In this example, we're passing in the value `"John"` as the value of the `name` attribute on the `HelloWorld` component. When the component is mounted, Livewire will automatically set the value of `$name` to the value of the attribute.

### Initializing properties via the `mount` method

Another way to initialize Livewire properties is by setting them in the `mount` method. For example, let's say we have a `UserDetails` component with a public `$user` property that we want to initialize with a `User` model:

```php
class UserDetails extends Component
{
    public $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.user-details');
    }
}
```

In this example, we've defined a `mount` method that accepts a `User` model as a parameter and sets the value of `$user` to the passed-in model. This allows us to pass in a `User` model when we instantiate the component:

```php
$user = User::find(1);
return view('user-details', ['user' => $user]);
```

In this example, we're passing in a `User` model with an ID of `1` to the `UserDetails` component. When the component is mounted, Livewire will automatically call the `mount` method and set the value of `$user` to the passed-in model.

Initializing properties via attributes or the `mount` method can be a powerful way to provide data to Livewire components and set up their initial state. By leveraging these initialization methods, you can create more flexible and dynamic components that can be easily customized and reused across your application.

## Bulk assignment

```php
public $foo;

public $baz;

public function mount()
{
	$this->fill(['foo' => 'bar', 'baz' => 'bob']);
}
```

## Resetting properties

Alternatively, you can reset properties at any time:

```php
public function savePost()
{
	$this->reset(['foo', 'baz']);
}
```

And the values will be reset to their initial values.


## Accessing using $wire

There are other ways to manipulate properties using `$wire` (the magic object available to Alpine code in your Livewire components)

```html
<span x-text="$wire.foo">
<span x-text="$wire.get('foo')">
<button x-on:click="$wire.set('foo', 'bar')">
<button x-on:click="$wire.foo = bar">
```

# Computed properties

In addition to regular properties, Livewire components also support computed properties. Computed properties are dynamic properties that are not stored as part of the component's state, but are instead calculated on-the-fly based on other data in the component.

One way to define a computed property in a Livewire component is by defining a method with a name that starts with `get`, followed by the name of the property. For example, let's say we have a `PostDetails` component that needs to display details about a post, and we want to retrieve the post data from the database. We can define a computed property called `$post` that retrieves the post data from the database:

```php
class PostDetails extends Component
{
    public $postId;

    public function getPostProperty()
    {
        return Post::findOrFail($this->postId);
    }

    public function render()
    {
        return view('livewire.post-details');
    }
}
```

In this example, we've defined a `getPostProperty` method that retrieves a `Post` model from the database based on the value of the `$postId` property. Because the method name starts with `get` followed by `Post`, Livewire will automatically treat it as a computed property.

We can access the value of the computed `$post` property throughout the component using the `$this->post` syntax. For example, we might use the computed `$post` property to display the post title and content in the component's Blade view:

```php
<div>
    <h1>{{ $this->post->title }}</h1>
    <div>{{ $this->post->content }}</div>
</div>
```

In this example, we're using the `$this->post` syntax to access the value of the computed `$post` property and display the post title and content.

We can also use the computed `$post` property in other methods in the component. For example, we might define a method called `deletePost` that deletes the current post:

```php
class PostDetails extends Component
{
    public $postId;

    public function getPostProperty()
    {
        return Post::findOrFail($this->postId);
    }

    public function deletePost()
    {
        $this->post->delete();
        session()->flash('message', 'Post deleted successfully!');
        return redirect()->route('posts.index');
    }

    public function render()
    {
        return view('livewire.post-details');
    }
}
```

In this example, we're using the computed `$post` property in the `deletePost` method to delete the current post and then redirect the user to the post index page.

Computed properties can be a powerful way to create dynamic, reactive components in Livewire. By defining computed properties that automatically update based on other data in the component, you can create more flexible and dynamic components that can respond to user actions and update their state in real time.

## Supporting custom PHP types as properties

### Synthesizers

```php
public function boot()
{
	Livewire::synth(SomeSynth::class);
}
```

```php
class SomeSynth extends \Livewire\Synth
{
	public static $key = 'smthing';

	public function match ($subject)
	{
		return $subject instanceof Something;
	}

	public function dehydrate($thing)
	{
		return [
			$thing->toArray(),
			[],
		];
	}

	public function hydrate($raw, $meta)
	{
		return collect($raw);
	}
}
```

For more info on these go here: #[]

Further reading:
* Data Binding
* Validating Properties
* Property lifecycle hooks
* Custom types with Synthesizers
