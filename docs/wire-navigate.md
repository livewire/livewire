
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

## Confirming when there are unsaved changes

Use `.dirty-confirm` to show a confirmation dialog before navigating away if any Livewire component on the page has unsaved state:

```blade
<a href="/posts" wire:navigate.dirty-confirm>Posts</a>
```

If the page is clean, navigation proceeds normally without showing a dialog.

### Using a custom dialog flow

You can provide an expression to `.dirty-confirm` and open your own modal/dialog instead of using the browser confirm:

```blade
<a
    href="/posts"
    wire:navigate.dirty-confirm="isOpenDialog = true; pendingUrl = $url"
>
    Posts
</a>
```

When an expression is present, Livewire evaluates it and pauses the current navigation. You can then continue later from your custom dialog by calling `Livewire.navigate(pendingUrl)`.

If you still want browser fallback inside your expression, use `$fallbackConfirm()`:

```blade
<a
    href="/posts"
    wire:navigate.dirty-confirm="if ($fallbackConfirm()) Livewire.navigate($url)"
>
    Posts
</a>
```

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
| `.dirty-confirm` | Prompts before navigating away when Livewire state is dirty |
