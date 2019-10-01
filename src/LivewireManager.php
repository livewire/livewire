<?php

namespace Livewire;

use Exception;
use Illuminate\Support\Str;
use Livewire\Testing\TestableLivewire;
use Livewire\Connection\ComponentHydrator;
use Livewire\Exceptions\ComponentNotFoundException;
use Illuminate\Routing\RouteDependencyResolverTrait;

class LivewireManager
{
    use RouteDependencyResolverTrait;

    protected $prefix = 'wire';
    protected $componentAliases = [];
    protected $customComponentResolver;
    protected $container;
    public static $isLivewireRequestTestingOverride;

    public function __construct()
    {
        // This property only exists to make the "RouteDependancyResolverTrait" work.
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
            $class = call_user_func($this->customComponentResolver, $alias);
        }

        $class = $class ?: (
            $this->componentAliases[$alias] ?? $finder->find($alias)
        );

        throw_unless($class, new ComponentNotFoundException(
            "Unable to find component: [{$alias}]"
        ));

        return $class;
    }

    public function activate($component, $id)
    {
        $componentClass = $this->getComponentClass($component);

        throw_unless(class_exists($componentClass), new Exception(
            "Component [{$component}] class not found: [{$componentClass}]"
        ));

        return new $componentClass($id);
    }

    public function mount($name, ...$options)
    {
        $id = Str::random(20);

        $instance = $this->activate($name, $id);

        $parameters = $this->resolveClassMethodDependencies(
            $options,
            $instance,
            'mount'
        );

        $instance->mount(...array_values($parameters));

        $dom = $instance->output();
        $properties = ComponentHydrator::dehydrate($instance);
        $events = $instance->getEventsBeingListenedFor();
        $children = $instance->getRenderedChildren();
        $checksum = (new ComponentChecksumManager)->generate($name, $id, $properties);

        return new InitialResponsePayload([
            'instance' => $instance,
            'id' => $id,
            'dom' => $dom,
            'data' => $properties,
            'name' => $name,
            'checksum' => $checksum,
            'children' => $children,
            'events' => $events,
        ]);
    }

    public function dummyMount($id, $tagName)
    {
        return "<{$tagName} wire:id=\"{$id}\"></{$tagName}>";
    }

    public function test($name, ...$params)
    {
        return new TestableLivewire($name, $this->prefix, $params);
    }

    public function assets($options = [])
    {
        $debug = config('app.debug');

        $jsFileName = $debug
            ? '/livewire.js'
            : '/livewire.min.js';

        $styles = $this->cssAssets();
        $scripts = $this->javaScriptAssets($jsFileName, $options);

        // HTML Label.
        $html = $debug ? ['<!-- Livewire assets -->'] : [];

        // CSS assets.
        $html[] = $debug ? $styles : $this->minify($styles);

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

    protected function javaScriptAssets($jsFileName, $options)
    {
        $jsonEncodedOptions = $options ? json_encode($options) : '';

        $appUrl = config('livewire.asset_url', rtrim($options['asset_url'] ?? '', '/'));

        $csrf = csrf_token();

        $manifest = json_decode(file_get_contents(__DIR__.'/../dist/mix-manifest.json'), true);
        $versionedFileName = $manifest[$jsFileName];

        // Default to dynamic `livewire.js` (served by a Laravel route).
        $fullAssetPath = "{$appUrl}/livewire{$versionedFileName}";
        $assetWarning = null;

        // Use static assets if they have been published
        if (file_exists(public_path('vendor/livewire'))) {
            $publishedManifest = json_decode(file_get_contents(public_path('vendor/livewire/mix-manifest.json')), true);
            $versionedFileName = $publishedManifest[$jsFileName];
            $fullAssetPath = "{$appUrl}/vendor/livewire{$versionedFileName}";

            if ($manifest !== $publishedManifest) {
                $assetWarning = <<<'HTML'
<script>
    console.warn("Livewire: The published Livewire assets are out of date\n See: https://laravel-livewire.com/docs/installation/")
</script>
HTML;
            }
        }

        // Adding semicolons for this JavaScript is important,
        // because it will be minified in production.
        return <<<HTML
<script>
    document.addEventListener('livewire:available', function () {
        window.livewire = new Livewire({$jsonEncodedOptions});
        window.livewire.start();
        window.livewire_app_url = '{$appUrl}';
        window.livewire_token = '{$csrf}';
    });
</script>
{$assetWarning}
<script src="{$fullAssetPath}" defer></script>
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
}
