# Rendering a component

There are two ways to render livewire components:

1. Sprinkle them into an existing blade view.
2. Render an entire page as a Livewire component.

## 1. The @livewire Blade directive

Let's say we have a Livewire component in `App\Http\Livewire\Counter.php`, the simplest way to render it in a browser, is to include it in a normal Laravel blade view via the `@livewire` directive.

Let's assume we have a route like `Route::get('/home', 'HomeController@show')`, and `HomeController` returns a view named `home.blade.php`. We can include our `Counter` component like so:

```html
@extends('layouts.app')

@section('content')
    {-- Some HTML --}
    @livewire(App\Http\Livewire\Counter::class)
@endsection
```

For some, it might feel weird passing an expression like `App\Http\Livewire\Counter::class` into a blade diretive. If this is the case for you, you can register component aliases in a ServiceProvider and just use those in your views like so:

**AppServiceProvider.php**
```
public function boot()
{
    Livewire::component('counter', Counter::class);
}
```

Now you can pass the component alias into the directive:
```
@livewire('counter')
```

Additionally, you can pass data into a component by passing additional parameters into the `@livewire` directive. For example, let's say we have an `ShowContact` Livewire component that needs to know which contact to show. Here's how you would pass in the contact id.

```
@livewire('show-contact', $contactId)
```

Any additional parameters passed into Livewire components, will be made available through the `created` lifecycle hook.

```php
class ShowContact extends LivewireComponent
{
    public $contact;

    public function created($contactId)
    {
        $this->contact = Contact::find($id)d;
    }
}
```

Using the `@livewire` blade directive hopefully feels natural to you as there is no special syntax to learn. However, if this becomes redundant, you can bypass Laravel Controllers alltogether. To do so, Livewire offers a convenient routing helper.

## 2. Livewire routing helper

```php
// Before
Route::get('/home', 'HomeController@show');

// After
Route::livewire('/home', App\Http\Livewire\Counter::class);
```

Note: for this feature to work, Livewire assumes you have a layout stored in `resources/views/layouts/app.blade.php` that yeilds a "content" section (`@yield('content')`)

If you use a different layout file or section name, you can configure these in the standard way you configure laravel routes:

```php
Route::livewire('/home', App\Http\Livewire\Counter::class)
    ->layout('layouts.base')
    ->section('body');
```

You can also configure these settings for an entire route group using the group option array syntax:

```php
Route::group(['layout' => 'layouts.base', 'section' => 'body'], function () {
    ...
});
```

Or the fluent alternative:
```php
Route::layout('layouts.base')->section('body')->group(function () {
    ...
});
```

### Route Parameters

Often you need to access route parameters inside your controller methods. Because we are no longer using controllers, Livewire attempts to mimick this behavior through it's `created` lifecycle hook. For example:

**web.php**
```php
Route::livewire('/contact/{id}', App\Http\Livewire\ShowContact::classs);
```

**App\Http\Livewire\ShowContact.php**
```php
class ShowContact extends LivewireComponent
{
    public $contact;

    public function created($id)
    {
        $this->contact = Contact::find($id);
    }
}
```

As you can see, the `created` method in a Livewire component is acting like a controller method would as far as it's parameters go. If you visit `/contact/123`, the `$id` variable passed into the `created` method will contain the value `123`.

Like you would expect, Livewire components implement all functionality you're used to in your controllers including route model binding. For example:

**web.php**
```php
Route::livewire('/contact/{contact}', App\Http\Livewire\ShowContact::classs);
```

**App\Http\Livewire\ShowContact.php**
```php
class ShowContact extends LivewireComponent
{
    public $contact;

    public function created(Contact $contact)
    {
        $this->contact = $contact;
    }
}
```

Now, after visiting `/contact/123`, the value passed into `created` will be an instance of the `Contact` model with id `123`.
