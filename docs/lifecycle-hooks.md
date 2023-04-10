Livewire provides a set of lifecycle hooks that allow you to execute code at specific points during a component's lifecycle. These hooks enable you to perform actions before or after specific events, such as initializing the component, updating properties or rendering the template.

Here's a list of all the available component lifecycle hooks:

| Hook Method        | Description                               |
|-----------------|-------------------------------------------|
| `mount()`    | Called when a component is created |
| `hydrate()`    | Called when a component is re-hydrated at the beginning of a subsequent request |
| `boot()`    | Called at the beginning of every request. Both initial, and subsequent |
| `updating()`    | Called before updating a component property |
| `updated()`    | Called after updating a property |
| `rendering()`    | Called before `render()` is called |
| `rendered()`    | Called after `render()` is called |
| `dehydrate()`    | Called at the end of every component request |

## Mount

In a standard PHP class, you typically use a constructor (`__construct()`) to take in outside parameters and initialize the state of the object. In Livewire, instead, you use the `mount()` method for accepting parametrs and intializing the state of your component.

The reason we don't use `__construct`, is Livewire components are "re-constructed" on subsequent network requets and we only want to initialize the component once, when it is first created.

Here's an example of using the `mount()` method to initialize the `name` and `email` properties of an `UpdateProfile` component:

```php
use Livewire\Component;

class UpdateProfile extends Component
{
    public $name;

    public $email;

    public function mount()
    {
        $this->name = Auth::user()->name;

        $this->email = Auth::user()->email;
    }

    // ...
}
```

As mentioned eariler, the `mount()` method recieves any data passed into the component as method parameters.

```php
use Livewire\Component;
use App\Models\Post;

class UpdatePost extends Component
{
    public $title;

    public $content;

    public function mount(Post $post)
    {
        $this->title = $post->title;

        $this->content = $post->content;
    }

    // ...
}
```

> [!tip] You can use dependancy injection with all hook methods
> If you normally utilize Laravel's method parameter dependancy injection in things like Controllers, Livewire brings this behavior to it's lifecycle hooks so you can use them here to!

Here are a few more places to learn about how and when to use the `mount()` method:

* Initializing properties
* Accessing outside data
* Accessing route parameters

## Boot

As useful as `mount()` is, it only runs once per component lifecycle. Sometimes you may want to run logic at the beginning of every single request to the server for a given component.

For these cases, Livewire provides a `boot()` method where you can write component setup code that you intend to run every single time the component class is booted up: both on initialization and on subsequent requests.

This can be useful for things like initializing protected properties, which are not persisted between requests. Below is an example of initializing a protected property as an eloquent model by referencing a public property:

```php
use Livewire\Component;
use Livewire\With\Locked;
use App\Models\Post;

class ShowPost extends Component
{
    #[Locked]
    public $postId = 1;

    protected Post $post;

    public function boot()
    {
        $this->post = Post::find($this->postId);
    }

    // ...
}
```

This is a technique you can use to have full control over initializing a component property in your Livewire component.

> [!warning] Always lock sensitive public properties
> As you can see above, we are using the `#[Locked]` attribute on the `$postId` property. In a scenario like above where you want to ensure the `$postId` property isn't tampered with by users on the client-side, it's important to authorize their value before using them or add `#[Locked]` to ensure they are never changed. [You can read more about this security concern here.](todo)

The technique used above to support a more sophistocated property type in a protected property between requests is poweful, but often a better alternative is to use [Livewire Getters](todo) to accomplish this same thing. Either technique is valid though, chose whichever you prefer.

## Update

Client-side users are able to update public properties in many different ways, most commonly by modifying an input with `wire:model` on it.

Livewire provides convenient hooks to intercept the updating of a public property so that you can validate or authorize a value before it's set, or ensure a property is set in a given format.

Below is an example of using `updating` to prevent the modification of the `$postId` property. 

It's worth noting that in a real application, you should use the `#[Locked]` attribute instead like in the example above.

```php
use Livewire\Component;

class ShowPost extends Component
{
    public $postId = 1;

    public function updating($property, $value)
    {
        // $property: The name of the current property being updated
        // $value: The value about to be set to the property

        if ($property === 'postId') {
            throw new \Exception;
        }
    }

    // ...
}
```

The above `updating()` method runs BEFORE the actual property is updated, allowing you catch invalid input and prevent it's updating. Below is an example of using `updated()` to ensure that a properties value stays consistent. 

