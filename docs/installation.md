Livewire is a Laravel package, so you will need to have a Laravel application up and running before you can install and use Livewire. If you need help setting up a new Laravel application, please see the [official Laravel documentation](https://laravel.com/docs/installation).

To install Livewire, open your terminal and navigate to your Laravel application directory, then run the following command:

```shell
composer require livewire/livewire
```

That's it — really. If you want more customization options, keep reading. Otherwise, you can jump right into using Livewire.

## Publishing the configuration file

Livewire is "zero-config", meaning you can use it by following conventions without any additional configuration. However, if needed, you can publish and customize Livewire's configuration file by running the following Artisan command:

```shell
php artisan livewire:publish --config
```

This will create a new `livewire.php` file in your Laravel application's `config` directory.

## Manually including Livewire's frontend assets

By default, Livewire injects the JavaScript and CSS assets it needs into each page that includes a Livewire component.

If you want more control over this behavior, you can manually include the assets on a page using the following Blade directives:

```html
<html>
<head>
	...
	@livewireStyles
</head>
<body>
	...
	@livewireScripts
</body>
</html>
```

By including these assets manually on a page, Livewire knows to not inject the assets automatically.

Though rarely required, you may disable Livewire's auto-injecting asset behavior by updating the `inject_assets` [configuration option](#publishing-config) in your application's `config/livewire.php` file:

```json
'inject_assets': false,
```

## Configuring Livewire's update endpoint

Every update in a Livewire component sends a network request to the server at the following endpoint: `https://example.com/livewire/update`

This can be a problem for some applications that use localization or multi-tenancy.

In those cases, you can register your own endpoint however you like, and as long as you do it inside `Livewire::setUpdateRoute()`,  Livewire will know to use this endpoint for all component updates:

```php
Livewire::setUpdateRoute(function ($handle) {
	return Route::post('/custom/livewire/update', $handle);
});
```

Now, instead of using `/livewire/update`, Livewire will send component updates to `/custom/livewire/update`.

## Customizing the asset URL

By default, Livewire will serve its JavaScript assets from the following URL: `https://example.com/livewire/livewire.js`. Additionally, Livewire will reference this asset from a script tag like so:

```html
<script src="/livewire/livewire.js" ...
```

If your application has global route prefixes due to localization or multi-tenancy, you can register your own endpoint that Livewire should use internally when fetching its JavaScript.

To use a custom JavaScript asset endpoint, you can register your own route inside `Livewire::setJavaScriptRoute()`:

```php
Livewire::setJavaScriptRoute(function ($handle) {
    return Route::get('/custom/livewire/livewire.js', $handle);
});
```

Now, Livewire will load its JavaScript like so:

```html
<script src="/custom/livewire/livewire.js" ...
```
