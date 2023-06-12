Livewire components are the building block of your Livewire application. They combine state and behavior to create reusable pieces of UI for your front end. Here, we'll cover the basics of creating and rendering components. 

## Creating components

A component is simply a PHP class that extends `Livewire\Component`. You can create the individual files by hand or use the following artisan command:

```shell
php artisan make:livewire CreatePost
```

If you prefer kebab-cased names, you can use them as well:

```shell
php artisan make:livewire create-post
```

You can use either the namespaced syntax or dot-notation to create your components in sub-directories. For example, the following commands will create a `CreatePost` component in the `Posts` subdirectory:

```shell
php artisan make:livewire Posts\\CreatePost
php artisan make:livewire posts.create-post
```

After running this command, Livewire will create two new files in your application. The first will be the component's class: `app/Http/Livewire/CreatePost.php`

```php
<?php

namespace App\Http\Livewire;

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

```html
<div>
	{{-- ... --}}
</div>
```

### Inline components

If your component is fairly small, you may want to create an _inline_ component instead. Inline components are single-file Livewire components whose view template is contained directly in the `render` method rather than a separate file:

```php
<?php

namespace App\Http\Livewire;

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

To reduce boilerplate in your components, you can omit the `render()` method entirely, and Livewire will use its own underlying `render()` method, which returns a view with the conventional corresponding name to your component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    //
}
```

If the above component is rendered on a page with no `render()` method, Livewire will render it using the `livewire.create-post` view automatically.

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

Now that these files live in your application, you can still use the `make:livewire` artisan command while maintaining a custom setup.

## Setting properties

Livewire components have properties that store data and can be easily accessed within the component's class and Blade view. This section covers the basics of adding a property to a component and using it in your application.

To add a property to a Livewire component, declare a public property in your component class. For example, let's create a property called `title` in the `CreatePost` component:

```php
<?php

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

### Accessing properties in the view

Component properties are automatically made available to the component's Blade view. You can reference it using standard Blade syntax. Here we'll display the value of the `$title` property:

```html
<div>
    <h1>Title: "{{ $title }}"</h1>
</div>
```

The rendered output of this component would be:

```html
<div>
    <h1>Title: "Post title...""</h1>
</div>
```

### Sharing additional data with the view

