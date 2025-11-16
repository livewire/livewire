
The `wire:current` directive allows you to easily detect and style currently active links on a page.

> [!tip] Consider using data-current instead
> Livewire automatically adds a `data-current` attribute to all `wire:navigate` links that match the current page. You can style these links directly with Tailwind's `data-current:` variant or CSS, without needing the `wire:current` directive. [Learn more about automatic data-current →](/docs/4.x/navigate#using-the-data-current-attribute)

Here's a simple example of adding `wire:current` to links in a navbar so that the currently active link has a stronger font weight:

```blade
<nav>
    <a href="/dashboard" ... wire:current="font-bold text-zinc-800">Dashboard</a>
    <a href="/posts" ... wire:current="font-bold text-zinc-800">Posts</a>
    <a href="/users" ... wire:current="font-bold text-zinc-800">Users</a>
</nav>
```

Now when a user visits `/posts`, the "Posts" link will have a stronger font treatment than the other links.

You should note that `wire:current` works out of the box with `wire:navigate` links and page changes, and automatically adds the `data-current` attribute to matching links in addition to the specified classes.

## Exact matching

By default, `wire:current` uses a partial matching strategy, meaning it will be applied if the link and current page share the beginning portion of the Url's path.

For example, if the link is `/posts`, and the current page is `/posts/1`, the `wire:current` directive will be applied.

If you wish to use exact matching, you can add the `.exact` modifier to the directive.

Here's an example where you might want to use exact matching to prevent the "Dashboard" link from being highlighted when the user visits `/posts`:

```blade
<nav>
    <a href="/" wire:current.exact="font-bold">Dashboard</a>
</nav>
```

## Strict matching

By default, `wire:current` will remove trailing slashes (`/`) from its comparison.

If you'd like to disable this behavior and force a stract path string comparison, you can append the `.strict` modifier:

```blade
<nav>
    <a href="/posts/" wire:current.strict="font-bold">Dashboard</a>
</nav>
```

## Troubleshooting

If `wire:current` is not detecting the current link correctly, ensure the following:

* You have at least one Livewire component on the page, or have hardcoded `@livewireScripts` in your layout
* You have a `href` attribute on the link.

## Reference

```blade
wire:current="classes"
```

### Modifiers

| Modifier | Description |
|----------|-------------|
| `.exact` | Use exact path matching instead of partial matching |
| `.strict` | Force strict path comparison including trailing slashes |
