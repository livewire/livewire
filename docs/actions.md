Livewire actions are methods on your component that can be triggered by frontend interactions like clicking a button or submitting a form. They provide the developer experience of being able to call a PHP method directly from the browser, allowing you to focus on the logic of your application without getting bogged down writing boilerplate code connecting your application's frontend and backend.

Let's explore a basic example of calling a `save` action on a `CreatePost` component:

```php
<?php

namespace App\Livewire;

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

```blade
<form wire:submit="save"> <!-- [tl! highlight] -->
    <input type="text" wire:model="title">

    <textarea wire:model="content"></textarea>

    <button type="submit">Save</button>
</form>
```

In the above example, when a user submits the form by clicking "Save", `wire:submit` intercepts the `submit` event and calls the `save()` action on the server.

In essence, actions are a way to easily map user interactions to server-side functionality without the hassle of submitting and handling AJAX requests manually.

## Refreshing a component

Sometimes you may want to trigger a simple "refresh" of your component. For example, if you have a component checking the status of something in the database, you may want to show a button to your users allowing them to refresh the displayed results.

You can do this using Livewire's simple `$refresh` action anywhere you would normally reference your own component method:

```blade
<button type="button" wire:click="$refresh">...</button>
```

When the `$refresh` action is triggered, Livewire will make a server-roundtrip and re-render your component without calling any methods.

It's important to note that any pending data updates in your component (for example `wire:model` bindings) will be applied on the server when the component is refreshed.

Internally, Livewire uses the name "commit" to refer to any time a Livewire component is updated on the server. If you prefer this terminology, you can use the `$commit` helper instead of `$refresh`. The two are identical.

```blade
<button type="button" wire:click="$commit">...</button>
```

You can also trigger a component refresh using AlpineJS in your Livewire component:

```blade
<button type="button" x-on:click="$wire.$refresh()">...</button>
```

Learn more by reading the [documentation for using Alpine inside Livewire](/docs/alpine).

## Confirming an action

When allowing users to perform dangerous actions—such as deleting a post from the database—you may want to show them a confirmation alert to verify that they wish to perform that action.

Livewire makes this easy by providing a simple directive called `wire:confirm`:

```blade
<button
    type="button"
    wire:click="delete"
    wire:confirm="Are you sure you want to delete this post?"
>
    Delete post <!-- [tl! highlight:-2,1] -->
</button>
```

When `wire:confirm` is added to an element containing a Livewire action, when a user tries to trigger that action, they will be presented with a confirmation dialog containing the provided message. They can either press "OK" to confirm the action, or press "Cancel" or hit the escape key.

For more information, visit the [`wire:confirm` documentation page](/docs/wire-confirm).

## Event listeners

Livewire supports a variety of event listeners, allowing you to respond to various types of user interactions:

| Listener        | Description                               |
|-----------------|-------------------------------------------|
| `wire:click`    | Triggered when an element is clicked      |
| `wire:submit`   | Triggered when a form is submitted        |
| `wire:keydown`  | Triggered when a key is pressed down      |
| `wire:keyup`  | Triggered when a key is released
| `wire:mouseenter`| Triggered when the mouse enters an element |
| `wire:*`| Whatever text follows `wire:` will be used as the event name of the listener |

Because the event name after `wire:` can be anything, Livewire supports any browser event you might need to listen for. For example, to listen for `transitionend`, you can use `wire:transitionend`.

### Listening for specific keys

You can use one of Livewire's convenient aliases to narrow down key press event listeners to a specific key or combination of keys.

For example, to perform a search when a user hits `Enter` after typing into a search box, you can use `wire:keydown.enter`:

```blade
<input wire:model="query" wire:keydown.enter="searchPosts">
```

You can chain more key aliases after the first to listen for combinations of keys. For example, if you would like to listen for the `Enter` key only while the `Shift` key is pressed, you may write the following:

```blade
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

Livewire also includes helpful modifiers to make common event-handling tasks trivial.

For example, if you need to call `event.preventDefault()` from inside an event listener, you can suffix the event name with `.prevent`:

```blade
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

Because `wire:` uses [Alpine's](https://alpinejs.dev) `x-on` directive under the hood, these modifiers are made available to you by Alpine. For more context on when you should use these modifiers, consult the [Alpine Events documentation](https://alpinejs.dev/essentials/events).

### Handling third-party events

Livewire also supports listening for custom events fired by third-party libraries.

For example, let's imagine you're using the [Trix](https://trix-editor.org/) rich text editor in your project and you want to listen for the `trix-change` event to capture the editor's content. You can accomplish this using the `wire:trix-change` directive:

```blade
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
> Within Livewire event handlers, you can access the event object via `$event`. This is useful for referencing information on the event. For example, you can access the element that triggered the event via `$event.target`.

