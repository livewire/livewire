Livewire is a Laravel package, so you will need to have a Laravel application up and running before you can install and use Livewire. If you need help setting up a new Laravel application, please see the [official Laravel documentation](https://laravel.com/docs/installation).

To install Livewire, open your terminal and navigate to your Laravel application directory, then run the following command:

```shell
composer require livewire/livewire
```

That's it—really. If you want more customization options, keep reading. Otherwise, you can jump right into using Livewire.

## Publishing the configuration file

Livewire is "zero-config", meaning you can use it by following conventions without any need for special configuration. However, you may still want to customize it further. In those cases, you can publish and customize Livewire's config file by running the following Artisan command:

```shell
php artisan livewire:publish --config
```

This will create a new `livewire.php` file in your Laravel app under: `config/livewire.php`

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

If for some reason you want to force Livewire to disable its auto-injecting assets behavior, you can do so by updating the `inject_assets` configuration in `config/livewire.php`: (See: [Publishing config](#publishing-config) if haven't published this config file yet)

```json
'inject_assets': false,
```

## Configuring Livewire's update endpoint

Every update in a Livewire component sends a network request to the server using the following endpoint: `https://example.com/livewire/update`

This can be a problem for some applications that use localization or multi-tenancy.

In those cases, you can register your own endpoint however you like, and as long as you do it inside `Livewire::setUpdateRoute()`,  Livewire will know to use this endpoint for all component updates.

```php
Livewire::setUpdateRoute(function ($handle) {
	return Route::post('/custom/livewire/update', $handle);
});
```

Now instead of `/livewire/update`, Livewire will send component updates to `/custom/livewire/update`.

## Customizing the asset URL

By default, Livewire will serve its JavaScript assets from the following URL: `https://example.com/livewire/livewire.js` and reference them from a script tag like so:

```html
<script src="/livewire/livewire.js" ...
```

If your application has global route prefixes because of something like localization or multi-tenancy, you can register your own endpoint to be used by Livewire internally for fetching its JavaScript.

To use a custom JavaScript asset endpoint, you can register your own route inside `Livewire::setJavaScriptRoute()` like so:

```php
Livewire::setJavaScriptRoute(function ($handle) {
    return Route::get('/custom/livewire/livewire.js', $handle);
});
```

Now, Livewire will use a `<script src` like the following:

```html
<script src="/custom/livewire/livewire.js" ...
```
