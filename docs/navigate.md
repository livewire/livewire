
Many modern web applications are built as "single page applications" (SPAs). In these applications, each page rendered by the application no longer requires a full browser page reload, avoiding the overhead of re-downloading JavaScript and CSS assets on every request.

The alternative to a *single page application* is a *multi-page application*. In these applications, every time a user clicks a link, an entirely new HTML page is requested and rendered in the browser.

While most PHP applications have traditionally been multi-page applications, Livewire offers a single page application experience via a simple attribute you can add to links in your application: `wire:navigate`.

## Basic usage

Let's explore an example of using `wire:navigate`. Below is a typical Laravel routes file (`routes/web.php`) with three Livewire components defined as routes:

```php
use App\Livewire\Dashboard;
use App\Livewire\ShowPosts;
use App\Livewire\ShowUsers;

Route::get('/', Dashboard::class);

Route::get('/posts', ShowPosts::class);

Route::get('/users', ShowUsers::class);
```

By adding `wire:navigate` to each link in a navigation menu on each page, Livewire will prevent the standard handling of the link click and replace it with its own, faster version:

```blade
<nav>
    <a href="/" wire:navigate>Dashboard</a>
    <a href="/posts" wire:navigate>Posts</a>
    <a href="/users" wire:navigate>Users</a>
</nav>
```

Below is a breakdown of what happens when a `wire:navigate` link is clicked:

* User clicks a link
* Livewire prevents the browser from visiting the new page
* Instead, Livewire requests the page in the background and shows a loading bar at the top of the page
* When the HTML for the new page has been received, Livewire replaces the current page's URL, `<title>` tag and `<body>` contents with the elements from the new page

This technique results in much faster page load times—often twice as fast—and makes the application "feel" like a JavaScript powered single page application.

## Redirects

When one of your Livewire components redirects users to another URL within your application, you can also instruct Livewire to use its `wire:navigate` functionality to load the new page. To accomplish this, provide the `navigate` argument to the `redirect()` method:

```php
return $this->redirect('/posts', navigate: true);
```

Now, instead of a full page request being used to redirect the user to the new URL, Livewire will replace the contents and URL of the current page with the new one.

## Prefetching links

By default, Livewire includes a gentle strategy to _prefetch_ pages before a user clicks on a link:

* A user presses down on their mouse button
* Livewire starts requesting the page
* They lift up on the mouse button to complete the _click_
* Livewire finishes the request and navigates to the new page

Surprisingly, the time between a user pressing down and lifting up on the mouse button is often enough time to load half or even an entire page from the server.

If you want an even more aggressive approach to prefetching, you may use the `.hover` modifier on a link:

```blade
<a href="/posts" wire:navigate.hover>Posts</a>
```

The `.hover` modifier will instruct Livewire to prefetch the page after a user has hovered over the link for `60` milliseconds.

> [!warning] Prefetching on hover increases server usage
> Because not all users will click a link they hover over, adding `.hover` will request pages that may not be needed, though Livewire attempts to mitigate some of this overhead by waiting `60` milliseconds before prefetching the page.

## Persisting elements across page visits

Sometimes, there are parts of a user interface that you need to persist between page loads, such as audio or video players. For example, in a podcasting application, a user may want to keep listening to an episode as they browse other pages.

You can achieve this in Livewire with the `@persist` directive.

By wrapping an element with `@persist` and providing it with a name, when a new page is requested using `wire:navigate`, Livewire will look for an element on the new page that has a matching `@persist`. Instead of replacing the element like normal, Livewire will use the existing DOM element from the previous page in the new page, preserving any state within the element.

Here is an example of an `<audio>` player element being persisted across pages using `@persist`:

```blade
@persist('player')
    <audio src="{{ $episode->file }}" controls></audio>
@endpersist
```

If the above HTML appears on both pages—the current page, and the next one—the original element will be re-used on the new page. In the case of an audio player, the audio playback won't be interrupted when navigating from one page to another.


## Script evaluation

When navigating to a new page using `wire:navigate`, it _feels_ like the browser has changed pages; however, from the browser's perspective, you are technically still on the original page.

Because of this, styles and scripts are executed normally on the first page, but on subsequent pages, you may have to tweak the way you normally write JavaScript.

Here are a few caveats and scenarios you should be aware of when using `wire:navigate`.

### Don't rely on `DOMContentLoaded`

It's common practice to place JavaScript inside a `DOMContentLoaded` event listener so that the code you want to run only executes after the page has fully loaded.

When using `wire:navigate`, `DOMContentLoaded` is only fired on the first page visit, not subsequent visits.

To run code on every page visit, swap every instance of `DOMContentLoaded` with `livewire:navigated`:

```js
document.addEventListener('DOMContentLoaded', () => { // [tl! remove]
document.addEventListener('livewire:navigated', () => { // [tl! add]
    // ...
})
```

Now, any code placed inside this listener will be run on the initial page visit, and also after Livewire has finished navigating to subsequent pages.

Listening to this event is useful for things like initializing third-party libraries.

### Scripts in `<head>` are loaded once

If two pages include the same `<script>` tag in the `<head>`, that script will only be run on the initial page visit and not on subsequent page visits.

```blade
<!-- Page one -->
<head>
    <script src="/app.js"></script>
</head>

<!-- Page two -->
<head>
    <script src="/app.js"></script>
</head>
```

### New `<head>` scripts are evaluated

If a subsequent page includes a new `<script>` tag in the `<head>` that was not present in the `<head>` of the initial page visit, Livewire will run the new `<script>` tag.

In the below example, _page two_ includes a new JavaScript library for a third-party tool. When the user navigates to _page two_, that library will be evaluated.

```blade
<!-- Page one -->
<head>
    <script src="/app.js"></script>
</head>

<!-- Page two -->
<head>
    <script src="/app.js"></script>
    <script src="/third-party.js"></script>
</head>
```

### Reloading when assets change

It's common practice to include a version hash in an application's main JavaScript file name. This ensures that after deploying a new version of your application, users will receive the fresh JavaScript asset, and not an old version served from the browser's cache.

But, now that you are using `wire:navigate` and each page visit is no longer a fresh browser page load, your users may still be receiving stale JavaScript after deployments.

To prevent this, you may add `data-navigate-track` to a `<script>` tag in `<head>`:

```blade
<!-- Page one -->
<head>
    <script src="/app.js?id=123" data-navigate-track></script>
</head>

<!-- Page two -->
<head>
    <script src="/app.js?id=456" data-navigate-track></script>
</head>
```

When a user visits _page two_, Livewire will detect a fresh JavaScript asset and trigger a full browser page reload.

If you are using [Laravel's Vite plug-in](https://laravel.com/docs/vite#loading-your-scripts-and-styles) to bundle and serve your assets, Livewire adds `data-navigate-track` to the rendered HTML asset tags automatically. You can continue referencing your assets and scripts like normal:

```blade
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

Livewire will automatically inject `data-navigate-track` onto the rendered HTML tags.

### Scripts in the `<body>` are re-evaluated

Because Livewire replaces the entire contents of the `<body>` on every new page, all `<script>` tags on the new page will be run:

```blade
<!-- Page one -->
<body>
    <script>
        console.log('Runs on page one')
    </script>
</body>

<!-- Page two -->
<body>
    <script>
        console.log('Runs on page two')
    </script>
</body>
```

If you have a `<script>` tag in the body that you only want to be run once, you can add the `data-navigate-once` attribute to the `<script>` tag and Livewire will only run it on the initial page visit:

```blade
<script data-navigate-once>
    console.log('Runs only on page one')
</script>
```