```php
use Livewire\Component;

class CreateUser extends Component
{
    public $username = '';

    public $email = '';

    public function updated($property)
    {
        // $property: The name of the current property that was updated

        if ($property === 'username') {
            $this->username = strtolower($this->username);
        }
    }

    // ...
}
```

Now, anytime the `$username` property is updated client-side, because we added the `updated()` hook, we can ensure that the value will always be lowercase.

Because you are often targeting a specific property when using update hooks, Livewire allows you to specify the property name directly as part of the method name. Here's the same example from about but rewritten using this technique.

```php
use Livewire\Component;

class CreateUser extends Component
{
    public $username = '';

    public $email = '';

    public function updatedUsername()
    {
        $this->username = strtolower($this->username);
    }

    // ...
}
```

This cleans up the code quite a bit by getting rid of the conditional check for the "username" property.

Of course, you can apply this technique to the `updating` hook as well.

## Hydrate & Dehydrate

Hydrate and dehydrate are lesser known and lesser utilized hooks. However, there are certain scenarios where they can be powerful.

The terms "dehydrate" and "hydrate" refer to the process of a Livewire component being serialized to JSON for the client-side and then unserialized back into a PHP on the next request.

We use the terms "hydrate" and "dehydrate" to refer to this process quite often throughout Livewire's codebase and the documentation as well. If you'd like more clarity on them, [you can get a deeper understanding here.](todo)

Let's look at an example that uses both `mount()` , `hydrate()`, and `dehydrate()` all together to support using a custom [data transfer object (DTO)](https://en.wikipedia.org/wiki/Data_transfer_object) instead of an Eloquent model to store the post data in the component:

```php
use Livewire\Component;

class ShowPost extends Component
{
    public $post;

    public function mount($title, $content)
    {
        // Runs at the beginning of the first initial request...

        $this->post = new PostDto([
            'title' => $title,
            'content' => $content,
        ]);
    }

    public function hydrate()
    {
        // Runs at the begninning of every "subsequent" request...
        // This doesn't run on the initial request ("mount" does)... 

        $this->post = new PostDto($this->post);
    }

    public function dehydrate()
    {
        // Runs at the end of every single request...

        $this->post = $this->post->toArray();
    }

    // ...
}
```

Now, from actions and other places inside your component, you can access the `PostDto` object instead of the primitive data directly.

The above example is used mainly to demonstrate the abilities and nature of the `hydrate()` and `dehydrate()` hooks. However, it is recommended that you use [Wireables](todo) or [Synthesizers](todo) to accomplish this instead.

See [Supporting custom property types] for more ergonomic variations of this technique.

## Render

If, for any reason, you want to hook into the process of rendering a component's Blade view, you can do so using the `rendering()` and `rendered()` hooks: 

```php
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function render()
    {
        return view('livewire.show-posts', [
            'post' => Post::all(),
        ])
    }

    public function rendering($view, $data)
    {
        // Runs BEFORE the provided view is rendered...
        //
        // $view: The view about to be rendered
        // $data: The data provided to the view
    }

    public function rendered($view, $html)
    {
        // Runs AFTER the provided view is rendered...
        //
        // $view: The rendered view
        // $html: The final, rendered, HTML
    }

    // ...
}
```

## Using hooks inside a trait

Traits are a helpful way to re-use code across components, or sometimes to just extract code from a single component into a dedicated file.

To avoid multiple traits from conflicting with each other when declaring lifecycle hook methods, Livewire supports prefixing hook methods with the camelCased name of the current trait declaring them.

This way you can have multiple traits, all using the same lifecycle hooks, and avoid conflicting method definitions.

Here's a component referencing a trait called `HasPostForm`:

```php
use Livewire\Component;

class CreatePost extends Component
{
    use HasPostForm;

    // ...
}
```

Now here's the actual `HasPostForm` trait containing all the available prefixed hooks:

```php
trait HasPostForm
{
    public $title = '';

    public $content = '';

    public function hasPostFormMount()
    {
        // ...
    }

    public function hasPostFormHydrate()
    {
        // ...
    }

    public function hasPostFormBoot()
    {
        // ...
    }

    public function hasPostFormUpdating()
    {
        // ...
    }

    public function hasPostFormUpdated()
    {
        // ...
    }

    public function hasPostFormRendering()
    {
        // ...
    }

    public function hasPostFormRendered()
    {
        // ...
    }

    public function hasPostFormDehydrated()
    {
        // ...
    }

    // ...
}
```

Traits are a powerful Livewire abstraction. You can learn more about [other available abstractions here.](todo)

---

* API Reference
* JavaScript Lifecycle Hooks
