
Livewire allows you to build dynamic, reactive interfaces using only PHP—no JavaScript required. Instead of writing frontend code in JavaScript frameworks, you write simple PHP classes and Blade templates, and Livewire handles all the complex JavaScript behind the scenes.

To demonstrate, we'll build a simple create-post form with real-time validation. You'll see how Livewire can validate inputs and update the page dynamically without writing a single line of JavaScript or manually handling AJAX requests.

## Prerequisites

Before we start, make sure you have the following installed:

- Laravel version 10 or later
- PHP version 8.1 or later

## Install Livewire

> [!warning] Livewire v4 is currently in beta
> Livewire v4 is still in active development and not yet stable. It's recommended to test thoroughly in a development environment before upgrading production applications. Breaking changes may occur between beta releases.

From the root directory of your Laravel app, run the following [Composer](https://getcomposer.org/) command:

```shell
composer require livewire/livewire:^4.0@beta
```

## Create a layout

Before creating our component, let's set up a layout file that Livewire components will render inside. By default, Livewire looks for a layout at: `resources/views/layouts/app.blade.php`

You can create this file by running the following command:

```shell
php artisan livewire:layout
```

This will generate `resources/views/layouts/app.blade.php` with the following contents:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>

        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
```

The `@livewireStyles` and `@livewireScripts` directives include the necessary JavaScript and CSS assets for Livewire to function. Your component will be rendered in place of the `{{ $slot }}` variable.

## Create a Livewire component

Livewire provides a convenient Artisan command to generate new components. Run the following command to make a new page component:

```shell
php artisan make:livewire pages::post.create
```

Since this component will be used as a full page, we use the `pages::` prefix to keep it organized in the pages directory.

This command will generate a new single-file component at `resources/views/pages/post/⚡create.blade.php`.

> [!info] What's with the ⚡ emoji?
> The lightning bolt makes Livewire components instantly recognizable in your editor. It's completely optional and can be disabled in your config if you prefer. See the [components documentation](/docs/components#creating-components) for more details.

## Write the component

Open `resources/views/pages/post/⚡create.blade.php` and replace its contents with the following:

```blade
<?php

use Livewire\Component;

new class extends Component
{
    public string $title = '';

    public string $content = '';

    public function save()
    {
        $this->validate([
            'title' => 'required|max:255',
            'content' => 'required',
        ]);

        dd($this->title, $this->content);
    }
};
?>

<form wire:submit="save">
    <label>
        Title
        <input type="text" wire:model="title">
        @error('title') <span style="color: red;">{{ $message }}</span> @enderror
    </label>

    <label>
        Content
        <textarea wire:model="content" rows="5"></textarea>
        @error('content') <span style="color: red;">{{ $message }}</span> @enderror
    </label>

    <button type="submit">Save Post</button>
</form>
```

> [!info] Don't worry about styling
> This form is intentionally unstyled so we can focus on Livewire's functionality. In a real application, you'd add CSS or use a framework like Tailwind.

Here's what's happening in the code above:

**Component properties:**
- `public string $title = '';` — Declares a public property for the post title
- `public string $content = '';` — Declares a public property for the post content

**Component methods:**
- `public function save()` — Called when the form is submitted. Validates the data and dumps the output for testing.

**Livewire directives:**
- `wire:submit="save"` — Calls the `save()` method when the form is submitted, preventing the default page reload
- `wire:model="title"` — Creates two-way data binding between the input and the `$title` property. As you type, the property updates automatically
- `wire:model="content"` — Same two-way binding for the textarea and `$content` property
- `@error('title')` and `@error('content')` — Display validation error messages when validation fails

> [!warning] Livewire components MUST have a single root element
> Components must have exactly one root HTML element. In this example, the `<form>` element is the single root. Multiple root elements or HTML comments outside the root element will cause an error. When rendering [full-page components](/docs/pages), named slots for the layout can be placed outside the root element.

> [!tip] In a real application
> The `save()` method uses `dd()` to dump the values for testing purposes. In a production application, you would typically save the data to a database and redirect:
> ```php
> public function save()
> {
>     $validated = $this->validate([
>         'title' => 'required|max:255',
>         'content' => 'required',
>     ]);
>
>     Post::create($validated); // Assumes you have a Post model and database table
>
>     return $this->redirect('/posts');
> }
> ```

## Register a route

Open the `routes/web.php` file in your Laravel application and add the following:

```php
Route::livewire('/post/create', 'pages::post.create');
```

Now when a user visits `/post/create`, Livewire will render the `pages::post.create` component inside your layout file.

## Test it out

With everything in place, let's test the component!

Start your Laravel development server if it's not already running:

```shell
php artisan serve
```

Visit `http://localhost:8000/post/create` in your browser (or `http://yourapp.test/post/create` if using Valet, Herd, or a similar tool).

You should see a simple form with two fields and a submit button.

**Try the following:**

1. **Test validation:** Click "Save Post" without filling in any fields. You'll see red error messages appear instantly below each field—no page reload required.

2. **Test data binding:** Start typing in the title field. Livewire is automatically syncing your input with the server-side `$title` property in real-time.

3. **Test submission:** Fill in both fields and click "Save Post". You should see a debug screen showing the values you entered.

This demonstrates Livewire's core power: reactive data binding, real-time validation, and form handling—all written in PHP without touching JavaScript.

## Troubleshooting

**Component not found error:**
- Make sure the component file exists at `resources/views/pages/post/⚡create.blade.php`
- Check that the component name in the route matches: `'pages::post.create'`

**Form doesn't submit or validation doesn't show:**
- Make sure `@livewireStyles` is in your `<head>` and `@livewireScripts` is before `</body>` in your layout
- Check your browser console for JavaScript errors

**404 error when visiting the route:**
- Verify the route was added to `routes/web.php`

## Next steps

Now that you've built your first Livewire component, here are some key concepts to explore:

- **[Components](/docs/components)** — Learn about single-file vs multi-file components, passing data, and more
- **[Properties](/docs/properties)** — Understand how component properties work and their lifecycle
- **[Actions](/docs/actions)** — Dive deeper into methods, parameters, and event handling
- **[Forms](/docs/forms)** — Explore Livewire's powerful form features including real-time validation
- **[Validation](/docs/validation)** — Master all of Livewire's validation capabilities

We've barely scratched the surface of what Livewire is capable of. Keep reading the documentation to see everything Livewire has to offer.
