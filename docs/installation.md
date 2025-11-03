Livewire is a Laravel package, so you will need to have a Laravel application up and running before you can install and use Livewire. If you need help setting up a new Laravel application, please see the [official Laravel documentation](https://laravel.com/docs/installation).

## Prerequisites

Before installing Livewire, make sure you have:

- Laravel version 10 or later
- PHP version 8.1 or later

## Install Livewire

> [!warning] Livewire v4 is currently in beta
> Livewire v4 is still in active development and not yet stable. It's recommended to test thoroughly in a development environment before upgrading production applications. Breaking changes may occur between beta releases.

To install Livewire, open your terminal and navigate to your Laravel application directory, then run the following command:

```shell
composer require livewire/livewire:^4.0@beta
```

That's it! Livewire uses Laravel's package auto-discovery, so no additional setup is required.

**Ready to build your first component?** Head over to the [Quickstart guide](/docs/quickstart) to create your first Livewire component in minutes.

## Create a layout file

When using Livewire components as full pages, you'll need a layout file. You can generate one using the Livewire command:

```shell
php artisan livewire:layout
```

This creates a layout file at `resources/views/layouts/app.blade.php` with the following contents:

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

The `@livewireStyles` and `@livewireScripts` directives include the necessary JavaScript and CSS assets for Livewire to function. Livewire bundles Alpine.js with its JavaScript, so both are loaded together.

> [!info] Asset injection is automatic
> Even without these directives, Livewire will automatically inject its assets into pages that contain Livewire components. However, including the directives gives you explicit control over where the assets are placed, which can be helpful for performance optimization or compatibility with other packages.

## Publishing the configuration file

Livewire is "zero-config", meaning you can use it by following conventions without any additional configuration. However, if needed, you can publish and customize Livewire's configuration file:

```shell
php artisan livewire:publish --config
```

This will create a new `livewire.php` file in your Laravel application's `config` directory where you can customize various Livewire settings.

---

# Advanced configuration

The following sections cover advanced scenarios that most applications won't need. Only configure these if you have a specific requirement.

## Manually bundling Livewire and Alpine

**When you need this:** If you want to use Alpine.js plugins or have fine-grained control over when Alpine and Livewire initialize.

By default, Livewire automatically loads Alpine.js bundled with its JavaScript. However, if you need to register Alpine plugins or customize the initialization order, you can manually bundle Livewire and Alpine using your JavaScript build tool.

First, add the `@livewireScriptConfig` directive to your layout file:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>

        @livewireStyles
        @vite(['resources/js/app.js'])
    </head>
    <body>
        {{ $slot }}

        @livewireScriptConfig
    </body>
</html>
```

The `@livewireScriptConfig` directive injects configuration and runtime globals that Livewire needs, but without the actual Livewire and Alpine JavaScript (since you're bundling those yourself). Replace `@livewireScripts` with `@livewireScriptConfig` when manually bundling.

Next, import and start Livewire and Alpine in your `resources/js/app.js` file:

```js
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Clipboard from '@ryangjchandler/alpine-clipboard'

Alpine.plugin(Clipboard)

Livewire.start()
```

> [!tip] Rebuild assets after Livewire updates
> When manually bundling, remember to rebuild your JavaScript assets (`npm run build`) whenever you update Livewire via Composer.

## Customizing Livewire's update endpoint

**When you need this:** If your application uses route prefixes for localization (like `/en/`, `/fr/`) or multi-tenancy (like `/tenant-1/`, `/tenant-2/`), you may need to customize Livewire's update endpoint to match your routing structure.

By default, Livewire sends component updates to `/livewire/update`. To customize this, register your own route in a service provider (typically `App\Providers\AppServiceProvider`):

```php
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/custom/livewire/update', $handle);
        });
    }
}
```

You can also add middleware to the update route:

```php
Livewire::setUpdateRoute(function ($handle) {
    return Route::post('/custom/livewire/update', $handle)
        ->middleware(['web', 'auth']);
});
```

## Customizing the JavaScript asset URL

**When you need this:** If your application uses route prefixes for localization or multi-tenancy, you may need to customize where Livewire serves its JavaScript from to match your routing structure.

By default, Livewire serves its JavaScript from `/livewire/livewire.js`. To customize this, register your own route in a service provider:

```php
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/custom/livewire/livewire.js', $handle);
        });
    }
}
```

## Publishing Livewire's assets to public directory

**When you need this:** If you want to serve Livewire's JavaScript through your web server directly (e.g., for CDN distribution or specific caching strategies) instead of Laravel routing.

You can publish Livewire's JavaScript assets to your `public` directory:

```bash
php artisan livewire:publish --assets
```

To ensure assets stay up-to-date when you update Livewire, add this to your `composer.json`:

```json
{
    "scripts": {
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=livewire:assets --ansi --force"
        ]
    }
}
```

> [!warning] Most applications don't need this
> Publishing assets is rarely necessary. Only do this if you have a specific architectural requirement that prevents Laravel from serving the assets dynamically.

## Disabling automatic asset injection

**When you need this:** If you want complete control over when and how Livewire's assets are loaded, you can disable automatic injection.

Update the `inject_assets` configuration option in your `config/livewire.php` file:

```php
'inject_assets' => false,
```

When disabled, you must manually include `@livewireStyles` and `@livewireScripts` in your layouts, or Livewire won't function.

Alternatively, you can force asset injection on specific pages:

```php
\Livewire\Livewire::forceAssetInjection();
```

Call this in a route or controller where you want to ensure assets are injected.

---

# Troubleshooting

## Livewire JavaScript not loading (404 error)

**Symptom:** Visiting `/livewire/livewire.js` returns a 404 error, or Livewire features don't work.

**Common causes:**

**Nginx configuration blocking the route:**
If you're using Nginx with a custom configuration, it may be blocking Laravel's route for `/livewire/livewire.js`. You can either:
- Configure Nginx to allow this route (see [this guide](https://benjamincrozat.com/livewire-js-404-not-found))
- [Manually bundle Livewire](#manually-bundling-livewire-and-alpine) to avoid serving through Laravel

**Route caching:**
If you've run `php artisan route:cache`, Laravel may not recognize Livewire's routes. Clear the cache:

```shell
php artisan route:clear
```

**Missing @livewireScripts:**
If you've disabled automatic asset injection, ensure `@livewireScripts` is in your layout file before `</body>`.

## Alpine.js not available on pages without Livewire components

**Symptom:** You want to use Alpine.js on a page that doesn't have any Livewire components.

**Solution:** Since Alpine is bundled with Livewire, you need to include `@livewireScripts` even on pages without Livewire components:

```blade
<!DOCTYPE html>
<html>
    <head>
        @livewireStyles
    </head>
    <body>
        <!-- No Livewire components, but we want Alpine -->
        <div x-data="{ open: false }">
            <button @click="open = !open">Toggle</button>
        </div>

        @livewireScripts
    </body>
</html>
```

Alternatively, [manually bundle Livewire and Alpine](#manually-bundling-livewire-and-alpine) and import Alpine in your JavaScript.

## Components not updating or errors in browser console

**Check the following:**
- Ensure `@livewireStyles` is in the `<head>` of your layout
- Ensure `@livewireScripts` is before `</body>` in your layout
- Check your browser's developer console for JavaScript errors
- Verify you're running a supported PHP version (8.1+) and Laravel version (10+)
- Clear your application cache: `php artisan cache:clear`

If issues persist, check the [troubleshooting documentation](/docs/troubleshooting) for more detailed debugging steps.
