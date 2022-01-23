<?php

namespace Livewire;

use Livewire\Types\BuiltInType;
use DateTimeInterface;
use Illuminate\Contracts\Queue\QueueableCollection;
use Illuminate\Contracts\Queue\QueueableEntity;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Livewire\Exceptions\PropertyNotFoundException;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;
use Livewire\Testing\TestableLivewire;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\Types\DefaultType;
use ReflectionClass;
use ReflectionProperty;

class LivewireManager
{
    protected $listeners = [];
    protected $componentAliases = [];
    protected $queryParamsForTesting = [];

    protected $shouldDisableBackButtonCache = false;

    protected $persistentMiddleware = [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        \Laravel\Jetstream\Http\Middleware\AuthenticateSession::class,
        \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\RedirectIfAuthenticated::class,
        \Illuminate\Auth\Middleware\Authenticate::class,
        \Illuminate\Auth\Middleware\Authorize::class,
        \App\Http\Middleware\Authenticate::class,
    ];

    protected $supportedPropertyTypes = [
        Wireable::class => Types\WireableType::class,
        QueueableEntity::class => Types\EloquentModelType::class,
        QueueableCollection::class => Types\EloquentModelCollectionType::class,
        Collection::class => Types\CollectionType::class,
        DateTimeInterface::class => Types\DateTimeType::class,
        Stringable::class => Types\StringableType::class,
    ];

    public static $isLivewireRequestTestingOverride = false;

    public static $currentCompilingViewPath;
    public static $currentCompilingChildCounter;

    public function component($alias, $viewClass = null)
    {
        if (is_null($viewClass)) {
            $viewClass = $alias;
            $alias = $viewClass::getName();
        }

        $this->componentAliases[$alias] = $viewClass;
    }

    public function registerPropertyType($class, $handler)
    {
        if (is_array($class)) {
            foreach ($class as $key => $value) {
                $this->registerPropertyType($key, $value);
            }

            return;
        }

        $this->supportedPropertyTypes[$class] = $handler;
    }

    public function hydrate($instance, $request, $name, $value)
    {
        if ($request) {
            if (! $handler = $this->resolveTypeHandlerFromRequest($request, $name)) {
                $handler = $this->hydrator($instance, $name, $value);
            }
        } else {
            $handler = $this->hydrator($instance, $name, $value);
        }

        return app($handler)->hydrate($instance, $request, $name, $value);
    }

    public function dehydrate($instance, $response, $name, $value)
    {
        $handler = $this->hydrator($instance, $name, $value);

        return app($handler)->dehydrate($instance, $response, $name, $value);
    }

    public function hydrator($instance, $name, $value)
    {
        $handler = $this->getPropertyTypeHandler($instance, $name, $value);

        if ($handler === true) {
            return DefaultType::class;
        }

        if ($handler === false) {
            throw new PublicPropertyTypeNotAllowedException(
                $instance::getName(), $name, $value
            );
        }

        return $handler;
    }

    public function attemptingToAssignNullToTypedPropertyThatDoesntAllowNullButIsUninitialized($instance, $name, $value)
    {
        if ($value) return false;

        if (! $type = ReflectionPropertyType::get($instance, $name)) return false;

        if ($type->allowsNull()) return false;

        return $this->isPropertyUninitialized($instance, $name);
    }

    public function isPropertyUninitialized($instance, $name)
    {
        return ! (new ReflectionProperty($instance, $name))->isInitialized($instance);
    }

    protected function getPropertyTypeHandler($instance, $name, $value)
    {
        if (! $type = ReflectionPropertyType::get($instance, $name)) {
            return $this->resolveTypeHandlerFromValue($value);
        }

        if ($type->isBuiltin()) {
            return BuiltInType::class;
        }

        if (! $handler = $this->getCustomPropertyTypeHandler($propertyType = $type->getName())) {
            if (is_null($value) && $type->allowsNull()) {
                return true;
            }

            if ($value instanceof $propertyType) {
                return true;
            }

            return false;
        }

        return $handler;
    }

