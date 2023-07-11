
> [!warning] Livewire 3 is still in beta
> Although we will try our best to not make breaking changes, it is possible while Livewire 3 is in beta. Therefore, we recommend testing your application thoroughly before using in production.

## Breaking changes

* [`wire:model`]()
* [Events]()
* [Eloquent models]()
* [AlpineJS]()
* [JavaScript API]()

## Step-by-step guide

### Upgrade PHP

Livewire now requires that your application is running on PHP version 8.1 or greater.

### Update composer version

Run the following composer command to upgrade your application's Livewire dependency from version 2 to 3.

```shell
composer require livewire/livewire:3.0.0-beta.1
```

The above command will lock you to the current beta version. If you want to receive more frequent updates, you can switch to the more flexible version constraint:

```shell
composer require livewire/livewire:^3.0@beta
```

### Update other dependencies

Any other packages in your application that depend on Livewire will need to be upgraded to a version that supports V3.

Below is a list of dependencies and their corresponding version with support for V3:

* `spatie/laravel-ignition` - ?

> [!warning] Some packages aren't V3 compatible yet
> Most of the major third-party Livewire packages either currently support V3 or are working on adding support soon. However, there may be some packages that will take much longer to upgrade.

### Clear artisan cache

Run the following Artisan command from your application's root directory to clear any cached/compiled Blade views and force Livewire to re-compile them to V3 compatible ones:

```shell
php artisan view:clear
```

### Merge new configuration

Livewire V3 has both added and removed certain configuration items. If your application has a published configuration file `config/livewire.php`, you will need to update it to account for the following changes.

If you'd rather view the changes in a more visual way, you can reference [the GitHub file comparison](???).

#### New configuration

The following configuration items have been introduced in version 3:

```php
'legacy_model_binding' => false,

'inject_assets' => true,

'inject_morph_markers' => true,

'navigate' => false,
```

