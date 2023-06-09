Modern web applications are often referred to as SPAs. SPA stands for Single Page Application. Meaning instead of each page in your application being a full browser page visit with lots of overhead—such as loading javascript assets—each page visit updates the current, existing page.

The alternative to a *Single Page App* is a *Multi-page App*, where every time a user clicks a link, an entirely new HTML page is requested and rendered in the browser.

Livewire provides this experience through a simple attribute you can add to links in your application: `wire:navigate`.

By adding `wire:navigate` to a link, when it's clicked, Livewire will request the page in the background, then when it's received, will swap its contents into the current page and update the URL.

The experience feels like using an SPA. Each page navigation is much faster than a native full-page load.

In addition to providing faster page loads, `wire:navigate` is capable of much more advanced functionality like _prefetching_ pages when a link is hovered over or caching and restoring pages when the back button is clicked.

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

By adding `wire:navigate` to each link in a navigation menu on each page, Livewire will know to prevent the standard handling of the link click and replace it with its own, faster version:

```html
<nav>
    <a href="/" wire:navigate>Dashboard</a>
    <a href="/posts" wire:navigate>Posts</a>
    <a href="/users" wire:navigate>Users</a>
</nav>
```

When one of these links is clicked, instead of initiating a full page load in the browser, Livewire will fetch the new page in the background using AJAX and swap only the `<body>` of the page. 

This technique results in much faster page load times and gives the feeling of a JavaScript single page application.

## Redirects

By default, Livewire will use this functionality on redirects triggered from components that have been loaded using `wire:navigate`, however, you can force Livewire to use "navigate" for a redirect by passing the `navigate` argument to `redirect()`:

```php
return $this->redirect('/somewhere', navigate: true);
```

Now, instead of a full page load being triggered, Livewire will replace the contents and URL of the current page with the new one.

## Prefetching links

You can speed up page visits even more by *prefetching* when a user hovers over a link.

Often an entire page can be fetched from the server between the time a user hovers over a link and when they click it, making the new page appear instantly as if there was no network request.

To enable link *prefetching*, append the `.prefetch` modifier to `wire:navigate` like so:

```html
<a href="/posts" wire:navigate.prefetch>Posts</a>
```

Using *prefetching* will increase the number of server requests sent because some links will be hovered but never clicked. If you are OK with this slight increase in server resource usage, there's no reason not to use `.prefetch`.

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


```html
<nav>
    <a href="/" wire:navigate class="{{ request()->is('/') && 'active' }}">Dashboard</a>
    <a href="/posts" wire:navigate class="{{ request()->is('/posts') && 'active' }}">Posts</a>
    <a href="/users" wire:navigate class="{{ request()->is('/users') && 'active' }}">Users</a>
</nav>
```

## Customizing the progress bar

### Hiding the progress bar

```php
"navigate": [
    "show_progress_bar": false,
],
```

## Navigation events

```html
<script wire:ignore>
    window.addEventListener('livewire:navigated', () => {
        // ...
    })
</script>
```

## Controlling asset evaluation

```html
<html>
    <!-- Everything in <head> will only be loaded initially -->
    <head>
        <link rel="stylesheet" href="/app.css" />

        <script src="/app.js"></script>
    </head>

    <!-- Any assets in <body> will be loaded on every visit -->
    <body>
        <!-- ... -->

        
    </body>
</html>
```

// todo
