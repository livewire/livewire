Laravel's pagination features allow you to query a subset of data and allow your users to navigate between *pages* of those results.

Because Laravel's paginator was designed to be used in static applications, in a non-Livewire app, each page navigation triggers a full browser visit to a new URL containing the desired page (`?page=2`).

However, when you use pagination inside a Livewire component, users can navigate between pages while remaining on the same page. Livewire will handle everything behind the scenes including updating the URL query string with the current page.

## Basic usage

Here's the most basic example of using pagination inside a `ShowPosts` component to only show 10 posts at a time.

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class ShowPosts extends Component
{
    public function render()
    {
        return view('show-posts', [
            'posts' => Post::paginate(10),
        ]);
    }
}
```

```html
<div>
    @foreach ($posts as $post)
        <!-- ... -->
    @endforeach

    {{ $posts->links() }}
</div>
```

As you can see, in addition to limiting the number of posts shone, `$posts->links()` will inject page navigation links.

For more information, visit [the Laravel pagination documentation](https://laravel.com/docs/10.x/pagination).

## Resetting the page

When sorting or filtering results, it is common to want to reset the page number back to `1`.

Livewire exposes a method called `$this->resetPage()` that allows you to reset the page number  from anywhere in your component.

The following component demonstrates using this method to reset the page after a search field is updated and submitted:

```php
<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Post;

class SearchPosts extends Component
{
    public $query = '';

    public function search()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('show-posts', [
            'posts' => Post::where('title', 'like', '%'.$this->query.'%')->paginate(10),
        ]);
    }
}
```

```html
<div>
    <form wire:submit="search">
        <input type="text" wire:model="query">

        <button type="submit">Search posts</button>
    </form>

    @foreach ($posts as $post)
        <!-- ... -->
    @endforeach

    {{ $posts->links() }}
</div>
```

Now, if a user was on page `5` of the results, then filtered the results further by pressing "Search posts", the page would be reset back to `1`.

### Available page navigation methods

In addition to `$this->resetPage()`, Livewire provides other useful methods for navigating between pages programmatically from your component:

| Method        | Description                               |
|-----------------|-------------------------------------------|
| `$this->setPage($page)`    | Set the paginator to a specific page number |
| `$this->resetPage()`    | Reset the page back to 0 |
| `$this->nextPage()`    | Go to the next page |
| `$this->previousPage()`    | Go to the previous page |

## Multiple paginators

Because both Laravel and Livewire use URL query string parameters to store and track the current page number, if you a single page contains multiple paginators, it's important to designate one of them with a different name.

To demonstrate the problem more clearly, consider the following `ShowClients` component:

```php
use Livewire\Component;
use App\Models\Client;

class ShowClients extends Component
{
    public function render()
    {
        return view('show-clients', [
            'clients' => Client::paginate(10),
        ]);
    }
}
```

As you can see, the above component contains a paginated set of *clients*. If a user were to navigate to page `2` of this result set, the URL might look like the following:

```
http://application.test/?page=2`
```

Now, suppose the page also contained a `ShowInvoices` component using pagination. In order to independantly track each paginator's current page, you need to specify a name for the second paginator like so:

```php
use Livewire\Component;
use App\Models\Invoices;

class ShowInvoices extends Component
{
    public function render()
    {
        return view('show-invoices', [
            'invoices' => Invoice::paginate(10, pageName: 'invoice-page'),
        ]);
    }
}
```

Now, because of the `pageName` parameter to the `paginate` method, when a user visits page `2` of the *invoices* as well, the URL will show the following:

```
`https://application.test/customers?page=2&invoice-page=2`
```

To use Livewire's page navigation methods on named paginator, you must pass in the page name as the second parameter like so:

```php
$this->setPage(2, pageName: 'invoice-page');
$this->resetPage(pageName: 'invoice-page');
$this->nextPage(pageName: 'invoice-page');
$this->previousPage(pageName: 'invoice-page');
```

## Hooking into page updates

Livewire allows you to execute code before and after a page is updated by defining either of the following methods inside your component:

```php
class ShowPosts extends Component
{
    public function updatingPage($page)
    {
        // Runs BEFORE the page is updated for this component...
    }
    
    public function updatedPage($page)
    {
        // Runs AFTER the page is updated for this component...
    }