    protected function getCustomPropertyTypeHandler($type)
    {
        foreach ($this->supportedPropertyTypes as $class => $handler) {
            if (is_a($type, $class, true)) {
                return $handler;
            }
        }

        return null;
    }

    protected function resolveTypeHandlerFromValue($value)
    {
        if (is_object($value)) {
            $type = get_class($value);

            if (! $handler = $this->getCustomPropertyTypeHandler($type)) {
                if ($value instanceof $type) {
                    return true;
                }

                return false;
            }

            return $handler;
        }

        if (
            is_bool($value)
                || is_null($value)
                || is_array($value)
                || is_numeric($value)
                || is_string($value)
        ) {
            return BuiltInType::class;
        }

        return false;
    }

    protected function resolveTypeHandlerFromRequest($request, $name)
    {
        return data_get($request->memo, "dataMeta.hydrators.$name", false);
    }

    public function getAlias($class, $default = null)
    {
        $alias = array_search($class, $this->componentAliases);

        return $alias === false ? $default : $alias;
    }

    public function getComponentAliases()
    {
        return $this->componentAliases;
    }

    public function getClass($alias)
    {
        $finder = app(LivewireComponentsFinder::class);

        $class = false;

        $class = $class ?: (
            // Let's first check if the user registered the component using:
            // Livewire::component('name', [Livewire component class]);
            // If not, we'll look in the auto-discovery manifest.
            $this->componentAliases[$alias] ?? $finder->find($alias)
        );

        $class = $class ?: (
            // If none of the above worked, our last-ditch effort will be
            // to re-generate the auto-discovery manifest and look again.
            $finder->build()->find($alias)
        );

        throw_unless($class, new ComponentNotFoundException(
            "Unable to find component: [{$alias}]"
        ));

        return $class;
    }

    public function getInstance($component, $id)
    {
        $componentClass = $this->getClass($component);

        throw_unless(class_exists($componentClass), new ComponentNotFoundException(
            "Component [{$component}] class not found: [{$componentClass}]"
        ));

        return new $componentClass($id);
    }

    public function mount($name, $params = [])
    {
        // This is if a user doesn't pass params, BUT passes key() as the second argument.
        if (is_string($params)) $params = [];

        $id = str()->random(20);

        if (class_exists($name)) {
            $name = $name::getName();
        }

        return LifecycleManager::fromInitialRequest($name, $id)
            ->boot()
            ->initialHydrate()
            ->mount($params)
            ->renderToView()
            ->initialDehydrate()
            ->toInitialResponse();
    }

    public function dummyMount($id, $tagName)
    {
        return "<{$tagName} wire:id=\"{$id}\"></{$tagName}>";
    }

    public function test($name, $params = [])
    {
        return new TestableLivewire($name, $params, $this->queryParamsForTesting);
    }

    public function visit($browser, $class, $queryString = '')
    {
        $url = '/livewire-dusk/'.urlencode($class).$queryString;

        return $browser->visit($url)->waitForLivewireToLoad();
    }

    public function actingAs(Authenticatable $user, $driver = null)
    {
        // This is a helper to be used during testing.

        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        auth()->guard($driver)->setUser($user);

        auth()->shouldUse($driver);

        return $this;
    }

    public function addPersistentMiddleware($middleware)
    {
        $this->persistentMiddleware = array_merge($this->persistentMiddleware, (array) $middleware);
    }

    public function setPersistentMiddleware($middleware)
    {
        $this->persistentMiddleware = (array) $middleware;
    }

    public function getPersistentMiddleware()
    {
        return $this->persistentMiddleware;
    }

    public function styles($options = [])
    {
        $debug = config('app.debug');

        $styles = $this->cssAssets($options);

        // HTML Label.
        $html = $debug ? ['<!-- Livewire Styles -->'] : [];

        // CSS assets.
        $html[] = $debug ? $styles : $this->minify($styles);

        return implode("\n", $html);
    }

