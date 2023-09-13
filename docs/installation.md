Livewire is a Laravel package, so you will need to have a Laravel application up and running before you can install and use Livewire. If you need help setting up a new Laravel application, please see the [official Laravel documentation](https://laravel.com/docs/installation).

To install Livewire, open your terminal and navigate to your Laravel application directory, then run the following command:

```shell
composer require livewire/livewire
```

That's it — really. If you want more customization options, keep reading. Otherwise, you can jump right into using Livewire.

## Publishing the configuration file

Livewire is "zero-config", meaning you can use it by following conventions, without any additional configuration. However, if needed, you can publish and customize Livewire's configuration file by running the following Artisan command:

```shell
php artisan livewire:publish --config
```

This will create a new `livewire.php` file in your Laravel application's `config` directory.

## Manually including Livewire's frontend assets

By default, Livewire injects the JavaScript and CSS assets it needs into each page that includes a Livewire component.

If you want more control over this behavior, you can manually include the assets on a page using the following Blade directives:

```blade
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

By including these assets manually on a page, Livewire knows not to inject the assets automatically.

> [!warning] AlpineJS is bundled with Livewire
> Because Alpine is bundled with Livewire's JavaScript assets, you must include @verbatim`@livewireScripts`@endverbatim on every page you wish to use Alpine. Even if you're not using Livewire on that page.

Though rarely required, you may disable Livewire's auto-injecting asset behavior by updating the `inject_assets` [configuration option](#publishing-config) in your application's `config/livewire.php` file:

```php
'inject_assets' => false,
```

If you'd rather force Livewire to inject it's assets on a single page or multiple pages, you can call the following global method from the current route or from a service provider.

```php
\Livewire\Livewire::forceAssetInjection();
```

## Configuring Livewire's messaging endpoints

Every update in a Livewire component sends a network request to the server at the following endpoint: `https://example.com/livewire/update`
The file upload and preview functionalities rely on two additionnal endpoints which are `https://example.com/livewire/upload-file` and `https://example.com/livewire/preview-file` by default.

This can be a problem for some applications that use localization or multi-tenancy or which have to apply constraint on their URL scheme for some reason.

In those cases, you can register your own endpoints however you like, and as long as you do it using the provided methodes, Livewire will know to use these endpoints for all component updates, file uploads and previews:

For example, for the update endpoint:
```php
use Livewire\Livewire;

Livewire::setUpdateRoute(function ($handle) {
	return Route::post('/custom/livewire/update', $handle);
});
```

Now, instead of using `/livewire/update`, Livewire will send component updates to `/custom/livewire/update`.

Because Livewire allows you to register your own update route, you can declare any additional middleware you want Livewire to use directly inside `setUpdateRoute()`:

```php
use Livewire\Livewire;

Livewire::setUpdateRoute(function ($handle) {
	return Route::post('/custom/livewire/update', $handle)
        ->middleware([...]); // [tl! highlight]
});
```

> [!tip] for the `update` endpoint, the Route you declare must be a `post` Route.

And you can do the same for the other endpoints with:
```php
use Livewire\Livewire;

Livewire::setUploadFileRoute(function ($handle) {
	return Route::post('/custom/livewire/upload-file', $handle);
});
```

> [!tip] for the `upload-file` endpoint, the Route you declare must be a `post` Route.

```php
use Livewire\Livewire;

Livewire::setPreviewFileRoute(function ($handle) {
	return Route::get('/custom/livewire/preview-file', $handle);
});
```

> [!tip] for the `preview-file` endpoint, the Route you declare must be a `get` Route.

## Customizing the asset URL

By default, Livewire will serve its JavaScript assets from the following URL: `https://example.com/livewire/livewire.js`. Additionally, Livewire will reference this asset from a script tag like so:

```blade
<script src="/livewire/livewire.js" ...
```

If your application has global route prefixes due to localization or multi-tenancy, you can register your own endpoint that Livewire should use internally when fetching its JavaScript.

To use a custom JavaScript asset endpoint, you can register your own route inside `Livewire::setScriptRoute()`:

```php
Livewire::setScriptRoute(function ($handle) {
    return Route::get('/custom/livewire/livewire.js', $handle);
});
```

Now, Livewire will load its JavaScript like so:

```blade
<script src="/custom/livewire/livewire.js" ...
```

> [!tip] Be sure to use a `get` Route when you customize the asset URL.

## Manually bundling Livewire and Alpine

By default, Alpine and Livewire are loaded using the `<script src="livewire.js">` tag, which means you have no control over the order in which these libraries are loaded. Consequently, importing and registering Alpine plugins, as shown in the example below, will no longer function:

```js
// Warning: This snippet demonstrates what NOT to do...

import Alpine from 'alpinejs'
import Clipboard from '@ryangjchandler/alpine-clipboard'

Alpine.plugin(Clipboard)
Alpine.start()
```

To address this issue, we need to inform Livewire that we want to use the ESM (ECMAScript module) version ourselves and prevent the injection of the `livewire.js` script tag. To achieve this, we must add the `@livewireScriptConfig` directive to our layout file (`resources/views/components/layouts/app.blade.php`):

```blade
<html>
<head>
    <!-- ... -->
    @livewireStyles
    @vite(['resources/js/app.js'])
</head>
<body>
    {{ $slot }}

    @livewireScriptConfig <!-- [tl! highlight] -->
</body>
</html>
```

When Livewire detects the `@livewireScriptConfig` directive, it will refrain from injecting the Livewire and Alpine scripts. If you are using the `@livewireScripts` directive to manually load Livewire, be sure to remove it. Make sure to add the `@livewireStyles` directive if it is not already present.

The final step is importing Alpine and Livewire in our `app.js` file, allowing us to register any custom resources, and ultimately starting Livewire and Alpine:

```js
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Clipboard from '@ryangjchandler/alpine-clipboard'

Alpine.plugin(Clipboard)

Livewire.start()
```
