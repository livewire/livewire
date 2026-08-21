
Livewire's `wire:navigate` feature makes page navigation much faster, providing an SPA-like experience for your users.

This page is a simple reference for the `wire:navigate` directive. Be sure to read the [page on Livewire's Navigate feature](/docs/4.x/navigate) for more complete documentation.

Below is a simple example of adding `wire:navigate` to links in a nav bar:

```blade
<nav>
    <a href="/" wire:navigate>Dashboard</a>
    <a href="/posts" wire:navigate>Posts</a>
    <a href="/users" wire:navigate>Users</a>
</nav>
```

When any of these links are clicked, Livewire will intercept the click and, instead of allowing the browser to perform a full page visit, Livewire will fetch the page in the background and swap it with the current page (resulting in much faster and smoother page navigation).

`wire:navigate` only intercepts same-origin page visits. External links, links with a non-`_self` target, download links, and URLs such as `mailto:` or `tel:` automatically fall back to the browser's default behavior.

## Styling active links with data-current

Livewire automatically adds a `data-current` attribute to any `wire:navigate` link that matches the current page URL. This allows you to style active navigation links using CSS or Tailwind without any additional directives:

```blade
<nav>
    <a href="/" wire:navigate class="data-current:font-bold">Dashboard</a>
    <a href="/posts" wire:navigate class="data-current:font-bold">Posts</a>
    <a href="/users" wire:navigate class="data-current:font-bold">Users</a>
</nav>
```

The `data-current` attribute is added and removed automatically as users navigate between pages. Read more about [highlighting active links in the Navigate documentation](/docs/4.x/navigate#using-the-data-current-attribute).

## Prefetching pages on hover

By adding the `.hover` modifier, Livewire will pre-fetch a page when a user hovers over a link. This way, the page will have already been downloaded from the server when the user clicks on the link.

```blade
<a href="/" wire:navigate.hover>Dashboard</a>
```

## Animating page visits

By adding the `.transition` modifier, Livewire will animate the page swap using the browser's [View Transitions API](https://developer.mozilla.org/en-US/docs/Web/API/View_Transition_API):

```blade
<a href="/" wire:navigate.transition>Dashboard</a>
```

Read more about [animating page visits in the Navigate documentation](/docs/4.x/navigate#animating-page-visits-with-view-transitions).

## Going deeper

For more complete documentation on this feature, visit [Livewire's navigate documentation page](/docs/4.x/navigate).

## See also

- **[Navigate](/docs/4.x/navigate)** — Complete guide to SPA navigation
- **[Pages](/docs/4.x/pages)** — Create routable page components
- **[@persist](/docs/4.x/directive-persist)** — Persist elements during navigation

## Reference

```blade
wire:navigate
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.hover` | Prefetches the page when user hovers over the link |
| `.transition` | Animates the page swap using the View Transitions API |