    public function scripts($options = [])
    {
        $debug = config('app.debug');

        $scripts = $this->javaScriptAssets($options);

        // HTML Label.
        $html = $debug ? ['<!-- Livewire Scripts -->'] : [];

        // JavaScript assets.
        $html[] = $debug ? $scripts : $this->minify($scripts);

        return implode("\n", $html);
    }

    protected function cssAssets($options = [])
    {
        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        return <<<HTML
<style {$nonce}>
    [wire\:loading], [wire\:loading\.delay], [wire\:loading\.inline-block], [wire\:loading\.inline], [wire\:loading\.block], [wire\:loading\.flex], [wire\:loading\.table], [wire\:loading\.grid], [wire\:loading\.inline-flex] {
        display: none;
    }

    [wire\:loading\.delay\.shortest], [wire\:loading\.delay\.shorter], [wire\:loading\.delay\.short], [wire\:loading\.delay\.long], [wire\:loading\.delay\.longer], [wire\:loading\.delay\.longest] {
        display:none;
    }

    [wire\:offline] {
        display: none;
    }

    [wire\:dirty]:not(textarea):not(input):not(select) {
        display: none;
    }

    input:-webkit-autofill, select:-webkit-autofill, textarea:-webkit-autofill {
        animation-duration: 50000s;
        animation-name: livewireautofill;
    }

    @keyframes livewireautofill { from {} }
</style>
HTML;
    }

    protected function javaScriptAssets($options)
    {
        $jsonEncodedOptions = $options ? json_encode($options) : '';

        $assetsUrl = config('livewire.asset_url') ?: rtrim($options['asset_url'] ?? '', '/');

        $appUrl = config('livewire.app_url')
            ?: rtrim($options['app_url'] ?? '', '/')
            ?: $assetsUrl;

        $jsLivewireToken = app()->has('session.store') ? "'" . csrf_token() . "'" : 'null';

        $manifest = json_decode(file_get_contents(__DIR__.'/../dist/manifest.json'), true);
        $versionedFileName = $manifest['/livewire.js'];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$assetsUrl}/livewire{$versionedFileName}";
        $assetWarning = null;

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        // Use static assets if they have been published
        if (file_exists(public_path('vendor/livewire/manifest.json'))) {
            $publishedManifest = json_decode(file_get_contents(public_path('vendor/livewire/manifest.json')), true);
            $versionedFileName = $publishedManifest['/livewire.js'];

            $fullAssetPath = ($this->isRunningServerless() ? config('app.asset_url') : $assetsUrl).'/vendor/livewire'.$versionedFileName;

            if ($manifest !== $publishedManifest) {
                $assetWarning = <<<'HTML'
<script {$nonce}>
    console.warn("Livewire: The published Livewire assets are out of date\n See: https://laravel-livewire.com/docs/installation/")
</script>
HTML;
            }
        }

	    $devTools = null;
	    $windowLivewireCheck = null;
	    $windowAlpineCheck = null;
        if (config('app.debug')) {
	        $devTools = 'window.livewire.devTools(true);';

	        $windowLivewireCheck = <<<'HTML'
if (window.livewire) {
	    console.warn('Livewire: It looks like Livewire\'s @livewireScripts JavaScript assets have already been loaded. Make sure you aren\'t loading them twice.')
	}
HTML;

	        $windowAlpineCheck = <<<'HTML'
/* Make sure Livewire loads first. */
	if (window.Alpine) {
	    /* Defer showing the warning so it doesn't get buried under downstream errors. */
	    document.addEventListener("DOMContentLoaded", function () {
	        setTimeout(function() {
	            console.warn("Livewire: It looks like AlpineJS has already been loaded. Make sure Livewire\'s scripts are loaded before Alpine.\\n\\n Reference docs for more info: http://laravel-livewire.com/docs/alpine-js")
	        })
	    });
	}

	/* Make Alpine wait until Livewire is finished rendering to do its thing. */
HTML;

        }

