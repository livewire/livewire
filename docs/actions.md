Livewire actions are methods on your components that can be triggered by frontend interactions like clicking a button or submitting a form. They provide the developer experience of being able to call a PHP method directly from the browser, which allows you to focus more on the logic of your application and not get bogged down with boilerplate code.

Let's take a look at a basic example of calling a `save` action in a `CreatePost` component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class CreatePost extends Component
{
	public $title = '';

    public $content = '';

    public function save()
    {
		Post::create([
			'title' => $this->title,
			'content' => $this->content,
		]);

		return redirect()->to('/posts');
    }

    public function render()
    {
        return view('livewire.create-post');
    }
}
```

```html
<form wire:submit="save"> <!-- [tl! highlight] -->
	<input type="text" wire:model="title">

	<textarea wire:model="content"></textarea>

	<button type="submit">Save</button>
</form>
```

In the above example, when a user submits the form by clicking "Save", `wire:submit` picks up the `submit` event and calls the `save` action on the server.

In essence, Actions are a way to easily map user interactions to server-side functionality without the hassle of submitting and handling AJAX requests manually.

## Event Listeners

Livewire supports a variety of event listeners that allow you to respond to various types of user interaction. Below is a table of common events to listen for:

| Listener        | Description                               |
|-----------------|-------------------------------------------|
| `wire:click`    | Triggered when an element is clicked      |
| `wire:submit`   | Triggered when a form is submitted        |
| `wire:keydown`  | Triggered when a key is pressed down      |
| `wire:mouseenter`| Triggered when the mouse enters an element |

Because the event name after `wire:` can be anything, Livewire supports any browser event you might need to listen for. For example, to listen for a more niche event like `transitionend`, you can use `wire:transitionend`.

### Listening for specific keys

You can use one of the convenient aliases Livewire provides to narrow down key press event listeners to a specific key or combination of keys.

For example, to perform a search when a user hits `Enter` after typing into a search box, you can use `wire:keydown.enter`:

```html
<input wire:model="query" wire:keydown.enter="searchPosts">
```

You can chain more key aliases after the first to listen for combinations of keys. For example, if you wanted to listen for the `Enter` key but only while the `Shift` key is held down, you would write the following:

```html
<input wire:keydown.shift.enter="...">
```

Below is a list of all the available key modifiers:

| Modifier      | Key                          |
|---------------|------------------------------|
| `.shift`      | Shift                        |
| `.enter`      | Enter                        |
| `.space`      | Space                        |
| `.ctrl`       | Ctrl                         |
| `.cmd`        | Cmd                          |
| `.meta`       | Cmd on Mac, Windows key on Windows |
| `.alt`        | Alt                          |
| `.up`         | Up arrow                     |
| `.down`       | Down arrow                   |
| `.left`       | Left arrow                   |
| `.right`      | Right arrow                  |
| `.escape`     | Escape                       |
| `.tab`        | Tab                          |
| `.caps-lock`  | Caps Lock                    |
| `.equal`      | Equal, `=`                   |
| `.period`     | Period, `.`                  |
| `.slash`      | Forward Slash, `/`           |

### Event handler modifiers

Livewire also ships with helpful modifiers to make common event-handling tasks trivial.

For example, if you need to call `event.preventDefault()` from inside an event listener, you can suffix the event name with `.prevent`:

```html
<input wire:keydown.prevent="...">
```

Here is a full list of all the available event listener modifiers and their functions:

| Modifier         | Key                                                     |
|------------------|---------------------------------------------------------|
| `.prevent`       | Equivalent of calling `.preventDefault()`               |
| `.stop`          | Equivalent of calling `.stopPropagation()`              |
| `.window`        | Listens for event on the `window` object                 |
| `.outside`       | Only listens for clicks "outside" the element            |
| `.document`      | Listens for events on the `document` object              |
| `.once`          | Ensures the listener is only called once                 |
| `.debounce`      | Debounce the handler by 250ms as a default               |
| `.debounce.100ms`| Debounce the handler for a specific amount of time       |
| `.throttle`      | Throttle the handler to being called every 250ms at minimum |
| `.throttle.100ms`| Throttle the handler at a custom duration                |
| `.self`          | Only call listener if event originated on this element, not children |
| `.camel`         | Converts event name to camel case (`wire:custom-event` -> "customEvent") |
| `.dot`           | Converts event name to dot notation (`wire:custom-event` -> "custom.event") |
| `.passive`       | `wire:touchstart.passive` won't block scroll performance |
| `.capture`       | Listen for event in the "capturing" phase                 |

Because `wire:` uses Alpine's `x-on` under the hood, these modifiers are made available to you by AlpineJS. For more context on when you should use these modifiers, you can [visit the AlpineJS Events documentation](https://alpinejs.dev/essentials/events).

### Handling third-party events

Livewire also supports listening for custom events fired by third-party libraries.

For example, let's say you're using the [Trix](https://trix-editor.org/) rich text editor in your project, and you want to listen for the `trix-change` event to capture the editor's content. You can do this using the `wire:trix-change` directive.

```html
<form wire:submit="save">
	<!-- ... -->

    <trix-editor
	    wire:trix-change="setPostContent($event.target.value)"
	></trix-editor>

	<!-- ... -->
