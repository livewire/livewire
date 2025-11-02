# Pages

Livewire components can be used as entire pages by assigning them directly to routes. This allows you to build complete application pages using Livewire components, with additional capabilities like custom layouts, page titles, and route parameters.

## Routing to components

To route to a component, use the `Route::livewire()` method in your `routes/web.php` file:

```php
Route::livewire('/posts/create', 'pages::create-post');
```

When you visit the specified URL, the component will be rendered as a complete page using your application's layout.

### Component namespaces

You may have noticed the `pages::` prefix in the route example above. Livewire automatically registers several component namespaces for better organization:

- `pages::` — Points to `resources/views/pages/`
- `layouts::` — Points to `resources/views/layouts/`

These namespaces are registered for both Livewire components and Blade components, allowing you to organize your application's pages and layouts in dedicated directories. For example:

```php
// These all work automatically:
Route::livewire('/dashboard', 'pages::dashboard');
Route::livewire('/profile', 'pages::user.profile');

// In Blade templates:
<livewire:pages::dashboard />
<x-layouts::app />
```

You can customize these namespaces or add your own in the `config/livewire.php` file:

```php
'component_namespaces' => [
    'layouts' => resource_path('views/layouts'),
    'pages' => resource_path('views/pages'),
    'admin' => resource_path('views/admin'),  // Add custom namespace
],
```

## Layouts

Components rendered via routes will use your application's layout file. By default, Livewire looks for a layout called `layouts::app` located at `resources/views/layouts/app.blade.php`.

You may create this file if it doesn't already exist by running the following command:

```shell
php artisan livewire:layout
```

This command will generate a file called `resources/views/layouts/app.blade.php`.

Ensure you have created a Blade file at this location and included a `{{ $slot }}` placeholder:

```blade
<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
```

You may customize the default layout by updating the `component_layout` configuration option in your `config/livewire.php` file:

```php
'component_layout' => 'layouts::dashboard',
```

### Component-specific layouts

To use a different layout for a specific component, you may place the `#[Layout]` attribute above your component class:

```php
<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts::dashboard')] // [tl! highlight]
class extends Component
{
	// ...
};
```

Alternatively, you may use the `->layout()` method within your component's `render()` method:

```php
<?php

use Livewire\Component;

new class extends Component
{
	// ...

    public function render()
    {
        return $this->view()
            ->layout('layouts::dashboard'); // [tl! highlight]
    }
};
```

## Setting the page title

Assigning unique page titles to each page in your application is helpful for both users and search engines.

To set a custom page title for a page component, first, make sure your layout file includes a dynamic title:

```blade
<head>
    <title>{{ $title ?? 'Page Title' }}</title>
</head>
```

Next, above your Livewire component's class, add the `#[Title]` attribute and pass it your page title:

```php
<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Title('Create post')] // [tl! highlight]
class extends Component
{
	// ...
};
```

This will set the page title for the component. In this example, the page title will be "Create Post" when the component is rendered.

If you need to pass a dynamic title, such as a title that uses a component property, you can use the `->title()` fluent method in the component's `render()` method:

```php
public function render()
{
    return $this->view()
	     ->title('Create Post'); // [tl! highlight]
}
```

## Setting additional layout file slots

If your layout file has any named slots in addition to `$slot`, you can set their content in your Blade view by defining `<x-slot>`s outside your root element. For example, if you want to be able to set the page language for each component individually, you can add a dynamic `$lang` slot into the opening HTML tag in your layout file:

```blade
<!-- resources/views/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $lang ?? app()->getLocale()) }}"> <!-- [tl! highlight] -->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
```

Then, in your component view, define an `<x-slot>` element outside the root element:

```blade
<x-slot:lang>fr</x-slot> // This component is in French <!-- [tl! highlight] -->

<div>
    // French content goes here...
</div>
```

## Accessing route parameters

When working with page components, you may need to access route parameters within your Livewire component.

To demonstrate, first, define a route with a parameter in your `routes/web.php` file:

```php
Route::livewire('/posts/{id}', 'pages::show-post');
```

Here, we've defined a route with an `id` parameter which represents a post's ID.

Next, update your Livewire component to accept the route parameter in the `mount()` method:

```php
<?php

use App\Models\Post;
use Livewire\Component;

new class extends Component
{
    public Post $post;

    public function mount($id) // [tl! highlight]
    {
        $this->post = Post::findOrFail($id);
    }
};
```

In this example, because the parameter name `$id` matches the route parameter `{id}`, if the `/posts/1` URL is visited, Livewire will pass the value of "1" as `$id`.

## Using route model binding

Laravel's route model binding allows you to automatically resolve Eloquent models from route parameters.

After defining a route with a model parameter in your `routes/web.php` file:

```php
Route::livewire('/posts/{post}', 'pages::show-post');
```

You can now accept the route model parameter through the `mount()` method of your component:

```php
<?php

use App\Models\Post;
use Livewire\Component;

new class extends Component
{
    public Post $post;

    public function mount(Post $post) // [tl! highlight]
    {
        $this->post = $post;
    }
};
```

Livewire knows to use "route model binding" because the `Post` type-hint is prepended to the `$post` parameter in `mount()`.

Like before, you can reduce boilerplate by omitting the `mount()` method:

```php
<?php

use Livewire\Component;
use App\Models\Post;

new class extends Component
{
    public Post $post; // [tl! highlight]
};
```

The `$post` property will automatically be assigned to the model bound via the route's `{post}` parameter.