> [!warning]
> The Trix demo code above is incomplete and only useful as a demonstration of event listeners. If used verbatim, a network request would be fired on every single keystroke. A more performant implementation would be:
>
> ```blade
> <trix-editor
>    x-on:trix-change="$wire.content = $event.target.value"
>></trix-editor>
> ```

### Listening for dispatched custom events

If your application dispatches custom events from Alpine, you can also listen for those using Livewire:

```blade
<div wire:custom-event="...">

    <!-- Deeply nested within this component: -->
    <button x-on:click="$dispatch('custom-event')">...</button>

</div>
```

When the button is clicked in the above example, the `custom-event` event is dispatched and bubbles up to the root of the Livewire component where `wire:custom-event` catches it and invokes a given action.

If you want to listen for an event dispatched somewhere else in your application, you will need to wait instead for the event to bubble up to the `window` object and listen for it there. Fortunately, Livewire makes this easy by allowing you to add a simple `.window` modifier to any event listener:

```blade
<div wire:custom-event.window="...">
    <!-- ... -->
</div>

<!-- Dispatched somewhere on the page outside the component: -->
<button x-on:click="$dispatch('custom-event')">...</button>
```

### Disabling inputs while a form is being submitted

Consider the `CreatePost` example we previously discussed:

```blade
<form wire:submit="save">
    <input wire:model="title">

    <textarea wire:model="content"></textarea>

    <button type="submit">Save</button>
</form>
```

When a user clicks "Save", a network request is sent to the server to call the `save()` action on the Livewire component.

But, let's imagine that a user is filling out this form on a slow internet connection. The user clicks "Save" and nothing happens initially because the network request takes longer than usual. They might wonder if the submission failed and attempt to click the "Save" button again while the first request is still being handled.

In this case, there would be two requests for the same action being processed at the same time.

To prevent this scenario, Livewire automatically disables the submit button and all form inputs inside the `<form>` element while a `wire:submit` action is being processed. This ensures that a form isn't accidentally submitted twice.

To further lessen the confusion for users on slower connections, it is often helpful to show some loading indicator such as a subtle background color change or SVG animation.

Livewire provides a `wire:loading` directive that makes it trivial to show and hide loading indicators anywhere on a page. Here's a short example of using `wire:loading` to show a loading message below the "Save" button:

```blade
<form wire:submit="save">
    <textarea wire:model="content"></textarea>

    <button type="submit">Save</button>

    <span wire:loading>Saving...</span> <!-- [tl! highlight] -->
</form>
```

`wire:loading` is a powerful feature with a variety of more powerful features. [Check out the full loading documentation for more information](/docs/wire-loading).

## Passing parameters

Livewire allows you to pass parameters from your Blade template to the actions in your component, giving you the opportunity to provide an action additional data or state from the frontend when the action is called.

For example, let's imagine you have a `ShowPosts` component that allows users to delete a post. You can pass the post's ID as a parameter to the `delete()` action in your Livewire component. Then, the action can fetch the relevant post and delete it from the database:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete($id)
    {
        $post = Post::findOrFail($id);

        $this->authorize('delete', $post);

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

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}">
            <h1>{{ $post->title }}</h1>
            <span>{{ $post->content }}</span>

            <button wire:click="delete({{ $post->id }})">Delete</button> <!-- [tl! highlight] -->
        </div>
    @endforeach
</div>
```

For a post with an ID of 2, the "Delete" button in the Blade template above will render in the browser as:

```blade
<button wire:click="delete(2)">Delete</button>
```

When this button is clicked, the `delete()` method will be called and `$id` will be passed in with a value of "2".

> [!warning] Don't trust action parameters
> Action parameters should be treated just like HTTP request input, meaning action parameter values should not be trusted. You should always authorize ownership of an entity before updating it in the database.
>
> For more information, consult our documentation regarding [security concerns and best practices](/docs/actions#security-concerns).


As an added convenience, you may automatically resolve Eloquent models by a corresponding model ID that is provided to an action as a parameter. This is very similar to [route model binding](/docs/components#using-route-model-binding). To get started, type-hint an action parameter with a model class and the appropriate model will automatically be retrieved from the database and passed to the action instead of the ID:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete(Post $post) // [tl! highlight]
    {
        $this->authorize('delete', $post);

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

You can take advantage of [Laravel's dependency injection](https://laravel.com/docs/controllers#dependency-injection-and-controllers) system by type-hinting parameters in your action's signature. Livewire and Laravel will automatically resolve the action's dependencies from the container:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
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

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}">
            <h1>{{ $post->title }}</h1>
            <span>{{ $post->content }}</span>

            <button wire:click="delete({{ $post->id }})">Delete</button> <!-- [tl! highlight] -->
        </div>
    @endforeach
</div>
```

