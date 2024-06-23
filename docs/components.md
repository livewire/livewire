Components are the building blocks of your Livewire application. They combine state and behavior to create reusable pieces of UI for your front end. Here, we'll cover the basics of creating and rendering components.

## Creating components

A Livewire component is simply a PHP class that extends `Livewire\Component`. You can create component files by hand or use the following Artisan command:

```shell
php artisan make:livewire CreatePost
```

If you prefer kebab-cased names, you can use them as well:

```shell
php artisan make:livewire create-post
```

After running this command, Livewire will create two new files in your application. The first will be the component's class: `app/Livewire/CreatePost.php`

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

The second will be the component's Blade view: `resources/views/livewire/create-post.blade.php`

```blade
<div>
	{{-- ... --}}
</div>
```

You may use namespace syntax or dot-notation to create your components in sub-directories. For example, the following commands will create a `CreatePost` component in the `Posts` sub-directory:

```shell
php artisan make:livewire Posts\\CreatePost
php artisan make:livewire posts.create-post
```

### Inline components

If your component is fairly small, you may want to create an _inline_ component. Inline components are single-file Livewire components whose view template is contained directly in the `render()` method rather than a separate file:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
	public function render()
	{
		return <<<'HTML' // [tl! highlight:4]
		<div>
		    {{-- Your Blade template goes here... --}}
		</div>
		HTML;
	}
}
```

You can create inline components by adding the `--inline` flag to the `make:livewire` command:

```shell
php artisan make:livewire CreatePost --inline
```

### Omitting the render method

To reduce boilerplate in your components, you can omit the `render()` method entirely and Livewire will use its own underlying `render()` method, which returns a view with the conventional name corresponding to your component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    //
}
```

If the component above is rendered on a page, Livewire will automatically determine it should be rendered using the `livewire.create-post` template.

### Customizing component stubs

You can customize the files (or _stubs_) Livewire uses to generate new components by running the following command:

```shell
php artisan livewire:stubs
```

This will create four new files in your application:

* `stubs/livewire.stub` — used for generating new components
* `stubs/livewire.inline.stub` — used for generating _inline_ components
* `stubs/livewire.test.stub` — used for generating test files
* `stubs/livewire.view.stub` — used for generating component views

Even though these files live in your application, you can still use the `make:livewire` Artisan command and Livewire will automatically use your custom stubs when generating files.

## Setting properties

Livewire components have properties that store data and can be easily accessed within the component's class and Blade view. This section discusses the basics of adding a property to a component and using it in your application.

To add a property to a Livewire component, declare a public property in your component class. For example, let's create a `$title` property in the `CreatePost` component:

```php
<?php

namespace App\Livewire;

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

### Accessing properties in the view

Component properties are automatically made available to the component's Blade view. You can reference it using standard Blade syntax. Here we'll display the value of the `$title` property:

```blade
<div>
    <h1>Title: "{{ $title }}"</h1>
</div>
```

The rendered output of this component would be:

```blade
<div>
    <h1>Title: "Post title..."</h1>
</div>
```

### Sharing additional data with the view

In addition to accessing properties from the view, you can explicitly pass data to the view from the `render()` method, like you might typically do from a controller. This can be useful when you want to pass additional data without first storing it as a property—because properties have [specific performance and security implications](/docs/properties#security-concerns).

To pass data to the view in the `render()` method, you can use the `with()` method on the view instance. For example, let's say you want to pass the post author's name to the view. In this case, the post's author is the currently authenticated user:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreatePost extends Component
{
    public $title;

    public function render()
    {
        return view('livewire.create-post')->with([
	        'author' => Auth::user()->name,
	    ]);
    }
}
```

Now you may access the `$author` property from the component's Blade view:

```blade
<div>
	<h1>Title: {{ $title }}</h1>

	<span>Author: {{ $author }}</span>
</div>
```

### Adding `wire:key` to `@foreach` loops

When looping through data in a Livewire template using `@foreach`, you must add a unique `wire:key` attribute to the root element rendered by the loop.

Without a `wire:key` attribute present within a Blade loop, Livewire won't be able to properly match old elements to their new positions when the loop changes. This can cause many hard to diagnose issues in your application.

For example, if you are looping through an array of posts, you may set the `wire:key` attribute to the post's ID:

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}"> <!-- [tl! highlight] -->
            <!-- ... -->
        </div>
    @endforeach
