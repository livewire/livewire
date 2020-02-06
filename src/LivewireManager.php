<?php

namespace Livewire;

use Illuminate\Support\Str;
use Illuminate\Support\Fluent;
use Livewire\Testing\TestableLivewire;
use Illuminate\Contracts\Auth\Authenticatable;
use Livewire\Exceptions\ComponentNotFoundException;
use Livewire\HydrationMiddleware\AddAttributesToRootTagOfHtml;

class LivewireManager
{
    use DependencyResolverTrait;

    protected $container;
    protected $prefix = 'wire';
    protected $componentAliases = [];
    protected $customComponentResolver;
    protected $hydrationMiddleware = [];
    protected $initialHydrationMiddleware = [];
    protected $initialDehydrationMiddleware = [];
    protected $listeners = [];

    public static $isLivewireRequestTestingOverride;

    public function __construct()
    {
        // This property only exists to make the "DependencyResolverTrait" work.
        $this->container = app();
    }

    public function prefix($prefix = null)
    {
        // Yes, this is both a getter and a setter. Fight me.
        return $this->prefix = $prefix ?: $this->prefix;
    }

    public function component($alias, $viewClass)
    {
        $this->componentAliases[$alias] = $viewClass;
    }

    public function componentResolver($callback)
    {
        $this->customComponentResolver = $callback;
    }