In this example, the `delete()` method receives an instance of `PostRepository` resolved via [Laravel's service container](https://laravel.com/docs/container#main-content) before receiving the provided `$postId` parameter.

## Calling actions from Alpine

Livewire integrates seamlessly with [Alpine](https://alpinejs.dev/). In fact, under the hood, every Livewire component is also an Alpine component. This means you can take full advantage of Alpine within your components to add JavaScript powered client-side interactivity.

To make this pairing even more powerful, Livewire exposes a magic `$wire` object to Alpine that can be treated as a JavaScript representation of your PHP component. In addition to [accessing and mutating public properties via `$wire`](/docs/properties#accessing-properties-from-javascript), you can call actions. When an action is invoked on the `$wire` object, the corresponding PHP method will be invoked on your backend Livewire component:

```blade
<button x-on:click="$wire.save()">Save Post</button>
```

Or, to illustrate a more complex example, you might use Alpine's [`x-intersect`](https://alpinejs.dev/plugins/intersect) utility to trigger a `incrementViewCount()` Livewire action when a given element is visible on the page:

```blade
<div x-intersect="$wire.incrementViewCount()">...</div>
```

### Passing parameters

Any parameters you pass to the `$wire` method will also be passed to the PHP class method. For example, consider the following Livewire action:

```php
public function addTodo($todo)
{
    $this->todos[] = $todo;
}
```

Within your component's Blade template, you can invoke this action via Alpine, providing the parameter that should be given to the action:

```blade
<div x-data="{ todo: '' }">
    <input type="text" x-model="todo">

    <button x-on:click="$wire.addTodo(todo)">Add Todo</button>
</div>
```

If a user had typed in "Take out the trash" into the text input and the pressed the "Add Todo" button, the `addTodo()` method will be triggered with the `$todo` parameter value being "Take out the trash".

### Receiving return values

For even more power, invoked `$wire` actions return a promise while the network request is processing. When the server response is received, the promise resolves with the value returned by the backend action.

For example, consider a Livewire component that has the following action:

```php
use App\Models\Post;

public function getPostCount()
{
    return Post::count();
}
```

Using `$wire`, the action may be invoked and its returned value resolved:

```blade
<span x-init="$el.innerHTML = await $wire.getPostCount()"></span>
```

In this example, if the `getPostCount()` method returns "10", the `<span>` tag will also contain "10".

Alpine knowledge is not required when using Livewire; however, it's an extremely powerful tool and knowing Alpine will augment your Livewire experience and productivity.

## JavaScript actions

Livewire allows you to define JavaScript actions that run entirely on the client-side without making a server request. This is useful in two scenarios:

1. When you want to perform simple UI updates that don't require server communication
2. When you want to optimistically update the UI with JavaScript before making a server request

To define a JavaScript action, you can use the `$js()` function inside a `<script>` tag in your component.

Here's an example of bookmarking a post that uses a JavaScript action to optimistically update the UI before making a server request. The JavaScript action immediately shows the filled bookmark icon, then makes a request to persist the bookmark in the database:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post;

    public $bookmarked = false;

    public function mount()
    {
        $this->bookmarked = $this->post->bookmarkedBy(auth()->user());
    }

    public function bookmarkPost()
    {
        $this->post->bookmark(auth()->user());

        $this->bookmarked = $this->post->bookmarkedBy(auth()->user());
    }

    public function render()
    {
        return view('livewire.show-post');
    }
}
```

```blade
<div>
    <button wire:click="$js.bookmark" class="flex items-center gap-1">
        {{-- Outlined bookmark icon... --}}
        <svg wire:show="!bookmarked" wire:cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
        </svg>

        {{-- Solid bookmark icon... --}}
        <svg wire:show="bookmarked" wire:cloak xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
            <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z" clip-rule="evenodd" />
        </svg>
    </button>
</div>

@script
<script>
    $js('bookmark', () => {
        $wire.bookmarked = !$wire.bookmarked

        $wire.bookmarkPost()
    })
</script>
@endscript
```

When a user clicks the heart button, the following sequence occurs:

1. The "bookmark" JavaScript action is triggered
2. The heart icon immediately updates by toggling `$wire.bookmarked` on the client-side
3. The `bookmarkPost()` method is called to save the change to the database

This provides instant visual feedback while ensuring the bookmark state is properly persisted.

### Calling from Alpine

You can call JavaScript actions directly from Alpine using the `$wire` object. For example, you may use the `$wire` object to invoke the `bookmark` JavaScript action:

```blade
<button x-on:click="$wire.$js.bookmark()">Bookmark</button>
```

### Calling from PHP

JavaScript actions can also be called using the `js()` method from PHP:

```php
<?php

namespace App\Livewire;

use Livewire\Component;

class CreatePost extends Component
{
    public $title = '';

    public function save()
    {
        // ...

        $this->js('onPostSaved'); // [tl! highlight]
    }
}
```

```blade
<div>
    <!-- ... -->

    <button wire:click="save">Save</button>
</div>

@script
<script>
    $js('onPostSaved', () => {
        alert('Your post has been saved successfully!')
    })
</script>
@endscript
```

In this example, when the `save()` action is finished, the `postSaved` JavaScript action will be run, triggering the alert dialog.

## Magic actions

Livewire provides a set of "magic" actions that allow you to perform common tasks in your components without defining custom methods. These magic actions can be used within event listeners defined in your Blade templates.

### `$parent`

The `$parent` magic variable allows you to access parent component properties and call parent component actions from a child component:

```blade
<button wire:click="$parent.removePost({{ $post->id }})">Remove</button>
```

In the above example, if a parent component has a `removePost()` action, a child can call it directly from its Blade template using `$parent.removePost()`.

### `$set`

The `$set` magic action allows you to update a property in your Livewire component directly from the Blade template. To use `$set`, provide the property you want to update and the new value as arguments:

```blade
<button wire:click="$set('query', '')">Reset Search</button>
```

In this example, when the button is clicked, a network request is dispatched that sets the `$query` property in the component to `''`.

### `$refresh`

The `$refresh` action triggers a re-render of your Livewire component. This can be useful when updating the component's view without changing any property values:

```blade
<button wire:click="$refresh">Refresh</button>
```

When the button is clicked, the component will re-render, allowing you to see the latest changes in the view.

### `$toggle`

The `$toggle` action is used to toggle the value of a boolean property in your Livewire component:

```blade
<button wire:click="$toggle('sortAsc')">
    Sort {{ $sortAsc ? 'Descending' : 'Ascending' }}
</button>
```

In this example, when the button is clicked, the `$sortAsc` property in the component will toggle between `true` and `false`.

### `$dispatch`

The `$dispatch` action allows you to dispatch a Livewire event directly in the browser. Below is an example of a button that, when clicked, will dispatch the `post-deleted` event:

```blade
<button type="submit" wire:click="$dispatch('post-deleted')">Delete Post</button>
```

### `$event`

The `$event` action may be used within event listeners like `wire:click`. This action gives you access to the actual JavaScript event that was triggered, allowing you to reference the triggering element and other relevant information:

```blade
<input type="text" wire:keydown.enter="search($event.target.value)">
```

When the enter key is pressed while a user is typing in the input above, the contents of the input will be passed as a parameter to the `search()` action.

### Using magic actions from Alpine

You can also call magic actions from Alpine using the `$wire` object. For example, you may use the `$wire` object to invoke the `$refresh` magic action:

```blade
<button x-on:click="$wire.$refresh()">Refresh</button>
```

## Skipping re-renders

Sometimes there might be an action in your component with no side effects that would change the rendered Blade template when the action is invoked. If so, you can skip the `render` portion of Livewire's lifecycle by adding the `#[Renderless]` attribute above the action method.

To demonstrate, in the `ShowPost` component below, the "view count" is logged when the user has scrolled to the bottom of the post:

```php
<?php

namespace App\Livewire;

use Livewire\Attributes\Renderless;
use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post;

    public function mount(Post $post)
    {
        $this->post = $post;
    }

    #[Renderless] // [tl! highlight]
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

```blade
<div>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->content }}</p>

    <div x-intersect="$wire.incrementViewCount()"></div>
