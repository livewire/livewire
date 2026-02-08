The `#[Layout]` attribute specifies which Blade layout a full-page component should use, allowing you to customize layouts on a per-component basis.

## Basic usage

Apply the `#[Layout]` attribute to a full-page component to use a specific layout:

```php
<?php // resources/views/pages/posts/⚡index.blade.php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\Post;

new #[Layout('layouts::dashboard')] class extends Component { // [tl! highlight]
    #[Computed]
    public function posts()
    {
        return Post::all();
    }
};
?>

<div>
    <h1>Posts</h1>
    @foreach ($this->posts as $post)
        <div wire:key="{{ $post->id }}">{{ $post->title }}</div>
    @endforeach
</div>
```

This component will render using the `resources/views/layouts/dashboard.blade.php` layout instead of the default.

## Default layout

By default, Livewire uses the layout specified in your `config/livewire.php` file:

```php
'component_layout' => 'layouts::app',
```

The `#[Layout]` attribute overrides this default for specific components.

## Passing data to layouts

You can pass additional data to your layout using array syntax:

```php
new #[Layout('layouts::dashboard', ['title' => 'Posts Dashboard'])] class extends Component { // [tl! highlight]
    // ...
};
```

In your layout file, the `$title` variable will be available:

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'My App' }}</title>
</head>
<body>
    {{ $slot }}
</body>
</html>
```

## Alternative: Using layout() method

Instead of the attribute, you can use the `layout()` method in your `render()` method:

```php
<?php // resources/views/pages/posts/⚡index.blade.php

use Livewire\Component;

new class extends Component {
    public function render()
    {
        return view('livewire.posts.index')
            ->layout('layouts::dashboard', ['title' => 'Posts']); // [tl! highlight]
    }
};
```

The attribute approach is cleaner for single-file components that don't need a `render()` method.

## Using different layouts per page

A common pattern is to use different layouts for different sections of your app:

```php
// Admin pages
new #[Layout('layouts::admin')] class extends Component { }

// Marketing pages
new #[Layout('layouts::marketing')] class extends Component { }

// Dashboard pages
new #[Layout('layouts::dashboard')] class extends Component { }
```

## When to use

Use `#[Layout]` when:

* You have multiple layouts in your application (admin, marketing, dashboard, etc.)
* A specific page needs a different layout than the default
* You're building a full-page component (not a regular component)
* You want to keep layout configuration close to the component definition

> [!info] Only for full-page components
> The `#[Layout]` attribute only applies to full-page components. Regular components that are rendered within other views don't use layouts.

## Learn more

For more information about full-page components and layouts, see the [Pages documentation](/docs/4.x/pages#layouts).

## Reference

```php
#[Layout(
    string $name,
    array $params = [],
)]
```

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$name` | `string` | *required* | The name of the Blade layout to use |
| `$params` | `array` | `[]` | Additional data to pass to the layout |