    public function getComponentClass($alias)
    {
        $finder = app()->make(LivewireComponentsFinder::class);

        $class = false;

        if ($this->customComponentResolver) {
            // A developer can hijack the way Livewire finds components using Livewire::componentResolver();
            $class = call_user_func($this->customComponentResolver, $alias);
        }

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

    public function activate($component, $id)
    {
        $componentClass = $this->getComponentClass($component);

        throw_unless(class_exists($componentClass), new ComponentNotFoundException(
            "Component [{$component}] class not found: [{$componentClass}]"
        ));

        return new $componentClass($id);
    }

    public function mount($name, ...$options)
    {
        $id = Str::random(20);

        // Allow instantiating Livewire components directly from classes.
        if (class_exists($name)) {
            $instance = new $name($id);
            // Set the name to the computed name, so that the full namespace
            // isn't leaked to the front-end.
            $name = $instance->getName();
        } else {
            $instance = $this->activate($name, $id);
        }

        $this->initialHydrate($instance, []);

        $parameters = $this->resolveClassMethodDependencies(
            $options, $instance, 'mount'
        );

        $instance->mount(...array_values($parameters));

        $dom = $instance->output();

        $response = new Fluent([
            'id' => $id,
            'name' => $name,
            'dom' => $dom,
        ]);

        $this->initialDehydrate($instance, $response);

        $response->dom = (new AddAttributesToRootTagOfHtml)($response->dom, [
            'initial-data' => array_diff_key($response->toArray(), array_flip(['dom'])),
        ]);

        $this->dispatch('mounted', $response);

        return $response;
    }

    public function dummyMount($id, $tagName)
    {
        return "<{$tagName} wire:id=\"{$id}\"></{$tagName}>";
    }

    public function test($name, ...$params)
    {
        return new TestableLivewire($name, $this->prefix, $params);
    }

    public function actingAs(Authenticatable $user, $driver = null)
    {
        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        auth()->guard($driver)->setUser($user);

        auth()->shouldUse($driver);

        return $this;
    }

    // @todo remove in 1.0
    public function assets($options = [])
    {
        $debug = config('app.debug');

        $styles = $this->cssAssets();

        $scripts = $this->javaScriptAssets($options, $defer = true);

        // HTML Label.
        $html = $debug ? ['<!-- Livewire Scripts -->'] : [];

        // JavaScript assets.
        $html[] = $debug ? $scripts : $this->minify($scripts);

        // HTML Label.
        $html[] = $debug ? '<!-- Livewire Styles -->' : '';

        // CSS assets.
        $html[] = $debug ? $styles : $this->minify($styles);

        return implode("\n", $html);
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
    [wire\:loading] {
        display: none;
    }

    [wire\:offline] {
        display: none;
    }

    [wire\:dirty]:not(textarea):not(input):not(select) {
        display: none;
    }
</style>
HTML;
    }

    protected function javaScriptAssets($options, $defer = false)
    {
        $jsonEncodedOptions = $options ? json_encode($options) : '';

        $appUrl = config('livewire.asset_url', rtrim($options['asset_url'] ?? '', '/'));

        $csrf = csrf_token();

        $manifest = json_decode(file_get_contents(__DIR__.'/../dist/manifest.json'), true);
        $versionedFileName = $manifest['/livewire.js'];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$appUrl}/livewire{$versionedFileName}";
        $assetWarning = null;

        // Use static assets if they have been published
        if (file_exists(public_path('vendor/livewire'))) {
            $publishedManifest = json_decode(file_get_contents(public_path('vendor/livewire/manifest.json')), true);
            $versionedFileName = $publishedManifest['/livewire.js'];

            $isHostedOnVapor = ($_ENV['SERVER_SOFTWARE'] ?? null) === 'vapor';

            $fullAssetPath = ($isHostedOnVapor ? config('app.asset_url') : $appUrl).'/vendor/livewire'.$versionedFileName;

            if ($manifest !== $publishedManifest) {
                $assetWarning = <<<'HTML'
<script>
    console.warn("Livewire: The published Livewire assets are out of date\n See: https://laravel-livewire.com/docs/installation/")
</script>
HTML;
            }
        }

        // @todo: remove in 1.0
        if ($defer) {
            return <<<HTML
<script>
    document.addEventListener('livewire:available', () => {
        window.livewire = new Livewire({$jsonEncodedOptions});
        window.livewire.start();
        window.livewire_app_url = '{$appUrl}';
        window.livewire_token = '{$csrf}';
    })
</script>
{$assetWarning}
<script src="{$fullAssetPath}" defer></script>
HTML;

        }

        // Adding semicolons for this JavaScript is important,
        // because it will be minified in production.
        return <<<HTML
{$assetWarning}
<script src="{$fullAssetPath}"></script>
<script>
    window.livewire = new Livewire({$jsonEncodedOptions});
    window.livewire_app_url = '{$appUrl}';
    window.livewire_token = '{$csrf}';

    document.addEventListener('DOMContentLoaded', function () {
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
        if (static::$isLivewireRequestTestingOverride) {
            return true;
        }

        return request()->hasHeader('X-Livewire');
    }

    public function registerHydrationMiddleware(array $classes)
    {
        $this->hydrationMiddleware += $classes;
    }

    public function registerInitialHydrationMiddleware(array $callables)
    {
        $this->initialHydrationMiddleware += $callables;
    }

    public function registerInitialDehydrationMiddleware(array $callables)
    {
        $this->initialDehydrationMiddleware += $callables;
    }

    public function hydrate($instance, $request)
    {
        foreach ($this->hydrationMiddleware as $class) {
            $class::hydrate($instance, $request);
        }
    }

    public function initialHydrate($instance, $request)
    {
        foreach ($this->initialHydrationMiddleware as $callable) {
            $callable($instance, $request);
        }
    }

    public function initialDehydrate($instance, $response)
    {
        foreach (array_reverse($this->initialDehydrationMiddleware) as $callable) {
            $callable($instance, $response);
        }
    }

    public function dehydrate($instance, $response)
    {
        // The array is being reversed here, so the middleware dehydrate phase order of execution is
        // the inverse of hydrate. This makes the middlewares behave like layers in a shell.
        foreach (array_reverse($this->hydrationMiddleware) as $class) {
            $class::dehydrate($instance, $response);
        }
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
        $this->listeners[$event] ?? $this->listeners[$event] = [];

        $this->listeners[$event][] = $callback;
    }

    public function isOnVapor()
    {
        return ($_ENV['SERVER_SOFTWARE'] ?? null) === 'vapor';
    }
}
