
The `wire:current` directive allows you to easily detect and style currently active links on a page.

Here's a simple example of adding `wire:current` to links in a navbar so that the currently active link has a stronger font weight:

```blade
<nav>
    <a href="/" ... wire:current="font-bold text-zinc-800">Dashboard</a>
    <a href="/posts" ... wire:current="font-bold text-zinc-800">Posts</a>
    <a href="/users" ... wire:current="font-bold text-zinc-800">Users</a>
</nav>
```

Now when a user visits `/posts`, the "Posts" link will have a stronger font treatment than the other links.

You should note that `wire:current` works out of the box with `wire:navigate` links and page changes.

## Troubleshooting

If `wire:current` is not detecting the current link correctly, ensure the following:

* You have at least one Livewire component on the page, or have hardcoded `@livewireScripts` in your layout
* You have a `href` attribute on the link.
* The `href` attribute starts with a `/`