</div>
```

If you are looping through an array that is rendering Livewire components you may set the key as a component attribute `:key()` or pass the key as a third argument when using the `@livewire` directive.

```blade
<div>
    @foreach ($posts as $post)
        <livewire:post-item :$post :key="$post->id">

        @livewire(PostItem::class, ['post' => $post], key($post->id))
    @endforeach
</div>
```

### Binding inputs to properties

One of Livewire's most powerful features is "data binding": the ability to automatically keep properties in-sync with form inputs on the page.

Let's bind the `$title` property from the `CreatePost` component to a text input using the `wire:model` directive:

```blade
<form>
    <label for="title">Title:</label>

    <input type="text" id="title" wire:model="title"> <!-- [tl! highlight] -->
</form>
```

Any changes made to the text input will be automatically synchronized with the `$title` property in your Livewire component.

> [!warning] "Why isn't my component live updating as I type?"
> If you tried this in your browser and are confused why the title isn't automatically updating, it's because Livewire only updates a component when an "action" is submitted—like pressing a submit button—not when a user types into a field. This cuts down on network requests and improves performance. To enable "live" updating as a user types, you can use `wire:model.live` instead. [Learn more about data binding](/docs/properties#data-binding).


Livewire properties are extremely powerful and are an important concept to understand. For more information, check out the [Livewire properties documentation](/docs/properties).

## Calling actions

Actions are methods within your Livewire component that handle user interactions or perform specific tasks. They're often useful for responding to button clicks or form submissions on a page.

To learn more about actions, let's add a `save` action to the `CreatePost` component:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
    public $title;

    public function save() // [tl! highlight:8]
    {
		$post = Post::create([
			'title' => $this->title
		]);

		return redirect()->to('/posts')
			 ->with('status', 'Post created!');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

Next, let's call the `save` action from the component's Blade view by adding the `wire:submit` directive to the `<form>` element:

```blade
<form wire:submit="save"> <!-- [tl! highlight] -->
    <label for="title">Title:</label>

    <input type="text" id="title" wire:model="title">

	<button type="submit">Save</button>
</form>
```

When the "Save" button is clicked, the `save()` method in your Livewire component will be executed and your component will re-render.

To keep learning about Livewire actions, visit the [actions documentation](/docs/actions).

## Rendering components

There are two ways to render a Livewire component on a page:

1. Include it within an existing Blade view
2. Assign it directly to a route as a full-page component

Let's cover the first way to render your component, as it's simpler than the second.

You can include a Livewire component in your Blade templates using the `<livewire:component-name />` syntax:

```blade
<livewire:create-post />
```

If the component class is nested deeper within the `app/Livewire/` directory, you may use the `.` character to indicate directory nesting. For example, if we assume a component is located at `app/Livewire/EditorPosts/CreatePost.php`, we may render it like so:

```blade
<livewire:editor-posts.create-post />
```

> [!warning] You must use kebab-case
> As you can see in the snippets above, you must use the _kebab-cased_ version of the component name. Using the _StudlyCase_ version of the name (`<livewire:CreatePost />`) is invalid and won't be recognized by Livewire.


### Passing data into components

To pass outside data into a Livewire component, you can use attributes on the component tag. This is useful when you want to initialize a component with specific data.

To pass an initial value to the `$title` property of the `CreatePost` component, you can use the following syntax:

```blade
<livewire:create-post title="Initial Title" />
```

If you need to pass dynamic values or variables to a component, you can write PHP expressions in component attributes by prefixing the attribute with a colon:

```blade
<livewire:create-post :title="$initialTitle" />
```

Data passed into components is received through the `mount()` lifecycle hook as method parameters. In this case, to assign the `$title` parameter to a property, you would write a `mount()` method like the following:

```php
<?php

namespace App\Livewire;

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

In this example, the `$title` property will be initialized with the value "Initial Title".

You can think of the `mount()` method as a class constructor. It runs on the initial load of the component, but not on subsequent requests within a page. You can learn more about `mount()` and other helpful lifecycle hooks within the [lifecycle documentation](/docs/lifecycle-hooks).

