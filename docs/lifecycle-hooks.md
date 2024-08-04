Livewire provides a variety of lifecycle hooks that allow you to execute code at specific points during a component's lifecycle. These hooks enable you to perform actions before or after particular events, such as initializing the component, updating properties, or rendering the template.

Here's a list of all the available component lifecycle hooks:

| Hook Method      | Description                                                                     |
|------------------|---------------------------------------------------------------------------------|
| `mount()`        | Called when a component is created                                              |
| `hydrate()`      | Called when a component is re-hydrated at the beginning of a subsequent request |
| `boot()`         | Called at the beginning of every request. Both initial, and subsequent          |
| `updating()`     | Called before updating a component property                                     |
| `updated()`      | Called after updating a property                                                |
| `rendering()`    | Called before `render()` is called                                              |
| `rendered()`     | Called after `render()` is called                                               |
| `dehydrate()`    | Called at the end of every component request                                    |
| `exception($e, $stopPropagation)` | Called when an exception is thrown                     |                    |

## Mount

In a standard PHP class, a constructor (`__construct()`) takes in outside parameters and initializes the object's state. However, in Livewire, you use the `mount()` method for accepting parameters and initializing the state of your component.

Livewire components don't use `__construct()` because Livewire components are _re-constructed_ on subsequent network requests, and we only want to initialize the component once when it is first created.

Here's an example of using the `mount()` method to initialize the `name` and `email` properties of an `UpdateProfile` component:

```php
use Illuminate\Support\Facades\Auth;
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

As mentioned earlier, the `mount()` method receives data passed into the component as method parameters:

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

> [!tip] You can use dependency injection with all hook methods
> Livewire allows you to resolve dependencies out of [Laravel's service container](https://laravel.com/docs/container#automatic-injection) by type-hinting method parameters on lifecycle hooks.

The `mount()` method is a crucial part of using Livewire. The following documentation provides further examples of using the `mount()` method to accomplish common tasks:

* [Initializing properties](/docs/properties#initializing-properties)
* [Receiving data from parent components](/docs/nesting#passing-props-to-children)
* [Accessing route parameters](/docs/components#accessing-route-parameters)

## Boot

As helpful as `mount()` is, it only runs once per component lifecycle, and you may want to run logic at the beginning of every single request to the server for a given component.

For these cases, Livewire provides a `boot()` method where you can write component setup code that you intend to run every single time the component class is booted: both on initialization and on subsequent requests.

The `boot()` method can be useful for things like initializing protected properties, which are not persisted between requests. Below is an example of initializing a protected property as an Eloquent model:

```php
use Livewire\Attributes\Locked;
use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    #[Locked]
    public $postId = 1;

    protected Post $post;

    public function boot() // [tl! highlight:3]
    {
        $this->post = Post::find($this->postId);
    }

    // ...
}
```

You can use this technique to have complete control over initializing a component property in your Livewire component.

> [!tip] Most of the time, you can use a computed property instead
> The technique used above is powerful; however, it's often better to use [Livewire's computed properties](/docs/computed-properties) to solve this use case.

> [!warning] Always lock sensitive public properties
> As you can see above, we are using the `#[Locked]` attribute on the `$postId` property. In a scenario like the above, where you want to ensure the `$postId` property isn't tampered with by users on the client-side, it's important to authorize the property's value before using it or add `#[Locked]` to the property ensure it is never changed.
>
> For more information, check out the [documentation on Locked properties](/docs/locked).


## Update

Client-side users can update public properties in many different ways, most commonly by modifying an input with `wire:model` on it.

Livewire provides convenient hooks to intercept the updating of a public property so that you can validate or authorize a value before it's set, or ensure a property is set in a given format.

Below is an example of using `updating` to prevent the modification of the `$postId` property.

It's worth noting that for this particular example, in an actual application, you should use the [`#[Locked]` attribute](/docs/locked) instead, like in the above example.