        // Adding semicolons for this JavaScript is important,
        // because it will be minified in production.
        return <<<HTML
{$assetWarning}
<script src="{$fullAssetPath}" data-turbo-eval="false" data-turbolinks-eval="false" {$nonce}></script>
<script data-turbo-eval="false" data-turbolinks-eval="false" {$nonce}>
    {$windowLivewireCheck}

    window.livewire = new Livewire({$jsonEncodedOptions});
    {$devTools}
    window.Livewire = window.livewire;
    window.livewire_app_url = '{$appUrl}';
    window.livewire_token = {$jsLivewireToken};

	{$windowAlpineCheck}
    window.deferLoadingAlpine = function (callback) {
        window.addEventListener('livewire:load', function () {
            callback();
        });
    };

    let started = false;

    window.addEventListener('alpine:initializing', function () {
        if (! started) {
            window.livewire.start();

            started = true;
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        if (! started) {
            window.livewire.start();

            started = true;
        }
    });
</script>
HTML;
    }

    protected function minify($subject)
    {
        return preg_replace('~(\v|\t|\s{2,})~m', '', $subject);
    }

    public function isLivewireRequest()
    {
        return $this->isProbablyLivewireRequest();
    }

    public function isDefinitelyLivewireRequest()
    {
        $route = request()->route();

        if (! $route) return false;

        return $route->named('livewire.message');
    }

    public function isProbablyLivewireRequest()
    {
        if (static::$isLivewireRequestTestingOverride) return true;

        return request()->hasHeader('X-Livewire');
    }

    public function originalUrl()
    {
        if ($this->isDefinitelyLivewireRequest()) {
            return url()->to($this->originalPath());
        }

        return url()->current();
    }

    public function originalPath()
    {
        if ($this->isDefinitelyLivewireRequest()) {
            // @depricted: "url" usage was removed in v2.3.17
            // This can be removed after a period of time
            // as users will have refreshed all pages
            // that still used "url".
            if (isset(request('fingerprint')['url'])) {
                return str(request('fingerprint')['url'])->after(request()->root());
            }

            return request('fingerprint')['path'];
        }

        return request()->path();
    }

    public function originalMethod()
    {
        if ($this->isDefinitelyLivewireRequest()) {
            // @depricted: "url" usage was removed in v2.3.17
            // This can be removed after a period of time
            // as users will have refreshed all pages
            // that still used "url".
            if (isset(request('fingerprint')['url'])) {
                return 'GET';
            }

            return request('fingerprint')['method'];
        }

        return request()->method();
    }

    public function getRootElementTagName($dom)
    {
        preg_match('/<([a-zA-Z0-9\-]*)/', $dom, $matches, PREG_OFFSET_CAPTURE);

        return $matches[1][0];
    }

    public function dispatch($event, ...$params)
    {
        foreach ($this->listeners[$event] ?? [] as $listener) {
            $listener(...$params);
        }
    }

    public function listen($event, $callback)
    {
        $this->listeners[$event][] = $callback;
    }

    public function isOnVapor()
    {
        return $this->isRunningServerless();
    }

    public function isRunningServerless()
    {
        return in_array($_ENV['SERVER_SOFTWARE'] ?? null, [
            'vapor',
            'bref',
        ]);
    }

    public function withQueryParams($queryParams)
    {
        $this->queryParamsForTesting = $queryParams;

        return $this;
    }

    public function setBackButtonCache()
    {
        /**
         * Reverse this boolean so that the middleware is only applied when it is disabled.
         */
        $this->shouldDisableBackButtonCache = ! config('livewire.back_button_cache', false);
    }

    public function disableBackButtonCache()
    {
        $this->shouldDisableBackButtonCache = true;
    }

    public function enableBackButtonCache()
    {
        $this->shouldDisableBackButtonCache = false;
    }

    public function shouldDisableBackButtonCache()
    {
        return $this->shouldDisableBackButtonCache;
    }

    public function flushState()
    {
        static::$currentCompilingChildCounter = null;
        static::$currentCompilingViewPath = null;

        $this->shouldDisableBackButtonCache = false;

        $this->dispatch('flush-state');
    }
}