</form>
```

In this example, the `setPostContent` action is called whenever the `trix-change` event is triggered, updating the `content` property in the Livewire component with the current value of the Trix editor.

> [!info] You can access the event object using `$event`
> Notice that from event handlers in Livewire, you can access the event object through `$event`. This is useful for referencing any event information you may need to. For example, you can access the element that triggered the event via `$event.target`.

> [!warning]
> The above demo code for the Trix editor is incomplete and only useful as a demonstration of event listeners. If used verbatim, a network request would be fired on every single key stroke. A more performant implementation would be:
> 
> ```html
> <trix-editor
>    x-on:trix-change="$wire.content = $event.target.value"
>></trix-editor>
> ```

### Listening for dispatched custom events

If you dispatch custom events from AlpineJS inside your application, you can also listen for those using Livewire, for example:

```html
<div wire:custom-event="...">

	<!-- Deeply nested within this component: -->
	<button x-on:click="$dispatch('custom-event')">...</button>

</div>
```

In the above example, when the button is clicked, the `custom-event` event is dispatched and bubbles up to the root of the Livewire component, where `wire:custom-event` catches it, and the action is called.

If you want to listen for an event dispatched somewhere else in your application, you will need to wait instead for the event to bubble up to the `window` object and listen for it there. Fortunately, Livewire makes this easy by allowing you to add a simple `.window` modifier to any event listener:

```html
<div wire:custom-event.window="...">
	<!-- ... -->
</div>

<!-- Dispatched somewhere on the page outside the component: -->
<button x-on:click="$dispatch('custom-event')">...</button>
```

### Disabling inputs while a form is being submitted

Consider the `CreatePost` example from before:

```html
<form wire:submit="save">
	<input wire:model="title">

	<textarea wire:model="content"></textarea>

	<button type="submit">Save</button>
</form>
```

When a user clicks "Save", a network request is sent to the server to call the `save` action in the Livewire component.

Let's say, for example, that a user is filling out this form on a slow internet connection. They click "Save", and nothing happens initially because the network request takes longer. They might wonder if the submission failed and attempt to click the "Save" button again while the first request is still out.

In this case, there would be TWO requests for the same action out at the same time.

To prevent this scenario, Livewire automatically disables the submit button and all form inputs inside the `<form>` element while a `wire:submit` action is being processed. This ensures that a form isn't submitted twice accidentally.

To further lessen the confusion for users on slower connections, it is often helpful to show some loading indicator, whether a subtle background color change, or an SVG animation.

Livewire provides a `wire:loading` directive that makes it trivial to show and hide loading indicators anywhere on a page. Here's a short example of using `wire:loading` to show a loading message below the "Save" button:

```html
<form wire:submit="save">
	<textarea wire:submit="content"></textarea>

	<button type="submit">Save</button>

	<span wire:loading>Saving...</span> <!-- [tl! highlight] -->
