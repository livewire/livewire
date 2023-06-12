
Modern web applications are often referred to as SPAs. SPA stands for Single Page Application. Instead of each page in your application being a full browser page visit with lots of overhead—such as loading javascript assets—each page visit updates the current, existing page.

The alternative to a *Single Page App* is a *Multi-page App*, where every time a user clicks a link, an entirely new HTML page is requested and rendered in the browser.

Livewire provides this experience through a simple attribute you can add to links in your application: `wire:navigate`.

## Basic Usage

To demonstrate, below is a standard Laravel routes file (`routes/web.php`) with three Livewire components defined as routes in the application:

```php
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\ShowPosts;
use App\Http\Livewire\ShowUsers;

Route::get('/', Dashboard::class);

Route::get('/posts', ShowPosts::class);

Route::get('/users', ShowUsers::class);
```

By adding `wire:navigate` to each link in a navigation menu on each page, Livewire will prevent the standard handling of the link click and replace it with its own, faster version:

```html
<nav>
    <a href="/" wire:navigate>Dashboard</a>
    <a href="/posts" wire:navigate>Posts</a>
    <a href="/users" wire:navigate>Users</a>
</nav>
```

Here is what happens when a `wire:navigate` link is clicked:
* User clicks a link
* Livewire prevents the browser from visiting the new page
* Instead, Livewire requests the page in the background via AJAX and shows a loading bar at the top of the page
* When the HTML for the new page has been received, Livewire replaces the current page's URL, `<title>` tag and `<body>` contents with the new one. 

This technique results in much faster page load times—often twice as fast—and gives the feeling of a JavaScript single page application.

## Redirects

By default, Livewire will use this navigation functionality on redirects triggered from components that have been loaded using `wire:navigate`, however, you can force Livewire to use _navigate_ for a redirect by passing the `navigate` argument to `redirect()`:

```php
return $this->redirect('/posts', navigate: true);
```

Now, instead of a full page load being triggered, Livewire will replace the contents and URL of the current page with the new one.

## Prefetching links

By default, Livewire includes a non-aggressive strategy to _prefetch_ pages before a user clicks on a link:

* A user presses down on their mouse button
* Livewire starts requesting the page
* They lift up on the mouse button to complete the _click_
* Livewire finishes the request and navigates to the new page

You might not realize, but the time between when a user presses down and lifts up on the mouse button is often enough time to load half or even an entire page from the server.

If you want an even more aggressive prefetch, you can enable it using the `.prefetch.hover` modifier:

```html
<a href="/posts" wire:navigate.prefetch.hover>Posts</a>
```

This will prefetch the page after a user has hovered over the link for `60` milliseconds.

> [!warning] Prefetching on hover increases server usage
> Because not all users will click a link they hover over, by adding `.prefetch.hover`, you will be request pages you may not need to. Livewire mitigates some of this overhead by waiting `60` milliseconds to prefetch, but it's worth mentioning nonetheless.

## Persisting elements across page visits

Sometimes, there are parts of a user interface that you need to persist between page loads. Audio and video players are typical examples of this. In a podcasting app, a user may want to keep listening to an episode as they browse other pages.

You can achieve this in Livewire with the `wire:persist` directive.

By adding `wire:persist` to an element and providing it with a name, when a new page is requested using `wire:navigate`, Livewire will look for an element on the new page matching the same `wire:persist`. Instead of replacing the element like normal, Livewire will use the existing DOM element from the previous page in the new page; this way, any state remains unchanged.

Here is an example of an `<audio>` player element being persisted across pages using `wire:persist`:

```html
<div wire:persist="player">
    <audio src="{{ $episode->file }}" controls></audio>
</div>
```

If the above HTML appears on both pages—the current page, and the next one—the original element will be re-used on the new page. In the case of an audio player, the audio playback won't be interupted when navigating from one page to another.

## Showing active link states

For the most part, you can use standard Blade utilities to detect the current page and show a corresponding visual indication:

```html
<nav>
    <a href="/" wire:navigate class="{{ request()->is('/') && 'active' }}">Dashboard</a>
    
    <a href="/posts" wire:navigate class="{{ request()->is('/posts') && 'active' }}">Posts</a>
    
    <a href="/users" wire:navigate class="{{ request()->is('/users') && 'active' }}">Users</a>
</nav>
```

## The progress bar

By default, Livewire will show a progress bar at the top of the page while a new page is being fetched from the server.

### Hiding the progress bar

If you wish to remove this progress bar entirely, you can do so in Livewire's configuration file—`config/livewire.php`:

```php
"navigate": [
    "show_progress_bar": false,
],
```

## Script evaluation

When navigating to a new page using `wire:navigate`, it _feels_ like the browser has changed pages; however, you are technically still on the original page—from the browser's perspective.

Because of this, styles and scripts are executed normally on the first page, but on subsequent pages, you may have to tweak the way you normally write JavaScript.

Here are a few gotchas and scenarios you should be aware of when using `wire:navigate`.

### Don't rely on `DOMContentLoaded`

It's common practice to place JavaScript inside a `DOMContentLoaded` event listener, so that the code you want to run only executes after the page has fully loaded and not prematurely.

When using `wire:navigate`, `DOMContentLoaded` is only fired on the first page visit, not subsequent ones.

To run code on every page visit, swap every instance of `DOMContentLoaded` with `livewire:navigated`:

```js
document.addEventListener('DOMContentLoaded', () => { // [tl! remove]
document.addEventListener('livewire:navigated', () => { // [tl! add]
    // ...
})
```

Now, any code placed inside this listener will be run on the initial page load, and after Livewire has finished navigating to future pages.

This is useful for things like initializing third-party libraries.

### Scripts in `<head>` are loaded once

Like you might guess, if two pages include the same `<script>` tag in the `<head>`, that script will only be run initially and not on subsequent page visits.

```html
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

It's worth mentioning that if a subsequent page includes a new `<script>` tag in the `<head>`, Livewire will run it.

In the below example, _page two_ includes a new JS library for a third-party tool. When the user navigates to _page two_, that library will be evaluated.

```html
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

It's common practice to include some kind of version hash in an app's main JavaScript file name. This ensures that after deploying a new version of your application, users will receive the fresh JavaScript asset, and not an old version served from the browser's cache.

Now that you are using `wire:navigate` and each page visit is no longer a fresh browser page load, your users may still be receiving stale JavaScript after a deploy.

To prevent this scenario, you can add `data-navigate-track` to a `<script>` tag in `<head>`:

```html
<!-- Page one -->
<head>
    <script src="/app.js?id=123" data-navigate-track></script>
</head>

<!-- Page two -->
<head>
    <script src="/app.js?id=456" data-navigate-track></script>
</head>

@vite('')
```

When a user visits _page two_ Livewire will detect a fresh JavaScript asset, and trigger a full browser page reload.

If you are using [Vite](https://laravel.com/docs/10.x/vite#loading-your-scripts-and-styles) in your application to bundle and serve your assets, Livewire takes care of this automatically. You can continue referencing your scripts like normal:

```html
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
```

And Livewire will automatically inject `data-navigate-track` onto the rendered HTML tags.

### Scripts in the `<body>` are re-evaluated

Because Livewire replaces the entire contents of the `<body>` on every new page, all `<script>` tags on the new page will be run:

```html
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

Sometimes this is desired, other times it is not.

If you have a `<script>` tag in the body that you only want to be run once, you can add the `data-navigate-once` attribute, and Livewire will only run it initially.

```html
<script data-navigate-once>
    console.log('Runs only on page one')
</script>
```

