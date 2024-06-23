
Livewire's `wire:navigate` feature makes page navigation much faster, providing an SPA-like experience for your users.

This page is a simple reference for the `wire:navigate` directive. Be sure to read the [page on Livewire's Navigate feature](/docs/navigate) for more complete documentation.

Below is a simple example of adding `wire:navigate` to links in a nav bar:

```blade
<nav>
    <a href="/" wire:navigate>Dashboard</a>
    <a href="/posts" wire:navigate>Posts</a>
    <a href="/users" wire:navigate>Users</a>
</nav>
```

When any of these links are clicked, Livewire will intercept the click and, instead of allowing the browser to perform a full page visit, Livewire will fetch the page in the background and swap it with the current page (resulting in much faster and smoother page navigation).

## Prefetching pages on hover

By adding the `.hover` modifier, Livewire will pre-fetch a page when a user hovers over a link. This way, the page will have already been downloaded from the server when the user clicks on the link.

```blade
<a href="/" wire:navigate.hover>Dashboard</a>
```

## Going deeper

For more complete documentation on this feature, visit [Livewire's navigate documentation page](/docs/navigate).
