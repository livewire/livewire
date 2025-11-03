Livewire components are essentially PHP classes with properties and methods can be called directly from a Blade template. This powerful combination allows you to create full-stack interactive interfaces with a fraction of the effort and complexity of modern JavaScript alternatives.

## Creating components

You can create a component using the `make:livewire` Artisan command and providing a kebab-cased component name like the following:

```shell
php artisan make:livewire create-post
```

Running this command will create a single-file component like the following:

`resources/views/components/⚡create-post.blade.php`
```blade
<?php

use Livewire\Component;

new class extends Component
{
    public $title = '';

    public function save()
    {
        // Save logic here...
    }
};
?>

<div>
    <input wire:model="title" type="text">
    <button wire:click="save">Save Post</button>
</div>
```

> [!info] Why the ⚡ emoji?
> You might be wondering about the lightning bolt in the filename. This small touch serves a practical purpose: it makes Livewire components instantly recognizable in your editor's file tree and search results. Since it's a Unicode character, it works seamlessly across all platforms — Windows, macOS, Linux, Git, and your production servers.
>
> The emoji is completely optional and if you find it outside your comfort zone you can disable it entirely with the following configuration:
>
> ```php
> 'make_command' => [
>     'emoji' => false,
> ],
> ```

### Creating components with namespaces

Livewire supports component namespaces, allowing you to organize components into specific directories.

By default, Livewire provides two namespaces:
- `pages::` — Points to `resources/views/pages/`
- `layouts::` — Points to `resources/views/layouts/`

You can define additional namespaces in your `config/livewire.php` file:

```php
'component_namespaces' => [
    'layouts' => resource_path('views/layouts'),
    'pages' => resource_path('views/pages'),
    'admin' => resource_path('views/admin'),    // Custom namespace
    'widgets' => resource_path('views/widgets'), // Another custom namespace
],
```

Then use them when creating components:

```shell
php artisan make:livewire admin::users-table
php artisan make:livewire widgets::stats-card
```

These namespaces also work when rendering components and defining routes:

```blade
<livewire:admin::users-table />
```

```php
Route::livewire('/admin/users-table', 'admin::users-table');
```

### Multi-file components

As your component or project grows, you might find the single-file approach limiting. Therefore Livewire offers a multi-file alternative that allows you to split your component into separate files.

This gives you better _separation of concerns_ and IDE support without sacrificing the benefits of colocation.

To create a multi-file component, pass the `--mfc` flag to the make command like so:

```shell
php artisan make:livewire create-post --mfc
```

Now, the new component will be created as a directory with all related files together:

```
resources/views/components/⚡create-post/
├── create-post.php        # PHP class
├── create-post.blade.php  # Blade template
├── create-post.js         # JavaScript (optional, with --js flag)
└── create-post.test.php   # Pest test (optional)
```

### Converting between formats

Livewire provides the `livewire:convert` command to seamlessly convert components between single-file and multi-file formats.

**Auto-detect and convert:**

If you don't specify a format, Livewire will automatically detect the current format and convert to the opposite:

```shell
php artisan livewire:convert create-post
# Single-file → Multi-file (or vice versa)
```

**Explicitly convert to multi-file:**

```shell
php artisan livewire:convert create-post --mfc
```

This will:
* Parse your single-file component
* Create a new directory for the multi-file component
* Split the PHP and Blade code into separate files
* Extract any JavaScript into its own file
* Delete the original single-file component

You can also create a test file during conversion by adding the `--test` flag:

```shell
php artisan livewire:convert create-post --mfc --test
```

**Explicitly convert to single-file:**

```shell
php artisan livewire:convert create-post --sfc
```

This will:
* Parse your multi-file component
* Combine the PHP class and Blade view into a single file
* Embed any JavaScript into a `<script>` tag
* Delete the multi-file directory

> [!warning] Test files are deleted when converting to single-file
> If your multi-file component has a test file, you'll be prompted to confirm before conversion since test files cannot be preserved in the single-file format.