</form>
```

`wire:loading` is a powerful feature capable of much more than the above snippet. [Check out the full documentation on it for more info](/docs/loading).

## Passing Parameters

Livewire allows you to pass parameters from your Blade template to the actions in your component. This enables you to pass additional data or state from the frontend when an action is called.

For example, let's say you have a `ShowPosts` component that allows users to delete a post. You can pass the post's ID as a parameter to the `delete()` action in your Livewire component to fetch the relevant post and delete it from the database:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete($id)
    {
		$post = Post::findOrFail($id);

		Auth::user()->can('update', $post);

		$post->delete();
    }

    public function render()
    {
        return view('livewire.show-posts', [
			'posts' => Auth::user()->posts,
        ]);
    }
}
```

```html
<div>
	@foreach ($posts as $post)
		<div>
			<h1>{{ $post->title }}</h1>
			<span>{{ $post->content }}</span>

			<button wire:click="delete({{ $post->id }})">Delete</button> <!-- [tl! highlight] -->
		</div>
	@endforeach
</div>
```

As you can see, for a post with an ID of 2, the "Delete" button in the above Blade template will render in the browser as:

```html
<button wire:click="delete(2)">Delete</button>
```

When this button is clicked, the `delete()` method will be called, and `$id` will be passed in with a value of "2".

