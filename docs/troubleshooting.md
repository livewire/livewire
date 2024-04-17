Here at Livewire HQ, we try to remove problems from your pathway before you hit them. However, sometimes, there are some problems that we can't solve without introducing new ones, and other times, there are problems we can't anticipate.

Here are some common errors and scenarios you may encounter in your Livewire apps.

## Component mismatches

When interacting with Livewire components on your page, you may encounter odd behavior or error messages like the following:

```
Error: Component already initialized
```

```
Error: Snapshot missing on Livewire component with id: ...
```

There are lots of reasons why you may encounter these messages, but the most common one is forgetting to add `wire:key` to elements and components inside a `@foreach` loop.

### Adding `wire:key`

Any time you have a loop in your Blade templates using something like `@foreach`, you need to add `wire:key` to the opening tag of the first element within the loop:

```blade
@foreach($posts as $post)
    <div wire:key="{{ $post->id }}"> <!-- [tl! highlight] -->
        ...
    </div>
@endforeach
```

This ensures that Livewire can keep track of different elements in the loop when the loop changes.

The same applies to Livewire components within a loop:

```blade
@foreach($posts as $post)
    <livewire:show-post :$post :key="$post->id" /> <!-- [tl! highlight] -->
@endforeach
```

However, here's a tricky scenario you might not have assumed:

When you have a Livewire component deeply nested inside a `@foreach` loop, you STILL need to add a key to it. For example:

```blade
@foreach($posts as $post)
    <div wire:key="{{ $post->id }}">
        ...
        <livewire:show-post :$post :key="$post->id" /> <!-- [tl! highlight] -->
        ...
    </div>
@endforeach
```

Without the key on the nested Livewire component, Livewire will be unable to match the looped components up between network requests.

#### Prefixing keys

Another tricky scenario you may run into is having duplicate keys within the same component. This often results from using model IDs as keys, which can sometimes collide.

Here's an example where we need to add a `post-` and an `author-` prefix to designate each set of keys as unique. Otherwise, if you have a `$post` and `$author` model with the same ID, you would have an ID collision:

```blade
<div>
    @foreach($posts as $post)
        <div wire:key="post-{{ $post->id }}">...</div> <!-- [tl! highlight] -->
    @endforeach

    @foreach($authors as $author)
        <div wire:key="author-{{ $author->id }}">...</div> <!-- [tl! highlight] -->
    @endforeach
</div>
```

## Multiple instances of Alpine

When installing Livewire, you may run into error messages like the following:

```
Error: Detected multiple instances of Alpine running
```

```
Alpine Expression Error: $wire is not defined
```

If this is the case, you likely have two versions of Alpine running on the same page. Livewire includes its own bundle of Alpine under the hood, so you must remove any other versions of Alpine on Livewire pages in your application.

One common scenario in which this happens is adding Livewire to an existing application that already includes Alpine. For example, if you installed the Laravel Breeze starter kit and then added Livewire later, you would run into this.

The fix for this is simple: remove the extra Alpine instance.

### Removing Laravel Breeze's Alpine

If you are installing Livewire inside an existing Laravel Breeze (Blade + Alpine version), you need to remove the following lines from `resources/js/app.js`:

```js
import './bootstrap';

import Alpine from 'alpinejs'; // [tl! remove:4]

window.Alpine = Alpine;

Alpine.start();
```

### Removing a CDN version of Alpine

Because Livewire version 2 and below didn't include Alpine by default, you may have included an Alpine CDN as a script tag in the head of your layout. In Livewire v3, you can remove this CDN altogether, and Livewire will automatically provide Alpine for you:

```html
    ...
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- [tl! remove] -->
</head>
```

Note: you can also remove any additional Alpine plugins, as Livewire includes all Alpine plugins except `@alpinejs/ui`.

## Missing `@alpinejs/ui`

Livewire's bundled version of Alpine includes all Alpine plugins EXCEPT `@alpinejs/ui`. If you are using headless components from [Alpine Components](https://alpinejs.dev/components), which relies on this plugin, you may encounter errors like the following:

```
Uncaught Alpine: no element provided to x-anchor
```

To fix this, you can simply include the `@alpinejs/ui` plugin as a CDN in your layout file like so:

```html
    ...
    <script defer src="https://unpkg.com/@alpinejs/ui@3.13.7-beta.0/dist/cdn.min.js"></script> <!-- [tl! add] -->
</head>
```

Note: be sure to include the latest version of this plugin, which you can find on [any component's documentation page](https://alpinejs.dev/component/headless-dialog/docs).
