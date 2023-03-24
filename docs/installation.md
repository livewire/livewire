---
Title: Installation
Order: 2
---

```toc
min_depth: 1
```

<a name="installation"></a>
# Installation

Livewire is a Laravel package, so you will need to have a Laravel application up and running before you can install and use Livewire. If you need help setting up a new Laravel application, please see the [official Laravel documentation](https://laravel.com/docs).

To install Livewire, open your terminal and navigate to your Laravel application directory, then run the following command:

```shell
composer require livewire/livewire
```

That's it. Really. If you want more customization options, keeps reading. Otherwise, you can jump right into using Livewire:

> Creating your first Livewire component

<a name="publishing-config"></a>
# Publishing the configuration file

Livewire is "zero-config", meaning you can use it by following conventions without any need for special configuration. However, you may still want to customize it further. In those cases, you can publish and customize Livewire's config file by running the following `artisan` command:

```shell
php artisan livewire:publish --config
```

This will create a new `livewire.php` file in your Laravel app under: `config/livewire.php`

<a name="customizing auto-inject"></a>
# Manually including Livewire's front-end assets

By default, Livewire injects the JavaScript and CSS assets it needs onto each page that includes a Livewire component.

If you want more control over this behavior, you can manually include them on a page using the following Blade directives:

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

If for some reason you want to force Livewire to disable its auto-injecting assets behavior, you can do so by updating the `auto_inject_assets` configuration in `config/livewire.php`: (See: [Publishing config](#publishing-config) if haven't published this config file yet)

```json
'auto_inject_assets': false,
```

<a name="custom-update-endpoint"></a>
# Configuring Livewire's update endpoint

Every update in a Livewire component sends a network request to the server using the following endpoint: `your-app.com/livewire/update`

This can be a problem for some applications that use localization or multi-tenancy.

In those case, you can register your own endpoint however you like, and as long as you use `Livewire::handleUpdate()` as the route handler, Livewire will know to use this endpoint for all component updates.

```php
Livewire::setUpdateRoute(function ($handle) {
	return Route::post('/custom/livewire/update', $handle);
});
```

<a name="custom-asset-url"></a>
# Customizing the asset URL

By default, Livewire will serve its JavaScript assets from the following URL: `your-app.com/livewire/livewire.js` and reference them in a script tag like so:

```html
<script src="/livewire/livewire.js" ...
```

If your application has global route prefixes because of something like localization or multi-tenancy, you can register your own endpoint to be used by Livewire internally for fetching its JavaScript.

```php


```

Now, Livewire will use a `<script src` like the following:

```html
<script src="/acme/livewire.js" ...
```

<a name="hosting-assets"></a>
# Publishing and hosting front-end assets

If the above options for controlling the JavaScript endpoint aren't enough, you can also publish Livewire's JavaScript assets to your own project and host them yourself using the following command:

```shell
php artisan livewire:publish --assets
```

This will create the following new file in your project: `/public/assets/livewire.js`

You can now force Livewire to use your own custom assets by passing the following "url" option to: `@livewireScripts`:

```html
@livewireScripts(['url' => '/assets/livewire.js'])
```

Whatever value you pass to `livewireScripts` as the "url" will be directly referenced from a `<script>` tag when the page renders like so:

```html
<script src="/assets/livewire.js" ...
```

It's important to keep this file up-to-date when you update Livewire in your application.

You can automatically do this by adding the following line to your `composer.json` file in your project's root directory:

```json
{
	"scripts": {
		"post-autoload-dump": [
			Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
			"@php artisan package:discover --ansi",
			"@php artisan vendor:publish --force --tag=livewire:assets --ansi"
		]
	}
}
```