```php
use Exception;
use Livewire\Component;

class ShowPost extends Component
{
    public $postId = 1;

    public function updating($property, $value)
    {
        // $property: The name of the current property being updated
        // $value: The value about to be set to the property

        if ($property === 'postId') {
            throw new Exception;
        }
    }

    // ...
}
```

The above `updating()` method runs before the property is updated, allowing you to catch invalid input and prevent the property from updating. Below is an example of using `updated()` to ensure a property's value stays consistent:

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

Now, anytime the `$username` property is updated client-side, we will ensure that the value will always be lowercase.

Because you are often targeting a specific property when using update hooks, Livewire allows you to specify the property name directly as part of the method name. Here's the same example from above but rewritten utilizing this technique:

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

Of course, you can also apply this technique to the `updating` hook.

### Arrays

Array properties have an additional `$key` argument passed to these functions to specify the changing element.

Note that when the array itself is updated instead of a specific key, the `$key` argument is null.

```php
use Livewire\Component;

class UpdatePreferences extends Component
{
    public $preferences = [];

    public function updatedPreferences($value, $key)
    {
        // $value = 'dark'
        // $key   = 'theme'
    }

    // ...
}
```

## Hydrate & Dehydrate

Hydrate and dehydrate are lesser-known and lesser-utilized hooks. However, there are specific scenarios where they can be powerful.

The terms "dehydrate" and "hydrate" refer to a Livewire component being serialized to JSON for the client-side and then unserialized back into a PHP object on the subsequent request.

We often use the terms "hydrate" and "dehydrate" to refer to this process throughout Livewire's codebase and the documentation. If you'd like more clarity on these terms, you can learn more by [consulting our hydration documentation](/docs/hydration).

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
        // Runs at the beginning of every "subsequent" request...
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

Now, from actions and other places inside your component, you can access the `PostDto` object instead of the primitive data.

The above example mainly demonstrates the abilities and nature of the `hydrate()` and `dehydrate()` hooks. However, it is recommended that you use [Wireables or Synthesizers](/docs/properties#supporting-custom-types) to accomplish this instead.

## Render

If you want to hook into the process of rendering a component's Blade view, you can do so using the `rendering()` and `rendered()` hooks:

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
        // $html: The final, rendered HTML
    }

    // ...
}
```

## Exception

Sometimes it can be helpful to intercept and catch errors, eg: to customize the error message or ignore specific type of exceptions. The `exception()` hook allows you to do just that: you can perform check on the `$error`, and use the `$stopPropagation` parameter to catch the issue.
This also unlocks powerful patterns when you want to stop further execution of code (return early), this is how internal methods like `validate()` works.

```php
use Livewire\Component;

class ShowPost extends Component
{
    public function mount() // [tl! highlight:3]
    {
        $this->post = Post::find($this->postId);
    }

    public function exception($e, $stopPropagation) {
        if($e instanceof NotFoundException) {
            $this->notify('Post is not found')
            $stopPropagation();
        }
    }

    // ...
}
```

## Using hooks inside a trait

Traits are a helpful way to reuse code across components or extract code from a single component into a dedicated file.

To avoid multiple traits conflicting with each other when declaring lifecycle hook methods, Livewire supports prefixing hook methods with the _camelCased_ name of the current trait declaring them.

This way, you can have multiple traits using the same lifecycle hooks and avoid conflicting method definitions.

Below is an example of a component referencing a trait called `HasPostForm`:

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

    public function mountHasPostForm()
    {
        // ...
    }

    public function hydrateHasPostForm()
    {
        // ...
    }

    public function bootHasPostForm()
    {
        // ...
    }

    public function updatingHasPostForm()
    {
        // ...
    }

    public function updatedHasPostForm()
    {
        // ...
    }

    public function renderingHasPostForm()
    {
        // ...
    }

    public function renderedHasPostForm()
    {
        // ...
    }

    public function dehydrateHasPostForm()
    {
        // ...
    }

    // ...
}
```