    pubic function render()
    {
        return view('show-posts', [
            'posts' => Post::paginate(10),
        ]);
    }
}
```

### Named paginator hooks

The previous hooks only apply to the default paginator. If you used a named paginator, like in the case of multiple paginators on a page, you must define the methods using the paginator's name.

Here's what a hook for a paginator named `invoices-page` would look like:

```php
public function updatingInvoicesPage($page)
{
    //
}
```

### General paginator hooks

If you prefer to not reference the paginator name in the hook method name, you can use the more generic alternatives:

```php
public function updatingPaginator($page, $pageName)
{
    // Runs BEFORE the page is updated for this component...
}

public function updatedPaginator($page, $pageName)
{
    // Runs AFTER the page is updated for this component...
}
```

Now there is a second parameter passed called `$pageName` that you have access to if you still want to access the name of the paginator.

## Using simple theme

For added speed and simplicity, you can use Laravel's `simplePaginate()` method instead of `paginate()`.

When paginating results using this method, only *next* and *previous* navigation buttons will be shown to the user:

```php
pubic function render()
{
    return view('show-posts', [
        'posts' => Post::simplePaginate(10),
    ]);
}
```

Read Laravels "simplePaginator" documentation for more information.

For more information, check out [Laravel's "simplePaginator" documentation.](https://laravel.com/docs/10.x/pagination#simple-pagination)

## Using cursor pagination

Livewire also supports using Laravel's cursor pagination--a faster pagination method useful in large datasets:

```php
pubic function render()
{
    return view('show-posts', [
        'posts' => Post::cursorPaginate(10),
    ]);
}
```

By using `cursorPaginate()`, instead of `paginate()` or `simplePaginate()`, the query string in your app's URL will store an encoded *cursor* instead of a standard page number. For example:

```
https://application.test/posts?cursor=eyJpZCI6MTUsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0
```

For more information, check out [Laravel's cursor pagination documentation.](https://laravel.com/docs/10.x/pagination#cursor-pagination)

## Using Bootstrap instead of Tailwind

If you are using [Bootstrap](https://getbootstrap.com/) instead of [Tailwind](https://tailwindcss.com/) as your app's CSS framework, you can configure Livewire to use pagination views constructed using Bootstrap classes instead of Tailwind classes.

You can configure this by opening `config/livewire.php` and setting the `pagination_theme` value to "bootstrap":

```json
"pagination_theme": "bootstrap",
```

> [!note] You must publish Livewire's config file if you haven't already
> Before customizing the pagination theme, you must first publish Livewire's configuration file to your application's `/config` directory by running the following command:
> ```shell
php artisan livewire:publish --config
> ```

## Modifying the default pagination views

If you wish to modify Livewire's pagination views to fit your application's style, you can do so by *publishing* them using the following command:

```shell
php artisan livewire:publish --pagination
```

After running this command the following four files will be inserted into `resources/views/vendor/livewire`:

| View file name        | Description                               |
|-----------------|-------------------------------------------|
| `tailwind.blade.php`    | The standard Tailwind pagination theme |
| `tailwind-simple.blade.php`    | The *simple* Tailwind pagination theme |
| `bootstrap.blade.php`    | The standard Bootstrap pagination theme |
| `bootstrap-simple.blade.php`    | The *simple* Bootstrap pagination theme |

Now, you have complete control over these files. When rendering pagination views using the paginated result's `->links()` method inside your template, Livewire will use these files instead of its own.

## Using custom pagination views

If you wish to bypass Livewire's pagintation views entirely, you can render your own in one of two ways:

### Via link

The first is by passing your custom pagination Blade view name to the `->links()` method directly:

```html
{{ $posts->links('custom-pagination-links') }}
```

Now Livewire will look for a file called `resources/views/custom-pagination-links.blade.php` when rendering the pagination links.

### Via component method

The second, is by declaring a `paginationView` method inside your component and returning the name of the view you'd like to use:

```php
public function paginationView()
{
    return 'custom-pagination-links-view';
}
```

### Sample pagination view

Below is an unstyled  sample of a simple Livewire pagination view for your reference.

As you can see, you can use Livewire's page navigation helpers like `$this->nextPage()` directly inside your template using `wire:click="nextPage"`:

```html
<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation">
            <span>
                @if ($paginator->onFirstPage())
                    <span>Previous</span>
                @else
                    <button wire:click="previousPage" wire:loading.attr="disabled" rel="prev">Previous</button>
                @endif
            </span>

            <span>
                @if ($paginator->onLastPage())
                    <span>Next</span>
                @else
                    <button wire:click="nextPage" wire:loading.attr="disabled" rel="next">Next</button>
                @endif
            </span>
        </nav>
    @endif
</div>
```