To reduce boilerplate code in your components, you can alternatively omit the `mount()` method and Livewire will automatically set any properties on your component with names matching the passed in values:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $title; // [tl! highlight]

    // ...
}
```

This is effectively the same as assigning `$title` inside a `mount()` method.

> [!warning] These properties are not reactive by default
> The `$title` property will not update automatically if the outer `:title="$initialValue"` changes after the initial page load. This is a common point of confusion when using Livewire, especially for developers who have used JavaScript frameworks like Vue or React and assume these "parameters" behave like "reactive props" in those frameworks. But, don't worry, Livewire allows you to opt-in to [making your props reactive](/docs/nesting#reactive-props).


## Full-page components

Livewire allows you to assign components directly to a route in your Laravel application. These are called "full-page components". You can use them to build standalone pages with logic and views, fully encapsulated within a Livewire component.

To create a full-page component, define a route in your `routes/web.php` file and use the `Route::get()` method to map the component directly to a specific URL. For example, let's imagine you want to render the `CreatePost` component at the dedicated route: `/posts/create`.

You can accomplish this by adding the following line to your `routes/web.php` file:

```php
use App\Livewire\CreatePost;

Route::get('/posts/create', CreatePost::class);
```

Now, when you visit the `/posts/create` path in your browser, the `CreatePost` component will be rendered as a full-page component.

### Layout files

Remember that full-page components will use your application's layout, typically defined in the `resources/views/components/layouts/app.blade.php` file.

You may create this file if it doesn't already exist by running the following command:

```shell
php artisan livewire:layout
```

This command will generate a file called `resources/views/components/layouts/app.blade.php`.

Ensure you have created a Blade file at this location and included a `{{ $slot }}` placeholder:

```blade
<!-- resources/views/components/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>
    </head>
    <body>
        {{ $slot }}
    </body>
</html>
```

#### Global layout configuration

To use a custom layout across all your components, you can set the `layout` key in `config/livewire.php` to the path of your custom layout, relative to `resources/views`. For example:

```php
'layout' => 'layouts.app',
```

With the above configuration, Livewire will render full-page components inside the layout file: `resources/views/layouts/app.blade.php`.

#### Per-component layout configuration

To use a different layout for a specific component, you can place Livewire's `#[Layout]` attribute above the component's `render()` method, passing it the relative view path of your custom layout:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

class CreatePost extends Component
{
	// ...

	#[Layout('layouts.app')] // [tl! highlight]
	public function render()
	{
	    return view('livewire.create-post');
	}
}
```

Or if you prefer, you can use this attribute above the class declaration:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')] // [tl! highlight]
class CreatePost extends Component
{
	// ...
}
```

PHP attributes only support literal values. If you need to pass a dynamic value, or prefer this alternative syntax, you can use the fluent `->layout()` method in the component's `render()` method:

```php
public function render()
{
    return view('livewire.create-post')
	     ->layout('layouts.app'); // [tl! highlight]
}
```

Alternatively, Livewire supports using traditional Blade layout files with `@extends`.

Given the following layout file:

```blade
<body>
    @yield('content')
</body>
```

You can configure Livewire to reference it using `->extends()` instead of `->layout()`:

```php
public function render()
{
    return view('livewire.show-posts')
        ->extends('layouts.app'); // [tl! highlight]
}
```

If you need to configure the `@section` for the component to use, you can configure that as well with the `->section()` method:

```php
public function render()
{
    return view('livewire.show-posts')
        ->extends('layouts.app')
        ->section('body'); // [tl! highlight]
}
```

### Setting the page title

Assigning unique page titles to each page in your application is helpful for both users and search engines.

To set a custom page title for a full-page component, first, make sure your layout file includes a dynamic title:

```blade
<head>
    <title>{{ $title ?? 'Page Title' }}</title>
</head>
```

Next, above your Livewire component's `render()` method, add the `#[Title]` attribute and pass it your page title:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

class CreatePost extends Component
{
	// ...

	#[Title('Create Post')] // [tl! highlight]
	public function render()
	{
	    return view('livewire.create-post');
	}
}
```

This will set the page title for the `CreatePost` Livewire component. In this example, the page title will be "Create Post" when the component is rendered.

If you prefer, you can use this attribute above the class declaration:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create Post')] // [tl! highlight]
class CreatePost extends Component
{
	// ...
}
```

If you need to pass a dynamic title, such as a title that uses a component property, you can use the `->title()` fluent method in the component's `render()` method:

```php
public function render()
{
    return view('livewire.create-post')
	     ->title('Create Post'); // [tl! highlight]
}
```

### Setting additional layout file slots