The convert command works with nested components too:

```shell
php artisan livewire:convert admin.user-form --mfc
```

## Rendering components

You can include a Livewire component within any Blade template using the `<livewire:component-name />` syntax:

```blade
<livewire:create-post />
```

If the component is located in a sub-directory, you can indicate this using the dot (`.`) character. For example:

`resources/views/components/post/⚡create.blade.php`
```blade
<livewire:post.create />
```

### Passing props

To pass outside data into a Livewire component, you can use prop attributes on the component tag. This is useful when you want to initialize a component with specific data.

To pass an initial value to the `$title` property of the `create-post` component, you can use the following syntax:

```blade
<livewire:create-post title="Initial Title" />
```

If you need to pass dynamic values or variables to a component, you can write PHP expressions in component attributes by prefixing the attribute with a colon:

```blade
<livewire:create-post :title="$initialTitle" />
```

Data passed into components is received through the `mount()` method parameters:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $title;

    public function mount($title = null)
    {
        $this->title = $title;
    }

    // ...
};
```

You can think of the `mount()` method as a class constructor. It runs when the component initializes, but not on subsequent requests within a page's session. You can learn more about `mount()` and other helpful lifecycle hooks within the [lifecycle documentation](/docs/4.x/lifecycle-hooks).

To reduce boilerplate code in your components, you can alternatively omit the `mount()` method and Livewire will automatically set any properties on your component with names matching the passed in values:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $title; // [tl! highlight]

    // ...
};
```

This is effectively the same as assigning `$title` inside a `mount()` method.

> [!warning] These properties are not reactive by default
> The `$title` property will not update automatically if the outer `:title="$initialValue"` changes after the initial page load. This is a common point of confusion when using Livewire, especially for developers who have used JavaScript frameworks like Vue or React and assume these "parameters" behave like "reactive props" in those frameworks. But, don't worry, Livewire allows you to opt-in to [making your props reactive](/docs/4.x/nesting#reactive-props).

### Page components

Components can also be routed to directly as full pages using `Route::livewire()`.

```php
Route::livewire('/posts/create', 'create-post');
```

For complete information about using components as pages, including layouts, titles, and route parameters, see the [Pages documentation](/docs/4.x/pages).

## Accessing data from view

Livewire provides several ways to pass data to your component's Blade view. Each approach has different performance and security characteristics, so understanding when to use each method is important.

### Passing data directly

Similar to a controller, you can pass data directly into the component's Blade view using the `view()` method inside `render()`:

```blade
<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view([
            'author' => Auth::user(),
            'currentTime' => now(),
        ]);
    }
};

<div>
    <p>Welcome, {{ $author->name }}</p>
    <p>Current time: {{ $currentTime }}</p>
</div>
```

This approach is useful for data that doesn't need to be a component property, but keep in mind that the `render()` method runs on every component update, so avoid expensive operations here.

### Computed properties

Computed properties allow you to define methods that act like memoized properties in your Blade templates. By adding the `#[Computed]` attribute to a method, Livewire will automatically memoize (cache for the duration of the request) its return value after the first call, making it perfect for expensive operations like database queries or complex calculations.

```php
<?php

use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function posts()
    {
        // This expensive query only runs once per request
        return Post::with('author')->latest()->get();
    }
};
```

```blade
<div>
    @foreach ($this->posts as $post)
        <article>{{ $post->title }}</article>
    @endforeach
</div>
```

Notice that computed properties must be accessed using `$this->` in your Blade template. This tells Livewire to call the method and cache the result.

For more details about computed properties, see the [computed properties section](/docs/4.x/properties#computed-properties) in the properties documentation.

### Component properties

You can also pass data to your view through component properties. Public properties are automatically available in your Blade template, while protected properties must be accessed with `$this->`:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $title = 'My Post';           // Available as {{ $title }}
    protected $secretKey = 'abc123';     // Available as {{ $this->secretKey }}
};
```

```blade
<div>
    <h1>{{ $title }}</h1>
    <input type="hidden" value="{{ $this->secretKey }}">