</div>
```

The example above uses [`x-intersect`](https://alpinejs.dev/plugins/intersect), an Alpine utility that calls the expression when the element enters the viewport (typically used to detect when a user scrolls to an element further down the page).

As you can see, when a user scrolls to the bottom of the post, `incrementViewCount()` is invoked. Since `#[Renderless]` was added to the action, the view is logged, but the template doesn't re-render and no part of the page is affected.

If you prefer to not utilize method attributes or need to conditionally skip rendering, you may invoke the `skipRender()` method in your component action:

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post;

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

## Security concerns

Remember that any public method in your Livewire component can be called from the client-side, even without an associated `wire:click` handler that invokes it. In these scenarios, users can still trigger the action from the browser's DevTools.

Below are three examples of easy-to-miss vulnerabilities in Livewire components. Each will show the vulnerable component first and the secure component after. As an exercise, try spotting the vulnerabilities in the first example before viewing the solution.

If you are having difficulty spotting the vulnerabilities and that makes you concerned about your ability to keep your own applications secure, remember all these vulnerabilities apply to standard web applications that use requests and controllers. If you use a component method as a proxy for a controller method, and its parameters as a proxy for request input, you should be able to apply your existing application security knowledge to your Livewire code.

### Always authorize action parameters

Just like controller request input, it's imperative to authorize action parameters since they are arbitrary user input.

