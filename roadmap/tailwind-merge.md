
## Tailwind merge

Tailwind CSS recommends that instead of extracting classes like `.btn` to group styles and create abstractions for the styling of your application, that you instead create components at the template level instead.

Therefore you wend up creating lots of little Blade components like `button.blade.php` who's nearly sole purpose is applying Tailwind classes to a button element.

## The scenario

You have a button component like the following:

```php
// Path: resources/views/components/button.blade.php

<button type="button" class="rounded shadow bg-white px-4 px-3 border text-black">
    {{ $slot }}
</button>
```

And you use it like so:

```php
<x-button>Create post</x-button>
```

However, consider that you want one of the buttons to have a different background color so you do something like this:

```php
<x-button class="bg-purple-500">Create post</x-button>
```

However nothing changes, this is because you're not "forwarding" and "merging" attributes from the component tag into the rendering of the button element.

Let's fix that by using Laravel's `{{ $attributes }}` bag and their merge or class shortcut helpers:

```php
// Path: resources/views/components/button.blade.php

<button type="button" {{ $attributes->class('rounded shadow bg-white px-4 px-3 border text-black') }}>
    {{ $slot }}
</button>
```

Now Laravel will automatically merge the provided attributes and classes with the ones defined in the component template.

However, it STILL doesn't work because when the HTML is rendered the original `bg-white` AND the new `bg-purple-500` both exist in the class list of the element like so:

```php
<button type="button" class="rounded shadow bg-white px-4 px-3 border text-black bg-purple-500">
    Create post
</button>
```

Instead of your `bg-purple-500` being used as a kind of override, the decision of which one wins is left up to chance: or more specifically the order in which those Tailwind utilities are defined on the page (later one will be more specific and win).

A solution to this problem is a tool that ingests a list of Tailwind classes, then allows you to "merge" in new utilities, and it will intelligently decide if it should omit any previously provided utilities.

So for example, this system could work like this:

```php
$classes = Tailwind::merge('rounded shadow bg-white', 'bg-purple-500');

// Now this would be the output...
$classes === 'rounded shadow bg-purple-500'
```

## API proposal

I'm proposing that we provide this functionality via a macro to the `$attributes` (Illuminate\View\ComponentAttributeBag::class) component attribute bag called `tailwind()` that provides this functionality.

For example:

```php
// Path: resources/views/components/button.blade.php

<button type="button" {{ $attributes->tailwind('rounded shadow bg-white px-4 px-3 border text-black') }}>
    {{ $slot }}
</button>
```

The new `tailwind` method might be defined like so:

```php
ComponentAttributeBag::macro('tailwind', function ($weakClasses) {
    $strongClasses = $this->class;

    $this->class = app(TailwindMerge::class)->merge($weakClasses, $strongClasses);

    return $this;
})
```

## Merge strategy

Now that the API is defined we need to define how exactly the system works.

Fortunately, there is a github repository and npm package called tailwind-merge.

GitHub repo for tailwind-merge: https://github.com/dcastil/tailwind-merge

This is the defacto Tailwind Merge library and all of it's tests can be used to test against our own implementation.

Our behavior should match It's behavior exactly.

## Implementation strategy

Because this is basically a PHP port of a JS tool, I propose the following workflow.

Create a new fold called src/V4/Tailwind

In it create an entrypoint class caleed Merge.php

That class has one public method:

`public function merge($weakClasses, $strongClasses): string`

Inside that method I propose we literally forward a call to the npm tailwind-merge project and return the result from that subprocess - just to get our test suite totally built and up-to-snuff.

Then I propose we port over the entire test suite from the tailwind-merge project into PHPUnit files.

Then we work until all tests are totally green.

once all those tests are totally green, we can remove the call to the original package start implementing our own and getting it to pass with a custom PHP implementation.

This will be the arduous part but at least we'll have a test suite we can fully rely on.

Then after all this and we have green tests we can set up a simple benchmark and start optimizing it for speed while keeping tests green.

This is the way.