> [!warning] Don't trust action parameters
> Action parameters should be treated as any form of user input, meaning they should not be trusted. Be sure to authorize ownership of an entity before updating it in the database.
> 
> For more information, visit the section on [security concerns and best practices](/docs/actions#security-concerns).


As an added convenience, you can also take advantage of the same mechanism behind [route model binding](/docs/components#using-route-model-binding) to avoid looking up a model by ID. Instead, you can type-hint a parameter with a model class, and the appropriate model will automatically be retrieved from the database and passed to the action instead of the ID:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete(Post $post) // [tl! highlight]
    {
		Auth::user()->can('update', $post);

		$post->delete();
    }

    public function render()
    {
        return view('livewire.show-posts', [
			'posts' => Auth::user()->posts,
        ]);
    }
}
```

## Dependency injection

You can take advantage of [Laravel's dependency injection](https://laravel.com/docs/10.x/controllers#dependency-injection-and-controllers) system by type-hinting parameters to be resolved out of the container just like you would in a normal controller method:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Repositories\PostRepository;

class ShowPosts extends Component
{
    public function delete(PostRepository $posts, $postId) // [tl! highlight]
    {
		$posts->deletePost($postId);
    }

    public function render()
    {
        return view('livewire.show-posts', [
			'posts' => Auth::user()->posts,
        ]);
    }
}
```

```html
<div>
	@foreach ($posts as $post)
		<div>
			<h1>{{ $post->title }}</h1>
			<span>{{ $post->content }}</span>

			<button wire:click="delete({{ $post->id }})">Delete</button> <!-- [tl! highlight] -->
		</div>
	@endforeach
</div>
```

In this example, the `delete` method receives an instance of `PostRepository` resolved from [Laravel's service container](https://laravel.com/docs/10.x/container#main-content) before receiving the passed in `$postId` parameter after.

## Calling actions from Alpine

Livewire integrates seamlessly with [AlpineJS](https://alpinejs.dev/). In fact, under the hood, every Livewire component is also an AlpineJS component. This means you can take full advantage of AlpineJS within your components to add interactivity better suited to the client side.

To make this pairing even more powerful, Livewire exposes a magic object called `$wire` to Alpine that can be treated as a JavaScript representation of your PHP component. In addition to [accessing and mutating public properties on `$wire`](/docs/properties#accessing-properties-from-javascript), you can call methods directly on it, and the PHP method will be called in the backend.

For example:

```html
<button x-on:click="$wire.save()">Save Post</button>
```

As you can see above, we are calling `$wire.save()` from an Alpine event listener. In this case, you would probably use `wire:click="save"`, but as you can imagine, there are many places where it's helpful to call actions from Alpine.

For example, here's a more complex scenario where you might use Alpine's [`x-intersect`](https://alpinejs.dev/plugins/intersect) utility to trigger a Livewire action called `incrementViewCount()` when a certain element is visible on the page:

```html
<div x-intersect="$wire.incrementViewCount()">...</div>
```


### Passing parameters

Any parameters you pass to the `$wire` method will also be passed into the PHP class method.

For example, consider the following Livewire action:

```php
public function addTodo($todo)
{
    $this->todos[] = $todo;
}
```

You can call it directly from Alpine inside your Livewire component's Blade template like so:

```html
<div x-data="{ todo: '' }">
    <input type="text" wire:model="todo">

    <button x-on:click="$wire.addTodo(todo)">Add Todo</button>
</div>
```

If a user had typed in "Take out the trash", when they press the "Add Todo" button, the `addTodo()` method will be triggered with the `$todo` parameter value being "Take out the trash".

### Receiving return values

For even more power, this method is an `async` JavaScript method that returns a promise while the network request is processing. When the server response is received, the promise resolves with any value you returned from the method in PHP.

This gives you the convenience of calling a JavaScript method, with the power of the server side behind it.

For example, assume a Livewire component has the following action:

```php
use App\Models\Post;

public function getPostCount()
{
	return Post::count();
}
```

You could now call it and receive the actual returned value using `$wire`:

```html
<span x-text="await $wire.getPostCount()"></span>
```

Now, if the `getPostCount` method returns "10", the `<span>` tag will also contain "10".

Alpine knowledge is not required when using Livewire, however, it's an extremely powerful tool, and knowing it is well worth your time.

## Livewire's "hybrid" JavaScript functions

Sometimes there are actions in your component that don't need to communicate with the server and can be better written in JavaScript.

In these cases, rather than writing them inside your Blade template or somewhere else, you can return the JavaScript function as a string directly inside your PHP class, and by marking the method with the `#[JS]` attribute, it will be callable from the frontend.

For example:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class SearchPosts extends Component
{
	public $query = '';

    #[JS] // [tl! highlight:6]
    public function reset()
    {
        return <<<JS
			this.query = '';
        JS;
    }

    public function render()
    {
        return view('livewire.search-posts', [
			'posts' => Post::whereTitle($this->query)->get(),
        ]);
    }
}
```

```html
<div>
	<input wire:model.live="query">

	<button wire:click="reset">Reset Search</button> <!-- [tl! highlight] -->

	@foreach ($posts as $post)
		<!-- ... -->
	@enforeach
</div>
```

In the above example, when the "Reset Search" button is pressed, the text input will be cleared without sending a single request to the server.

## Magic actions

Livewire provides a set of "magic" actions that allow you to perform common tasks in your components without defining custom methods. These magic actions can be used directly in your Blade templates inside event listeners.

### `$parent`

The `$parent` magic variable allows you to access parent component properties and call parent component actions from a child component.

```html
<button wire:click="$parent.removePost({{ $post->id }})">Remove</button>
```

In the above example, if a parent component has a `removePost` action, a child can call it directly from its Blade template using `$parent.removePost()`.

### `$set`

The `$set` magic action allows you to update a property in your Livewire component directly from the Blade template. To use `$set`, specify the property you want to update and the new value as arguments.

```html
<button wire:click="$set('query', '')">Reset Search</button>
```

In this example, when the button is clicked, a network request is sent, setting the `$query` property in the component to `''`.

### `$refresh`

The `$refresh` action triggers a re-render of your Livewire component. This can be useful when updating the component's view without changing any property values.

```html
<button wire:click="$refresh">Refresh</button>
```

When the button is clicked, the component will re-render, allowing you to see the latest changes in the view.

### `$toggle`

The `$toggle` magic is used to toggle the value of a boolean property in your Livewire component.

```html
<button wire:click="$toggle('sortAsc')">
	Sort {{ $sortAsc ? 'Descending' : 'Ascending' }}
</button>
```

In this example, when the button is clicked, the `sortAsc` property in the component will toggle between `true` and `false`.

### `$emit`

The `$emit` magic allows you to emit a Livewire event purely from the browser. Here's an example of a button that, when clicked, will emit the `post-deleted` event:

```html
<button type="submit" wire:click="$emit('post-deleted')">Delete Post</button>
```

### `$event`

The `$event` magic variable is available for use within event listeners like `wire:click`. It gives you access to the actual JavaScript event that was triggered, allowing you to reference the triggering element and other relevant information.

```html
<input type="text" wire:keydown.enter="search($event.target.value)">
```

When the enter key is pressed while a user is typing in the input above, the contents of the input will be passed as a parameter to the `search()` action.

### Using magic actions from Alpine

You can also call magic actions from AlpineJS using the `$wire` object. For example, here's how you would call the `$refresh` magic action:

```html
<button x-on:click="$wire.$refresh()">Refresh</button>
```

## Skipping re-renders

Sometimes there might be an action in your component with no side effects that would change the rendered Blade template when it's called. If so, you can skip the `render` portion of Livewire's lifecycle by adding the `#[SkipRender]` attribute above that action method.

To demonstrate, here's an example `ShowPost` component that after a user has scrolled to the bottom of the post, a "view count" is logged:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
	Post $post;

	public function mount(Post $post)
	{
		$this->post = $post;
	}

	#[SkipRender] // [tl! highlight]
	public function incrementViewCount()
	{
		$this->post->incrementViewCount();
	}

	public function render()
	{
		return view('livewire.show-post');
	}
}
```

```html
<div>
	<h1>{{ $post->title }}</h1>
	<p>{{ $post->content }}</p>

	<div x-intersect="$wire.incrementViewCount()"></div>
</div>
```

This example uses [`x-intersect`](https://alpinejs.dev/plugins/intersect), an Alpine utility that calls the expression when the element enters the viewport (typically used to detect when a user scrolls to an element further down the page).

As you can see, when a user scrolls to the bottom, `incrementViewCount` is called, and because `#[SkipRender]` was added, the view is logged, but the template doesn't re-render and no part of the page is affected.

You can also call the `$this->skipRender()` method directly if you prefer that syntax or if you want to skip rendering conditionally:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
	Post $post;

	public function mount(Post $post)
	{
		$this->post = $post;
	}

	public function incrementViewCount()
	{
		$this->post->incrementViewCount();

		$this->skipRender(); // [tl! highlight]
	}

	public function render()
	{
		return view('livewire.show-post');
	}
}
```

## Security Concerns

Remember that any public method in your Livewire component can be called from the client side. Even without something like `wire:click`  associated with it, users can still trigger it from the browser's DevTools.

Below are three examples of easy-to-miss vulnerabilities in Livewire components. Each will show the vulnerable component first and the secure component after. As an exercise, try spotting the vulnerabilities in the first, before viewing the solution after.

If you are having difficulty spotting the vulnerabilities and that makes you concerned about your ability to keep your own apps secure, remember all these vulnerabilities apply to standard web applications that use requests and controllers. If you use a component method as a proxy for a controller method, and its parameters as a proxy for request input, you should be able to apply your existing application security knowledge to your Livewire code.

### Always authorize action parameters

Just like controller request input, it's imperative to authorize action parameters as users can pass any parameters they want.

The following is a `ShowPosts` component where users can view all their posts on one page. They can delete any post they'd like using one of the post's "Delete" button.

Here is a vulnerable version of component:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete($id)
    {
		$post = Post::find($id);

		$post->delete();
    }

	public function render()
	{
		return view('livewire.show-posts', [
			'posts' => Auth::user()->posts,
		]);
	}
}
```

```html
<div>
	@foreach ($posts as $post)
		<div>
			<h1>{{ $post->title }}</h1>	
			<span>{{ $post->content }}</span>	
	
			<button wire:click="delete({{ $post->id }})">Delete</button>
		</div>
	@endforeach
</div>
```

Remember that a malicious user can call `delete` directly from a JavaScript console and pass in any parameters they'd like. This means that a user viewing one of their posts can delete another user's post by passing the un-owned post id to `delete()`.

To protected against this, we need to authorize that the user owns the post about to be deleted:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete($id)
    {
		$post = Post::find($id);

		if (! Auth::user()->can('update', $post)) { // [tl! highlight:2]
			abort(403);	
		}

		$post->delete();
    }

	public function render()
	{
		return view('livewire.show-posts', [
			'posts' => Auth::user()->posts,
		]);
	}
}
```

### Always authorize server-side

Like with standard Laravel controllers, Livewire actions can be called from any user, even if there isn't an affordance in the UI.

Consider the following `BrowsePosts` component where any user can view all the posts in the application, but only administrators can delete a post:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class BrowsePosts extends Component
{
    public function deletePost($id)
    {
		$post = Post::find($id);

		$post->delete();
    }

	public function render()
	{
		return view('livewire.browse-posts', [
			'posts' => Post::all(),
		]);
	}
}
```

```html
<div>
	@foreach ($posts as $post)
		<div>
			<h1>{{ $post->title }}</h1>	
			<span>{{ $post->content }}</span>	
	
			@if (Auth::user()->isAdmin())
				<button wire:click="deletePost({{ $post->id }})">Delete</button>
			@endif
		</div>
	@endforeach
</div>
```

As you can see, only administrators can see the "Delete" button; however, any user can call `deletePost()` on the component from the browser's DevTools console.

To patch this hole, we need to also authorize the action on the server like so:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class BrowsePosts extends Component
{
    public function deletePost($id)
    {
		if (! Auth::user()->isAdmin) { // [tl! highlight:2]
			abort(403);
		}

		$post = Post::find($id);

		$post->delete();
    }

	public function render()
	{
		return view('livewire.browse-posts', [
			'posts' => Post::all(),
		]);
	}
}
```

Now only administrators can ever delete a post from this component because only server-side authorization and validation can be trusted.

### Keep dangerous methods protected or private

Every public method inside your Livewire component is callable from the client. Even ones you haven't referenced inside a `wire:click` handler. To prevent a user from calling a method that isn't intended to be callable client-side, you should mark them as `protected` or `private`. By doing so, you restrict the visibility of that sensitive method to the component's class and its subclasses, ensuring they cannot be called from the client side.

Take the last `BrowsePosts` example, where users can view all posts in your application, but only administrators can delete one. In the [Always authorize server-side](/docs/actions#always-authorize-server-side) section, we made the action secure by adding server-side authorization. Now let's say we refactor the actual deletion of the post into a dedicated method like you might do to clean up your codebase in a more complex situation:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class BrowsePosts extends Component
{
    public function deletePost($id)
    {
		if (! Auth::user()->isAdmin) {
			abort(403);
		}

		$this->delete($id); // [tl! highlight]
    }

	public function delete($postId)  // [tl! highlight:5]
	{
		$post = Post::find($postId);

		$post->delete();
	}

	public function render()
	{
		return view('livewire.browse-posts', [
			'posts' => Post::all(),
		]);
	}
}
```

```html
<div>
	@foreach ($posts as $post)
	<div>
		<h1>{{ $post->title }}</h1>	
		<span>{{ $post->content }}</span>	

		<button wire:click="deletePost({{ $post->id }})">Delete</button>
	</div>
	@endforeach
</div>
```

As you can see, we refactored the post deletion logic into a dedicated method called `delete()`. Even though this method isn't referenced anywhere in our template, if a user gained knowledge of its existence, because it's `public`, they would be able to call it from the browser's DevTools.

To remedy this, we can mark the property as `protected` or `private`, and if a user tries to call it, they will receive an error:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class BrowsePosts extends Component
{
    public function deletePost($id)
    {
		if (! Auth::user()->isAdmin) {
			abort(403);
		}

		$this->delete($id);
    }

	protected function delete($postId) // [tl! highlight]
	{
		$post = Post::find($postId);

		$post->delete();
	}

	public function render()
	{
		return view('livewire.browse-posts', [
			'posts' => Post::all(),
		]);
	}
}
```

## Applying middleware

By default, Livewire re-applies authentication and authorization middleware on subsequent requests applied on the initial page load request.

For example, if your component is loaded inside a route with the "auth" middleware and a user's session ends, when the user triggers an action, the "auth" middleware will be re-applied, and the user will receive an error.

If there are specific middleware that you'd like to apply to a specific action, you can do so with the `#[Middleware]` attribute. For example, if we wanted to apply a `LogPostCreation` middleware to an action, we could do it like so:

```php
<?php

namespace App\Http\Livewire;

use App\Http\Middleware\LogPostCreation;
use Livewire\Component;

class CreatePost extends Component
{
    public $title;

    public $content;

    #[Middleware(LogPostCreation::class)] // [tl! highlight]
    public function save()
    {
        // Create the post...
    }

	// ...
}
```

Now, the `LogPostCreation` middleware will be applied only to the `createPost` action, ensuring that the activity is only being logged when users create a new post.
