Livewire components are essentially PHP classes with properties and methods that can be called directly from a Blade template. This powerful combination allows you to create full-stack interactive interfaces with a fraction of the effort and complexity of modern JavaScript alternatives.

This guide covers everything you need to know about creating, rendering, and organizing Livewire components. You'll learn about the different component formats available (single-file, multi-file, and class-based), how to pass data between components, and how to use components as full pages.

## Creating components

You can create a component using the `make:livewire` Artisan command:

```shell
php artisan make:livewire post.create
```

This creates a single-file component at:

`resources/views/components/post/⚡create.blade.php`
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
> The emoji is completely optional and if you find it outside your comfort zone you can disable it entirely in your `config/livewire.php` file:
>
> ```php
> 'make_command' => [
>     'emoji' => false,
> ],
> ```

### Creating page components

When creating components that will be used as full pages, use the `pages::` namespace to organize them in a dedicated directory:

```shell
php artisan make:livewire pages::post.create
```

This creates the component at `resources/views/pages/post/⚡create.blade.php`. This organization makes it clear which components are pages versus reusable UI components.

Learn more about using components as pages in the [Page components section](#page-components) below.

### Multi-file components

As your component or project grows, you might find the single-file approach limiting. Livewire offers a multi-file alternative that splits your component into separate files for better organization and IDE support.

To create a multi-file component, pass the `--mfc` flag:

```shell
php artisan make:livewire post.create --mfc
```

This creates a directory with all related files together:

```
resources/views/components/⚡post.create/
├── post.create.php        # PHP class
├── post.create.blade.php  # Blade template
├── post.create.js         # JavaScript (optional, with --js flag)
└── post.create.test.php   # Pest test (optional, with --test flag)
```

### Converting between formats

Livewire provides the `livewire:convert` command to seamlessly convert components between single-file and multi-file formats.

**Auto-detect and convert:**

```shell
php artisan livewire:convert post.create
# Single-file → Multi-file (or vice versa)
```

**Explicitly convert to multi-file:**

```shell
php artisan livewire:convert post.create --mfc
```

This will parse your single-file component, create a directory structure, split the files, and delete the original.

**Explicitly convert to single-file:**

```shell
php artisan livewire:convert post.create --sfc
```

This combines all files back into a single file and deletes the directory.

> [!warning] Test files are deleted when converting to single-file
> If your multi-file component has a test file, you'll be prompted to confirm before conversion since test files cannot be preserved in the single-file format.

### When to use each format

**Single-file components (default):**
- Best for most components
- Keeps related code together
- Easy to understand at a glance
- Perfect for small to medium components

**Multi-file components:**
- Better for large, complex components
- Improved IDE support and navigation
- Clearer separation when components have significant JavaScript
- Easier to test with dedicated test files

**Class-based components:**
- Familiar to developers from Livewire v2/v3
- Traditional Laravel separation of concerns
- Better for teams with established conventions
- See [Class-based components](#class-based-components) below

## Rendering components

You can include a Livewire component within any Blade template using the `<livewire:component-name />` syntax:

```blade
<livewire:post.create />
```

If the component is located in a sub-directory, you can indicate this using the dot (`.`) character:

`resources/views/components/post/⚡create.blade.php`
```blade
<livewire:post.create />
```

For page components, use the namespace prefix:

```blade
<livewire:pages::post.create />
```

### Passing props

To pass data into a Livewire component, you can use prop attributes on the component tag:

```blade
<livewire:post.create title="Initial Title" />
```

For dynamic values or variables, prefix the attribute with a colon:

```blade
<livewire:post.create :title="$initialTitle" />
```

Data passed into components is received through the `mount()` method:

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

To reduce boilerplate code, you can omit the `mount()` method and Livewire will automatically set any properties with names matching the passed values:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $title; // Automatically set from prop

    // ...
};
```

> [!warning] These properties are not reactive by default
> The `$title` property will not update automatically if the outer `:title="$initialValue"` changes after the initial page load. This is a common point of confusion when using Livewire, especially for developers who have used JavaScript frameworks like Vue or React and assume these "parameters" behave like "reactive props" in those frameworks. But, don't worry, Livewire allows you to opt-in to [making your props reactive](/docs/4.x/nesting#reactive-props).

### Passing route parameters as props

When using components as pages, you can pass route parameters directly to your component. The route parameters are automatically passed to the `mount()` method:

```php
Route::livewire('/posts/{id}', 'pages::post.show');
```

```php
<?php // resources/views/pages/post/⚡show.blade.php

use Livewire\Component;

new class extends Component
{
    public $postId;

    public function mount($id)
    {
        $this->postId = $id;
    }
};
```

Livewire also supports Laravel's route model binding:

```php
Route::livewire('/posts/{post}', 'pages::post.show');
```

```php
<?php // resources/views/pages/post/⚡show.blade.php

use App\Models\Post;
use Livewire\Component;

new class extends Component
{
    public Post $post; // Automatically bound from route

    // No mount() needed - Livewire handles it automatically
};
```

## Page components

Components can be routed to directly as full pages using `Route::livewire()`. This is one of Livewire's most powerful features, allowing you to build entire pages without traditional controllers.

```php
Route::livewire('/posts/create', 'pages::post.create');
```

When a user visits `/posts/create`, Livewire will render the `pages::post.create` component inside your application's layout file.

Page components work just like regular components, but they're rendered as full pages with access to:
- Custom layouts
- Page titles
- Route parameters and model binding
- Named slots for layouts

For complete information about page components, including layouts, titles, and advanced routing, see the [Pages documentation](/docs/4.x/pages).

## Accessing data in views

Livewire provides several ways to pass data to your component's Blade view. Each approach has different performance and security characteristics.

### Component properties

The simplest approach is using public properties, which are automatically available in your Blade template:

```php
<?php

use Livewire\Component;

new class extends Component
{
    public $title = 'My Post';
};
```

```blade
<div>
    <h1>{{ $title }}</h1>
</div>
```

Protected properties must be accessed with `$this->`:

```php
public $title = 'My Post';           // Available as {{ $title }}
protected $apiKey = 'secret-key';    // Available as {{ $this->apiKey }}
```

> [!info] Protected properties are not sent to the frontend
> Unlike public properties, protected properties are never sent to the frontend and cannot be manipulated by users. This makes them safe for sensitive data. However, they are not persisted between requests, which limits their usefulness in most Livewire scenarios. They're best used for static values defined in the property declaration that you don't want exposed to the frontend.

For complete information about properties, including persistence behavior and advanced features, see the [properties documentation](/docs/4.x/properties).

### Computed properties

Computed properties are methods that act like memoized properties. They're perfect for expensive operations like database queries:

```php
use Livewire\Attributes\Computed;

#[Computed]
public function posts()
{
    return Post::with('author')->latest()->get();
}
```

```blade
<div>
    @foreach ($this->posts as $post)
        <article>{{ $post->title }}</article>
    @endforeach
</div>
```

Notice the `$this->` prefix - this tells Livewire to call the method and cache the result. For more details, see the [computed properties section](/docs/4.x/properties#computed-properties) in the properties documentation.

### Passing data from render()

Similar to a controller, you can pass data directly to the view using the `render()` method:

```php
public function render()
{
    return $this->view([
        'author' => Auth::user(),
        'currentTime' => now(),
    ]);
}
```

Keep in mind that `render()` runs on every component update, so avoid expensive operations here unless you need fresh data on every update.

## Organizing components

While Livewire automatically discovers components in the default `resources/views/components/` directory, you can customize where Livewire looks for components and organize them using namespaces.

### Component namespaces

Component namespaces allow you to organize components into dedicated directories with a clean reference syntax.

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

Then use them when creating, rendering, and routing:

```shell
php artisan make:livewire admin::users-table
```

```blade
<livewire:admin::users-table />
```

```php
Route::livewire('/admin/users', 'admin::users-table');
```

### Additional component locations

If you want Livewire to discover components in additional directories beyond the defaults, you can configure them in your `config/livewire.php` file:

```php
'component_paths' => [
    resource_path('views/components'),
    resource_path('views/admin/components'),
    resource_path('views/widgets'),
],
```

Now Livewire will automatically discover components in all these directories.

### Programmatic registration

For more dynamic scenarios (like package development or runtime configuration), you can register components, locations, and namespaces programmatically in a service provider:

**Register an individual component:**

```php
use Livewire\Livewire;

// In a service provider's boot() method (e.g., App\Providers\AppServiceProvider)
Livewire::addComponent(
    name: 'custom-button',
    viewPath: resource_path('views/ui/button.blade.php')
);
```

**Register a component directory:**

```php
Livewire::addLocation(
    viewPath: resource_path('views/admin/components')
);
```

**Register a namespace:**

```php
Livewire::addNamespace(
    namespace: 'ui',
    viewPath: resource_path('views/ui')
);
```

This approach is useful when you need to register components conditionally or when building Laravel packages that provide Livewire components.

## Class-based components

For teams migrating from Livewire v3 or those who prefer a more traditional Laravel structure, Livewire fully supports class-based components. This approach separates the PHP class and Blade view into different files in their conventional Laravel locations.

### Creating class-based components

```shell
php artisan make:livewire CreatePost --class
```

This creates two separate files:

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

### When to use class-based components

**Use class-based components when:**
- Migrating from Livewire v2/v3
- Your team prefers traditional Laravel file structure
- You have established conventions around class-based architecture
- Working in a large team that values consistent separation patterns

**Use single-file or multi-file components when:**
- Starting a new Livewire v4 project
- You want better component colocation
- Your team prefers modern component-based patterns
- You want the benefits of single-file simplicity with the option to split later

### Configuring default component type

If you want class-based components by default, configure it in `config/livewire.php`:

```php
'make_command' => [
    'type' => 'class',
],
```

### Registering class-based components

Class-based components can be registered manually using the same methods shown earlier, but with the `class` parameter instead of `path`:

```php
use Livewire\Livewire;
use App\Livewire\CustomButton;

// In a service provider's boot() method (e.g., App\Providers\AppServiceProvider)

// Register an individual class-based component
Livewire::addComponent(
    name: 'custom-button',
    class: CustomButton::class
);

// Register a location for class-based components
Livewire::addLocation(
    classNamespace: 'App\\Admin\\Livewire'
);

// Create a namespace for class-based components
Livewire::addNamespace(
    namespace: 'admin',
    classNamespace: 'App\\Admin\\Livewire',
    classPath: app_path('Admin/Livewire'),
    classViewPath: resource_path('views/admin/livewire')
);
```

## Customizing component stubs

You can customize the files (or _stubs_) Livewire uses to generate new components by running:

```shell
php artisan livewire:stubs
```

This creates stub files in your application that you can modify:

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

Once published, Livewire will automatically use your custom stubs when generating new components.

## Troubleshooting

### Component not found

**Symptom:** Error message like "Component [post.create] not found" or "Unable to find component"

**Solutions:**
- Verify the component file exists at the expected path
- Check that the component name in your view matches the file structure (dots for subdirectories)
- For namespaced components, ensure the namespace is defined in `config/livewire.php`
- Try clearing your view cache: `php artisan view:clear`
- If using class-based components, ensure the namespace in the PHP file is correct

### Component shows blank or doesn't render

**Common causes:**
- Missing root element in your Blade template (Livewire requires exactly one root element)
- Syntax errors in the PHP section of your component
- Check your Laravel logs for detailed error messages

### Namespace not working

**Symptom:** Namespaced components like `pages::post.create` not found

**Solutions:**
- Ensure the namespace is defined in `config/livewire.php` under `component_namespaces`
- Check the path mapping is correct: `'pages' => resource_path('views/pages')`
- Verify the component file exists in the correct directory
- Clear your config cache: `php artisan config:clear`

### Class name conflicts

**Symptom:** Errors about duplicate class names when using single-file components

**Solution:** This can happen if you have multiple single-file components with the same name in different directories. Either:
- Rename one of the components to be unique
- Convert to class-based components where you have full control over namespaces

## Next steps

Now that you understand Livewire components, here are the key concepts to explore next:

- **[Properties](/docs/4.x/properties)** — Learn how component properties work, including types, security, and reactivity
- **[Actions](/docs/4.x/actions)** — Understand how to handle user interactions with methods and events
- **[Forms](/docs/4.x/forms)** — Build powerful forms with real-time validation and file uploads
- **[Pages](/docs/4.x/pages)** — Master page components, layouts, and routing
- **[Nesting](/docs/4.x/nesting)** — Learn how to compose components and communicate between them
