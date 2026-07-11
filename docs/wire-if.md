Livewire's `wire:if` directive conditionally renders an element based on the result of an expression — entirely on the client, without a server round-trip.

It is the Livewire equivalent of Alpine's [`x-if`](https://alpinejs.dev/directives/if) directive, evaluated against your component's properties instead of Alpine data.

Unlike `wire:show`, which toggles an element's visibility using CSS (`display: none`), `wire:if` adds and removes the element from the DOM entirely. And unlike Blade's `@if`, the condition is re-evaluated instantly on the client whenever the property changes — no network request required.

Because the content must be able to be added and removed from the page, `wire:if` must be used on a `<template>` tag:

```blade
<template wire:if="expression">
    <div>...</div>
</template>
```

The `<template>` tag itself is never displayed — when the expression is truthy, its contents are rendered directly after it in the DOM.

## Basic usage

Here's an example of using `wire:if` to reveal a delete confirmation prompt:

```php
use Livewire\Component;
use App\Models\Post;

class ShowPost extends Component
{
    public Post $post;

    public $confirmingDelete = false;

    public function delete()
    {
        $this->authorize('delete', $this->post);

        $this->post->delete();

        return $this->redirect('/posts');
    }
}
```

```blade
<div>
    <button x-on:click="$wire.confirmingDelete = true">Delete Post</button>

    <template wire:if="confirmingDelete">
        <div>
            <p>Are you sure you want to delete this post?</p>

            <button wire:click="delete">Yes, delete it</button>

            <button x-on:click="$wire.confirmingDelete = false">Cancel</button>
        </div>
    </template>
</div>
```

When the "Delete Post" button is clicked, the confirmation prompt is added to the page instantly — no server round-trip. When cancelled, it's removed from the DOM entirely.

> [!info] Single root element
> Like `x-if`, the `<template>` tag must contain a single root element.

## `wire:if` vs. `wire:show`

Both directives toggle content based on an expression, but they behave differently:

* `wire:show` renders the element up-front and toggles its CSS `display` property. Best for content that toggles frequently, or that you want to transition in and out.
* `wire:if` doesn't render its content until the expression is truthy, and removes it from the DOM completely when it isn't. Best for expensive content, or content that shouldn't exist on the page at all until needed (like elements with autofocus, validation, or third-party widgets).

## `wire:if` vs. `@if`

Blade's `@if` evaluates on the server — toggling it requires a network round-trip and a re-render. `wire:if` evaluates in the browser against your component's client-side state, so it responds instantly, even mid-request.

Because the content of `wire:if` is rendered on the client, it isn't present in the initial server-rendered HTML. If the content must be visible to search engines or before JavaScript loads, use `@if` or `wire:show` instead.

## Reference

```blade
<template wire:if="expression">
    <div>...</div>
</template>
```

This directive has no modifiers and must be used on a `<template>` tag containing a single root element.
