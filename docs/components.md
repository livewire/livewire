Livewire components are reusable pieces of UI that can be used throughout your application. They are the backbone of your Livewire application. Here we'll cover the basics of creating and rendering components. 

## Creating components

A component is simply a PHP class that extends `Livewire\Component`. You can create the individual files by hand, or by using the artisan command:

```shell
php artisan make:livewire CreatePost
```

If you prefer kebab-cased names, you can use them as well:

```shell
php artisan make:livewire create-post
```

To create your components in sub-directories, you can use either the namespaced syntax or dot-notation. For example, the following commands will create a `CreatePost` component in the `Posts` subdirectory:

```shell
php artisan make:livewire Posts\\CreatePost
php artisan make:livewire posts.create-post
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
	{{-- ... --}}
</div>
```

### Inline components

If your component is fairly small, you may instead want to create an "inline" component. Inline components are single-file Livewire components who's view template is contained directly in the `render` method, rather than a separate file:

```php
use \Livewire\Component;

class CreatePost extends Component
{
	public function render()
	{
		return <<<'HTML' // [tl! highlight:4]
		<div>
		    {{-- ... --}}
		</div>
		HTML;
	}
}
```

You can create inline components by adding the `--inline` flag to the `make:livewire` command:

```shell
php artisan make:livewire CreatePost --inline
```

### Customizing component stubs

@todo

## Setting properties

Livewire components have properties that store data and can be easily accessed within the component's class and Blade view. In this section, we'll cover the basics of adding a property to a component and using it in your application.

To add a property to a Livewire component, declare a public property in your component class. For example, let's create a property called `title` in the `CreatePost` component:

```php
namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $title = 'Post title...';

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

> More about properties here: #[]

### Accessing properties in the view

Component propertiers are automatically made availble to the component's Blade view. You can reference it using standard Blade syntax. Here we'll display the value of the `title` property:

```html
<div>
    <h1>Title: "{{ $title }}"</h1>
</div>
```

In this example, the rendered output of this component will be:

```html
<div>
    <h1>Title: "Post title...""</h1>
</div>
```

### Sharing data with the view directly

In addition to accessing properties from the view, you can also explicitly pass data into the view from the `render()` method. This can be useful when you want to pass additional data without storing it first as a property, as properties have #[certain performance and security implications].

To pass data to the view in the `render()` method, you can use the `with()` method on the view instance. For example, let's say you want to pass the post author's name, in this case the currently logged user, to the view: 

```php
namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $title;

    public function render()
    {
        return view('livewire.create-post')->with([
	        'author' => auth()->user()->name,
	    ]);
    }
}
```

Now, you can access the `$author` from the component's Blade view:

```html
<div>
	<h1>Title: {{ $title }}</h1>
	<span>Author: {{ $author }}</span>
</div>
```

### Binding inputs to properties

Livewire allows you to easily bind properties to form inputs. Here, we'll bind the `$title` property to a text input in your component's Blade view using the `wire:model` directive:

```html
<form>
    <label for="title">Title:</label>
    <input type="text" id="title" wire:model="title">
