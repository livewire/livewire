<?php

namespace Livewire;

use Exception;
use Livewire\Testing\TestableLivewire;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Exceptions\ComponentNotFoundException;

class LivewireManager
{
    protected $listeners = [];
    protected $componentAliases = [];
    protected $queryParamsForTesting = [];

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

    public static $isLivewireRequestTestingOverride = false;

    public function component($alias, $viewClass = null)
    {
        if (is_null($viewClass)) {
            $viewClass = $alias;
            $alias = $viewClass::getName();
        }

        $this->componentAliases[$alias] = $viewClass;
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

        $styles = $this->cssAssets();

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

    protected function cssAssets()
    {
        return <<<HTML
<style>
    [wire\:loading], [wire\:loading\.delay], [wire\:loading\.inline-block], [wire\:loading\.inline], [wire\:loading\.block], [wire\:loading\.flex], [wire\:loading\.table], [wire\:loading\.grid] {
        display: none;
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

        $appUrl = config('livewire.asset_url') ?: rtrim($options['asset_url'] ?? '', '/');

        $jsLivewireToken = app()->has('session.store') ? "'" . csrf_token() . "'" : 'null';

        $manifest = json_decode(file_get_contents(__DIR__.'/../dist/manifest.json'), true);
        $versionedFileName = $manifest['/livewire.js'];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$appUrl}/livewire{$versionedFileName}";
        $assetWarning = null;

        $nonce = isset($options['nonce']) ? "nonce=\"{$options['nonce']}\"" : '';

        // Use static assets if they have been published
        if (file_exists(public_path('vendor/livewire/manifest.json'))) {
            $publishedManifest = json_decode(file_get_contents(public_path('vendor/livewire/manifest.json')), true);
            $versionedFileName = $publishedManifest['/livewire.js'];

            $fullAssetPath = ($this->isOnVapor() ? config('app.asset_url') : $appUrl).'/vendor/livewire'.$versionedFileName;

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
<script src="{$fullAssetPath}" data-turbo-eval="false" data-turbolinks-eval="false"></script>
<script data-turbo-eval="false" data-turbolinks-eval="false"{$nonce}>
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

    document.addEventListener("DOMContentLoaded", function () {
        window.livewire.start();
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
        return ($_ENV['SERVER_SOFTWARE'] ?? null) === 'vapor';
    }

    public function withQueryParams($queryParams)
    {
        $this->queryParamsForTesting = $queryParams;

        return $this;
    }
}