You can reference the [GitHub comparison of Livewire's config file from version 2 to 3](??) for descriptions and copy-pastable code.

#### New configuration defaults

##### New class namespace

Livewire's default `class_namespace` has changed from `App\\Http\\Livewire`. You are welcome to keep the old configuration, however, you can migrate to the new directory by changing this configuration and following the [namespace upgrade guide](??):

```php
`class_namespace' => 'App\\Http\\Livewire', // [tl! remove]
`class_namespace' => 'App\\Livewire', // [tl! add]
```

##### New layout view path

In version 2, when rendering full-page components, Livewire would use a Blade component file located here: `resources/views/layouts/app.blade.php`. Because of a user-base preference for anonymous Blade components, Livewire 3 has changed the default to: `resources/views/components/layouts/app.blade.php`.

```php
'layout' => 'layouts.app', // [tl! remove]
'layout' => 'components.layouts.app', // [tl! add]
```

#### Removed configuration

Livewire no longer recognizes the following configuration items.

##### `app_url`

In V2, if your application is served under a non-root URI, you can use the `app_url` to configure the URL Livewire uses to make AJAX requests to.

Because a string configuration is too rigid, Livewire V3 has opted for a runtime configuration strategy. You can reference [the documentation on configuring Livewire's update endpoint](???) for more information.

##### `app_url`

In V2, if your application is served under a non-root URI, you can use the `asset_url` to configure the base URL that Livewire uses to serve its JavaScript assets.

Because a string configuration is too rigid, Livewire V3 has opted for a runtime configuration strategy. You can reference [the documentation on configuring Livewire's script asset endpoint](???) for more information.

##### `middleware_group`

Because Livewire now exposes a more flexible way to customize its update endpoint, the `middleware_group` config item has been removed.

You can reference the [documentation on customizing Livewire's update endpoint](???) for more information on applying custom middleware to Livewire requests.

##### `manifest_path`

Livewire V3 no longer uses a manifest file for component autoloading. Therefore, the `manifest_path` configuration is no longer necessary.

##### `back_button_cache`

Because Livewire V3 now offers an [SPA experience for your app using `wire:navigate`](???), the `back_button_cache` configuration is no longer necessary.

### Livewire app namespace

In version 2, Livewire components were generated and recognized automatically under the `App\Http\Livewire` namespace.

V3 has changed this default to simply: `App\Livewire`.

You can either move and edit all your components to the new location or add the following configuration to your `config/livewire.php` file to use the old default:

```php
'class_namespace' => 'App\\Http\\Livewire',
```
app/Http/Livewire -> app/Livewire

### Page component layout view

When rendering Livewire components as full pages using a syntax like the following:

```php
Route::get('/posts', ShowPosts::class);
```

The Blade layout file used by Livewire to render the component inside of has changed from `resources/views/layouts/app.blade.php` to `resources/views/layouts/app.blade.php`:

```shell
resources/views/layouts/app.blade.php #[tl! remove]
resources/views/components/layouts/app.blade.php #[tl! add]
```

You can either move your layout file to the new location or use the following configuration inside your `config/livewire.php` file:

```php
'layout' => 'components.layouts.app',
```

For more information, check out the documentation on [creating and using a page-component layout](???).

### Alpine

Livewire version 3 ships with [AlpineJS](https://alpinejs.dev) by default.

If you use Alpine in your Livewire application, you will need to remove it so that Livewire's built-in version doesn't conflict with it.

#### Including Alpine via script tag

If you include Alpine in your application via a script tag like the following:

```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- [tl! remove] -->
```

#### Including plugins via script tag

Livewire version 3 now ships with the following Alpine plugins:

* [Intersect](https://alpinejs.dev/docs/plugins/intersect)
* [Collapse](https://alpinejs.dev/docs/plugins/collapse)
* [Persist](https://alpinejs.dev/docs/plugins/persist)
* [Morph](https://alpinejs.dev/docs/plugins/morph)
* [Focus](https://alpinejs.dev/docs/plugins/focus)
* [Mask](https://alpinejs.dev/docs/plugins/mask)

If you have already included any of these in your application via `<script>` tags like so:

```html
<!-- Alpine Plugins -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script> <!-- [tl! remove] -->

<!-- Alpine Core -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- [tl! remove] -->
```

You can remove them along with core Alpine.

#### Accessing the Alpine global via script tag

If you are currently accessing the AlpineJS global from a script tag like so:

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data(...)
    })
</script>
```

You may continue to do so as Livewire's JavaScript internally includes and boots AlpineJS like normal, exposing it globally as: window.Alpine

#### Including via JS bundle

If you have included Alpine and any relevant plugins via NPM into your applications JavaScript bundle like so:

```js
// Warning: this is a snippet of the V2 way of including Alpine.

import Alpine from 'alpinejs'
import intersect from '@alpinejs/intersect'

Alpine.plugin(intersect)

Alpine.start()
```

You can remove them entirely because Livewire includes Alpine and more plugins by default now.

#### Accessing Alpine via JS bundle

If before, you were registering custom Alpine plugins or components from inside your application's JavaScript bundle like so:

```js
// Warning: this is a snippet of the V2 way of including Alpine.

import Alpine from 'alpinejs'
import customPlugin from './plugins/custom-plugin'

Alpine.plugin(customPlugin)

Alpine.start()
```

You can still accomplish this by importing the Livewire core ESM module into your bundle and accessing `Alpine` from there.

To import Livewire into your bundle, you must first disable Livewire's normal JavaScript injection and provide the necessary configuration to Livewire by replacing `@livewireScripts` with `@livewireScriptConfig` in your page's layout:

```blade
    <!-- ... -->

    @livewireScripts <!-- [tl! remove] -->
    @livewireScriptConfig <!-- [tl! add] -->
</body>
```

Now, you can import `Alpine` and `Livewire` into your app's bundle like so:

```js
import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import customPlugin from './plugins/custom-plugin'

Alpine.plugin(customPlugin)

Livewire.start()
```

Notice you no longer need to call `Alpine.start()`. Livewire will internally start Alpine.

Reference the full [documentation on manually bundling Livewire's JavaScript](???) for more information.

### `wire:model`

`wire:model` behaves differently from V2 to V3. By default, `wire:model` is "deferred" by default (instead of by `wire:model.defer`). To achieve the same behavior as V2, you must use `wire:model.live`.

To keep your application running as expected, make the following `wire:model` substitutions:

```html
<input wire:model="..."> <!-- [tl! remove] -->
<input wire:model.live="..."> <!-- [tl! add] -->

<input wire:model.defer="..."> <!-- [tl! remove] -->
<input wire:model="..."> <!-- [tl! add] -->

<input wire:model.lazy="..."> <!-- [tl! remove] -->
<input wire:model.blur="..."> <!-- [tl! add] -->
```

### `@entangle`

Similar to the changes to `wire:model`, version 3 defers all data binding by default. To match this behavior, `@entangle` has been updated as well.

To keep your application running as expected, make the following `@entangle` substitutions:

```blade
@entangle(...) <!-- [tl! remove] -->
@entangle(...).live <!-- [tl! add] -->

@entangle(...).defer <!-- [tl! remove] -->
@entangle(...) <!-- [tl! add] -->
```

### Events

In version 2, Livewire had two different PHP methods for triggering events:

* `emit()`
* `dispatchBrowserEvent()`

For version 3, Livewire has unified these two methods into a single method:

* `dispatch()`

Here is a basic example of dispatching and listening for an event in Livewire 3:

```php
// Dispatcher...
class CreatePost extends Component
{
    public Post $post;

    public function save()
    {
        $this->dispatch('post-created', postId: $this->post->id);
    }
}

// Listener...
class Dashboard extends Component
{
    #[On('post-created')]
    public function postAdded($postId)
    {
        //
    }
}
```

The two main changes from V2 you'll notice are:

1. `emit()` renamed to `dispatch()`
2. All event parameters must be named

For more information, check out the new [events documentation page](???).

Here are the "find and replace" differences in your application for this new version:

```php
$this->emit('post-created'); // [tl! remove]
$this->dispatch('post-created'); // [tl! add]

$this->emitTo('post-created'); // [tl! remove]
$this->dispatchTo('post-created'); // [tl! add]

$this->emitSelf('post-created'); // [tl! remove]
$this->dispatchSelf('post-created'); // [tl! add]

$this->emit('post-created', $post->id); // [tl! remove]
$this->dispatch('post-created', postId: $post->id); // [tl! add]

$this->dispatchBrowserEvent('post-created'); // [tl! remove]
$this->dispatch('post-created'); // [tl! add]

$this->dispatchBrowserEvent('post-created', ['postId' => $post->id]); // [tl! remove]
$this->dispatch('post-created', postId: $post->id); // [tl! add]
```

```html
<button wire:click="$emit('post-created')">...</button>
<button wire:click="$dispatch('post-created')">...</button>

<button wire:click="$emit('post-created', 1)">...</button>
<button wire:click="$dispatch('post-created', { postId: 1 })">...</button>

<button x-on:click="$wire.emit('post-created', 1)">...</button>
<button x-on:click="$dispatch('post-created', { postId: 1 })">...</button>
```

#### `emitUp()`

The concept of `emitUp` has been removed entirely. Events are not dispatched as actual browser events and therefore "bubble up" by default.

You can remove any instances of `$this->emitUp(...)` or `$emitUp(...)` from your components.

#### Testing events

Livewire has also changed event assertions to match. `assertEmitted()` has been replaced with `assertDispatched()`

### QueryString

In previous Livewire versions, if you bound a property to the URL's query string, the property value would always be present in the query string unless you used the `"except"` option.

In V3, all properties bound to the query string will only show up if their value has been changed after the page load. This default removes the need for the `"except"` option.

```php
public $search = '';

protected $queryString = [
    'search' => ['except' => ''], // [tl! remove]
    'search', // [tl! add]
];
```

If you'd like to refer to the V2 behavior of always showing a property in the query string no matter what it's value is, you can use the `"keep"` option:

```php
public $search = '';

protected $queryString = [
    'search' => ['keep' => true], // [tl! highlight]
];
```

### Pagination

- Republish pagination views if you have previously published them.
- Can no longer access `$page` directly -> `$paginators['page']` or `getPage()`

### `wire:click.prefetch`

Removed

### Component class

- The component ID is no longer a public property ($id), please use $this->id() or $this->getId() to get the component id.
- Can no longer use same names for properties and methods

### JavaScript

* prepend `$` to everything (`$watch`, `$upload`, etc...)
* Changed lifecycle hooks
* Removed page expired hook
* 'livewire:load' => 'livewire:init'

### Eloquent models

- model binding has been disabled
* You must set the config "livewire.legacy_model_binding" to true

### Localization

Livewire 2 included support for a locale prefix.

In Livewire 3 this automatic prefix has been removed. Instead, you will need to add a custom Livewire update route to your `routes/web.php` file inside your route group that applies localization.

For example, here is how you would use a custom Livewire update route along with the `mcamara/laravel-localization` package:

```php
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::prefix(LaravelLocalization::setLocale())
    ->group(function () {
        ... // Your other localized routes.

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('livewire/update', $handle);
        });
    });
```

See [[installation#Configuring Livewire's update endpoint]] for more details on creating a custom Livewire update endpoint.

---

## `wire:submit.prevent` no longer needed
- Change `wire:submit.prevent` to `wire:submit`
