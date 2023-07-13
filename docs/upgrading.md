
> [!warning] Livewire 3 is still in beta
> We will try our best to not make any more breaking changes. however, while v3 is still in beta, there is a chance. Therefore, we recommend testing your application thoroughly before using v3 in production.

## Automated upgrade tool

To save you time upgrading, we've included an Artisan command to automate as many parts of this as possible.

After [installing Livewire version 3](/docs/upgrading#update-livewire-to-version-3), run the following command and you will be receive prompts to upgrade each breaking change automatically:

```shell
php artisan livewire:upgrade
```

Although the above command can upgrade much of your application, the only way to ensure a complete upgrade is to follow the step-by-step guide below:

## Upgrade PHP

Livewire now requires that your application is running on PHP version 8.1 or greater.

## Update Livewire to version 3

Run the following composer command to upgrade your application's Livewire dependancy from version 2 to 3:

```shell
composer require livewire/livewire:3.0.0-beta.1
```

The above command will lock you to the current beta version. If you want to receive more frequent updates, you can switch to the more flexible version constraint:

```shell
composer require livewire/livewire:^3.0@beta
```

<!-- @todo after launch:
### Update composer dependancies

Any other packages in your application that depends on Livewire will need to be upgraded to a version that supports v3.

Below is a list of dependancies and their corresponding version with support for v3:

* `spatie/laravel-ignition` - ? -->

> [!warning] Some packages aren't v3 compatable yet
> Most of the major third-party Livewire packages either currently support v3 or are working on supporting it soon. However, there will innevitably be packages that take longer.

## Clear the view cache

Run the following Artisan command from your application's root directory to clear any cached/compiled Blade views and force Livewire to re-compile them to v3 compatible ones:

```shell
php artisan view:clear
```

## Merge new configuration

Livewire v3 has changed multiple configuration items. If your application has a published configuration file: `config/livewire.php`, you will need to update it to account for the following changes.

<!-- @todo after launch:
If you'd rather view the changes in a more visual way, you can reference [the GitHub file comparison](???). -->

### New configuration

The following configuration keys have been introduced in version 3:

```php
'legacy_model_binding' => false,

'inject_assets' => true,

'inject_morph_markers' => true,

'navigate' => false,
```

You can reference [Livewire's new config file on GitHub](https://github.com/livewire/livewire/blob/master/config/livewire.php) for descriptions and copy-pastable code.

### Changed configuration

The following config items have been changed with new default values:

#### New class namespace

Livewire's default `class_namespace` has changed from `App\Http\Livewire` to `App\Livewire`. You are welcome to keep the old configuration, however, if you update the configuration, you will have to move your Livewire component's to the new `app/Livewire` folder to match.

```php
'class_namespace' => 'App\\Http\\Livewire', // [tl! remove]
'class_namespace' => 'App\\Livewire', // [tl! add]
```

#### New layout view path

When rendering full-page components in version 2, Livewire would use `resources/views/layouts/app.blade.php` as the default layout Blade component.

Because of an growing community preference for anonymous Blade components, Livewire 3 has changed the default location to: `resources/views/components/layouts/app.blade.php`.

```php
'layout' => 'layouts.app', // [tl! remove]
'layout' => 'components.layouts.app', // [tl! add]
```

### Removed configuration

Livewire no longer recognizes the following configuration items.

#### `app_url`

If your application is served under a non-root URI, in v2 you could use the `app_url` to configure the URL Livewire uses to make AJAX requests to.

In this case, we've found a string configuration to be too rigid. Therefore, Livewire v3 has opted for runtime configuration instead. You can reference [the documentation on configuring Livewire's update endpoint](/docs/installation#configuring-livewires-update-endpoint) for more information.

#### `asset_url`

In v2, if your application was served under a non-root URI, you would use the `asset_url` to configure the base URL that Livewire uses to serve its JavaScript assets.

Livewire v3 has instead opted for a runtime configuration strategy. You can reference [the documentation on configuring Livewire's script asset endpoint](/docs/installation#customizing-the-asset-url) for more information.

#### `middleware_group`

Because Livewire now exposes a more flexible way to customize its update endpoint, the `middleware_group` config item has been removed.

You can reference the [documentation on customizing Livewire's update endpoint](/docs/installation#configuring-livewires-update-endpoint) for more information on applying custom middleware to Livewire requests.

#### `manifest_path`

Livewire v3 no longer uses a manifest file for component autoloading. Therefore, the `manifest_path` configuration is no longer necessary.

#### `back_button_cache`

Because Livewire v3 now offers an [SPA experience for your app using `wire:navigate`](/docs/navigate), the `back_button_cache` configuration is no longer necessary.

## Livewire app namespace

In version 2, Livewire components were generated and recognized automatically under the `App\Http\Livewire` namespace.

V3 has changed this default to: `App\Livewire`.

You can either move all your components to the new location or add the following configuration to `config/livewire.php`:

```php
'class_namespace' => 'App\\Http\\Livewire',
```

## Page component layout view

When rendering Livewire components as full pages using a syntax like the following:

```php
Route::get('/posts', ShowPosts::class);
```

The Blade layout file used by Livewire to render the component inside of has changed from `resources/views/layouts/app.blade.php` to `resources/views/components/layouts/app.blade.php`:

```shell
resources/views/layouts/app.blade.php #[tl! remove]
resources/views/components/layouts/app.blade.php #[tl! add]
```

You can either move your layout file to the new location or use the following configuration inside your `config/livewire.php` file:

```php
'layout' => 'layouts.app',
```

For more information, check out the documentation on [creating and using a page-component layout](/docs/components#layout-files).


## Eloquent model binding

Livewire version 2 supported `wire:model` binding directly to Eloquent model properties. For example, the following was a common pattern:

```php
public Post $post;

protected $rules = [
    'post.title' => 'required',
    'post.description' => 'required',
];
```

```html
<input wire:model="post.title">
<input wire:model="post.description">
```

In Livewire 3, binding directly to Eloquent models has been disabled in favor of using individual properties, or extracting [Form Objects](/docs/forms#extracting-a-form-object).

However, because this behavior is so heavily relied upon in Livewire applications, version 3 maintains support for this behavior via a configuration item in `config/livewire.php`:

```php
'legacy_model_binding' => true,
```

By setting `legacy_model_binding` to `true`, Livewire will handle Eloquent model properties exactly as it did in version 2.

## AlpineJS

Livewire version 3 ships with [AlpineJS](https://alpinejs.dev) by default.

If you use Alpine in your Livewire application, you will need to remove it so that Livewire's built-in version doesn't conflict.

### Including Alpine via a script tag

If you include Alpine into your application via a script tag like the following, you can remove it entirely and Livewire will load its internal version instead.

```html
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- [tl! remove] -->
```

### Including plugins via a script tag

Livewire version 3 now ships with the following Alpine plugins out-of-the-box:

* [Intersect](https://alpinejs.dev/docs/plugins/intersect)
* [Collapse](https://alpinejs.dev/docs/plugins/collapse)
* [Persist](https://alpinejs.dev/docs/plugins/persist)
* [Morph](https://alpinejs.dev/docs/plugins/morph)
* [Focus](https://alpinejs.dev/docs/plugins/focus)
* [Mask](https://alpinejs.dev/docs/plugins/mask)

If you have already included any of these in your application via `<script>` tags like below, you can remove them along with Alpine's core:

```html
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script> <!-- [tl! remove:1] -->
<!-- ... -->
```

### Accessing the Alpine global via a script tag

If you are currently accessing the `Alpine` global object from a script tag like so:

```html
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data(...)
    })
</script>
```

You may continue to do so as Livewire internally includes and registers Alpine's global object like before.

### Including via JS bundle

If you have included Alpine and any relevant plugins via NPM into your applications JavaScript bundle like so:

```js
// Warning: this is a snippet of the v2 way of including Alpine.

import Alpine from 'alpinejs'
import intersect from '@alpinejs/intersect'

Alpine.plugin(intersect)

Alpine.start()
```

You can remove them entirely because Livewire includes Alpine and more plugins by default now.

#### Accessing Alpine via JS bundle

If you are registering custom Alpine plugins or components inside your application's JavaScript bundle like so:

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

Notice you no longer need to call `Alpine.start()`. Livewire will internally start Alpine automatically.

For more information, you can reference the full [documentation on manually bundling Livewire's JavaScript](/docs/installation#manually-bundling-livewire-and-alpine).

## `wire:model`

In version 3, `wire:model` is "deferred" by default (instead of by `wire:model.defer`.) To achieve the same behavior as `wire:model` in v2, you must use `wire:model.live`.

Below is a list of the necessary substituations you will need to make in your templates to keep your application's behavior consistent:

```html
<input wire:model="..."> <!-- [tl! remove] -->
<input wire:model.live="..."> <!-- [tl! add] -->

<input wire:model.defer="..."> <!-- [tl! remove] -->
<input wire:model="..."> <!-- [tl! add] -->

<input wire:model.lazy="..."> <!-- [tl! remove] -->
<input wire:model.blur="..."> <!-- [tl! add] -->
```

## `@entangle`

Similar to the changes to `wire:model`, version 3 defers all data binding by default. To match this behavior, `@entangle` has been updated as well.

To keep your application running as expected, make the following `@entangle` substitutions:

```blade
@entangle(...) <!-- [tl! remove] -->
@entangle(...).live <!-- [tl! add] -->

@entangle(...).defer <!-- [tl! remove] -->
@entangle(...) <!-- [tl! add] -->
```

## Events

In version 2, Livewire had two different PHP methods for triggering events:

* `emit()`
* `dispatchBrowserEvent()`

Version 3 of Livewire has unified these two methods into a single method:

* `dispatch()`

Here is a basic example of dispatching and listening for an event in Livewire 3:

```php
// Dispatching...
class CreatePost extends Component
{
    public Post $post;

    public function save()
    {
        $this->dispatch('post-created', postId: $this->post->id);
    }
}

// Listening...
class Dashboard extends Component
{
    #[On('post-created')]
    public function postAdded($postId)
    {
        //
    }
}
```

The three main changes from v2 you'll notice are:

1. `emit()` has been renamed to `dispatch()`
1. `dispatchBrowserEvent()` has been renamed to `dispatch()`
2. All event parameters must be named

For more information, check out the new [events documentation page](/docs/events).

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
<button wire:click="$emit('post-created')">...</button> <!-- [tl! remove] -->
<button wire:click="$dispatch('post-created')">...</button> <!-- [tl! add] -->

<button wire:click="$emit('post-created', 1)">...</button> <!-- [tl! remove] -->
<button wire:click="$dispatch('post-created', { postId: 1 })">...</button> <!-- [tl! add] -->

<button x-on:click="$wire.emit('post-created', 1)">...</button> <!-- [tl! remove] -->
<button x-on:click="$dispatch('post-created', { postId: 1 })">...</button> <!-- [tl! add] -->
```

### `emitUp()`

The concept of `emitUp` has been removed entirely. Events are now dispatched using browser events and therefore will "bubble up" by default.

You can remove any instances of `$this->emitUp(...)` or `$emitUp(...)` from your components.

### Testing events

Livewire has also changed event assertions to match:

```php
Livewire::test(Component::class)->assertEmitted('post-created'); // [tl! remove]
Livewire::test(Component::class)->assertDispatched('post-created'); // [tl! add]

Livewire::test(Component::class)->assertEmittedTo(Foo::class, 'post-created'); // [tl! remove]
Livewire::test(Component::class)->assertDispatchedTo(Foo:class, 'post-created'); // [tl! add]

Livewire::test(Component::class)->assertNotEmitted('post-created'); // [tl! remove]
Livewire::test(Component::class)->assertNotDispatched('post-created'); // [tl! add]

Livewire::test(Component::class)->assertEmittedUp() // [tl! remove]
```

### URL query string

In previous Livewire versions, if you bound a property to the URL's query string, the property value would always be present in the query string unless you used the `"except"` option.

In v3, all properties bound to the query string will only show up if their value has been changed after the page load. This default removes the need for the `"except"` option.

```php
public $search = '';

protected $queryString = [
    'search' => ['except' => ''], // [tl! remove]
    'search', // [tl! add]
];
```

If you'd like to revert back to the v2 behavior of always showing a property in the query string no matter what its value, you can use the `"keep"` option:

```php
public $search = '';

protected $queryString = [
    'search' => ['keep' => true], // [tl! highlight]
];
```

## Pagination

The pagination system has been updated in version 3 to better support multiple paginators within the same component.

### Update published pagination views

If you've published Livewire's pagination views, you can reference the new ones in the [pagination directory on GitHub](https://github.com/livewire/livewire/tree/master/src/Features/SupportPagination/views).

### Accessing `$this->page` directly

Because Livewire now supports multiple paginators per component, it has removed the `$page` property from the component class and replaced it with a `$paginators` property that stores an array of paginators.

```php
$this->page = 2; // [tl! remove]
$this->paginators['page'] = 2; // [tl! add]
```

However, it is recommended that you use the provided getters and setters for modifying and accessing the current page:

```php
// Getter...
$this->getPage();

// Setter...
$this->setPage(2);
```

### `wire:click.prefetch`

Livewire's prefetching feature: `wire:click.prefetching`, has been removed entirely. If you depended on this feature, fortunately your application will still work, it will just be slightly less performant in the instances you were benefiting from `.prefetch`.

```html
<button wire:click.prefetch=""> <!-- [tl! remove] -->
<button wire:click="..."> <!-- [tl! add] -->
```

## Component class changes

The following changes have been made to Livewire's base `Livewire\Component` class that your app's components may have relied on.

### The component `$id` property

If you accessed the component's ID directly via `$this->id`, you will have to instead use `$this->getId()`.

```php
$this->id; // [tl! remove]

$this->getId(); // [tl! add]
```

### Duplicate method and property names

PHP allows you to use the same name for both a class property and method. In Livewire version 3, this will cause problems when calling methods from the frontend via `wire:click`.

It is strongly suggested that you use distinct names for all public methods and properties in a component.

```php
public $search = ''; // [tl! remove]

public function search() {
    // ...
}
```

```php
public $query = ''; // [tl! add]

public function search() {
    // ...
}
```

## JavaScript API changes

### `livewire:load`

In previous versions of Livewire, you would listen for the `livewire:load` event to execute JavaScript code immediately before Livewire initialized the page.

In version 3, that event name has been changed to `livewire:init` to match Alpine's `alpine:init`:

```js
document.addEventListener('livewire:load', () => {...}) // [tl! remove]
document.addEventListener('livewire:init', () => {...}) // [tl! add]
```

### Page expired hook

In version 2, Livewire exposed a dedicated JavaScript method for customizing the page expiration behavior: `Livewire.onPageExpired()`. This method has been removed in favor of using the more powerful `request` hooks directly.

```js
Livewire.onPageExpired(() => {...}) // [tl! remove]

Livewire.hook('request', ({ fail }) => { // [tl! add:8]
    fail(({ status, preventDefault }) => {
        if (status === 419) {
            preventDefault()

            confirm('Your custom page expiration behavior...')
        }
    })
})
```

### New lifecycle hooks

Many of Livewire's internal JavaScript lifecycle hooks have been changed in version 3.

Here is a comparison of old hooks and their new syntaxes for you to find/replace in your application:

```js
Livewire.hook('component.initialized', (component) => {}) // [tl! remove]
Livewire.hook('component.init', ({ component }) => {}) // [tl! add]

Livewire.hook('element.initialized', (el, component) => {}) // [tl! remove]
Livewire.hook('element.init', ({ el, component }) => {}) // [tl! add]

Livewire.hook('element.updating', (fromEl, toEl, component) => {}) // [tl! remove]
Livewire.hook('morph.updating', ({ el, toEl, component }) => {}) // [tl! add]

Livewire.hook('element.updated', (el, component) => {}) // [tl! remove]
Livewire.hook('morph.updated', ({ el, component }) => {}) // [tl! add]

Livewire.hook('element.removed', (el, component) => {}) // [tl! remove]
Livewire.hook('morph.removed', ({ el, component }) => {}) // [tl! add]

Livewire.hook('message.sent', (message, component) => {}) // [tl! remove]
Livewire.hook('message.failed', (message, component) => {}) // [tl! remove]
Livewire.hook('message.received', (message, component) => {}) // [tl! remove]
Livewire.hook('message.processed', (message, component) => {}) // [tl! remove]

Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => { // [tl! add:14]
    // Equivelant of 'message.sent'

    succeed(({ snapshot, effect }) => {
        // Equivelant of 'message.received'

        queueMicrotask(() => {
            // Equivelant of 'message.processed'
        })
    })

    fail(() => {
        // Equivelant of 'message.failed'
    })
})
```

Read through the new [JavaScript hook documentation](/docs/javascript) for a more thorough understanding of the new system.

## Localization

If your application uses a locale prefix in the URI such as `https://example.com/en/...`, version 2 automatically preserved this URL prefix when making component updates via `https://example.com/en/livewire/update`.

Version 3 has stopped supporting this behavior automatically. Instead, you can override Livewire's update endpoint with any URI prefixes you need using `setUpdateRoute()`:

```php
Route::group(['prefix' => LaravelLocalization::setLocale()], function ()
{
    // Your other localized routes...

    Livewire::setUpdateRoute(function ($handle) {
        return Route::post('/livewire/update', $handle);
    });
});
```

For more information, read the [documentation on configuring Livewire's update endpoint](/docs/installation#configuring-livewires-update-endpoint).