</div>
```

For complete information about properties, including security considerations, persistence behavior, and advanced features, see the [properties documentation](/docs/4.x/properties).

## Registering components

While Livewire automatically discovers components in your configured locations, you may sometimes need to manually register components. This is useful for organizing components, creating aliases, or integrating components from packages.

### Individual components

You can register individual components using the `addComponent()` method. This is useful for creating component aliases or registering components that live outside your standard locations.

For single-file and multi-file components, specify the view path:

```php
use Livewire\Livewire;

// In a service provider...
Livewire::addComponent(
    name: 'custom-button',
    path: resource_path('views/ui/button.blade.php')
);
```

### Component locations

Use `addLocation()` to register entire directories where Livewire should look for components. This is useful for adding additional component directories beyond the defaults.

```php
use Livewire\Livewire;

// In a service provider...
Livewire::addLocation(
    path: resource_path('views/admin/components')
);
```

### Component namespaces

Use `addNamespace()` to create named namespaces for better component organization. This allows you to group related components and reference them using namespace syntax.

```php
use Livewire\Livewire;

// In a service provider...
Livewire::addNamespace(
    namespace: 'ui',
    path: resource_path('views/ui')
);
```

Now you can reference components in this namespace:

```php
<livewire:ui::button />
```

## Class-based components

For teams migrating from Livewire v3 or those who prefer a more traditional Laravel structure, Livewire still fully supports class-based components. This approach separates the PHP class and Blade view into different files in their conventional locations.

### Creating class-based components

```shell
php artisan make:livewire create-post --class
```

This creates two separate files in their standard locations:

`app/Livewire/CreatePost.php`
```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
	public function render()
	{
		return view('livewire.create-post');
	}
}
```

`resources/views/livewire/create-post.blade.php`
```blade
<div>
	{{-- ... --}}
</div>
```

If you wish to configure the make command to generate class-based components by default, you can do so in your Livewire config file:

```php
// config/livewire.php
'make_command' => [
    'type' => 'class',
],
```

#### Registering class-based components

Class-based components can also be registered manually using the same methods shown earlier, but with the `class` parameter instead of `path`:

```php
use Livewire\Livewire;
use App\Livewire\CustomButton;

// In a service provider...

// Register an individual class-based component
Livewire::addComponent(
    name: 'custom-button',
    class: CustomButton::class
);

// Register a location for class-based components
Livewire::addLocation(
    class: 'App\\Admin\\Livewire'
);

// Create a namespace for class-based components
Livewire::addNamespace(
    namespace: 'admin',
    class: 'App\\Admin\\Livewire'
);
```

## Customizing component stubs

You can customize the files (or _stubs_) Livewire uses to generate new components by running the following command:

```shell
php artisan livewire:stubs
```

This will create stub files in your application:

**Single-file component stubs:**
* `stubs/livewire-sfc.stub` — Single-file components

**Multi-file component stubs:**
* `stubs/livewire-mfc-class.stub` — PHP class for multi-file components
* `stubs/livewire-mfc-view.stub` — Blade view for multi-file components
* `stubs/livewire-mfc-js.stub` — JavaScript for multi-file components
* `stubs/livewire-mfc-test.stub` — Pest test for multi-file components

**Class-based component stubs:**
* `stubs/livewire.stub` — PHP class for class-based components
* `stubs/livewire.view.stub` — Blade view for class-based components

**Additional stubs:**
* `stubs/livewire.attribute.stub` — Attribute classes
* `stubs/livewire.form.stub` — Form classes
* `stubs/livewire.test.stub` — PHPUnit test files
* `stubs/livewire.pest-test.stub` — Pest test files

Even though these files live in your application, you can still use the `make:livewire` Artisan command and Livewire will automatically use your custom stubs when generating files.
