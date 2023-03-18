---
Title: Components
Order: 3
---

Livewire components are reusable pieces of UI that can be used throughout your application. Here we'll cover the basics of creating and rendering Livewire components. A component is simply a PHP class that extends `Livewire\Component`.

# Creating components

You can create the individual files by hand, or by using the artisan command:

```shell
php artisan make:component CreatePost
```

After running this command, Livewire will create two new files in your application. The first will be the component's class called `app/Http/Livewire/CreatePost.php`:

```php
<?php

namespace App\Http\Livewire;

use \Livewire\Component;

class CreatePost extends Component
{
	public function render()
	{
		return view('livewire.create-post');
	}
}
```

The second will be the component's Blade view: `resources/views/livewire/create-post.blade.php`

```html
<div>
	{{-- // --}}
</div>
```

## Inline components

If your component is fairly small, you may instead want to create an inline component. Inline components are single-file Livewire components who's view template is contained directly in the `render` method:

```php
use \Livewire\Component;

class CreatePost extends Component
{
	public function render()
	{
		return <<<'HTML'
		<div>
		    {{-- // --}}
		</div>
		HTML;
	}
}
```

You can create inline components by adding the `--inline` flag to the make command:

```shell
php artisan make:component CreatePost --inline
```

# Rendering components
There are two ways to render a Livewire component in Laravel:
1. As a full-page component
2. From within a Blade view

## Full-page components
Livewire allows you to render a component for an entire page. Treating it as if it were a controller that returns a Blade view.

For example, you can render the earlier `CreatePost` component as a full-page component by assigning it to a route in your `routes/web.php`:

```php
use App\Http\Livewire\CreatePost;

Route::get('/post/create', CreatePost::class);
```

By default, Livewire will look in your application for a layout called `resources/views/components/layout.blade.php` to render itself inside of.

### Creating a layout
If you have not created a layout yet, you will need to, otherwise Livewire will throw a `LayoutNotFound` exception.

Here's an example of a basic layout component:

```html
<html>
<head>
	<title>Acme Inc.</title>
</head>
<body>
	<nav>...</nav>

	{{ $slot }}
</body>
</html>
```

You may also reference the Laravel docs for more information on layout components.

### Configuring the layout
If you choose to store your layout somewhere else in your application, for example `resources/layouts/app.blade.php`, you can configure it for the entire application or for each individual component.

To configure a custom layout globally, set the following configuration in your `config/livewire.php` config file:

```php
"layout": "layouts.app",
```

If you want to configure a specific component to use a certain layout, you can add the `Layout` attribute above the component's `render` method:

```php
class CreatePost extends Component
{
	#[Layout('layouts.app')]
	public function render()
	{
		return view('livewire.create-post');
	}
}
```

Now that you have a layout configured, you should be able to visit the `/post/create` URL in the browser and see your component loaded on the page.

## Setting a page title

```php
class CreatePost extends Component
{
	#[Title('Some page')]
	public function render()
	{
		return view('livewire.create-post');
	}
}
```

### Using route parameters
Route parameters are automatically assigned to properties with the same name on your component. For example:

```php
use App\Http\Livewire\CreatePost;

Route::get('/post/{id}', ShowPost::class);
```

```php
class CreatePost extends Component
{
	public $id;

	...
}
```

Now if a user visists the following URL: `/post/1`, the value `1` will be assigned to the `$id` property on the component automatically.

### Route model binding
You can also take advantage of Laravel's "Route Model Binding" by adding an eloquent model's type to the property of the same name as the route parameter in your component:

```php
use App\Http\Livewire\CreatePost;

Route::get('/post/{post}', ShowPost::class);
```

```php
use App\Models\Post;

class CreatePost extends Component
{
	public Post $post;

	...
}
```

Now in the above example, if the visisted URL is: `/post/1`, `$post` will be set to the Post model with an id of "1".

### Accessing data from a Blade view
Livewire component properties are automatically available inside Blade views. For example:

```php
use App\Models\Post;

class ShowPost extends Component
{
	public Post $post;

	public render()
	{
	return view('livewire.show-post');
	}
}
```

```html
<div>
	<h1>{{ $post->title }}</h1>

	<span>{{ $post->body }}</h1>
</div>
```

### Passing extra data to the Blade view

You can also pass data directly into the Blade view like you would from a controller.

```php
public function render()
{
	return view('livewire.show-posts', [
		'posts' => auth()->user()->posts,
	]);
}
```

# Rendering a single component

Much like a standard Blade component, you can render a Livewire component from any Blade file, even non-Livewire Blade files. Livewire uses a tag syntax to do so:

```html
<livewire:create-post />
```

## Passing params

You can pass parameters to the Livewire component by using attributes on the tag.

For example, to pass an `id` parameter to a component, you can do the following:

```html
<livewire:show-post id="1" />
```

If the value you are passing in is dynamic, you can also use PHP expressions inside the component paramters by prepending the name with `:` like so:

```html
<livewire:show-post :id="$post->id" />
```

Now the `id` parameter passed into the component will be automatically set on the component's `$id` property:

```php
class ShowPost extends Component
{
	public $id;
}
```

Additionally, to cut down on boilerplate, you can use the short syntax:
```html
<livewire:show-post :$post />

<!-- Same as: -->

<livewire:show-post :post="$post" />
```

Now the `post` parameter will automatically be assigned to the `$post` property in the following example:

```php
class ShowPost extends Component
{
	public Post $post;
}
```