Below is a `ShowPosts` component where users can view all their posts on one page. They can delete any post they like using one of the post's "Delete" buttons.

Here is a vulnerable version of the component:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
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

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}">
            <h1>{{ $post->title }}</h1>
            <span>{{ $post->content }}</span>

            <button wire:click="delete({{ $post->id }})">Delete</button>
        </div>
    @endforeach
</div>
```

Remember that a malicious user can call `delete()` directly from a JavaScript console, passing any parameters they would like to the action. This means that a user viewing one of their posts can delete another user's post by passing the un-owned post ID to `delete()`.

To protect against this, we need to authorize that the user owns the post about to be deleted:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function delete($id)
    {
        $post = Post::find($id);

        $this->authorize('delete', $post); // [tl! highlight]

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

Like standard Laravel controllers, Livewire actions can be called by any user, even if there isn't an affordance for invoking the action in the UI.

Consider the following `BrowsePosts` component where any user can view all the posts in the application, but only administrators can delete a post:

```php
<?php

namespace App\Livewire;

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

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}">
            <h1>{{ $post->title }}</h1>
            <span>{{ $post->content }}</span>

            @if (Auth::user()->isAdmin())
                <button wire:click="deletePost({{ $post->id }})">Delete</button>
            @endif
        </div>
    @endforeach
</div>
```

As you can see, only administrators can see the "Delete" button; however, any user can call `deletePost()` on the component from the browser's DevTools.

To patch this vulnerability, we need to authorize the action on the server like so:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
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

With this change, only administrators can delete a post from this component.

### Keep dangerous methods protected or private

Every public method inside your Livewire component is callable from the client. Even methods you haven't referenced inside a `wire:click` handler. To prevent a user from calling a method that isn't intended to be callable client-side, you should mark them as `protected` or `private`. By doing so, you restrict the visibility of that sensitive method to the component's class and its subclasses, ensuring they cannot be called from the client-side.

Consider the `BrowsePosts` example that we previously discussed, where users can view all posts in your application, but only administrators can delete posts. In the [Always authorize server-side](/docs/actions#always-authorize-server-side) section, we made the action secure by adding server-side authorization. Now imagine we refactor the actual deletion of the post into a dedicated method like you might do in order to simplify your code:

```php
// Warning: This snippet demonstrates what NOT to do...
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
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

```blade
<div>
    @foreach ($posts as $post)
        <div wire:key="{{ $post->id }}">
            <h1>{{ $post->title }}</h1>
            <span>{{ $post->content }}</span>

            <button wire:click="deletePost({{ $post->id }})">Delete</button>
        </div>
    @endforeach
</div>
```

As you can see, we refactored the post deletion logic into a dedicated method named `delete()`. Even though this method isn't referenced anywhere in our template, if a user gained knowledge of its existence, they would be able to call it from the browser's DevTools because it is `public`.

To remedy this, we can mark the method as `protected` or `private`. Once the method is marked as `protected` or `private`, an error will be thrown if a user tries to invoke it:

```php
<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
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

<!--
## Applying middleware

By default, Livewire re-applies authentication and authorization related middleware on subsequent requests if those middleware were applied on the initial page load request.

For example, imagine your component is loaded inside a route that is assigned the `auth` middleware and a user's session ends. When the user triggers another action, the `auth` middleware will be re-applied and the user will receive an error.

If there are specific middleware that you would like to apply to a specific action, you may do so with the `#[Middleware]` attribute. For example, we could apply a `LogPostCreation` middleware to an action that creates posts:

```php
<?php

namespace App\Livewire;

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

-->