If your [layout file](#layout-files) has any named slots in addition to `$slot`, you can set their content in your Blade view by defining `<x-slot>`s outside your root element. For example, if you want to be able to set the page language for each component individually, you can add a dynamic `$lang` slot into the opening HTML tag in your layout file:

```blade
<!-- resources/views/components/layouts/app.blade.php -->

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', $lang ?? app()->getLocale()) }}"> <!-- [tl! highlight] -->
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>
    </head>
    <body>
        {{ $slot }}
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


### Accessing route parameters

When working with full-page components, you may need to access route parameters within your Livewire component.

To demonstrate, first, define a route with a parameter in your `routes/web.php` file:

```php
use App\Livewire\ShowPost;

Route::get('/posts/{id}', ShowPost::class);
```

Here, we've defined a route with an `id` parameter which represents a post's ID.

Next, update your Livewire component to accept the route parameter in the `mount()` method:

```php
<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount($id) // [tl! highlight]
    {
        $this->post = Post::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

In this example, because the parameter name `$id` matches the route parameter `{id}`, if the `/posts/1` URL is visited, Livewire will pass the value of "1" as `$id`.

### Using route model binding

Laravel's route model binding allows you to automatically resolve Eloquent models from route parameters.

After defining a route with a model parameter in your `routes/web.php` file:

```php
use App\Livewire\ShowPost;

Route::get('/posts/{post}', ShowPost::class);
```

You can now accept the route model parameter through the `mount()` method of your component:

```php
<?php

namespace App\Livewire;

use App\Models\Post;
use Livewire\Component;

class ShowPost extends Component
{
    public Post $post;

    public function mount(Post $post) // [tl! highlight]
    {
        $this->post = $post;
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

Livewire knows to use "route model binding" because the `Post` type-hint is prepended to the `$post` parameter in `mount()`.

Like before, you can reduce boilerplate by omitting the `mount()` method:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post; // [tl! highlight]

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

The `$post` property will automatically be assigned to the model bound via the route's `{post}` parameter.

### Modifying the response

In some scenarios, you might want to modify the response and set a custom response header. You can hook into the response object by calling the `response()` method on the view and use a closure to modify the response object:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Response;

class ShowPost extends Component
{
    public function render()
    {
        return view('livewire.show-post')
            ->response(function(Response $response) {
                $response->header('X-Custom-Header', true);
            });
    }
}
```

## Using JavaScript

There are many instances where the built-in Livewire and Alpine utilities aren't enough to accomplish your goals inside your Livewire components.

Fortunately, Livewire provides many useful extension points and utilities to interact with bespoke JavaScript. You can learn from the exhaustive reference on [the JavaScript documentation page](/docs/javascript). But for now, here are a few useful ways to use your own JavaScript inside your Livewire components.

### Executing scripts

Livewire provides a helpful `@script` directive that, when wrapping a `<script>` element, will execute the given JavaScript when your component is initialized on the page.

Here is an example of a simple `@script` that uses JavaScript's `setInterval()` to refresh your component every two seconds:

```blade
@script
<script>
    setInterval(() => {
        $wire.$refresh()
    }, 2000)
</script>
@endscript
```

You'll notice we are using an object called `$wire` inside the `<script>` to control the component. Livewire automatically makes this object available inside any `@script`s. If you're unfamiliar with `$wire`, you can learn more about `$wire` in the following documentation:
* [Accessing properties from JavaScript](/docs/properties#accessing-properties-from-javascript)
* [Calling Livewire actions from JS/Alpine](/docs/actions#calling-actions-from-alpine)
* [The `$wire` object reference](/docs/javascript#the-wire-object)

### Loading assets

In addition to one-off `@script`s, Livewire provides a helpful `@assets` utility to easily load any script/style dependencies on the page.

It also ensures that the provided assets are loaded only once per browser page, unlike `@script`, which executes every time a new instance of that Livewire component is initialized.

Here is an example of using `@assets` to load a date picker library called [Pikaday](https://github.com/Pikaday/Pikaday) and initialize it inside your component using `@script`:

```blade
<div>
    <input type="text" data-picker>
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js" defer></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
@endassets

@script
<script>
    new Pikaday({ field: $wire.$el.querySelector('[data-picker]') });
</script>
@endscript
```

> [!info] Using `@verbatim@script@endverbatim` and `@verbatim@assets@endverbatim` inside Blade components
> If you are using [Blade components](https://laravel.com/docs/blade#components) to extract parts of your markup, you can use `@verbatim@script@endverbatim` and `@verbatim@assets@endverbatim` inside them as well; even if there are multiple Blade components inside the same Livewire component. However, `@verbatim@script@endverbatim` and `@verbatim@assets@endverbatim` are currently only supported in the context of a Livewire component, meaning if you use the given Blade component outside of Livewire entirely, those scripts and assets won't be loaded on the page.