</form>
```

Now, any changes made to the text input will be automatically synchronized with the `$title` property in your Livewire component when an action is performed.

> If you tried this in your browser and you're confused why the title isn't automatically updating it's because, by default, Livewire only updates a component when an "action" is submitted. Not as a user types. This is intended to cut down on network requests and improve performance. If you do require "live" updating as a user types, you can use `wire:model.live`. You can #[learn more about data binding here].

### Calling actions

Actions are methods within your Livewire component that handle user interactions or perform specific tasks. In this section, we'll demonstrate how to call a `save` action in the `CreatePost` component.

```php
namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    public $title;

    public function save()
    {
		$post = Post::create([
			'title' => $this->title
		]);

		return redirect()->to('/posts')
			 ->with('status', 'Post created!');;
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

Here, we've added the `save` method to the component to handle the logic for saving a post.

Next, you can call the `save` action from a button in your component's Blade view using the `wire:submit` directive on the `<form>` element:

```html
<!-- resources/views/livewire/create-post.blade.php -->

<form wire:submit="save">
    <label for="title">Title:</label>
    <input type="text" id="title" wire:model="title">

	<button type="submit">Save</button>
</form>
```

Now, when the "Save" button is clicked, the `save` method in your Livewire component will be executed and your component will re-render.

## Rendering components

There are two ways to render a Livewire component on a page in your Laravel application:

1. By including it within an existing Blade view
2. By asigning it directly to a route as a full-page component

Let's first cover the first way to render your component as it's simpler than the second.

Livewire components are included in your Blade templates using the `<livewire:component-name />` syntax like so:

```html
<livewire:create-post />
```

> Note: we're using the "kebab-cased" version of the component name in the above snippet. Rather than writing `<livewire:CreatePost />`, which would be invalid HTML, we've written `<livewire:create-post />`.

### Passing data as tag attributes

To access outside data from inside a component you can use tag attributes. This is useful when you want to initialize a component with specific data.

To pass an initial value to the `title` property of the `CreatePost` component, you can use the following syntax:

```html
<livewire:create-post title="Initial Title" />
```

If you need to pass dynamic values instead, you can use the following syntax:

```html
<livewire:create-post :title="$initialTitle" />
```

This data is passed as parameters through the `mount()` method. You might access the `title` value and assign it to a property like so:

```php
namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $title;

    public function mount($title = null)
    {
        $this->title = $title;
    }

    // ...
}
```

Now, the `title` property will be initialized with the value "Initial Title". 

You can think of the `mount` method like a class constructor. It runs on initial load of the component, but not on subsequent requests within a page. #[Learn more about `mount()` and other useful lifecycle hooks here.]

> Note: The `$title` property will not update automatically if the outer `:title="$initialValue"` changes after initial page load. This is a common point of confusion, especially for developers who have used JavaScript frameworks like Vue or React and assume these "parameters" behave like "reactive props" in those frameworks. Livewire however DOES have reactive props and you can #[learn more about them here.]

## Full-page components

Livewire allows you to create full-page components, which can be assigned directly to a dedicated route. This is useful when you want to build standalone pages that have their logic and views fully encapsulated within a Livewire component.

To create a full-page component, you can define a route in your `routes/web.php` file and use the `Route::get()` method to map the component directly to a specific URL. For example, let's say you want to render the `CreatePost` component as a dedicated route at the `/create-post` URL. You can add the following line to your `routes/web.php` file:

```php
use App\Http\Livewire\CreatePost;

Route::get('/post/create', CreatePost::class);
```

Now, when you visit the `/post/create` URL in your browser, the `CreatePost` component will be rendered as a full-page component.

### Layout files

Keep in mind that full-page components will use your application's layout, which is typically defined in the `resources/views/components/layout.blade.php` file.

Ensure you have created a Blade file at this location and included a `{{ $slot }}` placeholder:

```html
<!-- resources/views/components/layout.blade.php -->

<html>
	<head>
	    <title>Page Title</title>
	</head>
	<body>
		{{ $slot }}
	</body>
</html>
```

> For more information on layout files in Laravel you can reference the #[Laravel documentation on the topic.]

#### Global layout configuration

To use a custom layout across all your components, you can set the `'layout'` key in `config/livewire.php` to the path of your custom layout, relative to `resources/views`. For example:

```php
"layout": "layouts.app",
```

With the above configuration, Livewire will render full-page compoents inside the following layout file: `resources/views/layouts/app.blade.php`.

#### Per-component layout configuration

To use a different layout for a specific component, you can use Livewire's `#[Layout]` attribute above the `render()` method and pass it the relative view path of your custom layout:

```php
namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Use\Layout;

class CreatePost extends Component
{
	// ...
	
	#[Layout('layouts.custom')]
	public function render()
	{
	    return view('livewire.create-post');
	}
}
```

> If PHP attributes are new to you, you can [read more about them here], and also #[view other available PHP attributes in Livewire.]

PHP attributes only support literal values. If you need to pass a dynamic value instead, or prefer this alternative syntax, you can use the fluent `->layout()` method in `render()`: 

```php
public function render()
{
    return view('livewire.create-post')
	     ->layout('layouts.custom');
}
```

### Setting the page title

Unique page titles for each page an your application is helpful for both the users of your app and SEO.

To set a custom page title for a full-page component, first, update your layout file to include a dynamic title:

```html
<html>
	<head>
	    <title>{{ $title ?? 'Default Page Title' }}</title>
	</head>
	<body>
		{{ $slot }}
	</body>
</html>
```

Now, above your Livewire component's `render()` method, add the `#[Title]` attribute and pass it your page title.

```php

```php
namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Use\Title;

class CreatePost extends Component
{
	// ...
	
	#[Title('Create Post')]
	public function render()
	{
	    return view('livewire.create-post');
	}
}

```

This will set the page title for the specific Livewire component. In this example, the page title will be "Create Post" when the component is rendered.

If you need to pass a dynamic title, for example a title that uses a compoent property, you can use the `->title()` fluent method in `render()`: 

```php
public function render()
{
    return view('livewire.create-post')
	     ->title('Create Post');
}
```

### Accessing route parameters

When working with full-page components, you may need to access route parameters within your Livewire component.

To demonstrate, first, define a route with a parameter in your `routes/web.php` file:

```php
use App\Http\Livewire\ShowPost;

Route::get('/post/{id}', ShowPost::class);
```

Here, we've defined a route that includes an `id` parameter, which represents a post's ID.

Next, update your Livewire component to accept the route parameter in the `mount()` method:

```php
namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount($id)
    {
        $this->post = Post::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

In this example, because the parameter name `$id` matches the route parameter `{id}`, if the `/post/1` URL is visited, Livewire will pass the value of "1" in as `$id`.

> Notice we set an eloquent model directly as a component property. This technique has special considerations and rules associated with it. [You can read more on that here.]

### Using route model binding

Route model binding allows you to automatically resolve Eloquent models from route parameters.

After defining a route with a model parameter in your `routes/web.php` file:

```php
use App\Http\Livewire\ShowPost;

Route::get('/post/{post}', ShowPost::class);
```

You can now accept the route model parameter through the `mount()` method of your component:

```php
namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount(Post $post)
    {
        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

Livewire knows to use "route model binding" because the type `Post` is prepended to the `$post` parameter in `mount()`.

> For more about using Eloquent models inside Livewire components, [visit the documentation page.]