In addition to accessing properties from the view, you can explicitly pass data into the view from the `render()` method like you might typically do from a controller. This can be useful when you want to pass additional data without storing it first as a property—because properties have [specific performance and security implications](http://livewire-next-docs.test/docs/properties#security-concerns).

To pass data to the view in the `render()` method, you can use the `with()` method on the view instance. For example, let's say you want to pass the post author's name—in this case, the currently logged user—to the view: 

```php
<?php

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

One of Livewire's most powerful feautures is called "data binding": the ability to automatically keep properties in-sync with form inputs on the page.

Here, we'll bind the `$title` property from the `CreatePost` component to a text input using the `wire:model` directive:

```html
<form>
    <label for="title">Title:</label>

    <input type="text" id="title" wire:model="title"> <!-- [tl! highlight] -->
</form>
```

Any changes made to the text input will be automatically synchronized with the `$title` property in your Livewire component.

> [!warning] _"Why isn't my component live updating as I type?"_
> If you tried this in your browser and are confused why the title isn't automatically updating, it's because Livewire only updates a component when an "action" is submitted—like pressing a submit button—not when a user types into a field. This is intended to cut down on network requests and improve performance. If you require "live" updating as a user types, you can use `wire:model.live` instead. [Learn more about data binding](http://livewire-next-docs.test/docs/properties#data-binding).

Properties in Livewire are extremely powerful and an important concept to understand. For more information, [visit the Livewire properties documentation](/docs/properties).

## Calling actions

Actions are methods within your Livewire component that handle user interactions or perform specific tasks. They're often useful for responding to button clicks or form submissions on a page.

To demonstrate, we'll add a `save` action to the `CreatePost` component:

```php
<?php

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

Now, we can call the `save` action from a button in the component's Blade view using the `wire:submit` directive on the `<form>` element:

```html
<form wire:submit="save">
    <label for="title">Title:</label>

    <input type="text" id="title" wire:model="title">

	<button type="submit">Save</button>
</form>
```

When the "Save" button is clicked, the `save` method in your Livewire component will be executed, and your component will re-render.

For a complete understanding of actions in Livewire, [visit the actions documentation page](http://livewire-next-docs.test/docs/actions).

## Rendering components

There are two ways to render a Livewire component on a page in your Laravel application:

1. Include it within an existing Blade view
2. Assign it directly to a route as a full-page component

Let's cover the first way to render your component, as it's simpler than the second.

You can include a Livewire component in your Blade templates using the `<livewire:component-name />` syntax:

```html
<livewire:create-post />
```

> [!warning] You must use _kebab-case_
> As you can see in the above snippet, you must use the _kebab-cased_ version of the component name. Using the _StudlyCase_ version of the name (`<livewire:CreatePost />`) is invalid and won't be recognized by Livewire.

### Passing data into components

To access outside data from inside a component you can use attributes on the component tag. This is useful when you want to initialize a component with specific data.

To pass an initial value to the `title` property of the `CreatePost` component, you can use the following syntax:

```html
<livewire:create-post title="Initial Title" />
```

If you need to pass dynamic values instead, you can write PHP expressions using the following syntax:

```html
<livewire:create-post :title="$initialTitle" />
```

Data passed into components is received through the `mount()` lifecycle hook as method parameters. In this case, to assign the `title` parameter as a property you would write a `mount()` method like the following:

```php
<?php

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

The `title` property will be initialized with the value "Initial Title". 

You can think of the `mount()` method as a class constructor. It runs on the initial load of the component, but not on subsequent requests within a page. [Learn more about `mount()` and other helpful lifecycle hooks here](http://livewire-next-docs.test/docs/lifecycle-hooks).

To reduce boilerplate code in your components, you can alternatively use the `#[Prop]` attribute to denote that a property should be assigned from an attribute passed into the component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Attributes\Prop;
use Livewire\Component;

class CreatePost extends Component
{
    #[Prop] // [tl! highlight]
    public $title;

    // ...
}
```

This is effectively the same as assigning `$title` inside a `mount()` method.

> [!warning] Props are not reactive by default
> The `$title` property will not update automatically if the outer `:title="$initialValue"` changes after the initial page load. This is a common point of confusion, especially for developers who have used JavaScript frameworks like Vue or React and assume these "parameters" behave like "reactive props" in those frameworks. However, Livewire supports reactive properties using a slightly different syntax. [Learn more about reactive props](/docs/nesting#making-child-props-reactive).

## Full-page components

Livewire allows you to assign components directly to a route in your Laravel application. Thes are called "full-page components". You can use them to build standalone pages with logic and views fully encapsulated within a Livewire component.

To create a full-page component, you can define a route in your `routes/web.php` file and use the `Route::get()` method to map the component directly to a specific URL. For example, let's say you want to render the `CreatePost` component as a dedicated route: `/post/create`, you can add the following line to your `routes/web.php` file:

```php
use App\Http\Livewire\CreatePost;

Route::get('/post/create', CreatePost::class);
```

Now, when you visit the `/post/create` path in your browser, the `CreatePost` component will be rendered as a full-page component.

### Layout files

Remember that full-page components will use your application's layout, typically defined in the `resources/views/components/layout.blade.php` file.

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

#### Global layout configuration

To use a custom layout across all your components, you can set the `'layout'` key in `config/livewire.php` to the path of your custom layout relative to `resources/views`. For example:

```php
"layout": "layouts.app",
```

With the above configuration, Livewire will render full-page components inside the layout file: `resources/views/layouts/app.blade.php`.

#### Per-component layout configuration

To use a different layout for a specific component, you can use Livewire's `#[Layout]` attribute above the `render()` method and pass it the relative view path of your custom layout:

```php
<?php

namespace App\Http\Livewire;

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

PHP attributes only support literal values. If you need to pass a dynamic value instead, or prefer this alternative syntax, you can use the fluent `->layout()` method in `render()`: 

```php
public function render()
{
    return view('livewire.create-post')
	     ->layout('layouts.app'); // [tl! highlight]
}
```

### Setting the page title

Unique page titles for each page in your application are helpful for both users and search engines.

To set a custom page title for a full-page component, first, update your layout file to include a dynamic title:

```html
<html>
	<head>
	    <title>{{ $title ?? 'Default Page Title' }}</title> <!-- [tl! highlight] -->
	</head>

	<body>
		{{ $slot }}
	</body>
</html>
```

Now, above your Livewire component's `render()` method, add the `#[Title]` attribute and pass it your page title.

```php
<?php

namespace App\Http\Livewire;

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

This will set the page title for this specific Livewire component. In this example, the page title will be "Create Post" when the component is rendered.

If you need to pass a dynamic title, for example, a title that uses a compoent property, you can use the `->title()` fluent method in `render()`: 

```php
public function render()
{
    return view('livewire.create-post')
	     ->title('Create Post'); // [tl! highlight]
}
```

### Accessing route parameters

You may need to access route parameters within your Livewire component when working with full-page components.

To demonstrate, first, define a route with a parameter in your `routes/web.php` file:

```php
use App\Http\Livewire\ShowPost;

Route::get('/post/{id}', ShowPost::class);
```

Here, we've defined a route with an `id` parameter, representing a post's ID.

Next, update your Livewire component to accept the route parameter in the `mount()` method:

```php
<?php

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

### Using route model binding

Route model binding allows you to resolve Eloquent models from route parameters automatically.

After defining a route with a model parameter in your `routes/web.php` file:

```php
use App\Http\Livewire\ShowPost;

Route::get('/post/{post}', ShowPost::class);
```

You can now accept the route model parameter through the `mount()` method of your component:

```php
<?php

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

Like before, you can reduce boilerplate by using the `#[Prop]` attribute:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Attributes\Prop;
use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    #[Prop] // [tl! highlight]
    public Post $post;

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

The `$post` property will automatically be assigned to the model bound out of the route's `{post}` parameter.